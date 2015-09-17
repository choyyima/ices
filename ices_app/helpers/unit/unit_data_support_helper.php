<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Unit_Data_Support{
    
    public static function unit_get($id){
        $db = new DB();
        $result = null;
        $q = '
            select *
            from unit
            where id = '.$db->escape($id).'
                and status > 0
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
    }

}
?>