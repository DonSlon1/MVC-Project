<?php

namespace Core\ORM;

use Core\ORM\Exceptions\AttributeNotFound;
class Entity
{
    public array $attributes;

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * @param string $name
     * @return  mixed
     * @throws AttributeNotFound
     */
    public function get(string $name): array
    {
        if (!isset($this->attributes[$name])) {
            throw new AttributeNotFound("Attribute {$name} not found");
        }
        return $this->attributes[$name];
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return  void
     * @throws AttributeNotFound
     */
    public function set(string $name, mixed $value): void
    {
        if (array_key_exists($name, $this->attributes)){
            throw new AttributeNotFound("Attribute {$name} not found");
        }
        $this->attributes[$name] = $value;
    }

    public function save(): void
    {

    }
}