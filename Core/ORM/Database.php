<?php

namespace Core\ORM;

use Core\Utils\Config\Manager as ConfigManager;
use PDO;
use PDOStatement;
class Database
{
    private ?PDO $conn;
    public function __construct(
        private readonly ConfigManager $configManager,
    )
    {
        $this->conn = $this->connect();
    }

    private function connect(): PDO
    {
        $databaseConfig = $this->configManager->get('database');
        return new PDO(
            "mysql:host={$databaseConfig['host']};dbname={$databaseConfig['dbname']}",
            $databaseConfig['user'],
            $databaseConfig['password'],
        );
    }

    /**
     * prepare and execute sql query
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     */
    public function query(string $sql, array $params= []): PDOStatement
    {
        $stm = $this->conn->prepare($sql);
        $stm->execute($params);
        return $stm;
    }


    /**
     * Insert data to db and return entity id
     * @param string $sql
     * @param array $params
     * @return string
     */
    public function insertQuery(string $sql, array $params=[]): string
    {
        $this->query($sql, $params);
        return $this->conn->lastInsertId();
    }
    /**
     * Fetch date form database if not found return empty array
     * @param PDOStatement $stm
     * @param int $flags 2 = PDO::FETCH_ASSOC
     * @return array
     */
    public function fetch(PDOStatement $stm,int $flags = 2): array
    {
        $stm->setFetchMode($flags);
        $fetchData = $stm->fetchAll();
        if ($fetchData === false) {
            return [];
        }
        return $fetchData;
    }
}