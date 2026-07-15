<?php

namespace Tests\Feature;

use App\Http\Controllers\PanelController;
use App\Models\Course;
use App\Models\Difficulty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PanelControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_dashboard_stats_by_tutor_username(): void
    {
        $adminRole = Role::create(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $tutorAlpha = User::factory()->create(['username' => 'tutor-alpha']);
        $tutorBeta = User::factory()->create(['username' => 'tutor-beta']);
        $difficulty = Difficulty::create(['name' => 'Test Difficulty']);

        $courseAlpha = Course::create([
            'title' => 'Course Alpha',
            'description' => 'Test course alpha',
            'private' => false,
            'enabled' => true,
            'difficulty_id' => $difficulty->id,
        ]);
        $courseBeta = Course::create([
            'title' => 'Course Beta',
            'description' => 'Test course beta',
            'private' => false,
            'enabled' => true,
            'difficulty_id' => $difficulty->id,
        ]);

        $courseAlpha->tutors()->attach($tutorAlpha->id, ['is_owner' => true]);
        $courseBeta->tutors()->attach($tutorBeta->id, ['is_owner' => true]);

        $this->actingAs($admin);

        $request = new Request(['username' => 'tutor-alpha']);

        $response = (new PanelController())->index($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame(1, $response->getData(true)['courses_count']);
    }
}
