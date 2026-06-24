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
        $locations = [
            [
                'mapDataID' => 'KK_OSPREY',
                'title' => 'KK OSPREY',
                'description' => '3D model of Kota Kinabalu area.',
                'xAxis' => 116.070466,
                'yAxis' => 5.957839,
                '3dTiles' => 'https://3dhub.geosabah.my/3dmodel/KK_OSPREY/tileset.json',
                'thumbNailUrl' => 'https://res.cloudinary.com/dssf0n6zc/image/upload/v1/3dhub/kkOsprey_pin_image.jpg',
                'purchase_price_tokens' => 10,
                'updateDateTime' => now(),
            ],
            [
                'mapDataID' => 'KB_3DTiles_Lite',
                'title' => 'KB 3DTiles Lite',
                'description' => '3D model of buildings in Kota Kinabalu.',
                'xAxis' => 116.430814,
                'yAxis' => 6.355872,
                '3dTiles' => 'https://3dhub.geosabah.my/3dmodel/Building_Planning/KB_3DTiles_Lite/tileset.json',
                'thumbNailUrl' => 'https://res.cloudinary.com/dssf0n6zc/image/upload/v1/3dhub/kb_3dtiles_lite_pin_image.jpg',
                'purchase_price_tokens' => 20,
                'updateDateTime' => now(),
            ],
            [
                'mapDataID' => 'fisheye_test_kolombong_18mac2025',
                'title' => 'Kolombong Fisheye Test',
                'description' => 'Fisheye test model in Kolombong area.',
                'xAxis' => 116.115222,
                'yAxis' => 5.982686,
                '3dTiles' => 'https://3dhub.geosabah.my/3dmodel/Building_Planning/fisheye_test_kolombong_18mac2025/tileset.json',
                'thumbNailUrl' => 'https://res.cloudinary.com/dssf0n6zc/image/upload/v1/3dhub/kolombong_pin_image.jpg',
                'purchase_price_tokens' => 15,
                'updateDateTime' => now(),
            ],
            [
                'mapDataID' => 'wismamerdeka',
                'title' => 'WISMA MERDEKA',
                'description' => '3D model of Wisma Merdeka complex.',
                'xAxis' => 116.075578,
                'yAxis' => 5.985513,
                '3dTiles' => 'https://3dhub.geosabah.my/3dmodel/wismamerdeka/tileset.json',
                'thumbNailUrl' => 'https://res.cloudinary.com/dssf0n6zc/image/upload/v1/3dhub/wisma_merdeka_pin_image.jpg',
                'purchase_price_tokens' => 25,
                'updateDateTime' => now(),
            ],
            [
                'mapDataID' => 'ppns_ys',
                'title' => 'PPNS YS',
                'description' => '3D model of PPNS area.',
                'xAxis' => 116.110547,
                'yAxis' => 6.015390,
                '3dTiles' => 'https://3dhub.geosabah.my/3dmodel/ppns_ys/tileset.json',
                'thumbNailUrl' => 'https://res.cloudinary.com/dssf0n6zc/image/upload/v1/3dhub/ppns_ys_pin_image.jpg',
                'purchase_price_tokens' => 30,
                'updateDateTime' => now(),
            ],
            [
                'mapDataID' => 'Keningau_Sabah_2026',
                'title' => 'KENINGAU SABAH',
                'description' => 'the city of Keningau Sabah',
                'xAxis' => 116.1649,
                'yAxis' => 5.3555,
                '3dTiles' => 'https://3dhub.geosabah.my/3dmodel/keningau_sabah_2026/tileset.json',
                'thumbNailUrl' => 'https://res.cloudinary.com/dssf0n6zc/image/upload/v1778685160/keningau_sabah_2026_pin_image_pvew6e.jpg',
                'purchase_price_tokens' => 25,
                'updateDateTime' => now(),
            ],
        ];

        foreach ($locations as $loc) {
            // v176: Use firstOrCreate so manual updates (Cloudinary links) are NOT overwritten by the seeder.
            MapData::firstOrCreate(['mapDataID' => $loc['mapDataID']], $loc);
        }
    }
}
