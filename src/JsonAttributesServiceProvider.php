<?php


namespace Denismitr\JsonAttributes;


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;

class JsonAttributesServiceProvider extends ServiceProvider
{
    public function register()
    {
        Blueprint::macro('jsonData', function (string $column = 'json_data') {
            return $this->json($column) ->nullable();
        });
    }
}