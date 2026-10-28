<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PlaceSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_search_places(): void
    {
        $this->getJson(route('orders.places.search', ['q' => 'monas']))
            ->assertUnauthorized();
    }

    public function test_user_without_permission_cannot_search_places(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson(route('orders.places.search', ['q' => 'monas']))
            ->assertForbidden();
    }

    public function test_search_uses_nominatim_when_google_key_is_missing(): void
    {
        config(['services.google.maps_key' => null]);
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([
                [
                    'place_id' => 99,
                    'lat' => '-6.1753924',
                    'lon' => '106.8271528',
                    'display_name' => 'Monumen Nasional, Jakarta',
                ],
            ], 200),
        ]);

        $this->actingAs($this->userWithPermissions(['create-orders']))
            ->getJson(route('orders.places.search', ['q' => 'monas']))
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.0.label', 'Monumen Nasional, Jakarta')
            ->assertJsonPath('data.0.needs_details', false);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'nominatim.openstreetmap.org'));
    }

    public function test_search_uses_google_autocomplete_when_key_is_set(): void
    {
        config(['services.google.maps_key' => 'test-key']);
        Http::fake([
            'maps.googleapis.com/maps/api/place/autocomplete/json*' => Http::response([
                'status' => 'OK',
                'predictions' => [
                    ['description' => 'Monumen Nasional, Jakarta', 'place_id' => 'ChIJ123'],
                ],
            ], 200),
        ]);

        $this->actingAs($this->userWithPermissions(['edit-orders']))
            ->getJson(route('orders.places.search', ['q' => 'monas']))
            ->assertOk()
            ->assertJsonPath('data.0.id', 'ChIJ123')
            ->assertJsonPath('data.0.needs_details', true);
    }

    public function test_details_returns_google_place_coordinates(): void
    {
        config(['services.google.maps_key' => 'test-key']);
        Http::fake([
            'maps.googleapis.com/maps/api/place/details/json*' => Http::response([
                'status' => 'OK',
                'result' => [
                    'name' => 'Monas',
                    'formatted_address' => 'Jakarta Pusat',
                    'geometry' => ['location' => ['lat' => -6.175, 'lng' => 106.827]],
                    'url' => 'https://maps.google.com/?cid=1',
                ],
            ], 200),
        ]);

        $this->actingAs($this->userWithPermissions(['create-orders']))
            ->getJson(route('orders.places.details', ['place_id' => 'ChIJ123']))
            ->assertOk()
            ->assertJsonPath('data.address', 'Jakarta Pusat')
            ->assertJsonPath('data.latitude', -6.175)
            ->assertJsonPath('data.maps_url', 'https://maps.google.com/?cid=1');
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
}
