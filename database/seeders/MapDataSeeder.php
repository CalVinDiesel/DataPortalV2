<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MapData;

class MapDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // MapData::query()->delete(); // Removed to prevent 'Relation does not exist' errors during restoration
        $locations = [
            [
                'mapDataID' => 'KK_OSPREY',
                'title' => 'KK OSPREY',
                'description' => '3D model of Kota Kinabalu area.',
                'xAxis' => 116.070466,
                'yAxis' => 5.957839,
                '3dTiles' => 'https://3dhub.geosabah.my/3dmodel/KK_OSPREY/tileset.json',
                'thumbNailUrl' => '/assets/img/front-pages/locations/kkOsprey_pin_image.jpg',
                'purchase_price_tokens' => 10,
                'updateDateTime' => now(),
            ],
            [
                'mapDataID' => 'KB_3DTiles_Lite',
                'title' => 'KB 3DTiles Lite',
                'description' => '3D model of buildings in Kota Kinabalu.',
                'xAxis' => 116.073466,
                'yAxis' => 5.960839,
                '3dTiles' => 'https://3dhub.geosabah.my/3dmodel/Building_Planning/KB_3DTiles_Lite/tileset.json',
                'thumbNailUrl' => '/assets/img/front-pages/locations/kb 3dtiles lite_pin_image.jpg',
                'purchase_price_tokens' => 20,
                'updateDateTime' => now(),
            ],
            [
                'mapDataID' => 'fisheye_test_kolombong_18mac2025',
                'title' => 'Kolombong Fisheye Test',
                'description' => 'Fisheye test model in Kolombong area.',
                'xAxis' => 116.110466,
                'yAxis' => 5.977839,
                '3dTiles' => 'https://3dhub.geosabah.my/3dmodel/Building_Planning/fisheye_test_kolombong_18mac2025/tileset.json',
                'thumbNailUrl' => '/assets/img/front-pages/locations/kolombong_pin_image.jpg',
                'purchase_price_tokens' => 15,
                'updateDateTime' => now(),
            ],
            [
                'mapDataID' => 'wismamerdeka',
                'title' => 'WISMA MERDEKA',
                'description' => '3D model of Wisma Merdeka complex.',
                'xAxis' => 116.074466,
                'yAxis' => 5.984839,
                '3dTiles' => 'https://3dhub.geosabah.my/3dmodel/wismamerdeka/tileset.json',
                'thumbNailUrl' => '/assets/img/front-pages/locations/wisma merdeka_pin_image.jpg',
                'purchase_price_tokens' => 25,
                'updateDateTime' => now(),
            ],
            [
                'mapDataID' => 'ppns_ys',
                'title' => 'PPNS YS',
                'description' => '3D model of PPNS area.',
                'xAxis' => 116.082466,
                'yAxis' => 5.992839,
                '3dTiles' => 'https://3dhub.geosabah.my/3dmodel/ppns_ys/tileset.json',
                'thumbNailUrl' => '/assets/img/front-pages/locations/ppns ys_pin_image.jpg',
                'purchase_price_tokens' => 30,
                'updateDateTime' => now(),
            ],
        ];

        foreach ($locations as $loc) {
            MapData::updateOrCreate(['mapDataID' => $loc['mapDataID']], $loc);
        }
    }
}
