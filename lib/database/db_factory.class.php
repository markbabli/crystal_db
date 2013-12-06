<?php
Direct_Call::isDirectCall();
class DB_Factory
{
    private static $_databaseDriver = "mysql";
    
    private function __construct()
    {
        ;
    }
    public static function DB($host,$user,$pass,$name,$port)
    {
        switch(self::$_databaseDriver){
            case "mysql":
                return new DB_MySQL($host,$user,$pass,$name,$port);
                break;
            case "mssql":
            case "oracle":
            case "postgres":
                throw new Database_Exception("DB: Interface Not Implemented");
        }
    }
}
