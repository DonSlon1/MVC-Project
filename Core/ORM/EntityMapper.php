<?php

namespace Core\ORM;

use Core\ORM\Entities\Entity;
class EntityMapper
{
    public static function map(Entity $entity): array
    {
        $fields = [];

        foreach ($entity->getAttributesMap() as $field) {
            $fields[] = self::getColumnName($field['columnName']);
        }
        return $fields;
    }

    private static function getColumnName(string $field): string
    {
        return lcfirst($field);
    }
}