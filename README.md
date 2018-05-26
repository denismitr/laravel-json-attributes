# Laravel Json Attributes for Eloquent Models

## Author
Denis Mitrofanov<br>
[thecollection.ru](https://thecollection.ru)

### Requirements
PHP 7.1 or higher
MYSQL 5.7 or higher
or 
POSTGRES (the one I tested it on is 9.6) probably any 9.* version or higher will do

### Installation
```composer require denismitr/laravel-json-attributes```

### Usage
1. First add a `jsonData` column to your table
```php
Schema::create('orders', function (Blueprint $table) {
    $table->increments('id');
    $table->string('description');
    $table->jsonData('json_data');
});
```

Underneath `$table->jsonData('json_data');` is just `$table->json('json_data')->nullable()`.

Examples of usage:

```php
$array = [
    'supplier' => 'Boeing',
    'total_cost' => 245.99,
];

$record = Record::create(['json_data' => $array]);

$this->assertEquals($array, $record->json_data->all());
$this->assertEquals('Boeing', $record->json_data->supplier);
$this->assertEquals('Boeing', $record->json_data['supplier']);
``` 

Another examples with Laravel dot notation
```php
$this->record->json_data->member = ['name' => 'John', 'age' => 30];
$this->record->json_data->forget('member.age');
$this->assertEquals($this->record->json_data->member, ['name' => 'John']);
```

```php
$this->record->json_data->set('settings.connection', 'mysql');
$this->record->json_data->set('colors.navbar', 'dark');

$this->assertEquals('mysql', $this->record->json_data->get('settings.connection'));
$this->assertEquals('dark', $this->record->json_data->get('colors.navbar'));
```

You can assign a whole array
```php
$array = [
    'one' => 'value',
    'two' => 'another value',
];

$this->record->json_data->array = $array;

$this->assertEquals($array, $this->record->json_data->array);
```

#### To see more look at the tests.

### Eloquent Model

First of all you need to cast the *json attributes* to array
Here is how the `Record` model from test suite looks like:
<br>
```php
use Denismitr\JsonAttributes\JsonAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
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
```
<br>
Persistence works regularly just like with any other Eloquent model