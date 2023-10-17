<?php

namespace Core\ORM;

use Core\ORM\Entities\Entity;

class QueryBuilder
{
    private array $params = [];

    public function limit(?int $limit, ?int $offset): self
    {
        if ($limit !== null) {
            $this->params['limit'] = $limit;
        }
        if ($offset !== null) {
            $this->params['offset'] = $offset;
        }
        return $this;
    }

    /**
     * @todo change how works conditions
     * @param array $conditions
     * @return $this
     */
    public function where(array $conditions): self
    {
        $this->params['where'] = $conditions;
        return $this;
    }

    public function orderBy(array $conditions): self
    {
        $this->params['orderBy'] = $conditions;
        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function select(array $columns): self
    {
        $this->params['select'] = $columns;
        return $this;
    }

    public function from(string $table):self
    {
        $table = strtolower($table);
        $this->params['from'] = $table;
        return $this;
    }

    public function update(Entity $entity):string
    {
        $sql = 'UPDATE ' . $entity->getDbEntityName() . ' SET ';
        $sql .= implode(' , ', array_map(fn($key) => "$key = :$key", $entity->getEntityMap()));
        $sql .= ' WHERE id = :id';
        return $sql;

    }
    public function insert(Entity $entity):string
    {
        $sql = 'INSERT INTO ' . $entity->getDbEntityName() . ' (';
        $sql .= implode(', ', $entity->getEntityMap());
        $sql .= ') VALUES (';
        $sql .= implode(' , ', array_map(fn($key) => " :$key ", $entity->getEntityMap()));
        $sql .= ')';
        return $sql;
    }
    public function build():string
    {
        $sql = '';
        if (isset($this->params['select'])) {
            $sql .= 'SELECT ' . implode(', ', $this->params['select']);
        }else{
            $sql .= 'SELECT *';
        }

        if (isset($this->params['from'])) {
            $sql .= ' FROM ' . $this->params['from'];
        }

        if (isset($this->params['where'])) {

            $sql .= ' WHERE ';
            $sql .= implode(' AND ', array_map(fn($key, $value) => "$key = '$value'", array_keys($this->params['where']), array_values($this->params['where'])));
        }

        if (isset($this->params['orderBy'])) {
            $sql .= ' ORDER BY ' . implode(', ', $this->params['orderBy']);
        }

        if (isset($this->params['limit'])) {
            $sql .= ' LIMIT ' . $this->params['limit'];
        }

        if (isset($this->params['offset'])) {
            $sql .= ' OFFSET ' . $this->params['offset'];
        }
        return $sql;
    }



}