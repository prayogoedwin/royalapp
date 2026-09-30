<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class UploadFolderTest extends TestCase
{
    use RefreshDatabase;

    public function test_month_folder_can_be_downloaded_as_zip(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('uploads/2026/09/foto.jpg', 'foto-bytes');
        $user = $this->userWithPermissions(['view-upload-folders']);

        $response = $this->actingAs($user)->get(route('upload-folders.download', [
            'year' => '2026',
            'month' => '09',
        ]));

        $response->assertOk();
        $response->assertDownload('uploads-2026-09.zip');

        $zip = new ZipArchive;
        $zip->open($response->baseResponse->getFile()->getPathname());
        $this->assertSame('foto-bytes', $zip->getFromName('foto.jpg'));
        $zip->close();
    }

    public function test_delete_requires_the_displayed_code(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('uploads/2026/09/foto.jpg', 'foto-bytes');
        $user = $this->userWithPermissions(['view-upload-folders', 'delete-upload-folders']);

        $index = $this->actingAs($user)->get(route('upload-folders.index'));
        $code = session('upload_folder_confirm.202609');

        $this->assertMatchesRegularExpression('/^[A-Z]{4}$/', $code);
        $index->assertSee($code);
        $index->assertSee('Download');

        $this->actingAs($user)
            ->withSession(['upload_folder_confirm' => ['202609' => $code]])
            ->delete(route('upload-folders.destroy', ['year' => '2026', 'month' => '09']), [
                'confirmation_code' => 'ZZZZ',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('confirmation_code');

        Storage::disk('public')->assertExists('uploads/2026/09/foto.jpg');

        $this->actingAs($user)
            ->withSession(['upload_folder_confirm' => ['202609' => $code]])
            ->delete(route('upload-folders.destroy', ['year' => '2026', 'month' => '09']), [
                'confirmation_code' => strtolower($code),
            ])
            ->assertRedirect(route('upload-folders.index'));

        Storage::disk('public')->assertMissing('uploads/2026/09/foto.jpg');
    }

    /**
     * @param  list<string>  $permissions
     */
    private function userWithPermissions(array $permissions): User
    {
        $role = Role::query()->firstOrCreate(['name' => 'folder-tester']);
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
