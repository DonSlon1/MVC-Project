<?php

namespace Core\ORM;

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
        $this->params['from'] = $table;
        return $this;
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
            $sql .= ' WHERE ' . implode(' AND ', $this->params['where']);
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