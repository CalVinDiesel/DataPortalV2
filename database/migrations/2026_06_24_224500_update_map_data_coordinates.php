<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $coordinates = [
            'KK_OSPREY' => ['xAxis' => 116.070466, 'yAxis' => 5.957839],
            'KB_3DTiles_Lite' => ['xAxis' => 116.430814, 'yAxis' => 6.355872],
            'fisheye_test_kolombong_18mac2025' => ['xAxis' => 116.115222, 'yAxis' => 5.982686],
            'wismamerdeka' => ['xAxis' => 116.075578, 'yAxis' => 5.985513],
            'ppns_ys' => ['xAxis' => 116.110547, 'yAxis' => 6.015390],
            'Keningau_Sabah_2026' => ['xAxis' => 116.1649, 'yAxis' => 5.3555],
        ];

        foreach ($coordinates as $id => $coords) {
            DB::table('map_data')
                ->where('mapDataID', $id)
                ->update($coords);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed for coordinates correction
    }
};
