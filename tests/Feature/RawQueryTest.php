<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\TryCatch;
use Tests\TestCase;

use function PHPUnit\Framework\assertEquals;

class RawQueryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('DELETE FROM categories');
    }

    public function testCrud()
    {
        DB::insert('INSERT INTO categories(id, name, description, created_at) VALUES (?, ?, ?, ?)', [
            'GADGET', 'Gadget', 'Gadget Category', '2000-01-01 00:00:00'
        ]);

        // collection
        $result = DB::select('SELECT * FROM categories WHERE id = ?', ['GADGET']);

        assertEquals(1, count($result));
        assertEquals('GADGET', $result[0]->id);
        assertEquals('Gadget', $result[0]->name);
        assertEquals('Gadget Category', $result[0]->description);
        assertEquals('2000-01-01 00:00:00', $result[0]->created_at);
    }

    public function testNamedBinding()
    {
        DB::insert('INSERT INTO categories(id, name, description, created_at) VALUES (:id, :name, :description, :created_at)', [
            'id' => 'GADGET',
            'name' => 'Gadget',
            'description' => 'Gadget Category',
            'created_at' => '2000-01-01 00:00:00'
        ]);

        // collection
        $result = DB::select('SELECT * FROM categories WHERE id = ?', ['GADGET']);

        assertEquals(1, count($result));
        assertEquals('GADGET', $result[0]->id);
        assertEquals('Gadget', $result[0]->name);
        assertEquals('Gadget Category', $result[0]->description);
        assertEquals('2000-01-01 00:00:00', $result[0]->created_at);
    }
}