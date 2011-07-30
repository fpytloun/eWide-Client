<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * eWide Client
 *
 * @copyright  Copyright (c) 2010 eWide
 * @package    eWide Client
 */

/**
 * Export model
 *
 * @author     eWide
 * @package    eWide Client
 */

class ExportModel
{
    private static $tables = array( 'clients',
                             'clients_groups',
                             'clients_orders',
                             'clients_persons',
                             'clients_tags',
                             'clients_visits',
                             'clients_visits_types',
                             #'settings',
                             #'users',
                             #'users_settings'
                             );

    public static function exportSql()
    {
        foreach (self::$tables as $key => $table) {
            $dump[] = "charset utf8;";
            $dump[] = "---";
            $dump[] = "--- Dump for table $table";
            $dump[] = "---";
            $table_query = dibi::query("SELECT * FROM `$table`");

            if ($table_query->rowCount > 0) {
                while ($row = $table_query->fetch()) {
                    $values = array();
                    foreach ($row as $key => $value) {
                        $values[] = "'".$value."'";
                    }
                    $dump[] = "INSERT INTO `$table` VALUES(".implode(',', $values).");";
                }
            }
        }

        return implode("\n", $dump);
    }
}

