<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(4), // Schema me 'name' hai
            'description' => fake()->paragraph(),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'type' => fake()->randomElement(['task', 'bug', 'story', 'feature']),
            // Note: 'status' default 'todo' hai schema me, usko chhedne ki need nahi
            // 'stage' hum Seeder me override karenge taaki Project se match kare
            'stage' => 'todo',

            'due_date' => fake()->dateTimeBetween('now', '+1 month'),
            'order' => fake()->numberBetween(0, 100),

            'project_id' => Project::factory(),
            'assigned_by' => User::factory(), // FK Error se bachne ke liye default
            'assigned_to' => null,
        ];
    }
}
