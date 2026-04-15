<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OrderEtollTransaction;
use App\Models\OrderExpense;
use App\Models\OrderPhoto;
use App\Models\OrderReport;
use App\Models\OrderVehicleIssue;
use App\Support\UploadPath;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Storage;

class OrderApiController extends Controller
{
    private function etollPayload(OrderEtollTransaction $trx): array
    {
        return [
            'id' => $trx->id,
            'order_id' => $trx->order_id,
            'amount' => $trx->usage_amount,
            'receipt_photo' => $trx->receipt_photo,
            'balance_before' => $trx->balance_before,
            'balance_after' => $trx->balance_after,
            'topup_amount' => $trx->topup_amount,
            'usage_amount' => $trx->usage_amount,
            'created_by' => $trx->created_by,
            'created_at' => $trx->created_at,
            'updated_at' => $trx->updated_at,
        ];
    }

    private function okResponse(mixed $data = [], string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    private function errorResponse(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => [],
        ], $status);
    }

    private function userEmployeeId(Request $request): ?int
    {
        return $request->user()->employee?->id;
    }

    private function userOrderQuery(Request $request)
    {
        $employeeId = $this->userEmployeeId($request);
        return Order::query()->whereHas('orderCrews', fn ($q) => $q->where('employee_id', $employeeId));
    }

    private function findUserOrder(Request $request, int $orderId): ?Order
    {
        return $this->userOrderQuery($request)->whereKey($orderId)->first();
    }

    /**
     * @OA\Get(path="/api/orders", tags={"Orders"}, summary="Get orders by logged user (as crew)", security={{"sanctum":{}}}, @OA\Response(response=200, description="Success"))
     */
    public function myOrders(Request $request): JsonResponse
    {
        $employeeId = $this->userEmployeeId($request);
        if (!$employeeId) {
            return $this->errorResponse('Employee not linked to user.', 404);
        }

        $perPage = min((int) $request->query('per_page', 15), 100);
        $query = $this->userOrderQuery($request)->with(['orderStatus', 'division']);

        if ($request->filled('order_status_id')) {
            $query->where('order_status_id', (int) $request->query('order_status_id'));
        }

        if ($request->filled('status')) {
            $statusName = trim((string) $request->query('status'));
            $query->whereHas('orderStatus', function ($q) use ($statusName) {
                $q->whereRaw('LOWER(name) = ?', [mb_strtolower($statusName)]);
            });
        }

        $orders = $query->orderByDesc('id')->paginate($perPage);

        return $this->okResponse($orders);
    }

    public function statuses(): JsonResponse
    {
        $statuses = OrderStatus::query()
            ->select(['id', 'name', 'color'])
            ->orderBy('name')
            ->get();

        return $this->okResponse($statuses);
    }

    /**
     * @OA\Get(path="/api/orders/{order}", tags={"Orders"}, summary="Order detail", security={{"sanctum":{}}}, @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Success"))
     */
    public function show(Request $request, int $orderId): JsonResponse
    {
        $order = $this->findUserOrder($request, $orderId);
        if (!$order) {
            return $this->errorResponse('Order not found.', 404);
        }

        $order->load([
            'division',
            'orderStatus',
            'orderCrews.employee.position',
            'orderPhotos',
            'orderExpenses',
            'orderEtollTransactions',
            'orderVehicleIssues',
            'orderReport',
        ]);

        return $this->okResponse($order);
    }

    /** @OA\Get(path="/api/orders/{order}/photos", tags={"Order Photos"}, summary="List order photos", security={{"sanctum":{}}}, @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Success")) */
    public function photos(Request $request, int $orderId): JsonResponse
    {
        $order = $this->findUserOrder($request, $orderId);
        if (!$order) {
            return $this->errorResponse('Order not found.', 404);
        }

        return $this->okResponse($order->orderPhotos()->latest()->get());
    }

    /** @OA\Post(path="/api/orders/{order}/photos", tags={"Order Photos"}, summary="Create order photo", security={{"sanctum":{}}}, @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=201, description="Created")) */
    public function storePhoto(Request $request, int $orderId): JsonResponse
    {
        $order = $this->findUserOrder($request, $orderId);
        if (!$order) {
            return $this->errorResponse('Order not found.', 404);
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'photo' => ['required', 'image', 'max:4096'],
        ]);

        $path = $request->file('photo')->store(UploadPath::dir('order-photos'), 'public');
        $photo = OrderPhoto::create([
            'order_id' => $order->id,
            'title' => $validated['title'] ?? null,
            'description' => $validated['description'] ?? null,
            'path' => $path,
            'created_by' => $request->user()->id,
        ]);

        return $this->okResponse($photo, 'Created', 201);
    }

    /** @OA\Post(path="/api/orders/{order}/photos/{photo}", tags={"Order Photos"}, summary="Update order photo", security={{"sanctum":{}}}, @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")), @OA\Parameter(name="photo", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Updated")) */
    public function updatePhoto(Request $request, int $orderId, int $photoId): JsonResponse
    {
        $order = $this->findUserOrder($request, $orderId);
        if (!$order) {
            return $this->errorResponse('Order not found.', 404);
        }

        $photo = $order->orderPhotos()->find($photoId);
        if (!$photo) {
            return $this->errorResponse('Photo not found.', 404);
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('photo')) {
            if ($photo->path) {
                Storage::disk('public')->delete($photo->path);
            }
            $validated['path'] = $request->file('photo')->store(UploadPath::dir('order-photos'), 'public');
        }

        $validated['updated_by'] = $request->user()->id;
        $photo->update($validated);

        return $this->okResponse($photo->fresh());
    }

    /** @OA\Delete(path="/api/orders/{order}/photos/{photo}", tags={"Order Photos"}, summary="Delete order photo", security={{"sanctum":{}}}, @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")), @OA\Parameter(name="photo", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Deleted")) */
    public function destroyPhoto(Request $request, int $orderId, int $photoId): JsonResponse
    {
        $order = $this->findUserOrder($request, $orderId);
        if (!$order) {
            return $this->errorResponse('Order not found.', 404);
        }
        $photo = $order->orderPhotos()->find($photoId);
        if (!$photo) {
            return $this->errorResponse('Photo not found.', 404);
        }
        if ($photo->path) {
            Storage::disk('public')->delete($photo->path);
        }
        $photo->delete();

        return $this->okResponse([], 'Photo deleted.');
    }

    /** @OA\Get(path="/api/orders/{order}/expenses", tags={"Order Expenses"}, summary="List order expenses", security={{"sanctum":{}}}, @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Success")) */
    public function expenses(Request $request, int $orderId): JsonResponse
    {
        $order = $this->findUserOrder($request, $orderId);
        if (!$order) return $this->errorResponse('Order not found.', 404);
        return $this->okResponse($order->orderExpenses()->latest()->get());
    }

    /** @OA\Post(path="/api/orders/{order}/expenses", tags={"Order Expenses"}, summary="Create order expense", security={{"sanctum":{}}}, @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=201, description="Created")) */
    public function storeExpense(Request $request, int $orderId): JsonResponse
    {
        $order = $this->findUserOrder($request, $orderId);
        if (!$order) return $this->errorResponse('Order not found.', 404);
        $validated = $request->validate([
            'expense_category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'receipt_photo' => ['nullable', 'image', 'max:4096'],
        ]);
        if ($request->hasFile('receipt_photo')) {
            $validated['receipt_photo'] = $request->file('receipt_photo')->store(UploadPath::dir('order-expenses'), 'public');
        }
        $validated['order_id'] = $order->id;
        $validated['created_by'] = $request->user()->id;
        $expense = OrderExpense::create($validated);
        return $this->okResponse($expense, 'Created', 201);
    }

    /** @OA\Put(path="/api/orders/{order}/expenses/{expense}", tags={"Order Expenses"}, summary="Update order expense", security={{"sanctum":{}}}, @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")), @OA\Parameter(name="expense", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Updated")) */
    public function updateExpense(Request $request, int $orderId, int $expenseId): JsonResponse
    {
        $order = $this->findUserOrder($request, $orderId);
        if (!$order) return $this->errorResponse('Order not found.', 404);
        $expense = $order->orderExpenses()->find($expenseId);
        if (!$expense) return $this->errorResponse('Expense not found.', 404);
        $validated = $request->validate([
            'expense_category' => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'receipt_photo' => ['nullable', 'image', 'max:4096'],
        ]);
        if ($request->hasFile('receipt_photo')) {
            if ($expense->receipt_photo) Storage::disk('public')->delete($expense->receipt_photo);
            $validated['receipt_photo'] = $request->file('receipt_photo')->store(UploadPath::dir('order-expenses'), 'public');
        }
        $expense->update($validated);
        return $this->okResponse($expense->fresh());
    }

    /** @OA\Delete(path="/api/orders/{order}/expenses/{expense}", tags={"Order Expenses"}, summary="Delete order expense", security={{"sanctum":{}}}, @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")), @OA\Parameter(name="expense", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Deleted")) */
    public function destroyExpense(Request $request, int $orderId, int $expenseId): JsonResponse
    {
        $order = $this->findUserOrder($request, $orderId);
        if (!$order) return $this->errorResponse('Order not found.', 404);
        $expense = $order->orderExpenses()->find($expenseId);
        if (!$expense) return $this->errorResponse('Expense not found.', 404);
        if ($expense->receipt_photo) Storage::disk('public')->delete($expense->receipt_photo);
        $expense->delete();
        return $this->okResponse([], 'Expense deleted.');
    }

    /** @OA\Put(path="/api/orders/{order}/report", tags={"Order Report"}, summary="Update order report", security={{"sanctum":{}}}, @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Updated")) */
    public function updateReport(Request $request, int $orderId): JsonResponse
    {
        $order = $this->findUserOrder($request, $orderId);
        if (!$order) return $this->errorResponse('Order not found.', 404);
        $validated = $request->validate([
            'km_awal' => ['nullable', 'numeric', 'min:0'],
            'km_akhir' => ['nullable', 'numeric', 'min:0'],
            'saldo_etoll_before' => ['nullable', 'numeric', 'min:0'],
            'saldo_etoll_after' => ['nullable', 'numeric', 'min:0'],
            'deliver_datetime' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'order_status_id' => ['nullable', 'exists:order_statuses,id'],
        ]);
        $report = OrderReport::updateOrCreate(
            ['order_id' => $order->id],
            [
                'km_awal' => $validated['km_awal'] ?? null,
                'km_akhir' => $validated['km_akhir'] ?? null,
                'saldo_etoll_before' => $validated['saldo_etoll_before'] ?? null,
                'saldo_etoll_after' => $validated['saldo_etoll_after'] ?? null,
                'deliver_datetime' => $validated['deliver_datetime'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'updated_by' => $request->user()->id,
                'created_by' => $order->orderReport?->created_by ?? $request->user()->id,
            ]
        );
        if (!empty($validated['order_status_id'])) {
            $order->update(['order_status_id' => $validated['order_status_id']]);
        }
        return $this->okResponse($report);
    }

    /** @OA\Get(path="/api/orders/{order}/etolls", tags={"Order Etoll"}, summary="List etoll transactions", security={{"sanctum":{}}}, @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Success")) */
    public function etolls(Request $request, int $orderId): JsonResponse
    {
        $order = $this->findUserOrder($request, $orderId);
        if (!$order) return $this->errorResponse('Order not found.', 404);
        $rows = $order->orderEtollTransactions()
            ->latest()
            ->get()
            ->map(fn (OrderEtollTransaction $trx) => $this->etollPayload($trx))
            ->values();
        return $this->okResponse($rows);
    }

    /** @OA\Post(path="/api/orders/{order}/etolls", tags={"Order Etoll"}, summary="Create etoll transaction", security={{"sanctum":{}}}, @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=201, description="Created")) */
    public function storeEtoll(Request $request, int $orderId): JsonResponse
    {
        $order = $this->findUserOrder($request, $orderId);
        if (!$order) return $this->errorResponse('Order not found.', 404);
        $validated = $request->validate([
            'amount' => ['required_without:usage_amount', 'nullable', 'numeric', 'min:0'],
            'usage_amount' => ['required_without:amount', 'nullable', 'numeric', 'min:0'],
            'receipt_photo' => ['nullable', 'image', 'max:4096'],
        ]);
        $amount = $validated['amount'] ?? $validated['usage_amount'] ?? 0;
        if ($request->hasFile('receipt_photo')) {
            $validated['receipt_photo'] = $request->file('receipt_photo')->store(UploadPath::dir('order-etoll'), 'public');
        }
        $trx = OrderEtollTransaction::create([
            'order_id' => $order->id,
            'topup_amount' => null,
            'usage_amount' => $amount,
            'balance_before' => null,
            'balance_after' => null,
            'receipt_photo' => $validated['receipt_photo'] ?? null,
            'created_by' => $request->user()->id,
        ]);
        return $this->okResponse($this->etollPayload($trx), 'Created', 201);
    }

    /** @OA\Put(path="/api/orders/{order}/etolls/{trx}", tags={"Order Etoll"}, summary="Update etoll transaction", security={{"sanctum":{}}}, @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")), @OA\Parameter(name="trx", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Updated")) */
    public function updateEtoll(Request $request, int $orderId, int $trxId): JsonResponse
    {
        $order = $this->findUserOrder($request, $orderId);
        if (!$order) return $this->errorResponse('Order not found.', 404);
        $trx = $order->orderEtollTransactions()->find($trxId);
        if (!$trx) return $this->errorResponse('E-toll transaction not found.', 404);
        $validated = $request->validate([
            'amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'usage_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'receipt_photo' => ['nullable', 'image', 'max:4096'],
        ]);
        if (array_key_exists('amount', $validated) || array_key_exists('usage_amount', $validated)) {
            $validated['usage_amount'] = $validated['amount'] ?? $validated['usage_amount'];
            $validated['topup_amount'] = null;
            $validated['balance_before'] = null;
            $validated['balance_after'] = null;
        }
        unset($validated['amount']);
        if ($request->hasFile('receipt_photo')) {
            if ($trx->receipt_photo) Storage::disk('public')->delete($trx->receipt_photo);
            $validated['receipt_photo'] = $request->file('receipt_photo')->store(UploadPath::dir('order-etoll'), 'public');
        }
        $trx->update($validated);
        return $this->okResponse($this->etollPayload($trx->fresh()));
    }

    /** @OA\Delete(path="/api/orders/{order}/etolls/{trx}", tags={"Order Etoll"}, summary="Delete etoll transaction", security={{"sanctum":{}}}, @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")), @OA\Parameter(name="trx", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Deleted")) */
    public function destroyEtoll(Request $request, int $orderId, int $trxId): JsonResponse
    {
        $order = $this->findUserOrder($request, $orderId);
        if (!$order) return $this->errorResponse('Order not found.', 404);
        $trx = $order->orderEtollTransactions()->find($trxId);
        if (!$trx) return $this->errorResponse('E-toll transaction not found.', 404);
        if ($trx->receipt_photo) Storage::disk('public')->delete($trx->receipt_photo);
        $trx->delete();
        return $this->okResponse([], 'E-toll transaction deleted.');
    }

    /** @OA\Get(path="/api/orders/{order}/vehicle-issues", tags={"Order Vehicle Issues"}, summary="List vehicle issues", security={{"sanctum":{}}}, @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Success")) */
    public function vehicleIssues(Request $request, int $orderId): JsonResponse
    {
        $order = $this->findUserOrder($request, $orderId);
        if (!$order) return $this->errorResponse('Order not found.', 404);
        return $this->okResponse($order->orderVehicleIssues()->latest()->get());
    }

    /** @OA\Post(path="/api/orders/{order}/vehicle-issues", tags={"Order Vehicle Issues"}, summary="Create vehicle issue", security={{"sanctum":{}}}, @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=201, description="Created")) */
    public function storeVehicleIssue(Request $request, int $orderId): JsonResponse
    {
        $order = $this->findUserOrder($request, $orderId);
        if (!$order) return $this->errorResponse('Order not found.', 404);
        $validated = $request->validate([
            'issue_category' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'is_resolved' => ['nullable', 'boolean'],
            'resolution_notes' => ['nullable', 'string'],
            'issue_photo' => ['nullable', 'image', 'max:4096'],
            'repair_photo' => ['nullable', 'image', 'max:4096'],
        ]);
        if ($request->hasFile('issue_photo')) {
            $validated['issue_photo'] = $request->file('issue_photo')->store(UploadPath::dir('vehicle-issues/issue'), 'public');
        }
        if ($request->hasFile('repair_photo')) {
            $validated['repair_photo'] = $request->file('repair_photo')->store(UploadPath::dir('vehicle-issues/repair'), 'public');
        }
        $validated['order_id'] = $order->id;
        $validated['unit_code'] = $order->unit_code;
        $validated['created_by'] = $request->user()->id;
        if (!empty($validated['is_resolved'])) {
            $validated['resolved_at'] = now();
            $validated['resolved_by'] = $request->user()->id;
        }
        $issue = OrderVehicleIssue::create($validated);
        return $this->okResponse($issue, 'Created', 201);
    }

    /** @OA\Put(path="/api/orders/{order}/vehicle-issues/{issue}", tags={"Order Vehicle Issues"}, summary="Update vehicle issue", security={{"sanctum":{}}}, @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")), @OA\Parameter(name="issue", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Updated")) */
    public function updateVehicleIssue(Request $request, int $orderId, int $issueId): JsonResponse
    {
        $order = $this->findUserOrder($request, $orderId);
        if (!$order) return $this->errorResponse('Order not found.', 404);
        $issue = $order->orderVehicleIssues()->find($issueId);
        if (!$issue) return $this->errorResponse('Vehicle issue not found.', 404);
        $validated = $request->validate([
            'issue_category' => ['sometimes', 'string', 'max:100'],
            'description' => ['sometimes', 'string'],
            'priority' => ['sometimes', 'in:low,medium,high,urgent'],
            'is_resolved' => ['nullable', 'boolean'],
            'resolution_notes' => ['nullable', 'string'],
            'issue_photo' => ['nullable', 'image', 'max:4096'],
            'repair_photo' => ['nullable', 'image', 'max:4096'],
        ]);
        if ($request->hasFile('issue_photo')) {
            if ($issue->issue_photo) Storage::disk('public')->delete($issue->issue_photo);
            $validated['issue_photo'] = $request->file('issue_photo')->store(UploadPath::dir('vehicle-issues/issue'), 'public');
        }
        if ($request->hasFile('repair_photo')) {
            if ($issue->repair_photo) Storage::disk('public')->delete($issue->repair_photo);
            $validated['repair_photo'] = $request->file('repair_photo')->store(UploadPath::dir('vehicle-issues/repair'), 'public');
        }
        if (array_key_exists('is_resolved', $validated)) {
            if ($validated['is_resolved']) {
                $validated['resolved_at'] = now();
                $validated['resolved_by'] = $request->user()->id;
            } else {
                $validated['resolved_at'] = null;
                $validated['resolved_by'] = null;
            }
        }
        $issue->update($validated);
        return $this->okResponse($issue->fresh());
    }

    /** @OA\Delete(path="/api/orders/{order}/vehicle-issues/{issue}", tags={"Order Vehicle Issues"}, summary="Delete vehicle issue", security={{"sanctum":{}}}, @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")), @OA\Parameter(name="issue", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Deleted")) */
    public function destroyVehicleIssue(Request $request, int $orderId, int $issueId): JsonResponse
    {
        $order = $this->findUserOrder($request, $orderId);
        if (!$order) return $this->errorResponse('Order not found.', 404);
        $issue = $order->orderVehicleIssues()->find($issueId);
        if (!$issue) return $this->errorResponse('Vehicle issue not found.', 404);
        if ($issue->issue_photo) Storage::disk('public')->delete($issue->issue_photo);
        if ($issue->repair_photo) Storage::disk('public')->delete($issue->repair_photo);
        $issue->delete();
        return $this->okResponse([], 'Vehicle issue deleted.');
    }
}
