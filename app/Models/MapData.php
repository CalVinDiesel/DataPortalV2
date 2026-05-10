<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MapData extends Model
{
    protected $table = 'map_data';
    protected $primaryKey = 'mapDataID';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'mapDataID',
        'title',
        'description',
        'xAxis',
        'yAxis',
        '3dTiles',
        'thumbNailUrl',
        'purchase_price_tokens',
        'updateDateTime'
    ];
}

