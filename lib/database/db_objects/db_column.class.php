<?php
Direct_Call::isDirectCall();
class DB_Column
{
    protected $column_name,
              $ordinal_position,
              $column_type,
              $is_nullable,
              $column_default;
    
    
    public function __set($name, $value)
    {
        throw new Database_Exception("DB:Unsupported Column Attribute");
    }
    
    public function __construct($name,$position,$type,$nullable,$default)
    {
        $this->column_name = isset($name) ? trim($name) : null;
        $this->ordinal_position = isset($position) ? (int) $position : null;
        $this->column_type = isset($type) ? trim($type) : null;
        $this->column_default = isset($default) ? trim($default) : null;
    }
    
    public function __get($name)
    {
        return $this->$name;
    }
}
