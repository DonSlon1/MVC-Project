<?php

namespace Core\ORM\Entities;

use Core\ORM\Database;
use Core\Utils\Config\Manager as ConfigManager;
use Core\Utils\File\Manager as FileManager;
use Exception;

class EntityDefs
{

    public function __construct(
        private readonly ConfigManager $configManager,
        private readonly FileManager $fileManager,
        private readonly Database $db,
    )
    {
    }

    /**
     * @throws Exception
     */
    public function getEntityDefs(string $entityType): array
    {
        $rootDir = $this->configManager->get('rootDir');
        // todo impelement file finder
        $entityDefsFile = $rootDir . '/App/Resources/entityDef/' . $entityType . '.json';
        if (!file_exists($entityDefsFile)) {
            $this->createEntityDefs($entityType);
        }
        return $this->fileManager->getJsonContents($entityDefsFile);
    }

    /**
     * @throws Exception
     */
    public function createEntityDefs(string $entityType): void
    {
        $sql = "
            SELECT
                `TABLE_NAME` as 'tableName',                            -- Foreign key table
                `COLUMN_NAME` as 'columnName',                           -- Foreign key column
                `REFERENCED_TABLE_NAME` as 'referencedTableName',                 -- Origin key table
                `REFERENCED_COLUMN_NAME` as 'referencedColumnName'               -- Origin key column
            FROM
                `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE`  -- Will fail if user don't have privilege
            WHERE
                `TABLE_SCHEMA` = SCHEMA()                -- Detect current schema in USE
            AND `REFERENCED_TABLE_NAME` IS NOT NULL
            AND (TABLE_NAME = '$entityType' OR REFERENCED_TABLE_NAME = '$entityType'); -- On
        ";
        $stm = $this->db->query($sql);
        $resoult = $this->db->fetch($stm);
        if (empty($resoult)) {
            throw new Exception('Table not found');
        }
        foreach ($resoult as $key => $value) {
            if ($value['tableName'] === $entityType) {
                $resoult[$key]['type'] = '1:n';
            } else {
                $resoult[$key]['type'] = 'n:1';
            }
        }
        $tableInfo = [
            'relations' => $resoult
        ];
        $sql = "
            SELECT
                COLUMN_NAME as 'columnName',
                DATA_TYPE as 'dataType',
                IS_NULLABLE as 'isNullable',
                COLUMN_TYPE as 'columnType',
                COLUMN_DEFAULT as 'columnDefault'
            FROM information_schema.COLUMNS WHERE TABLE_NAME = '$entityType';
        ";
        $stm = $this->db->query($sql);
        $columns = $this->db->fetch($stm);
        foreach ($columns as $key => $value) {
            if ($value['isNullable'] === 'NO') {
                $columns[$key]['isNullable'] = false;
            } else {
                $columns[$key]['isNullable'] = true;
            }

            if ($value['columnDefault'] === "NULL" || $value['columnDefault'] === null) {
                $columns[$key]['columnDefault'] = null;
            }
            elseif (is_numeric($value['columnDefault'])) {
                $columns[$key]['columnDefault'] = (int)$value['columnDefault'];
            }
            else {
                $columns[$key]['columnDefault'] = substr($value['columnDefault'], 1, -1);
            }
            foreach ($tableInfo['relations'] as $relation) {
                if ($relation['columnName'] === $value['columnName'] && $relation['tableName'] === $entityType) {
                    $columns[$key]['type'] = 'link';
                }
            }
        }
        $tableInfo['columns'] = $columns;
        $this->saveEntityDefs($entityType, $tableInfo);
    }

    private  function saveEntityDefs(string $entityType, array $entityDefs): void
    {
        $entityName = ucfirst($entityType);
        $this->fileManager->putJsonContents($this->configManager->get('rootDir')."/App/Resources/entityDef/$entityName.json", $entityDefs);
        $this->fileManager->putContents($this->configManager->get('rootDir')."/App/Entities/$entityName.php", "<?php
namespace App\Entities;

use Core\ORM\Entities\Entity;

class $entityName extends Entity 
{
    public const ENTITY_NAME = '$entityName';
}
    ");

    }
}