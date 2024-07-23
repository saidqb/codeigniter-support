<?php

namespace Saidqb\CodeigniterSupport\Concerns;

use Config\Api;
use Saidqb\CorePhp\Lib\Str;


trait HasDb
{
    protected $db;

    protected $collect_lines;

    protected function initDb()
    {
        if ($this->currentControllerExistArray(Api::$sqlMode['enable_in'])) {
            $this->sqlEnableMode();
        }
    }

    protected function sqlEnableMode()
    {
        foreach (Api::$sqlMode['enable'] as $mode) {
            $this->db->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'$mode',''));");
        }
    }

    protected function getSqlMode()
    {
        $this->db->query("SELECT @@SESSION.sql_mode;");
        return $this;
    }

    protected function importSqlFile($dirFile)
    {
        $templine = '';
        $this->collect_lines = file($dirFile);
        $this->sqlQueryMap();
    }


    protected function runSqlString($string)
    {
        $this->collect_lines = preg_split("/\r\n|\n|\r/", $string);
        $this->sqlQueryMap();
    }

    protected function sqlQueryMap()
    {
        $templine = '';

        foreach ($this->collect_lines as $line) {
            if (substr($line, 0, 2) == '--' || $line == '')
                continue;

            $templine .= $line;

            // If it has a semicolon at the end, it's the end of the query so can process this templine
            if (substr(trim($line), -1, 1) == ';') {
                $this->db->query($templine);
                $templine = '';
            }
        }
    }

    protected function selectAs($table, $prefix)
    {
        $field = $this->db->getFieldNames($table);
        $select = [];
        foreach ($field as $value) {
            $new_name = Str::removePrefix($value, $prefix);
            $select[] = $value . ' as ' . $new_name;
        }
        return $select;
    }

    protected function selectArrayAs($table, $prefix)
    {
        $field = $this->db->getFieldNames($table);
        $select = [];
        foreach ($field as $value) {
            $new_name = Str::removePrefix($value, $prefix);
            $select[$value] = $new_name;
        }
        return $select;
    }
}
