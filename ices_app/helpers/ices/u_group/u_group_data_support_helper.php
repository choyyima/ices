<?php
class U_Group_Data_Support {
    static function helper_init(){
        //<editor-fold defaultstate="collapsed">
        $ices = SI::type_get('ICES_Engine','ices','$app_list');
        get_instance()->load->helper($ices['app_base_dir'].'u_group/u_group_engine');
        //</editor-fold>
    }
    
    public static function u_group_get($id){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = array();
        $q = '
            select *
            from u_group
            where id = '.$db->escape($id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
        //</editor-fold>
    }

    public static function u_group_security_controller_get($u_group_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db_name = SI::type_get('ICES_Engine','ices','$app_list')['app_db_conn_name'];
        $db = new DB(array('db_name'=>$db_name));
        $q = '
            select sc.*
            from security_controller sc
                inner join u_group_security_controller ugsc on sc.id = ugsc.security_controller_id
            where ugsc.u_group_id = '.$db->escape($u_group_id).'
                order by sc.name, sc.method
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs;
        }
        return $result;
        //</editor-fold>
    }
    
    public static function u_group_security_menu_get($u_group_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $ices = SI::type_get('ICES_Engine','ices','$app_list');
        $db = new DB(array('db_name'=>$ices['app_db_conn_name']));
        $q = '
            select ugsm.menu_id 
            from u_group_security_menu  ugsm
            inner join u_group t2 on ugsm.u_group_id = t2.id 
            where t2.id = '.$db->escape($u_group_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = $rs;
        return $result;
        //</editor-fold>
    }
    
    
}
?>
