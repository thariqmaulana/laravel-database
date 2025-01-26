<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')
            ->insert(['id' => 'SMARTPHONE', 'name' => 'Smartphone']);
        DB::table('categories')
            ->insert(['id' => 'FOOD', 'name' => 'Food']);
        DB::table('categories')
            ->insert(['id' => 'LAPTOP', 'name' => 'laptop']);
        DB::table('categories')
            ->insert(['id' => 'FASHION', 'name' => 'Fashion']);
    }
}
