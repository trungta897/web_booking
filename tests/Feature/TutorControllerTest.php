<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tutor;
use App\Models\User;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TutorControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_tutors_list()
    {
        $tutor = Tutor::factory()->create();
        $subject = Subject::factory()->create();
        $tutor->subjects()->attach($subject);

        $response = $this->get(route('tutors.index'));

        $response->assertStatus(200)
            ->assertViewIs('tutors.index')
            ->assertViewHas('tutors')
            ->assertViewHas('subjects');
    }

    public function test_can_view_tutor_details()
    {
        $tutor = Tutor::factory()->create();

        $response = $this->get(route('tutors.show', $tutor));

        $response->assertStatus(200)
            ->assertViewIs('tutors.show')
            ->assertViewHas('tutor');
    }

    public function test_can_filter_tutors_by_subject()
    {
        $subject = Subject::factory()->create();
        $tutor = Tutor::factory()->create();
        $tutor->subjects()->attach($subject);

        $response = $this->get(route('tutors.index', ['subject' => $subject->id]));

        $response->assertStatus(200)
            ->assertViewIs('tutors.index')
            ->assertViewHas('tutors');
    }

    public function test_can_filter_tutors_by_price_range()
    {
        $tutor = Tutor::factory()->create(['hourly_rate' => 50]);

        $response = $this->get(route('tutors.index', ['price_range' => '40-60']));

        $response->assertStatus(200)
            ->assertViewIs('tutors.index')
            ->assertViewHas('tutors');
    }

    public function test_can_filter_tutors_by_minimum_rating()
    {
        $tutor = Tutor::factory()->create();
        $user = User::factory()->create();
        $tutor->reviews()->create([
            'student_id' => $user->id,
            'rating' => 5,
            'comment' => 'Great tutor!'
        ]);

        $response = $this->get(route('tutors.index', ['rating' => 4]));

        $response->assertStatus(200)
            ->assertViewIs('tutors.index')
            ->assertViewHas('tutors');
    }

    public function test_can_toggle_favorite_tutor()
    {
        $user = new User([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'student'
        ]);
        $user->save();

        $tutor = Tutor::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('tutors.favorite', $tutor));

        $response->assertStatus(200)
            ->assertJson(['is_favorite' => true]);

        $this->assertTrue($user->favoriteTutors()->where('tutor_id', $tutor->id)->exists());

        // Test unfavorite
        $response = $this->actingAs($user)
            ->post(route('tutors.favorite', $tutor));

        $response->assertStatus(200)
            ->assertJson(['is_favorite' => false]);

        $this->assertFalse($user->favoriteTutors()->where('tutor_id', $tutor->id)->exists());
    }

    public function test_can_check_tutor_availability()
    {
        $tutor = Tutor::factory()->create();
        $tutor->availability()->create([
            'day_of_week' => 'monday',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'is_available' => true
        ]);

        $response = $this->get(route('tutors.availability', ['tutor' => $tutor, 'day' => 'monday']));

        $response->assertStatus(200)
            ->assertJson([
                'available' => true,
                'slots' => ['09:00 - 17:00']
            ]);
    }
}
