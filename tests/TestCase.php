<?php


namespace Denismitr\JsonAttributes\Tests;

use Denismitr\JsonAttributes\JsonAttributesServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            JsonAttributesServiceProvider::class,
        ];
    }

    protected function setUpDatabase()
    {
        Schema::dropIfExists('records');

        Schema::create('records', function (Blueprint $table) {
            $table->increments('id');
            $table->jsonData();
        });
    }
}