<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class test_job{
    
    public function start($sys_db,$config){
        $result = array('success'=>1,'msg'=>'');
        $db = new DB(array('db_name'=>'MY_damaiberjaya3'));
        var_dump($db);
        
        return $result;
    }
};

?>