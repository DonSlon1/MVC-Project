<?php

namespace Core\ORM\Entities;

use Core\Utils\ClassFinder;
use Exception;

readonly class EntityFactory
{
    public function __construct(
        private EntityDefs  $entityDefs,
        private ClassFinder $classFinder
    )
    {
    }

    /**
     * @throws Exception
     */
    public function create(string $entityName): Entity
    {
        $entityDefs = $this->entityDefs->getEntityDefs($entityName);
        $entityClass = $this->classFinder->findClass($entityName, 'Entities');
        if (!class_exists($entityClass)) {
            throw new Exception('Třída pro entitu ' . $entityClass . ' neexistuje.');
        }
        echo 'Vytvářím instanci entity ' . $entityClass . PHP_EOL;
        return new $entityClass($entityDefs["columns"], $entityDefs["relations"]);
    }
}