<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class App_Access_Time_Engine {
    public static function u_group_allowed($app_name=''){
        $result = false;
        $db_name = SI::type_get('ICES_Engine', 'ices', '$app_list')['app_db_conn_name'];
        $db = new DB(array('db_name'=>$db_name));
        $u_group_id = User_Info::get()['u_group_id'];
        $q = '
            select 1
            from u_group_security_app_access_time t1
                inner join security_app_access_time t2 on t1.security_app_access_time_id = t2.id
            where t1.u_group_id = '.$db->escape($u_group_id).'
                and  "'.Tools::_date('','H:i').'" between 
                    concat(t2.hour_start,":",t2.min_start,":00") 
                    and concat(t2.hour_end,":",t2.min_end,":00")
                and t2.day = '.Tools::_date('','w').' 
                and t2.app_name='.$db->escape($app_name).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = true;
        
        return $result;
    }
    
}
?>