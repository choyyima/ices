<?php
class Coba_Data_Support {
    public static function coba_get($id){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = array();
        $q = '
            select *
            from coba
            where id = '.$db->escape($id).'
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
