<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $me = User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'type' => 'admin',
            'profile_pic' => "https://ui-avatars.com/api/?name=Admin+User&background=0D8ABC&color=fff",
        ]);

        // 2. Team Members
        $team = User::factory(50)->create([
            'type' => 'developer'
        ]);

        // 3. Projects Create karo (Admin ke naam pe)
        $projects = Project::factory(25)->create([
            'user_id' => $me->id,
        ]);

        // 4. Tasks Create karo
        foreach ($projects as $project) {

            // Project ke valid stages uthao (e.g., ['todo', 'review', 'done'])
            // Fallback lagaya hai agar stages null nikle to
            $projectStages = $project->stages ?? ['todo', 'in_progress', 'done'];

            // Har project me 10-12 tasks
            $numberOfTasks = rand(10, 12);

            for ($i = 0; $i < $numberOfTasks; $i++) {
                Task::factory()->create([
                    'project_id' => $project->id,
                    'name' => fake()->jobTitle(), // Task Name

                    // ✅ Important: Task ki stage project ke columns me se hi honi chahiye
                    'stage' => fake()->randomElement($projectStages),

                    'assigned_by' => $me->id, // Task kisne banaya? (Admin ne)

                    // Assign kisko kiya? (Ya Admin ko, ya Team Member ko, ya Null)
                    'assigned_to' =>  $project->members->random()->id,
                ]);
            }
        }

        echo "\n✅ Database Seeded Successfully!";
        echo "\n👉 Login: admin@example.com / password\n";
    }
}
