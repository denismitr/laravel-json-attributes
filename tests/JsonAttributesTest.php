<?php


namespace Denismitr\JsonAttributes\Tests;


use Illuminate\Support\Collection;

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
    
    /** @test */
    public function it_returns_default_value_if_attribute_does_not_exist()
    {
        $this->assertEquals(
            'default',
            $this->record->json_data->get('non_existent', 'default')
        );
    }
    
    /** @test */
    public function it_sets_json_attribute()
    {
        $this->record->json_data->username = 'some username';
        
        $this->assertEquals('some username', $this->record->json_data->username);
    }
    
    /** @test */
    public function it_can_determine_if_it_has_an_attribute()
    {
        $this->assertFalse($this->record->json_data->has('username'));

        $this->record->json_data->username = 'some username';

        $this->assertTrue($this->record->json_data->has('username'));
    }
    
    /** @test */
    public function json_attributes_get_saved_with_the_model()
    {
        $this->record->json_data->username = 'foo';

        $this->record->save();

        $this->assertEquals('foo', $this->record->refresh()->json_data->username);
    }
    
    /** @test */
    public function it_can_set_whole_array()
    {
        $array = [
            'one' => 'value',
            'two' => 'another value',
        ];

        $this->record->json_data->array = $array;

        $this->assertEquals($array, $this->record->json_data->array);
    }
    
    /** @test */
    public function it_can_get_values_using_dot_notation()
    {
        $this->record->json_data->settings = ['connection' => 'mysql'];
        $this->record->json_data->colors = ['navbar' => 'dark'];

        $this->assertEquals('mysql', $this->record->json_data->get('settings.connection'));
        $this->assertEquals('dark', $this->record->json_data->get('colors.navbar'));
    }
    
    /** @test */
    public function it_can_set_values_using_dot_notation()
    {
        $this->record->json_data->set('settings.connection', 'mysql');
        $this->record->json_data->set('colors.navbar', 'dark');

        $this->assertEquals('mysql', $this->record->json_data->get('settings.connection'));
        $this->assertEquals('dark', $this->record->json_data->get('colors.navbar'));
    }
    
    /** @test */
    public function it_can_set_all_json_attributes_at_once()
    {
        $data = [
            'settings' => ['connection' => 'mysql'],
            'colors' => ['navbar' => 'dark']
        ];

        $this->record->json_data = $data;
        $this->assertEquals($data, $this->record->json_data->all());
    }

    /** @test */
    public function it_can_forget_a_single_json_attribute()
    {
        $this->record->json_data->name = 'value';
        $this->assertEquals('value', $this->record->json_data->name);

        $this->record->json_data->forget('name');
        $this->assertNull($this->record->json_data->name);
    }

    /** @test */
    public function it_can_forget_a_json_attribute_using_dot_notation()
    {
        $this->record->json_data->member = ['name' => 'John', 'age' => 30];

        $this->record->json_data->forget('member.age');

        $this->assertEquals($this->record->json_data->member, ['name' => 'John']);
    }

    /** @test */
    public function it_can_get_all_json_attributes()
    {
        $this->record->json_data = ['name' => 'value'];
        $this->assertEquals(['name' => 'value'], $this->record->json_data->all());
    }

    /** @test */
    public function it_will_use_the_correct_data_types()
    {
        $this->record->json_data->boolean = true;
        $this->record->json_data->float = 22.65;

        $this->record->save();
        $this->record->fresh();

        $this->assertSame(true, $this->record->json_data->boolean);
        $this->assertSame(22.65, $this->record->json_data->float);
    }

    /** @test */
    public function it_can_be_handled_as_an_array()
    {
        $this->record->json_data['name'] = 'value';

        $this->assertEquals('value', $this->record->json_data['name']);
        $this->assertTrue(isset($this->record->json_data['name']));

        unset($this->record->json_data['name']);

        $this->assertFalse(isset($this->record->json_data['name']));
        $this->assertNull($this->record->json_data['name']);
    }

    /** @test */
    public function it_can_be_counted()
    {
        $this->assertCount(0, $this->record->json_data);

        $this->record->json_data->name = 'value';

        $this->assertCount(1, $this->record->json_data);
    }

    /** @test */
    public function it_can_be_used_as_an_arrayable()
    {
        $this->record->json_data->name = 'value';

        $this->assertEquals(
            $this->record->json_data->toArray(),
            $this->record->json_data->all()
        );
    }

    /** @test */
    public function it_can_add_and_save_json_attributes_in_one_go()
    {
        $array = [
            'name' => 'value',
            'name2' => 'value2',
        ];

        $record = Record::create(['json_data' => $array]);

        $this->assertEquals($array, $record->json_data->all());
    }

    /** @test */
    public function it_can_set_multiple_attributes_one_after_the_other()
    {
        $this->record->json_data->name = 'value';
        $this->record->json_data->name2 = 'value2';

        $this->assertEquals([
            'name' => 'value',
            'name2' => 'value2',
        ], $this->record->json_data->all());
    }

    /** @test */
    public function it_has_a_scope_to_get_models_with_the_given_json_attributes()
    {
        Record::truncate();

        $recordA = Record::create(['json_data' => [
            'name' => 'value',
            'name2' => 'value2',
        ]]);

        $recordB = Record::create(['json_data' => [
            'name' => 'value',
            'name2' => 'value2',
        ]]);

        $recordC = Record::create(['json_data' => [
            'name' => 'value',
            'name2' => 'value3',
        ]]);

        $this->assertContainsModels(
            [$recordA, $recordB],
            Record::withJsonData(['name' => 'value', 'name2' => 'value2'])->get()
        );

        $this->assertContainsModels(
            [$recordC],
            Record::withJsonData(['name' => 'value', 'name2' => 'value3'])->get()
        );

        $this->assertContainsModels(
            [$recordA, $recordB, $recordC],
            Record::withJsonData(['name' => 'value'])->get()
        );

        $this->assertContainsModels(
            [], Record::withJsonData(['name' => 'non-existent'])->get()
        );
    }

    /** @test */
    public function it_uses_scope_to_get_models_with_the_nested_json_attributes()
    {
        Record::truncate();

        $recordA = Record::create(['json_data' => [
            'company' => 'Ecommelite',
            'user' => [
                'name' => 'Denis',
                'job_title' => 'developer'
            ]
        ]]);

        $recordB = Record::create(['json_data' => [
            'company' => 'Ecommelite',
            'user' => [
                'name' => 'Tom',
                'job_title' => 'developer'
            ]
        ]]);

        $recordC = Record::create(['json_data' => [
            'company' => 'Ecommelite',
            'address' => [
                'street' => '1st Street',
                'phone' => 1234556
            ]
        ]]);

        $this->assertContainsModels(
            [$recordA],
            Record::withJsonData(['user.name' => 'Denis'])->get()
        );

        $this->assertContainsModels(
            [$recordA, $recordB],
            Record::withJsonData(['user.job_title' => 'developer'])->get()
        );

        $this->assertContainsModels(
            [$recordA, $recordB],
            Record::withJsonData(['user.job_title' => 'developer', 'company' => 'Ecommelite'])->get()
        );

        $this->assertContainsModels(
            [$recordA],
            Record::withJsonData([
                'user.job_title' => 'developer',
                'company' => 'Ecommelite',
                'user.name' => 'Denis'
            ])->get()
        );

        $this->assertContainsModels(
            [$recordA, $recordB],
            Record::withJsonData('user.job_title', 'developer')->get()
        );

        $this->assertContainsModels(
            [], Record::withJsonData(['non.existent' => 'record'])->get()
        );

        $this->assertContainsModels(
            [], Record::withJsonData(['name' => 'non-existent'])->get()
        );
    }

    protected function assertContainsModels(array $expectedModels, Collection $actualModels)
    {
        $assertionFailedMessage = vsprintf('Expected %d models. Got %d models', [
            count($expectedModels),
            $actualModels->count()
        ]);

        $this->assertCount(count($expectedModels), $actualModels, $assertionFailedMessage);
    }
}