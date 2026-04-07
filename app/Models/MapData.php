<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MapData extends Model
{
    protected $table = 'MapData';
    protected $primaryKey = 'mapDataID';
    public $incrementing = false;
    protected $keyType = 'string';

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

