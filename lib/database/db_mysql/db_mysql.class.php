<?php
Direct_Call::isDirectCall();
class DB_MySQL extends Database
{
    private static $_connPipe = NULL;

    public function __construct($host,$user,$pass,$name,$port)
    {
       $this->setDatabaseParams($host,$user,$pass,$name,$port);
    }

    public function connect()
    {
        if ($this->_readyToConnect) {
            if (is_null(self::$_connPipe)) {
                if($this->_connectionMode == "params"){
                    //always initiate a new connection 
                    self::$_connPipe = mysql_connect($this->_dbHost.':'.
                                                      $this->_dbPort, 
                                                      $this->_dbUser, 
                                                      $this->_dbPass,
                                                      true); 
                    
                    if(!self::$_connPipe){
                        $this->_connectionIsOpen = false;
                        $this->_errorMsg = mysql_error();
                        $this->_errorNum = mysql_errno();
                    }
                    
                    if(!mysql_select_db($this->_dbName)){
                        $this->_errorNum = -1;
                        $this->_errorMsg = mysql_error();
                        throw new 
                          Database_Exception("MYSQL:Unable To Select Database");
                    }
                    $this->_connectionIsOpen = true;
                }else{
                    $this->_connectionIsOpen = true;
                    $this->_errorMsg = "";
                    $this->_errorNum = 0;
                }
            } else {
                $this->_connectionIsOpen = true;
            }
        } else {
            throw new Database_Exception("MYSQL:Not Ready To Connect");
        }
        
        $this->discoverDatabase($this->_dbName);
        return true;
    }
    
    
    public function discoverDatabase($database)
    {
        $this->_dbStructure = null;
        $result = $this->query("SELECT DISTINCT 
                                       `TABLE_NAME` 
                                FROM 
                                        information_schema.columns 
                                WHERE 
                                        table_schema='{$this->_dbName}';");
        if($result){
            $this->_dbStructure=new DB($this->_dbName);
            
            foreach($this->_queryResult as $table){
                $table_nm   = $table->TABLE_NAME;
                
                
                $table_data = $this->discoverTable($table_nm);
                $table_keys = $this->discoverTableKeys($table_nm);
                
                $dbTableObj = null;
                $dbTableObj = new DB_Table($table_nm);
                
                
                foreach($table_data as $column){
                    $tableColumn  = null;
                    $tableColumn = new DB_Column($column->column_name,
                                                 $column->column_position,
                                                 $column->column_type,
                                                 $column->is_nullable,
                                                 $column->column_default
                                                );
                    $dbTableObj->addColumn($tableColumn);
                }
                
                if(count($table_keys) > 0){
                    foreach($table_keys as $key){
                        $tableKey = null;
                        $tableKey = new DB_ForeignKey($key->name, 
                                                      $key->table_name, 
                                                      $key->column_name, 
                                                      $key->ref_table, 
                                                      $key->ref_col
                                                      );
                        $dbTableObj->addForeignKey($tableKey);
                    }
                }
                
                $this->_dbStructure->addTable($dbTableObj);
            }
        }
    }
    
    public function discoverTable($table)
    {
        $result = $this->query("SELECT DISTINCT 
                                        `COLUMN_NAME` column_name,
                                        `ORDINAL_POSITION` column_position,
                                        `COLUMN_TYPE` column_type,
                                        `IS_NULLABLE` is_nullable,
                                        `COLUMN_DEFAULT` column_default
                                    FROM 
                                        `INFORMATION_SCHEMA`.`COLUMNS` 
                                    WHERE 
                                        `TABLE_SCHEMA`='{$this->_dbName}' 
                                         AND 
                                        `TABLE_NAME`='$table'
                                    ORDER BY 2;");
       if($result){
           return $this->_queryResult;
       }
    }
    
    
    public function discoverTableKeys($table)
    {
      $result = $this->query(" SELECT 
                                    CONSTRAINT_NAME name, 
                                    TABLE_NAME table_name, 
                                    COLUMN_NAME column_name, 
                                    REFERENCED_TABLE_NAME ref_table,
                                    REFERENCED_COLUMN_NAME ref_col
                             FROM   
                                    information_schema.`KEY_COLUMN_USAGE`
                             WHERE  
                                    table_schema = '{$this->_dbName}' 
                                    AND TABLE_NAME = '$table'
                                    AND referenced_column_name IS NOT NULL; ");
       if($result){
           return $this->_queryResult;
       }   
    }
    
    public function getDatabaseStructure()
    {
        return $this->_dbStructure;
    }

    public function disconnect()
    {
        if(self::$_connPipe){
            mysql_close(self::$_connPipe);
        }
    }
    
    public function query($query,$returnType="object")
    {
        if(!$this->_connectionIsOpen){
            throw new Database_Exception("MYSQL:Closed Connection Detected");
        }
        
        $beginTime = microtime(true);
        $tmpResult = mysql_query($query, self::$_connPipe);
        $finishTime= microtime(true);
        
        $this->_queryTimeInSeconds = ($finishTime - $beginTime);
        $this->_queryMemoryUsage   = $this->convertMemorySize(
                                            memory_get_usage(true)
                                     );
        
        if(!$tmpResult){
            $this->_errorMsg = mysql_error();
            $this->_errorNum = mysql_errno();
            throw new Database_Exception("MYSQL:".mysql_error());
        }
        
        $this->_queryResult  = null;
        $this->_affectedRows = mysql_affected_rows(self::$_connPipe);
        
        if($this->_affectedRows > 0){
            while($row = mysql_fetch_object($tmpResult)){
                $this->_queryResult[] = $row;
            }
        }
        return true;
    }
    
    public function insert()
    {
        ;
    }

    public function update()
    {
        ;
    }

    public function delete()
    {
        ;
    }

    public function checkExists()
    {
        ;
    }
}
