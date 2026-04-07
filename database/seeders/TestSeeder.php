<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class TestSeeder extends Seeder {
    public function run() {
        $tables = DB::select('SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = \'public\'');
        foreach($tables as $t) {
            echo "FINDING TABLE: " . $t->tablename . "\n";
        }
    }
}
