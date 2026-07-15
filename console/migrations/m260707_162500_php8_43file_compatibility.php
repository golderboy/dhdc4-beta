<?php

use yii\db\Migration;

class m260707_162500_php8_43file_compatibility extends Migration
{
    public function safeUp()
    {
        $database = $this->db->createCommand('SELECT DATABASE()')->queryScalar();
        if ($database) {
            $this->execute(
                'ALTER DATABASE ' . $this->db->quoteTableName($database) .
                ' CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci'
            );
        }

        $columns = [
            ['anc', 'WEIGHT', 'decimal(5,1) DEFAULT NULL AFTER `CID`'],
            ['dhdc_tmp_anc', 'WEIGHT', 'decimal(5,1) DEFAULT NULL AFTER `CID`'],
            ['drugallergy', 'PROVIDER', 'varchar(15) DEFAULT NULL AFTER `D_UPDATE`'],
            ['dhdc_tmp_drugallergy', 'PROVIDER', 'varchar(15) DEFAULT NULL AFTER `D_UPDATE`'],
            ['labfu', 'PROVIDER', 'varchar(15) DEFAULT NULL AFTER `CID`'],
            ['dhdc_tmp_labfu', 'PROVIDER', 'varchar(15) DEFAULT NULL AFTER `CID`'],
            ['newborn', 'LENGTH', 'decimal(5,1) DEFAULT NULL AFTER `CID`'],
            ['newborn', 'HEADCIRCUM', 'decimal(5,1) DEFAULT NULL AFTER `LENGTH`'],
            ['dhdc_tmp_newborn', 'LENGTH', 'decimal(5,1) DEFAULT NULL AFTER `CID`'],
            ['dhdc_tmp_newborn', 'HEADCIRCUM', 'decimal(5,1) DEFAULT NULL AFTER `LENGTH`'],
            ['prenatal', 'PROVIDER', 'varchar(15) DEFAULT NULL AFTER `D_UPDATE`'],
            ['prenatal', 'HEIGHT', 'int(3) DEFAULT NULL AFTER `CID`'],
            ['dhdc_tmp_prenatal', 'PROVIDER', 'varchar(15) DEFAULT NULL AFTER `D_UPDATE`'],
            ['dhdc_tmp_prenatal', 'HEIGHT', 'int(3) DEFAULT NULL AFTER `CID`'],
            ['service', 'HSUB', 'varchar(5) DEFAULT NULL AFTER `D_UPDATE`'],
            ['dhdc_tmp_service', 'HSUB', 'varchar(5) DEFAULT NULL AFTER `D_UPDATE`'],
            ['dhdc_tmp_chronicfu', 'CHRONICFUPLACE', 'varchar(5) DEFAULT NULL AFTER `D_UPDATE`'],
        ];

        foreach ($columns as $column) {
            $this->addColumnIfMissing($column[0], $column[1], $column[2]);
        }
    }

    public function safeDown()
    {
        echo "m260707_162500_php8_43file_compatibility is not reversible without risking imported 43-file data.\n";
        return false;
    }

    private function addColumnIfMissing($table, $column, $definition)
    {
        $schema = $this->db->getTableSchema($table, true);
        if ($schema === null) {
            echo "Table {$table} does not exist, skipping {$column}.\n";
            return;
        }

        if ($schema->getColumn($column) !== null) {
            return;
        }

        $this->execute(
            'ALTER TABLE ' . $this->db->quoteTableName($table) .
            ' ADD COLUMN ' . $this->db->quoteColumnName($column) . ' ' . $definition
        );
        $this->db->schema->refreshTableSchema($table);
    }
}
