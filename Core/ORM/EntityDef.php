<?php

namespace Core\ORM;

use Core\Utils\Config\Manager as ConfigManager;

class EntityDef
{
    public string $table;

    private readonly Database $database;
    private readonly ConfigManager $configManager;

    public function __construct(string $table)
    {
        $this->configManager = new ConfigManager();
        $this->database = new Database($this->configManager);
        $this->table = $table;
    }

    private function getFields(): array
    {

        $sql = 'SELECT * FROM information_schema.columns WHERE table_name = \''. $this->table. '\'';
        $statement = $this->database->query($sql);

        $fields = [];

        while ($row = $statement->fetch()) {
            $fields[] = (object)[
                'COLUMN_NAME'=>$row['COLUMN_NAME'],
                'DATA_TYPE'=>$row['DATA_TYPE'],
                'IS_NULLABLE'=>$row['IS_NULLABLE'] === 'NO'
            ];
        }

        return $fields;
    }

    private function getLinks(): array
    {
        $sql = 'SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE table_name = \''. $this->table .'\' AND referenced_table_name IS NOT NULL';
        $statement = $this->database->query($sql);

        $links = [];

        while ($row = $statement->fetch()) {
            $links[] = [
                'COLUMN_NAME'=>$row['COLUMN_NAME'],
                'REFERENCED_COLUMN_NAME'=>$row['REFERENCED_COLUMN_NAME'],
                'CONSTRAINT_TYPE'=>$row['CONSTRAINT_TYPE'] === 'FOREIGN KEY' ? 'belongsTo' : 'hasMany',
                'REFERENCED_TABLE_NAME'=>$row['REFERENCED_TABLE_NAME']
            ];
        }

        return $links;
    }

    public function getTableInfo(): array
    {
        return [
            'fields' => $this->getFields(),
            'links' => $this->getLinks()
        ];
    }
}