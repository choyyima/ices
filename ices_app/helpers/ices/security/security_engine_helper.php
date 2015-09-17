<?php
class Security_Engine{
    public static function get_user_id($username,$password){
        $id = null;
        $db = new DB(array('db_name'=>'ices'));
        $username = $db->escape($username);
        $password = $db->escape($password);
        $q = '
            select e.id 
            from employee e
            where e.status>0 and e.username = '.$username.' and e.password = md5('.$password.')
                and e.employee_status = "active"
        ';
        
        $rs = $db->query_array_obj($q);

        if(count($rs)>0) $id = $rs[0]->id;
        return $id;
    }

    public static function sign_out(){
        User_Info::flush();
    }

    public static function get_component_permission($user_id="",$module="",$comp_id=""){
        $result = false;
        if(strlen($user_id) > 0 && strlen($module) > 0 && strlen($comp_id) > 0 ){
            $db_name = SI::type_get('ICES_Engine', 'ices', '$app_list')['app_db_conn_name'];
            $db = new DB($db_name);

            $q = '
                select 1 
                from employee t1 
                inner join employee_u_group t2 on t1.id = t2.employee_id
                inner join u_group t3 on t3.id = t2.u_group_id
                where t1.id = '.$db->escape($user_id).' and lower(t3.name) = "root"
            ';

            if(count($db->query_array_obj($q))>0) $result = true;

            $q = '
                select 1 
                from u_group_security_component t1 
                inner join u_group t2 on t1.u_group_id = t2.id
                inner join employee_u_group t3 on t2.id = t3.u_group_id
                inner join employee t4 on t4.id = t3.employee_id 
                inner join security_component t5 on t5.id = t1.security_component_id
                where t5.status > 0
                    and t4.id = '.$db->escape($user_id).'
                    and t5.module = '.$db->escape($module).'
                    and t5.comp_id = '.$db->escape($comp_id).'
                    and t5.security_component_status = "active"
            ';
            if(count($db->query_array_obj($q))>0) $result = true;            
        }
        return $result;
    }

    public static function get_controller_permission($app_name='',$user_id="",$name="",$method=""){
        $result = false;
        $db_name = SI::type_get('ICES_Engine', 'ices', '$app_list')['app_db_conn_name'];
        $db = new DB(array('db_name'=>$db_name));

        $q = '
            select 1 
            from employee t1 
            inner join employee_u_group t2 on t1.id = t2.employee_id
            inner join u_group t3 on t3.id = t2.u_group_id
            where t1.id = '.$db->escape($user_id).' and lower(t3.name) = "root"
        ';

        if(count($db->query_array_obj($q))>0){
            $result = true;
        }

        $q = '
            select 1 
            from u_group_security_controller t1 
            inner join u_group t2 on t1.u_group_id = t2.id
            inner join employee_u_group t3 on t2.id = t3.u_group_id
            inner join employee t4 on t4.id = t3.employee_id 
            inner join security_controller t5 on t5.id = t1.security_controller_id
            where t5.status > 0
                and t4.id = '.$db->escape($user_id).'
                and t5.method = '.$db->escape($method).'
                and t5.name = '.$db->escape($name).'
                and t5.app_name = '.$db->escape($app_name).'
                and t5.security_controller_status = "active"
                
        ';
        if(count($db->query_array_obj($q))>0){
            $result = true;            
        }
        
        if(in_array($name,
            ICES_Engine::$app['non_permission_controller'])
        ){
            $result = true;
        }
        
        return $result;
    }

    public static function is_timeout(){
        //<editor-fold defaultstate="collapsed">
        $result = true;
        $expiration = get_instance()->config->config['MY_']['timeout'];
        $now = strtotime(Date('Y-m-d H:i:s'));
        $last_request = strtotime(User_Info::get()['last_request']);
        $timediff = $now - $last_request;


        if($timediff<$expiration){
            $result = false;
        }

        $uri = get_instance()->uri->uri_string;
        $uncalculated_uri = array(
            'app_message'
            ,'common_ajax_listener/is_timeout'
            ,'app_notificaation'
        );
        $calculate = true;

        foreach($uncalculated_uri as $uncalculated){
            if(strpos($uri,$uncalculated)!==false) $calculate = false;
        }
        if($calculate){
            get_instance()->session->set_userdata(array("last_request"=>Date('Y-m-d H:i:s')));
        }

        return $result;
        //</editor-fold>
    }

}
?>
