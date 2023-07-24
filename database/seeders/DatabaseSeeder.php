<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Language;
use App\Models\StationType;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {

        \App\Models\User::factory()->create([
            'name' => 'Alex Windsor',
            'email' => 'alexwindsormusic@gmail.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' // 'password'
            ]    
        );
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        //     'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' // 'password'
        //     ]    
        // );

        StationType::create(['name' => 'Broadcast Station']);
        StationType::create(['name' => 'Utility Station']);

        Language::create(['name' => 'Unknown or n/a']);
        Language::create(['name' => 'morse code, numbers, databursts etc.']);

        // shell_exec('mysql -u alex -pm0r3t0n highfreq_dev < ' . __DIR__ . '/highfreq.sql');
    }
}
