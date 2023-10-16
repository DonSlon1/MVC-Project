<?php

namespace Core\ORM;

use Core\ORM\Entities\Entity;
use Core\ORM\Exceptions\EntityNotFound;
use Core\Utils\Config\Manager as ConfigManager;
use Core\ORM\Entities\EntityFactory;
use Di\Container as DiContainer;
class RDBRepository
{

    private readonly Database $database;
    private readonly ConfigManager $configManager;
    private readonly EntityFactory $entityFactory;
    private readonly DiContainer $container;
    private readonly QueryBuilder $queryBuilder;
    public function __construct(private readonly string $entityType)
    {
        $this->container = new DiContainer();
        $this->configManager = new ConfigManager();
        $this->database = new Database($this->configManager);
        $this->queryBuilder = new QueryBuilder();
        $this->queryBuilder->from($this->entityType);
        $this->entityFactory = $this->container->get(EntityFactory::class);
    }
    /*public function find(): Entity
    {

        $sql = $this->queryBuilder->build();

        $entity = new Entity();

    }*/
    public function findOne(): Entity
    {
        $this->limit(1, 0);
        $sql = $this->queryBuilder->build();
        $stm = $this->database->query($sql);
        $response = $this->database->fetch($stm);
        if (count($response) === 0) {
            throw new EntityNotFound("Entity not found");
        }
        $entity = $response[0];
        return $this->entityFactory->create($this->entityType);
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

    public function getEntityById(string $id): Entity
    {
        $this->where(['id' => $id]);
        $this->limit(1, 0);

        $sql = $this->queryBuilder->build();
        $stm = $this->database->query($sql);
        $response = $this->database->fetch($stm);
        if (count($response) === 0) {
            throw new EntityNotFound("Entity not found");
        }
        return new Entity($this->entityType,$response[0]);
    }

    public function saveEntity(Entity $entity, array $options = []): Entity
    {
        if ($entity->isNew()) {
            $this->insert($entity, $options);
        } else {
            $this->update($entity, $options);
        }

        return $entity;
    }

    private function insert(Entity $entity, array $options = []): void
    {
        $sql = $this->queryBuilder->insert($entity);
        $id = $this->database->insertQuery($sql);
        $entity->setId($id);
        $entity->setIsNew(false);
    }

    private function update(Entity $entity, array $options = []): void
    {
        $sql = $this->queryBuilder->update($entity);
        $params = $entity->getAttributes();
        $params['id'] = $entity->getId();
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