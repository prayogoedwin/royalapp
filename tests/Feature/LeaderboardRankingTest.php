<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\Order;
use App\Models\OrderCrew;
use App\Models\OrderReport;
use App\Models\OrderStatus;
use App\Models\Permission;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaderboardRankingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_leaderboard(): void
    {
        $this->get(route('leaderboard.index'))->assertRedirect('/login');
        $this->get(route('leaderboard.ranking', 'orders-done'))->assertRedirect('/login');
    }

    public function test_user_without_permission_cannot_open_leaderboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('leaderboard.index'))->assertForbidden();
        $this->actingAs($user)->get(route('leaderboard.ranking', 'orders-done'))->assertForbidden();
        $this->actingAs($user)->get(route('leaderboard.ranking', 'km-done'))->assertForbidden();
    }

    public function test_order_done_preview_is_top_five_and_full_list_shows_all_drivers(): void
    {
        $viewer = $this->userWithPermissions(['view-leaderboard']);
        $done = $this->makeStatus('Done');
        $driverNames = [];

        foreach ([6, 5, 4, 3, 2, 1] as $count) {
            $name = 'Driver '.$count;
            $driverNames[$count] = $name;
            $employee = $this->makeEmployee($name, 'Driver');
            for ($i = 0; $i < $count; $i++) {
                $this->makeDoneOrder($done, $employee, 'driver');
            }
        }

        $nurse = $this->makeEmployee('Nurse Top', 'Nurse');
        for ($i = 0; $i < 20; $i++) {
            $this->makeDoneOrder($done, $nurse, 'nurse');
        }

        $index = $this->actingAs($viewer)->get(route('leaderboard.index', ['all_time' => 1]));
        $index->assertOk()
            ->assertSee('Order Done terbanyak')
            ->assertSee('Lihat semua driver')
            ->assertSee($driverNames[6])
            ->assertSee($driverNames[2])
            ->assertDontSee($driverNames[1])
            ->assertDontSee('Nurse Top')
            ->assertSee(route('leaderboard.ranking', ['metric' => 'orders-done', 'all_time' => 1]), false);

        $ranking = $this->actingAs($viewer)
            ->get(route('leaderboard.ranking', ['metric' => 'orders-done', 'all_time' => 1]));

        $ranking->assertOk()
            ->assertSee('Order Done terbanyak')
            ->assertSee($driverNames[6])
            ->assertSee($driverNames[1])
            ->assertDontSee('Nurse Top');
    }

    public function test_km_ranking_lists_all_drivers(): void
    {
        $viewer = $this->userWithPermissions(['view-leaderboard']);
        $done = $this->makeStatus('Done');

        $far = $this->makeEmployee('Driver Jauh', 'Driver');
        $near = $this->makeEmployee('Driver Dekat', 'Driver');
        $nurse = $this->makeEmployee('Nurse KM', 'Nurse');

        $this->makeDoneOrder($done, $far, 'driver', 100, 250);
        $this->makeDoneOrder($done, $near, 'driver', 10, 40);
        $this->makeDoneOrder($done, $nurse, 'nurse', 0, 900);

        $this->actingAs($viewer)
            ->get(route('leaderboard.ranking', ['metric' => 'km-done', 'all_time' => 1]))
            ->assertOk()
            ->assertSee('Total KM (order Done)')
            ->assertSee('Driver Jauh')
            ->assertSee('Driver Dekat')
            ->assertDontSee('Nurse KM');
    }

    /**
     * @param  list<string>  $permissions
     */
    private function userWithPermissions(array $permissions): User
    {
        $role = Role::query()->firstOrCreate(['name' => 'tester-'.implode('-', $permissions)]);
        $permissionIds = [];

        foreach ($permissions as $name) {
            $permissionIds[] = Permission::query()->firstOrCreate(['name' => $name])->id;
        }

        $role->permissions()->syncWithoutDetaching($permissionIds);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function makeStatus(string $name): OrderStatus
    {
        return OrderStatus::query()->firstOrCreate(['name' => $name], ['color' => 'green']);
    }

    private function makeEmployee(string $fullName, string $positionName): Employee
    {
        $user = User::factory()->create(['name' => $fullName, 'email' => strtolower(str_replace(' ', '.', $fullName)).'@royal.test']);
        $position = Position::query()->firstOrCreate(['nama' => $positionName]);
        $division = Division::query()->firstOrCreate(['nama' => 'Royal Ambulance']);
        $type = EmployeeType::query()->firstOrCreate(['nama' => 'Permanent']);

        return Employee::create([
            'user_id' => $user->id,
            'position_id' => $position->id,
            'division_id' => $division->id,
            'employee_type_id' => $type->id,
            'nik' => 'NIK-'.uniqid(),
            'full_name' => $fullName,
            'status' => 'active',
            'join_date' => now()->toDateString(),
        ]);
    }

    private function makeDoneOrder(OrderStatus $status, Employee $employee, string $role, ?int $kmAwal = null, ?int $kmAkhir = null): Order
    {
        $division = Division::query()->firstOrCreate(['nama' => 'Royal Ambulance']);

        $order = Order::create([
            'order_number' => 'RA.'.uniqid(),
            'division_id' => $division->id,
            'order_status_id' => $status->id,
            'customer_name' => 'Customer Test',
            'customer_phone' => '08123456789',
            'pickup_address' => 'Jakarta',
            'destination_address' => 'Bandung',
            'pickup_datetime' => now(),
            'price' => 0,
        ]);

        OrderCrew::create([
            'order_id' => $order->id,
            'employee_id' => $employee->id,
            'role' => $role,
        ]);

        if ($kmAwal !== null && $kmAkhir !== null) {
            OrderReport::create([
                'order_id' => $order->id,
                'km_awal' => $kmAwal,
                'km_akhir' => $kmAkhir,
                'status' => 'submitted',
            ]);
        }

        return $order;
    }
}
