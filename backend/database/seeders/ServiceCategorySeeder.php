<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Surveillance',
                'description' => 'CCTV installation, security camera setup, and monitoring',
                'is_active' => true,
            ],
            [
                'name' => 'Smart Home',
                'description' => 'Home automation, smart devices, and integrated systems',
                'is_active' => true,
            ],
            [
                'name' => 'Solar',
                'description' => 'Solar panel installation, solar energy systems, and maintenance',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ServiceCategory::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
