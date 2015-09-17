<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Expedition_Data_Support {
        
        
        public static function expedition_get($expedition_id){
            //<editor-fold defaultstate="collapsed">
            $result = array();
            $db = new DB();
            $q = '
                select t1.*
                from expedition t1
                where t1.id = '.$db->escape($expedition_id).'
                    and t1.status>0
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0) $result = $rs[0];
            return $result;
            //</editor-fold>
        }
        
        
    }
?>