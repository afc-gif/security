<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Product;
use App\Models\Solution;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user if not exists
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'status' => 'approved',
            ]
        );

        // Create sample POS user if not exists (pre-approved for testing)
        User::firstOrCreate(
            ['email' => 'pos@example.com'],
            [
                'name' => 'POS User',
                'password' => Hash::make('pos123'),
                'role' => 'pos',
                'status' => 'approved',
            ]
        );

        // Create sample user if not exists
        User::firstOrCreate(
            ['email' => 'john@example.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'status' => 'approved',
            ]
        );

        // Create the 6 main solutions from index.html
        $solutions = [
            [
                'name' => 'SURVEILLANCE',
                'icon' => '📹',
                'description' => 'HD/4K CCTV systems with AI detection, cloud archival, and 24/7 remote monitoring',
                'sort_order' => 1,
            ],
            [
                'name' => 'SOLAR POWER',
                'icon' => '⚡',
                'description' => 'Hybrid inverters with lithium batteries sized for enterprise loads',
                'sort_order' => 2,
            ],
            [
                'name' => 'ACCESS CONTROL',
                'icon' => '🔐',
                'description' => 'Smart gates, biometric locks, and visitor management systems',
                'sort_order' => 3,
            ],
            [
                'name' => 'PERIMETER SECURITY',
                'icon' => '🔌',
                'description' => 'High-voltage electric fencing with integrated alarms and monitoring',
                'sort_order' => 4,
            ],
            [
                'name' => 'SMART AUTOMATION',
                'icon' => '🏠',
                'description' => 'Unified control for lighting, climate, security, and energy management',
                'sort_order' => 5,
            ],
            [
                'name' => 'FULL INTEGRATION',
                'icon' => '🔗',
                'description' => 'Complete enterprise stack with unified monitoring and control',
                'sort_order' => 6,
            ],
        ];

        foreach ($solutions as $solutionData) {
            Solution::firstOrCreate(['name' => $solutionData['name']], $solutionData);
        }

        // Create sample products
        Product::create([
            'name' => 'Security Camera System',
            'description' => 'Advanced HD security camera system with night vision',
            'price' => 299.99,
            'stock' => 15,
            'category' => 'SURVEILLANCE',
        ]);

        Product::create([
            'name' => 'Power Backup Unit',
            'description' => 'Reliable UPS system for enterprise applications',
            'price' => 599.99,
            'stock' => 8,
            'category' => 'SOLAR POWER',
        ]);

        Product::create([
            'name' => 'Access Control Panel',
            'description' => 'Modern biometric access control system',
            'price' => 1299.99,
            'stock' => 5,
            'category' => 'ACCESS CONTROL',
        ]);

        Product::create([
            'name' => 'Perimeter Fence System',
            'description' => 'Professional electric fencing system for secure perimeters',
            'price' => 449.99,
            'stock' => 12,
            'category' => 'PERIMETER SECURITY',
        ]);

        Product::create([
            'name' => 'Smart Home Automation Hub',
            'description' => 'Central automation hub for integrated building control',
            'price' => 149.99,
            'stock' => 25,
            'category' => 'SMART AUTOMATION',
        ]);

        Product::create([
            'name' => 'Enterprise Integration Platform',
            'description' => 'Complete platform for full system integration and management',
            'price' => 1999.99,
            'stock' => 3,
            'category' => 'FULL INTEGRATION',
        ]);
    }
}
