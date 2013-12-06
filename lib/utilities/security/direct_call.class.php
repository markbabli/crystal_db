<?php

class Direct_Call
{
    public static function isDirectCall(){
        if(!defined('config_version')){
            throw new security_exception("Direct access to this file is now allowed");
        }
    }
}
