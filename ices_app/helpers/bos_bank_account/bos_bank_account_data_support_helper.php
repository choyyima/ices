<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bos_Bank_Account_Data_Support{
    
    public static function bos_bank_account_get($bos_bank_account_id){
        //<editor-fold defaultstate="collapsed">
        $result = null;
        $db = new DB();
        $q = '
            select *
            from bos_bank_account t1
            where t1.id = '.$db->escape($bos_bank_account_id).'
                and t1.status>0
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = $rs[0];
        return $result;
        //</editor-fold>
    }
    
    public static function bos_bank_account_list_get($opt = array()){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $bba_status = isset($opt['bos_bank_account_status'])?Tools::_str($opt['bos_bank_account_status']):'active';
        $db = new DB();
        $q = '
            select *
            from bos_bank_account bba
            where bba.bos_bank_account_status = '.$db->escape($bba_status).'
                and bba.status>0
        ';
        
        
        $rs = $db->query_array($q);
        
        if(count($rs)>0){
            $result = $rs;            
        }
        
        return $result;
        //</editor-fold>
    }
}
?>