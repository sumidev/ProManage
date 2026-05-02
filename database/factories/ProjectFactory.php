<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stageTemplates = [
            // ['todo', 'in_progress', 'done'],
            ['backlog', 'todo', 'in_progress', 'review', 'done'],
            // ['todo', 'in_progress', 'review', 'done'],
        ];

        return [
            'name' => fake()->catchPhrase(),
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement(['Software', 'Marketing', 'Design', 'Consulting']),

            // ✅ Random Stages pick karega
            'stages' => fake()->randomElement($stageTemplates),

            'status' => 'active',
            'deadline' => fake()->dateTimeBetween('+1 week', '+3 months'),
            'user_id' => User::factory(), // Default User agar seeder se pass na ho
        ];
    }

    public function configure(): static
    {
       return $this->afterCreating(function ($project) {

        $project->members()->attach($project->user_id, ['role' => 'admin']);

            $users = User::where('id', '!=', $project->user_id)
                         ->inRandomOrder()
                         ->take(rand(4, 10))
                         ->get();

            if ($users->count() > 0) {
                $project->members()->attach($users, ['role' => 'member']);
            }
        });
    }
}
