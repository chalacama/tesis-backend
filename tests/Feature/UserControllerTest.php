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
}
