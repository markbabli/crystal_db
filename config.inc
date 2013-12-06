<?php
// you change this constants name and everything breaks....see direct_call class
define('config_version','1.0');





class Crystal_Loader {
    private static $_cachedList = array();
    private static $stderr;
    
    private function __construct() {
        
    }

    // Autoload Function
    public static function LoadClass($class_name) {
        self::$stderr = fopen('php://stderr','w');
        // check to make sure that we are checking the cached registry first
        if (@isset(self::$_cachedList[$class_name])) {
            require_once(self::$_cachedList[$class_name]);
            return true;
        }

        if (strstr($class_name, '\\')) {
            // get the last portion of the 
            preg_match('/[a-zA-Z_-]+$/', $class_name, $matches);
            $class_name = $matches[0];
        }

        // Make sure the class name is not empty
        if (empty($class_name)) {
            fprintf(self::$stderr, "[%s] [%s] [%s] [%s]\n", "EXCEPTION:ERROR", date("Y-m-d h:i:s"), "Loader", "Empty Class Name!");
        }

        // base directory
        $baseDir = dirname(__FILE__);


        try {
            $findInLibrary = self::FindFileInDir($baseDir . DIRECTORY_SEPARATOR . "lib", $class_name . ".class.php");
            if (is_file($findInLibrary)) {
                self::$_cachedList[$class_name] = $findInLibrary;
                require_once(self::$_cachedList[$class_name]);
                return true;
            }

            $findInApp = self::FindFileInDir($baseDir . DIRECTORY_SEPARATOR . "app", $class_name . ".class.php");
            if (is_file($findInApp)) {
                self::$_cachedList[$class_name] = $findInApp;
                require_once(self::$_cachedList[$class_name]);
                return true;
            }
        } catch (Exception $ex) {
            die($ex->getMessage());
        }
        fprintf(self::$stderr, "[%s] [%s] [%s] [%s]\n", "EXCEPTION:ERROR", date("Y-m-d h:i:s"), "Loader", "Class Was Not Found!");
        fclose(self::$stderr);
        return false;
    }

    // Recursive Functions
    private static function FindFileInDir($dir, $file_name) {
        // Check if the directory exists
        if (!is_dir($dir)) {
            throw new Exception("Invalid Directory Passed to recursive Function!");
        }
        // Check if the file name is empty
        if (empty($file_name)) {
            throw new Exception("Invalid File Name Passed to recursive Function!");
        }

        // best cases check...the file is right there! 
        if (is_file($dir . DIRECTORY_SEPARATOR . $file_name)) {
            return $dir . DIRECTORY_SEPARATOR . $file_name;
        }

        // open the directory and recurse
        $dh = opendir($dir);
        if (false == $dh) {
            throw new Exception("Unable to open directory, possibly permissions?");
        }

        //read the directory and skip the '.' and the '..' links
        while (false !== ($file = readdir($dh))) {
            if ($file == "." || $file == "..") {
                continue;
            }
            // is directory, recurse into the new directory
            if ((is_dir($dir . DIRECTORY_SEPARATOR . $file))) {
                if ($found = self::FindFileInDir($dir . DIRECTORY_SEPARATOR . $file, $file_name)) {
                    return $found;
                }
            }
            // is file, return the file 
            if (is_file(($dir . DIRECTORY_SEPARATOR . $file)) && strtolower($file) == strtolower($file_name)) {
                return $dir . DIRECTORY_SEPARATOR . $file_name;
            }
        }
        return false;
    }
}

// register the auto loaders
spl_autoload_register('Crystal_Loader::LoadClass');