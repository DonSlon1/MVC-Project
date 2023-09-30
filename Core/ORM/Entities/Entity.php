<?php

namespace Core\ORM\Entities;

use Core\ORM\Exceptions\AttributeNotFound;

class Entity
{
    public const ENTITY_NAME = self::ENTITY_NAME;
    private array $attributes;
    private ?string $id;
    private ?bool $isNew = false;
    public function __construct(private readonly string $entityType, ?array $attributes = [])
    {
        if (isset($attributes['id'])) {
            $this->id = $attributes['id'];
            unset($attributes['id']);
        }else{
            $this->id = null;
        }
        $this->attributes = $attributes;
    }

    public function isNew(): bool
    {
        return $this->isNew;
    }

    public function setIsNew(bool $isNew): void
    {
        $this->isNew = $isNew;
    }
    /**
     * @param string $name
     * @return ?mixed
     * @throws AttributeNotFound
     */
    public function get(string $name): ?string
    {
        if (!isset($this->attributes[$name])) {
            return null;
        }
        return $this->attributes[$name];
    }
    public function getId(): ?string
    {
        return $this->id;
    }

    public function hasId(): bool
    {
        return $this->id !== null;
    }

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function has(string $name): bool
    {
        return isset($this->attributes[$name]);
    }
    /**
     * @param string $name
     * @param mixed $value
     * @return  void
     */
    public function set(string $name, mixed $value): void
    {
        if (array_key_exists($name, $this->attributes)) {
            $this->attributes[$name] = $value;
        } else {
            throw new AttributeNotFound("Attribute $name not found in entity " . static::ENTITY_NAME);
        }
    }

    /**
     * @param string $id
     * @return void
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }
    public function setMultiple(array $attributes): void
    {
        foreach ($attributes as $name => $value) {
            $this->set($name, $value);
        }
    }

    public function save(): void
    {

    }
}