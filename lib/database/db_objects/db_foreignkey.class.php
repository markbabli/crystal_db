<?php
Direct_Call::isDirectCall();
class DB_ForeignKey
{
    protected $name,
              $table_name,
              $column_name,
              $ref_table,
              $ref_col;
    
    public function __set($name, $value)
    {
        throw new Database_Exception("ForeignKey:Invalid Attribute");
    }
    
    public function __get($name)
    {
        return $this->$name;
    }
    
    public function __construct($consName,$tableName,$columnName,
                                                    $refTable,$refColumn)
    {
        $this->name         = isset($consName)  ? trim( $consName ) : null;
        $this->table_name   = isset($tableName) ? trim($tableName)  : null;
        $this->column_name  = isset($columnName)? trim($columnName) : null;
        $this->ref_table    = isset($refTable)  ? trim($refTable)   : null;
        $this->ref_col      = isset($refColumn) ? trim($refColumn)  : null;
    }
}
