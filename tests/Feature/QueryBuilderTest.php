<?php

namespace Tests\Feature;

use Database\Seeders\CategorySeeder;
use Database\Seeders\CounterSeeder;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;

class QueryBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('DELETE FROM counters');
        DB::delete('DELETE FROM products');
        DB::delete('DELETE FROM categories');
    }

    public function testInsert()
    {
        DB::table('categories')->insert([
            'id' => 'GADGET',
            'name' => 'Gadget'
        ]);
        DB::table('categories')->insert([
            'id' => 'FOOD',
            'name' => 'Food'
        ]);

        $result = DB::select('SELECT COUNT(id) as total FROM categories');
        // assertCount(2, $result[0]->total);//untuk menghitung jumlah elemen array
        self::assertEquals(2, $result[0]->total); //untuk menghitung jumlah elemen array
    }

    public function testSelect()
    {
        $this->testInsert();

        $collection = DB::table('categories')->select(['id', 'name'])->get();
        assertNotNull($collection);

        $collection->each(function ($record) {
            Log::info(json_encode($record));
        });
    }

    public function testInsertCategories()
    {
        // DB::table('categories')
        //     ->insert(['id' => 'SMARTPHONE', 'name' => 'Smartphone']);
        // DB::table('categories')
        //     ->insert(['id' => 'FOOD', 'name' => 'Food']);
        // DB::table('categories')
        //     ->insert(['id' => 'LAPTOP', 'name' => 'laptop']);
        // DB::table('categories')
        //     ->insert(['id' => 'FASHION', 'name' => 'Fashion']);

        $this->seed(CategorySeeder::class);
    }

    public function testWhere()
    {
        $this->testInsertCategories();

        $collection = DB::table('categories')->where(function (Builder $builder) {
            $builder->where('id', '=', 'SMARTPHONE'); //yg pertama where
            $builder->orWhere('id', '=', 'LAPTOP');
            // SELECT * FROM categories WHERE (id = smartphone or id = laptop)
        })->get();

        assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereIn()
    {
        $this->testInsertCategories();

        $collection = DB::table('categories')->whereIn('id', ['SMARTPHONE', 'LAPTOP'])->get();

        assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereNull()
    {
        $this->testInsertCategories();

        $collection = DB::table('categories')->whereNull('description')->get();

        assertCount(4, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testUpdate()
    {
        $this->testInsertCategories();

        DB::table('categories')->where('id', '=', 'SMARTPHONE')->update(['name' => 'Handphone']);

        $collection = DB::table('categories')->where('name', '=', 'Handphone')->get();
        assertCount(1, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testUpdateOrInsert()
    {
        // kalau ada di update, kalau tidak ada di insert
        DB::table('categories')->updateOrInsert([
            'id' => 'VOUCHER'
        ], [
            'name' => 'Voucher',
            'description' => 'Ticket and Voucher',
            'created_at' => '2000-01-01 10:10:10'
        ]);

        $collection = DB::table('categories')->where('id', '=', 'VOUCHER')->get();
        assertCount(1, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testIncrement()
    {
        $this->seed(CounterSeeder::class);
        DB::table('counters')->where('id', '=', 'sample')->increment('counter', 1);

        $collection = DB::table('counters')->where('id', '=', 'sample')->get();
        assertCount(1, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testDelete()
    {
        $this->testInsertCategories();

        DB::table('categories')->where('id', '=', 'SMARTPHONE')->delete();

        $collection = DB::table('categories')->where('id', '=', 'SMARTPHONE')->get();
        assertCount(0, $collection);
    }

    public function insertTableProducts()
    {
        $this->testInsertCategories();

        DB::table('products')
            ->insert([
                'id' => '1',
                'name' => 'Bakso',
                'category_id' => 'FOOD',
                'price' => 20000
            ]);
        DB::table('products')
            ->insert([
                'id' => '2',
                'name' => 'Sate',
                'category_id' => 'FOOD',
                'price' => 15000
            ]);
    }

    public function testJoin()
    {
        $this->insertTableProducts();

        $collection = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.id', 'products.name', 'categories.name as category_name', 'products.price') //biar tidak bentrok
            ->get();

        assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testOrdering()
    {
        $this->insertTableProducts();

        $collection = DB::table('products')
            ->orderBy('price', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testTakeAndSkip()
    {
        $this->testInsertCategories();

        $collection = DB::table('categories')
            ->skip(2)
            ->take(2)
            ->get();

        assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testChunk()
    {
        $this->testInsertCategories();

        DB::table('categories')
            ->orderBy('id')
            ->chunk(1, function ($items) {
                //collection. tentu di real app bukan cuma 1
                assertNotNull($items);
                $items->each(function ($item) {
                    Log::info(json_encode($item));
                });
            });
    }

    public function testlazyResults()
    {
        $this->testInsertCategories();

        DB::table('categories')
            ->orderBy('id')
            ->lazy(1)
            ->each(function ($item) {
                assertNotNull($item);
                Log::info(json_encode($item));
            });
    }

    public function testCursor()
    {
        $this->testInsertCategories();

        DB::table('categories')
            ->orderBy('id')
            ->cursor()
            ->each(function ($item) {
                assertNotNull($item);
                Log::info(json_encode($item));
            });
    }

    public function testAggregate()
    {
        $this->insertTableProducts();

        $result = DB::table('products')
            ->count('id');
        assertEquals(2, $result);

        $result = DB::table('products')
            ->max('price');
        assertEquals(20000, $result);

        $result = DB::table('products')
            ->min('price');
        assertEquals(15000, $result);

        $result = DB::table('products')
            ->avg('price');
        assertEquals(17500, $result);

        $result = DB::table('products')
            ->sum('price');
        assertEquals(35000, $result);
    }

    public function testAggregateRaw()
    {
        $this->insertTableProducts();

        $collection = DB::table('products')
            ->select(
                DB::raw('count(*) as total_product'),
                DB::raw('min(price) as min_price'),
                DB::raw('max(price) as max_price'),
            )->get();

        assertEquals(2, $collection[0]->total_product);
        assertEquals(15000, $collection[0]->min_price);
        assertEquals(20000, $collection[0]->max_price);
    }

    public function insertProductFashion()
    {
        DB::table('products')
            ->insert([
                'id' => '3',
                'name' => 'Kemeja',
                'category_id' => 'FASHION',
                'price' => 50000
            ]);
        DB::table('products')
            ->insert([
                'id' => '4',
                'name' => 'Kaos',
                'category_id' => 'FASHION',
                'price' => 40000
            ]);
    }

    public function testGrouping()
    {
        $this->insertTableProducts();
        $this->insertProductFashion();

        $collection = DB::table('products')
            ->select('category_id', DB::raw('count(*) as total_product'))
            ->groupBy('category_id')
            ->orderBy('category_id', 'desc')
            ->get();

        assertCount(2, $collection);
        assertEquals('FOOD', $collection[0]->category_id);
        assertEquals('FASHION', $collection[1]->category_id);
        assertEquals(2, $collection[0]->total_product);
        assertEquals(2, $collection[1]->total_product);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testHaving()
    {
        $this->insertTableProducts();
        $this->insertProductFashion();

        $collection = DB::table('products')
            ->select('category_id', DB::raw('count(*) as total_product'))
            ->groupBy('category_id')
            ->orderBy('category_id', 'desc')
            ->having(DB::raw('count(*)'), '>', 2)
            ->get();

        assertCount(0, $collection);
    }

    public function testLocking()
    {
        $this->insertTableProducts();

        DB::transaction(function () {
            $collection = DB::table('products')
                ->where('id', '=', '1')
                ->lockForUpdate()
                ->get();

            assertCount(1, $collection);
        });
    }

    public function testPagination()
    {
        $this->testInsertCategories();

        $paginate = DB::table('categories')->paginate(perPage: 2);

        assertEquals(1, $paginate->currentPage());
        assertEquals(2, $paginate->perPage());
        assertEquals(2, $paginate->lastPage());
        assertEquals(4, $paginate->total());

        $arrItem = $paginate->items();
        assertCount(2, $arrItem); //baru di hal pertama
        foreach ($arrItem as $item) {
            Log::info(json_encode($item));
        }
    }

    public function testIterateAllPagination()
    {
        $this->testInsertCategories();

        $page = 1;

        while (true) {
            $paginate = DB::table('categories')->paginate(perPage: 2, page: $page);

            if ($paginate->isEmpty()) {
                break;
            } else {
                $page++;
                $arrItem = $paginate->items();
                assertCount(2, $arrItem);
                foreach ($arrItem as $item) {
                    Log::info(json_encode($item));
                }
            }
        }
    }

    public function testCursorPagination()
    {
        $this->testInsertCategories();

        $cursor = 'id';
        while (true) {
            $paginate = DB::table('categories')->orderBy('id')->cursorPaginate(perPage: 2, cursor: $cursor);

            $arrItem = $paginate->items();
            assertCount(2, $arrItem);
            foreach ($arrItem as $item) {
                Log::info(json_encode($item));
            }

            $cursor = $paginate->nextCursor();
            if ($cursor == null) {
                break;
            }
        }
    }
}
