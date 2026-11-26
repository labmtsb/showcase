<?php
namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create admin
        User::create([
            "name"     => "Alex",
            "email"    => "alex@lab.net",
            "username" => "alex5758",
            "role"     => "admin",
            "password" => Hash::make('alex88'),
        ]);

        // create user
        DB::transaction(function () {
            $user = User::create([
                "name"     => "User",
                "email"    => "user@lab.net",
                "username" => "user5758",
                "nis"      => "123456789",
                "role"     => "user",
                "password" => Hash::make('user88'),
            ]);

            Project::create([
                'user_id'      => $user->id,
                'project_name' => 'Test Project',
                'slug'         => 'test-project',
                'path'         => 'storage/app/',
            ]);
        });

    }
}
