<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\TryCatch;
use Tests\TestCase;

use function PHPUnit\Framework\assertEquals;

class TransactionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('DELETE FROM categories');
    }

    public function testTransactionSuccess()
    {
        DB::transaction(function () {
            DB::insert('INSERT INTO categories(id, name, description, created_at) VALUES (?, ?, ?, ?)', [
                'GADGET', 'Gadget', 'Gadget Category', '2000-01-01 00:00:00'
            ]);
            DB::insert('INSERT INTO categories(id, name, description, created_at) VALUES (?, ?, ?, ?)', [
                'FOOD', 'Food', 'Food Category', '2000-01-01 00:00:00'
            ]);
        });

        $result = DB::select('SELECT * FROM categories');
        assertEquals(2, count($result));
    }

    public function testTransactionFailed()
    {
        try {
            DB::transaction(function () {
                DB::insert('INSERT INTO categories(id, name, description, created_at) VALUES (?, ?, ?, ?)', [
                    'GADGET', 'Gadget', 'Gadget Category', '2000-01-01 00:00:00'
                ]);
                DB::insert('INSERT INTO categories(id, name, description, created_at) VALUES (?, ?, ?, ?)', [
                    'GADGET', 'Gadget', 'Gadget Category', '2000-01-01 00:00:00'
                ]);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            
        }

        // kena rollback

        $result = DB::select('SELECT * FROM categories');
        assertEquals(0, count($result));
    }

    public function testTransactionManual()
    {
        try {
            DB::beginTransaction();
            DB::insert('INSERT INTO categories(id, name, description, created_at) VALUES (?, ?, ?, ?)', [
                'GADGET', 'Gadget', 'Gadget Category', '2000-01-01 00:00:00'
            ]);
            DB::insert('INSERT INTO categories(id, name, description, created_at) VALUES (?, ?, ?, ?)', [
                'FOOD', 'Food', 'Food Category', '2000-01-01 00:00:00'
            ]);
            DB::commit();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            throw $e;
        }

        $result = DB::select('SELECT * FROM categories');
        assertEquals(2, count($result));
    }
}