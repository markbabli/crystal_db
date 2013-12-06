<?php
Direct_Call::isDirectCall();
class DB_Table
{
   protected $name,
             $columns,
             $foreign_keys,
             $indexes,
             $engine,
             $remarks;
   
   public function __set($name, $value)
   {
       throw new Database_Exception("DB: Unsupported Table Attribute");
   }
   
   public function __get($name)
   {
       return $this->$name;
   }
   
   public function __construct($table_name)
   {
       $this->name = isset($table_name)? $table_name : null;
   }
   
   public function addColumn(DB_Column $column)
   {
       $this->columns[] = $column;
   }
   
   public function addForeignKey(DB_ForeignKey $key)
   {
       $this->foreign_keys[] = $key;
   }
}