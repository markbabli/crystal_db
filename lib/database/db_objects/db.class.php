<?php
Direct_Call::isDirectCall();
class DB
{
    protected $db_name,
              $db_tables;
              
    public function __set($name, $value)
    {
        throw new Database_Exception("DB: Invalid Database Attribute");
    }
    
    public function __get($name)
    {
        return $this->$name;
    }
    
    public function __construct($name)
    {
        $this->db_name = isset($name) ? trim ($name) : null;
    }
    
    public function addTable(DB_Table $table)
    {
        $this->db_tables[] = $table;
    }
}
