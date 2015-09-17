<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Security_Menu_Data_Support {
    static function helper_init(){
        //<editor-fold defaultstate="collapsed">
        $ices = SI::type_get('ICES_Engine','ices','$app_list');
        get_instance()->load->helper($ices['app_base_dir'].'security_menu/security_menu_engine');
        //</editor-fold>
    }
    
    public static function menu_list_get($app_name){
        //<editor-fold defaultstate="collapsed">
        $menu = Tools::_arr(isset(Security_Menu_Engine::$menu_list[$app_name])?Security_Menu_Engine::$menu_list[$app_name]:array());
        $result = array();
        $tab = "&nbsp &nbsp &nbsp &nbsp &nbsp ";
        foreach($menu as $key1=>$lvl1){
            $result[]=array(
                'selected'=>''
                ,'id'=>$lvl1['id']
                ,'menu'=>$key1
            );
            if(isset($lvl1['child'])){
                foreach($lvl1['child'] as $key2=>$lvl2){
                    $result[]=array(
                        'selected'=>''
                        ,'menu'=>$tab.$key2
                        ,'id'=>$lvl2['id']
                    );
                    if(isset($lvl2['child'])){
                        foreach($lvl2['child'] as $key3=>$lvl3){
                            $result[] = array(
                                'selected'=>''
                                ,'menu'=>$tab.$tab.$key3
                                ,'id'=>$lvl3['id']
                            );
                        }
                    }

                }
            }
        }

        return $result;
        //</editor-fold>
    }
    
    public static function u_group_security_menu_by_employee_get($user_id, $app_name){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $ices = SI::type_get('ICES_Engine','ices','$app_list');
        $db = new DB(array('db_name'=>$ices['app_db_conn_name']));
        $q = '
            select ugsm.menu_id 
            from u_group_security_menu  ugsm
            inner join u_group t2 on ugsm.u_group_id = t2.id 
            inner join employee_u_group t3 on t3.u_group_id = t2.id
            where t3.employee_id = '.$db->escape($user_id).'
                 and ugsm.app_name = '.$db->escape($app_name).'
        ';
        $rs = $db->query_array_obj($q);
        if(count($rs)>0) $result = $rs;
        return $result;
        //</editor-fold>
    }
    

}
?>
