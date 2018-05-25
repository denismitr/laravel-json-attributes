<?php


namespace Denismitr\JsonAttributes\Tests;


class JsonAttributesTest extends TestCase
{
    private $record;

    public function setUp()
    {
        parent::setUp();

        $this->record = Record::create();
    }

    /** @test */
    public function it_returns_null_if_attribute_does_not_exist()
    {
        $this->assertNull($this->record->json_data->non_existent);
    }
}