<?php
require_once('config.inc');

$dbObj = DB_Factory::DB('127.0.0.1', 'root', 'h1k9o8j2', 'sakila', '3306');

if ($dbObj->connect()) {
    echo "SELECT RESULT <pre>";
    print_r($dbObj->select('customer'));
    echo "</pre>";
    echo "Took " . $dbObj->getQueryExecutionTime() . "  Seconds (Memory-" . $dbObj->getQueryExecutionMemory() . ")";
}
    