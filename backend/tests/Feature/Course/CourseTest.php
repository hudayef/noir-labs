<?php

namespace Tests\Feature\Course;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_can_create_course()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/courses', [
            'title' => 'Vue 3 untuk Pemula',
            'description' => 'Belajar Vue 3 dari dasar',
            'price' => 150000,
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.title', 'Vue 3 untuk Pemula');
    }

    public function test_guest_cannot_create_course()
    {
        $response = $this->postJson('/api/courses', [
            'title' => 'Vue 3 untuk Pemula',
        ]);

        $response->assertStatus(401);
    }
}
