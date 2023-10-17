<?php

namespace Core\ORM;

use Core\ORM\Entities\Entity;
use Core\ORM\Exceptions\EntityNotFound;
use Core\Utils\Config\Manager as ConfigManager;
use Core\ORM\Entities\EntityFactory;
use Core\Utils\Log;
use Di\Container as DiContainer;
use Exception;

class RDBRepository
{

    private readonly Database $database;
    private readonly ConfigManager $configManager;

    private readonly Log $log;
    private readonly EntityFactory $entityFactory;
    private readonly DiContainer $container;
    private readonly QueryBuilder $queryBuilder;
    private readonly string $entityType;
    public function __construct(string $entityType)
    {
        $this->entityType = $entityType;
        $this->container = new DiContainer();
        $this->configManager = new ConfigManager();
        $this->database = $this->container->get(Database::class);
        $this->log = $this->container->get(Log::class);
        $this->queryBuilder = new QueryBuilder();
        $this->queryBuilder->from($this->entityType);
        $this->entityFactory = $this->container->get(EntityFactory::class);
    }
    /*public function find(): Entity
    {

        $sql = $this->queryBuilder->build();

        $entity = new Entity();

    }*/
    public function findOne(): ?Entity
    {
        $this->limit(1, 0);
        $sql = $this->queryBuilder->build();
        $stm = $this->database->query($sql);
        $response = $this->database->fetch($stm);
        if (count($response) === 0) {
            return null;
        }
        $data = $response[0];
        try {
            $entity = $this->entityFactory->create($this->entityType);
        } catch (Exception $e) {
            $this->log->error($e->getMessage());
            return null;
        }
        $entity->setMultiple($data);
        return $entity;
    }


    public function getNewEntity(): Entity
    {
        $entity = $this->entityFactory->create($this->entityType);
        $entity->setIsNew(true);
        return $entity;
    }

    public function createEntity($data = [], array $options = []): Entity
    {
        $entity = $this->getNewEntity();
        $entity->setIsNew(true);
        $entity->setMultiple($data);
        $this->saveEntity($entity, $options);

        return $entity;
    }

    private function getEntity(string $entityType, ?string $id = null): Entity
    {
        $entity = new $entityType();
        if ($id !== null) {
            $entity->set('id', $id);
        }
        return $entity;
    }

    public function getEntityById(string $id): ?Entity
    {
        $this->where(['id' => $id]);
        $this->limit(1, 0);

        $sql = $this->queryBuilder->build();
        $stm = $this->database->query($sql);
        $response = $this->database->fetch($stm);
        if (count($response) === 0) {
            throw new EntityNotFound("Entity not found");
        }
        try {
            $entity = $this->entityFactory->create($this->entityType);
        }catch (Exception $e){
            $this->log->error($e->getMessage());
            return null;
        }
        $entity->setMultiple($response[0]);
        return $entity;
    }

    public function saveEntity(Entity $entity, array $options = []): void
    {
        if ($entity->isNew()) {
            $this->insert($entity, $options);
        } else {
            $this->update($entity, $options);
        }
    }

    private function insert(Entity $entity, array $options = []): void
    {
        $sql = $this->queryBuilder->insert($entity);
        $params = [];
        foreach ($entity->getEntityMap() as $field) {
            $params[$field] = $entity->get($field);
        }
        $id = $this->database->insertQuery($sql, $params);
        $entity->setId($id);
        $entity->setIsNew(false);
    }

    private function update(Entity $entity, array $options = []): void
    {
        $sql = $this->queryBuilder->update($entity);
        $params = [];
        foreach ($entity->getEntityMap() as $field) {
            $params[$field] = $entity->get($field);
        }
        $this->database->query($sql, $params);
    }
    public function select(array $columns): self
    {
        $this->queryBuilder->select($columns);
        return $this;
    }
    public function orderBy(array $conditions): self
    {
        $this->queryBuilder->orderBy($conditions);
        return $this;
    }
    public function where(array $conditions): self
    {
        $this->queryBuilder->where($conditions);
        return $this;
    }
    public function limit(?int $limit, ?int $offset): self
    {
        $this->queryBuilder->limit($limit, $offset);
        return $this;
    }
}