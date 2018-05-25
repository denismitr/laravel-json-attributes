<?php


namespace Denismitr\JsonAttributes\Tests;


use Denismitr\JsonAttributes\JsonAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    public $timestamps = false;

    public $guarded = [];

    protected $casts = ['json_data' => 'array'];

    /**
     * @return JsonAttributes
     */
    public function getJsonDataAttribute(): JsonAttributes
    {
        return JsonAttributes::create($this, 'json_data');
    }

    /**
     * @return Builder
     */
    public function scopeWithJsonData(): Builder
    {
        return JsonAttributes::scopeWithJsonAttributes('json_data');
    }
}