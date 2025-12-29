<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Product;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // Create sample user
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        // Create sample products
        Product::create([
            'name' => 'Security Camera System',
            'description' => 'Advanced HD security camera system with night vision',
            'price' => 299.99,
            'stock' => 15,
            'category' => 'Security',
        ]);

        Product::create([
            'name' => 'Power Backup Unit',
            'description' => 'Reliable UPS system for enterprise applications',
            'price' => 599.99,
            'stock' => 8,
            'category' => 'Power',
        ]);

        Product::create([
            'name' => 'Access Control Panel',
            'description' => 'Modern biometric access control system',
            'price' => 1299.99,
            'stock' => 5,
            'category' => 'Access Control',
        ]);

        Product::create([
            'name' => 'Network Monitor',
            'description' => 'Professional network monitoring equipment',
            'price' => 449.99,
            'stock' => 12,
            'category' => 'Network',
        ]);

        Product::create([
            'name' => 'Emergency Light System',
            'description' => 'LED emergency lighting for safety',
            'price' => 149.99,
            'stock' => 25,
            'category' => 'Lighting',
        ]);
    }
}
