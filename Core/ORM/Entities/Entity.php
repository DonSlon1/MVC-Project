<?php

namespace Core\ORM\Entities;

use Core\ORM\EntityMapper;
use Core\ORM\Exceptions\AttributeNotFound;

class Entity
{
    public const ENTITY_NAME = self::ENTITY_NAME;
    protected array $attributes = [];
    private ?string $id = null;
    private ?bool $isNew = false;
    public function __construct(protected array $attributesMap,  private readonly array $relations)
    {
        foreach ($this->attributesMap as $fieldDef) {
            if ($fieldDef["columnName"] !== 'id')
                $this->attributes[$fieldDef['columnName']] = $fieldDef['columnDefault'] ?? null;
        }
    }

    public function isNew(): bool
    {
        return $this->isNew;
    }

    public function getAttributesMap(): array
    {
        return $this->attributesMap;
    }
    public function getRelationsMap(): array
    {
        return $this->relations;
    }

    public function hasRelation(string $name): bool
    {
        return isset($this->relations[$name]);
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
        if (method_exists($this,'get'.ucfirst($name))) {
            return $this->{'get' . ucfirst($name)}();
        }
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

    public function getDbEntityName(): string
    {
       return strtolower(static::ENTITY_NAME);
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getEntityMap(): array
    {
       return EntityMapper::map($this);
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
        } elseif (property_exists($this, $name)) {
            $this->$name = $value;
        }
        else {
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