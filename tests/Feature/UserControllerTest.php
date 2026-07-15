<?php

namespace Tests\Feature;

use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_username_password_and_invalidate_sessions(): void
    {
        $adminRole = Role::create(['name' => 'admin']);
        $admin = User::factory()->create([
            'password' => Hash::make('admin-password'),
        ]);
        $admin->assignRole($adminRole);

        $user = User::factory()->create([
            'username' => 'old-username',
            'password' => Hash::make('old-password'),
        ]);

        $user->createToken('test-token');

        $this->actingAs($admin);

        $request = new Request([
            'username' => 'new-username',
            'password' => 'new-password',
            'admin_password' => 'admin-password',
            'logout_all_sessions' => true,
        ]);
        $request->setUserResolver(fn () => $admin);

        $response = (new UserController())->update($request, $user);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame('new-username', $user->fresh()->username);
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertCount(0, $user->fresh()->tokens);
    }

    public function test_changing_to_admin_requires_admin_password_and_can_verify_contact_data(): void
    {
        $adminRole = Role::create(['name' => 'admin']);
        $admin = User::factory()->create([
            'password' => Hash::make('admin-password'),
        ]);
        $admin->assignRole($adminRole);

        $user = User::factory()->create([
            'email' => 'old@example.com',
            'phone_number' => '+593999999999',
            'email_verified_at' => null,
            'phone_verified_at' => null,
        ]);

        $request = new Request([
            'email' => 'new@example.com',
            'verify_email' => true,
            'phone_number' => '+593987654321',
            'verify_phone' => true,
            'role_id' => $adminRole->id,
            'admin_password' => 'admin-password',
        ]);
        $request->setUserResolver(fn () => $admin);

        $response = (new UserController())->update($request, $user);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertNotNull($user->fresh()->phone_verified_at);
        $this->assertTrue($user->fresh()->hasRole('admin'));
    }
}
