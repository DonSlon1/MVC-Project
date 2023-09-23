<?php

namespace Core\ORM;

use Core\ORM\Exceptions\EntityNotFound;

class EntityRepository
{

    public function __construct(
        private readonly QueryBuilder $queryBuilder,
        private readonly Database $database
    )
    {
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
        return new Entity($entity);

    }


    public function getNewEntity(): Entity
    {
        return new Entity();
    }

    private  function getTableInfo(): array
    {
        $sql = "DESCRIBE {$this->queryBuilder->getParams()['from']}";
        $stm = $this->database->query($sql);
        return $this->database->fetch($stm);
    }
    public function from(string $table): self
    {
        $this->queryBuilder->from($table);
        return $this;
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