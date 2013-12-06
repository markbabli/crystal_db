<?php
Direct_Call::isDirectCall();
class DB_SQL
{
    protected static $table_name;
    protected static $ref_tables = array();
    
    public static function buildColumnList(DB_Table $table, $operation)
    {
        $columnList  = strtoupper($operation). "  ";
        if($operation){
            if(trim(strtolower($operation)) == "select"){
                $columnList .= " DISTINCT ";
            }
        }
        
        self::$table_name = $table->name;
        
        
        foreach($table->columns as $column){
            $columnList .= " ".self::$table_name.".".$column->column_name." AS ".self::$table_name."_".$column->column_name.",";
        }
        
        return substr($columnList,0,-1); 
    }
    
    public static function buildFrom(DB_Table $table,$joinType="left")
    {
        $fromClause = "  FROM  ".$table->name. "  ";
        $joinTables = " ";
        $joinClause = "";
        
        switch($joinType){
            case "left":
            case "left join":
                $joinClause = " LEFT JOIN  ";
                break;
            case "right":
            case "right join":    
                $joinClause = " RIGHT JOIN ";
                break;
            case "inner":
            case "inner join":
                $joinClause = " INNER JOIN ";
                break;
            default:
                throw new Database_Exception("DB:INVALID JOIN TYPE");
        }
        
        $joinedTables = array();
        
        foreach($table->foreign_keys as $key){
            if(!in_array($key->ref_table, $joinedTables)){
                $joinTables .=  "  ".$joinClause . "  ".$key->ref_table 
                                    . "  ON  ".$key->table_name.'.'
                                    .$key->column_name .' = '
                                    .$key->ref_table.'.'.$key->ref_col."  ";
                $joinedTables[] = $key->ref_table;
            }
        }
        
        return $fromClause . $joinTables. " ";
    }
    
    public static function buildWhere($conditions)
    {
        
    }
}