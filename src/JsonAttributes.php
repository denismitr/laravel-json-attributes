<?php


namespace Denismitr\JsonAttributes;

use ArrayAccess;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class JsonAttributes implements ArrayAccess, Countable, Arrayable
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var string
     */
    protected $attributeName;

    /**
     * @var array
     */
    protected $jsonAttributes = [];

    /**
     * JsonAttributes constructor.
     * @param Model $model
     * @param string $attributeName
     */
    public function __construct(Model $model, string $attributeName)
    {
        $this->model = $model;
        $this->attributeName = $attributeName;
        $this->jsonAttributes = $this->getDecodedAttributes();
    }

    /**
     * @param Model $model
     * @param string $attributeName
     * @return JsonAttributes
     */
    public static function create(Model $model, string $attributeName): self
    {
        return new static($model, $attributeName);
    }

    /**
     * @param string $attribute
     * @return mixed
     */
    public function __get(string $attribute)
    {
        return $this->get($attribute);
    }

    /**
     * @param string $attribute
     * @param null $default
     * @return mixed
     */
    public function get(string $attribute, $default = null)
    {
        return array_get($this->jsonAttributes, $attribute, $default);
    }

    /**
     * @param string $attribute
     * @param $value
     */
    public function __set(string $attribute, $value)
    {
        $this->set($attribute, $value);
    }

    /**
     * @param string $attribute
     * @param $value
     */
    public function set(string $attribute, $value)
    {
        array_set($this->jsonAttributes, $attribute, $value);

        $this->model->{$this->attributeName} = $this->jsonAttributes;
    }

    /**
     * @param string $attribute
     * @return bool
     */
    public function has(string $attribute): bool
    {
        return array_has($this->jsonAttributes, $attribute);
    }

    /**
     * @param string $attribute
     * @return JsonAttributes
     */
    public function forget(string $attribute): self
    {
        $this->model->{$this->attributeName} = array_except($this->jsonAttributes, $attribute);

        return $this;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->getDecodedAttributes();
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        $this->forget($offset);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->jsonAttributes);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->all();
    }

    /**
     * @param string $attributeName
     * @return Builder
     */
    public static function scopeWithJsonAttributes(string $attributeName): Builder
    {
        $arguments = debug_backtrace()[1]['args'];

        // only builder
        if (count($arguments) === 1) {
            list($builder) = $arguments;
            $jsonAttributes = [];
        }

        // builder, ['key' => 'value']
        if (count($arguments) === 2) {
            list($builder, $jsonAttributes) = $arguments;
        }

        // builder, key, value
        if (count($arguments) >= 3) {
            list($builder, $name, $value) = $arguments;
            $jsonAttributes = [$name => $value];
        }

        foreach ($jsonAttributes as $name => $value) {
            // substitute dot notation with ->
            $name = static::normalizePropertyName($name);

            $builder->where("{$attributeName}->{$name}", $value);
        }

        return $builder;
    }

    /**
     * @return array
     */
    protected function getDecodedAttributes(): array
    {
        return json_decode($this->model->getAttributes()[$this->attributeName] ?? '{}', true);
    }

    /**
     * substitutes dot notation with ->
     *
     * e.g user.name becomes user->name
     *
     * @param string $name
     * @return string
     */
    protected static function normalizePropertyName($name)
    {
        return str_replace('.', '->', $name);
    }
}