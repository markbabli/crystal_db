<?php
Direct_Call::isDirectCall();
abstract class Database
{
    protected $_dbStructure,        //database structure..
              $_dbHost,             //database hostname
              $_dbUser,             //database username
              $_dbPass,             //database password
              $_dbName,             //database name
              $_dbPort,             //database port
              $_connectionString,   //connection string
              $_query,              //query 
              $_queryResult,        //query result
              $_errorMsg="",        //query error message
              $_errorNum=0,         //query error number
              $_affectedRows=0,     //affected rows by the query
              $_debugOn=false,      //debug on
              $_cacheOn=false,      //cache on
              $_connectionMode=NULL,//connection string or database parameters
              $_readyToConnect=false,   //ready to connect? 
              $_connectionIsOpen=false, //connection open?
              $_queryTimeInSeconds=0,   //how long did the query take to execute
              $_queryMemoryUsage=0;     //how much memory was used for query
    /**
     * Prevent users from accidentally assigning new class variables to the 
     * object at realtime
     * @param any $name
     * @param any $val
     * @throws Exception
     */
    public final function __set($name,$val){
        throw new Database_Exception("DB:Cannot add properties to this object");
    }
    
    /**
     * close the mysql resource when the object is about to be garbage collected
     */
    public final function __destruct()
    {
        $this->disconnect();
    }
    
    /**
     * returns whether or not the connection is actually open
     * @return true/false
     */
    public final function isConnected(){
        return $this->_connectionIsOpen;
    }
    /**
     * log errir messages
     * @todo Need to specifiy a way to handle message logging
     */
    private function logMessage()
    {
        
    }
    
    protected function  convertMemorySize($size)
    {
        $unit=array('b','kb','mb','gb','tb','pb');
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }
    
    private function tableInStructure($tableName){
        if(!$tableName){
            throw new Database_Exception("MYSQL:Blank Table Name");
        }
        foreach($this->_dbStructure->db_tables as $table){
            if($table->name == $tableName)
                return $table;
        }
        return false;
    }
    
    /**
     * set debug on/off (true/false)
     * @param type $status
     */
    protected final function debugOn($status = true)
    {
        if($status){
            $this->_debugOn = true;
        }else{
            $this->_debugOn = false;
        }
    }
    
    /**
     * set cache on/off (true/off)
     * @param type $status
     */
    protected final function cacheOn($status=true)
    {
        if($status){
            $this->_cacheOn = true;
        }else{
            $this->_cacheOn = false;
        }
    }
    
    /**
     * return any error message number from the last query
     * @return type
     */
    protected final function gerErrorNumber()
    {
        return $this->_errorNum;
    }
    
    /**
     * return any error message string from last query
     * @return type
     */
    protected final function getErrorMessage()
    {
        return $this->_errorMsg;
    }
    
    /**
     * return the number of affected rows from last query 
     * @return type
     */
    protected final function getAffectedRows()
    {
        return $this->_affectedRows; 
    }
    
    /**
     * set database parameters for the connection
     * @param type $host
     * @param type $user
     * @param type $pass
     * @param type $name
     * @param type $port
     * @throws Exception
     */
    protected final function setDatabaseParams($host,$user,$pass,$name,$port)
    {
        $this->_dbHost = isset($host) ? trim($host) : NULL;
        $this->_dbUser = isset($user) ? trim($user) : NULL;
        $this->_dbPass = isset($pass) ? trim($pass) : NULL;
        $this->_dbName = isset($name) ? trim($name) : NULL;
        $this->_dbPort = isset($port) ? trim($port) : NULL;
        
        if(NULL == $this->_dbHost)
            throw new Database_Exception("DB:Blank Hostname");
        if(NULL == $this->_dbUser)
            throw new Database_Exception("DB:Blank User");
        if(NULL == $this->_dbPass)
            throw new Database_Exception("DB:Blank Password");
        if(NULL == $this->_dbName)
            throw new Database_Exception("DB:Blank Database Name");
        if(NULL == $this->_dbPort)
            throw new Database_Exception("DB:Blank Port");
        
        $this->_connectionMode = "params";
        $this->_readyToConnect = true;
    }
    
    /**
     * set the database connection string
     * @param type $connStr
     * @throws Exception
     */
    protected final function setDatabaseConnectionString($connStr)
    {
        $this->_connectionString = isset($connStr)? trim($connStr) : NULL;
        if(NULL == $this->_connectionString)
            throw new Database_Exception ("DB:Blank Connection String");
        $this->_connectionMode = "connection string";
        $this->_readyToConnect = true;
    }
   
    /**
     * return the query result
     */
    public final function getQueryResult()
    {
        return $this->_queryResult;
    }
    
    /**
     * select rows from table
     */
    public final function select($table_name,$count=1000,$startat=0)
    {
        if(!$this->_connectionIsOpen){
            throw new Database_Exception("MYSQL:Connection is Closed");
        }
        
        $table = $this->tableInStructure($table_name);
        if(!$table){
            throw new Database_Exception("MYSQL:Invalid Table Name");
        }
        
        // initial select 
        $selectString = DB_SQL::buildColumnList($table, "select");
        // foreign keys 
        if(count($table->foreign_keys)>0){
            foreach($table->foreign_keys as $key){
                $tableJoin = $this->tableInStructure($key->ref_table);
                $selectString .= " ,". DB_SQL::buildColumnList($tableJoin,null);
            }
        }
        $selectString .= DB_SQL::buildFrom($table);
        if($this->query($selectString)){
            return $this->getQueryResult();
        }else{
            return false;
        }
    }
    
     /**
     * bulk insert into the database
     */
    public abstract function insert();
    
    /**
     * bulk update into the database
     */
    public abstract function update();
    
    /**
     * delete rows from database
     */
    public abstract function delete();
    
    
    /**
     * get query execution time
     */
    public final function getQueryExecutionTime()
    {
        return number_format($this->_queryTimeInSeconds,3);
    }
    
    /**
     * get query execution memory usage
     */
    public final function getQueryExecutionMemory()
    {
        return $this->_queryMemoryUsage;
    }
   
    /**
     * connect to the database. implementation dependent 
     */ 
    public abstract function connect();
    
    /**
     * disconnect to the database. implementation dependent
     */
    public abstract function disconnect();
    
    /**
     * discover the database (tables)
     */
    public abstract function discoverDatabase($database);
    
    /**
     * discover table columns 
     */
    public abstract function discoverTable($table);
    
    /**
     * discover table foreign keys
     */
    public abstract function discoverTableKeys($table);
    
    /**
     * query the database (any query)
     */
    public abstract function query($query);
    
    /**
     * check if exists prior to insert/update
     */
    public abstract function checkExists();     
    
    /**
     * get the database structure
     */
    public abstract function getDatabaseStructure();
    
    
}

