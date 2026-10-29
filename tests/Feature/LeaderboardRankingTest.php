<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\LokasiTerkini;
use App\Models\Order;
use App\Models\OrderCrew;
use App\Models\OrderReport;
use App\Models\OrderStatus;
use App\Models\Permission;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
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

    public function test_order_done_and_km_lists_all_drivers_on_index(): void
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

        $this->actingAs($viewer)
            ->get(route('leaderboard.index', ['all_time' => 1]))
            ->assertOk()
            ->assertSee('Order Done terbanyak')
            ->assertSee('Total KM (order Done)')
            ->assertSee($driverNames[6])
            ->assertSee($driverNames[2])
            ->assertSee($driverNames[1])
            ->assertDontSee('Nurse Top')
            ->assertDontSee('Lihat semua driver');
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
            ->get(route('leaderboard.index', ['all_time' => 1]))
            ->assertOk()
            ->assertSee('Total KM (order Done)')
            ->assertSee('Driver Jauh')
            ->assertSee('Driver Dekat')
            ->assertDontSee('Nurse KM')
            ->assertDontSee('Lihat semua driver');
    }

    public function test_ongoing_drivers_appear_on_index_regardless_of_period(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-28 12:00:00'));

        $viewer = $this->userWithPermissions(['view-leaderboard']);
        $ongoing = $this->makeStatus('Ongoing');
        $done = $this->makeStatus('Done');

        $busy = $this->makeEmployee('Driver Ongoing', 'Driver');
        $finished = $this->makeEmployee('Driver Selesai', 'Driver');
        $nurse = $this->makeEmployee('Nurse Ongoing', 'Nurse');

        $order = $this->makeDoneOrder($ongoing, $busy, 'driver');
        $order->forceFill([
            'created_at' => now()->subDay(),
            'pickup_datetime' => now()->subDay(),
        ])->save();

        $this->makeDoneOrder($done, $finished, 'driver');
        $this->makeDoneOrder($ongoing, $nurse, 'nurse');

        $this->actingAs($viewer)
            ->get(route('leaderboard.index'))
            ->assertOk()
            ->assertSee('Driver ongoing saat ini')
            ->assertSee('Semua driver')
            ->assertSee('Driver Ongoing')
            ->assertSee($order->order_number)
            ->assertSee('Driver Selesai')
            ->assertDontSee('Nurse Ongoing');
    }

    public function test_all_drivers_toggle_changes_list_and_map_points(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-28 15:30:00'));

        $viewer = $this->userWithPermissions(['view-leaderboard']);
        $ongoing = $this->makeStatus('Ongoing');

        $busy = $this->makeEmployee('Driver Ongoing Peta', 'Driver');
        $idle = $this->makeEmployee('Driver Idle Peta', 'Driver');
        $nurse = $this->makeEmployee('Nurse Peta', 'Nurse');

        $this->makeDoneOrder($ongoing, $busy, 'driver');
        $this->makeDoneOrder($ongoing, $nurse, 'nurse');

        LokasiTerkini::create([
            'user_id' => $busy->user_id,
            'employee_id' => $busy->id,
            'latitude' => -6.2100,
            'longitude' => 106.8456,
            'waktu_hp' => now()->setTime(15, 20),
        ]);
        LokasiTerkini::create([
            'user_id' => $idle->user_id,
            'employee_id' => $idle->id,
            'latitude' => -7.2500,
            'longitude' => 112.7500,
            'waktu_hp' => now()->setTime(15, 00),
        ]);
        LokasiTerkini::create([
            'user_id' => $nurse->user_id,
            'employee_id' => $nurse->id,
            'latitude' => -6.9900,
            'longitude' => 110.4200,
            'waktu_hp' => now()->setTime(15, 10),
        ]);

        $this->actingAs($viewer)
            ->get(route('leaderboard.index'))
            ->assertOk()
            ->assertSee('Driver ongoing saat ini')
            ->assertSee('Driver Ongoing Peta')
            ->assertDontSee('Driver Idle Peta')
            ->assertSee('-6.21%2C106.8456', false)
            ->assertDontSee('-7.25%2C112.75', false)
            ->assertDontSee('-6.99%2C110.42', false);

        $allDrivers = $this->actingAs($viewer)
            ->get(route('leaderboard.index', ['drivers' => 'all']));

        $allDrivers
            ->assertOk()
            ->assertSee('Semua driver')
            ->assertSee('name="drivers"', false)
            ->assertSee('value="all"', false)
            ->assertSee('Driver Ongoing Peta')
            ->assertSee('Driver Idle Peta')
            ->assertDontSee('Nurse Peta')
            ->assertSee('-6.21%2C106.8456', false)
            ->assertSee('-7.25%2C112.75', false)
            ->assertDontSee('-6.99%2C110.42', false);

        $mapDrivers = collect($allDrivers->viewData('mapDrivers'))->keyBy('name');
        $this->assertSame(1, (int) $mapDrivers['Driver Ongoing Peta']->ongoing);
        $this->assertSame(0, (int) $mapDrivers['Driver Idle Peta']->ongoing);
        $this->assertSame(0, (int) $mapDrivers['Driver Idle Peta']->pending);
        $this->assertSame(0, (int) $mapDrivers['Driver Idle Peta']->waiting);
        $this->assertSame(0, (int) $mapDrivers['Driver Idle Peta']->done);
        $this->assertSame(0, (int) $mapDrivers['Driver Idle Peta']->cancelled);
    }

    public function test_all_drivers_table_shows_counts_per_status(): void
    {
        $viewer = $this->userWithPermissions(['view-leaderboard']);
        $pending = $this->makeStatus('Pending');
        $waiting = $this->makeStatus('Waiting');
        $ongoing = $this->makeStatus('Ongoing');
        $done = $this->makeStatus('Done');
        $cancelled = $this->makeStatus('Cancelled');

        $driver = $this->makeEmployee('Driver Status Mix', 'Driver');
        $this->makeDoneOrder($pending, $driver, 'driver');
        $this->makeDoneOrder($waiting, $driver, 'driver');
        $this->makeDoneOrder($ongoing, $driver, 'driver');
        $this->makeDoneOrder($done, $driver, 'driver');
        $this->makeDoneOrder($cancelled, $driver, 'driver');

        $response = $this->actingAs($viewer)
            ->get(route('leaderboard.index', ['drivers' => 'all']))
            ->assertOk()
            ->assertSee('Pending')
            ->assertSee('Waiting')
            ->assertSee('Ongoing')
            ->assertSee('Done')
            ->assertSee('Cancelled')
            ->assertSee('Driver Status Mix');

        $row = collect($response->viewData('mapDrivers'))->firstWhere('name', 'Driver Status Mix');
        $this->assertNotNull($row);
        $this->assertSame(1, (int) $row->pending);
        $this->assertSame(1, (int) $row->waiting);
        $this->assertSame(1, (int) $row->ongoing);
        $this->assertSame(1, (int) $row->done);
        $this->assertSame(1, (int) $row->cancelled);
    }

    public function test_leaderboard_map_shows_ongoing_driver_points_with_latest_flag(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-28 15:30:00'));

        $viewer = $this->userWithPermissions(['view-leaderboard']);
        $ongoing = $this->makeStatus('Ongoing');
        $done = $this->makeStatus('Done');

        $busy = $this->makeEmployee('Driver Ongoing', 'Driver');
        $finished = $this->makeEmployee('Driver Selesai', 'Driver');
        $this->makeDoneOrder($ongoing, $busy, 'driver');
        $this->makeDoneOrder($done, $finished, 'driver');

        LokasiTerkini::create([
            'user_id' => $busy->user_id,
            'employee_id' => $busy->id,
            'latitude' => -6.2000,
            'longitude' => 106.8000,
            'waktu_hp' => now()->setTime(14, 10),
        ]);
        $latest = LokasiTerkini::create([
            'user_id' => $busy->user_id,
            'employee_id' => $busy->id,
            'latitude' => -6.2100,
            'longitude' => 106.8456,
            'waktu_hp' => now()->setTime(15, 20),
        ]);
        LokasiTerkini::create([
            'user_id' => $finished->user_id,
            'employee_id' => $finished->id,
            'latitude' => -7.2500,
            'longitude' => 112.7500,
            'waktu_hp' => now()->setTime(15, 00),
        ]);

        $this->actingAs($viewer)
            ->get(route('leaderboard.index'))
            ->assertOk()
            ->assertSee('Peta lokasi hari ini')
            ->assertSee('id="lokasi-map"', false)
            ->assertSee('#dc2626', false)
            ->assertSee('Lihat titik sebelumnya')
            ->assertSee('"has_previous":true', false)
            ->assertSee('14:10')
            ->assertSee('15:20')
            ->assertSee('-6.21%2C106.8456', false)
            ->assertSee('"is_latest":true', false)
            ->assertDontSee('-7.25%2C112.75', false);

        $this->assertNotNull($latest);
    }

    public function test_default_period_is_today_and_form_has_start_end_dates(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-28 12:00:00'));

        $viewer = $this->userWithPermissions(['view-leaderboard']);
        $done = $this->makeStatus('Done');

        $todayDriver = $this->makeEmployee('Driver Hari Ini', 'Driver');
        $yesterdayDriver = $this->makeEmployee('Driver Kemarin', 'Driver');
        $this->makeDoneOrder($done, $todayDriver, 'driver');
        $old = $this->makeDoneOrder($done, $yesterdayDriver, 'driver');
        $old->forceFill([
            'created_at' => now()->subDay(),
            'pickup_datetime' => now()->subDay(),
        ])->save();

        $this->actingAs($viewer)
            ->get(route('leaderboard.index'))
            ->assertOk()
            ->assertSee('Tanggal mulai', false)
            ->assertSee('Tanggal selesai', false)
            ->assertSee('name="start_date"', false)
            ->assertSee('name="end_date"', false)
            ->assertSee('value="2026-10-28"', false)
            ->assertSee('Hari ini (28 Oktober 2026)', false)
            ->assertSee('submitForm()', false)
            ->assertSee('Driver Hari Ini')
            ->assertDontSee('Driver Kemarin');
    }

    public function test_can_filter_leaderboard_by_month_and_year(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-28 12:00:00'));

        $viewer = $this->userWithPermissions(['view-leaderboard']);
        $done = $this->makeStatus('Done');

        $october = $this->makeEmployee('Driver Oktober', 'Driver');
        $january = $this->makeEmployee('Driver Januari', 'Driver');
        $this->makeDoneOrder($done, $october, 'driver');
        $janOrder = $this->makeDoneOrder($done, $january, 'driver');
        $janOrder->forceFill([
            'created_at' => Carbon::parse('2026-01-15 10:00:00'),
            'pickup_datetime' => Carbon::parse('2026-01-15 10:00:00'),
        ])->save();

        $this->actingAs($viewer)
            ->get(route('leaderboard.index', ['year' => 2026, 'month' => 1]))
            ->assertOk()
            ->assertSee('Januari 2026', false)
            ->assertSee('Driver Januari')
            ->assertDontSee('Driver Oktober');

        $this->actingAs($viewer)
            ->get(route('leaderboard.index', ['year' => 2026]))
            ->assertOk()
            ->assertSee('Tahun 2026', false)
            ->assertSee('Driver Oktober')
            ->assertSee('Driver Januari');
    }

    public function test_can_filter_leaderboard_by_start_and_end_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-28 12:00:00'));

        $viewer = $this->userWithPermissions(['view-leaderboard']);
        $done = $this->makeStatus('Done');

        $inRange = $this->makeEmployee('Driver Rentang', 'Driver');
        $outside = $this->makeEmployee('Driver Luar', 'Driver');
        $this->makeDoneOrder($done, $inRange, 'driver');
        $old = $this->makeDoneOrder($done, $outside, 'driver');
        $old->forceFill([
            'created_at' => Carbon::parse('2026-10-01 09:00:00'),
            'pickup_datetime' => Carbon::parse('2026-10-01 09:00:00'),
        ])->save();

        $this->actingAs($viewer)
            ->get(route('leaderboard.index', [
                'start_date' => '2026-10-20',
                'end_date' => '2026-10-28',
            ]))
            ->assertOk()
            ->assertSee('20 Oktober 2026 – 28 Oktober 2026', false)
            ->assertSee('Driver Rentang')
            ->assertDontSee('Driver Luar');
    }

    public function test_year_filter_uses_pickup_datetime_not_created_at(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-28 12:00:00'));

        $viewer = $this->userWithPermissions(['view-leaderboard']);
        $done = $this->makeStatus('Done');

        $driver = $this->makeEmployee('Driver Pickup 2026', 'Driver');
        $order = $this->makeDoneOrder($done, $driver, 'driver');
        $order->forceFill([
            'created_at' => Carbon::parse('2025-12-20 09:00:00'),
            'pickup_datetime' => Carbon::parse('2026-03-02 08:00:00'),
        ])->save();

        $this->actingAs($viewer)
            ->get(route('leaderboard.index', ['year' => 2026]))
            ->assertOk()
            ->assertSee('Driver Pickup 2026');
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
