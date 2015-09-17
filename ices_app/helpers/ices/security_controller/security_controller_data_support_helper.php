<?php
class Security_Controller_Data_Support {
    static function helper_init(){
        //<editor-fold defaultstate="collapsed">
        $ices = SI::type_get('ICES_Engine','ices','$app_list');
        get_instance()->load->helper($ices['app_base_dir'].'security_controller/security_controller_engine');
        //</editor-fold>
    }
    
    public static function security_controller_get($id){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = array();
        $q = '
            select *
            from security_controller
            where id = '.$db->escape($id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
        //</editor-fold>
    }
    
    public static function security_controller_by_app_name_get($app_name,$param = array()){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $ices = SI::type_get('ICES_Engine','ices','$app_list');
        $db = new DB(array('db_name'=>$ices['app_db_conn_name']));
        
        $q_security_controller_status = isset($param['security_controller_status'])?
            'and sc.security_controller_status = '.
                $db->escape(Tools::_str($param['security_controller_status'])):
            '';
        
        $q = '
            select sc.*
            from security_controller sc
            where sc.app_name = '.$db->escape($app_name).'
                and sc.status>0
                '.$q_security_controller_status.'
            order by sc.name, sc.method
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = $rs;
        return $result;
        //</editor-fold>
    }

}
?>
