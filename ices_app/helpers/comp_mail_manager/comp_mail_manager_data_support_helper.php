<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Comp_Mail_Manager_Data_Support{
    
    
    public static function company_mail_by_code_get($code){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = array();
        $q = '
            select cm.*
            from company_mail cm
            where code = '.$db->escape($code).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
        //</editor-fold>
    }
    
    
}
?>