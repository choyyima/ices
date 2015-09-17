<?php
class u_group_Engine {

    public static function save($u_group_data){
        //<editor-fold defaultstate="collapsed">
        $db = get_instance()->db;
        $success = 1;
        $msg = array();
        $action = "";

        if(strlen($u_group_data['id'])==0){
            unset($u_group_data['id']);
            $action = "insert";
        }
        else{
            $action = "update";
            if(isset($u_group_data['status'])){
                if($u_group_data['status'] == 0) $action = "delete";
            }

        }

        if(in_array($action,array("insert","update"))){
            $validation_res = self::validate($u_group_data);
            $success = $validation_res['success']; 
            $msg = $validation_res['msg'];
        }

        if($success == 1){
            $u_group_data = self::adjust($u_group_data);
            switch($action){
                case 'insert':
                    $db->insert('u_group',$u_group_data);
                    $msg[] = 'Add User Group Success';
                    break;
                case 'update':
                    $db->update('u_group',$u_group_data,array("id"=>$u_group_data['id']));
                    $msg[] = "Update User Group Success";
                    break;
                case 'delete':
                    $db->update('u_group',array("status"=>0),array("id"=>$u_group_data['id']));
                    $msg[] = "Delete User Group Success";
                    break;
            }                    
            Message::set('success',$msg);
        }
        else{
            Message::set('error',$msg);
        }

        if($success == 1) return 1;
        else return 0;
        //</editor-fold>
    }

    public static function validate($data=array()){
        //<editor-fold defaultstate="collapsed">
        $result = array(
            "success"=>1
            ,"msg"=>""
        );

        if(strlen($data['name'])==0){
            $result['success'] = 0;
            $result['msg'][] = 'User Group Name Cannot be Empty';
        }

        $db = new DB();
        $id = isset($data['id'])?$data['id']:'';
        $q = ' select 1 from u_group where status>0 and id != '.$db->escape($id).' and name = '.$db->escape($data['name']).'';
        $rs = $db->query_array_obj($q);
        if(count($rs)>0){
            $result['success'] = 0;
            $result['msg'][] = 'Duplicate User Group Name';
        }

        return $result;
        //</editor-fold>
    }

    public static function adjust($data=array()){
        $result = $data;
        return $result;
    }

    public static function get($id=""){
        $db = new DB();
        $q = "select * from u_group where status>0 and id = ".$db->escape($id);
        $rs = $db->query_array_obj($q);
        if(count($rs)>0)
            $rs = $rs[0];
        else $rs = null;
        return $rs;
    }

    public static function detail_render($pane,$data){
        //<editor-fold defaultstate="collapsed">
        $u_group = self::get($data['id']);
        $pane->div_add()->div_set("class","form-group");

        $first_row = $pane->div_add()->div_set("class","form-group");
        $first_row->label_add()->label_set("value",'Name: ');
        $first_row->span_add()->span_set("value",$u_group->name);
        if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'u_group','edit')){
            $first_row->button_add()->button_set('value','Edit')
                    ->button_set('style','margin-left:20px')
                    ->button_set('icon','fa fa-pencil-square-o')
                    ->button_set('href',get_instance()->config->base_url().'u_group/edit/'.$data['id']);
        }
        if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'u_group','delete')){
            $first_row->button_add()->button_set('value','Delete')
                    ->button_set('icon','fa fa-cut')
                    ->button_set('class','btn btn-danger')
                    ->button_set('confirmation',true)
                    ->button_set('confirmation msg','Are you sure want to delete '.$u_group->name.'?')
                    ->button_set('href',get_instance()->config->base_url().'u_group/delete/'.$data['id']);
        }


        $pane->label_span_add()->label_span_set("value",array('label'=>"Mod ID: ","span"=>$u_group-> modid));
        $pane->label_span_add()->label_span_set("value",array('label'=>"Mod Date: ","span"=>$u_group-> moddate));

        $pane->hr_add();
        $pane->button_add()->button_set('value','BACK')
            ->button_set('icon','fa fa-arrow-left')
            ->button_set('class','btn btn-default')
            ->button_set('href',get_instance()->config->base_url().'u_group/index');
        //</editor-fold>
    }

    public static function app_access_time_render($pane, $data){
        //<editor-fold defaultstate="collapsed">
        
        $q = '
            select id,day,hour_start,min_start,hour_end, min_end
            from security_app_access_time 
            order by day,hour_start,min_start
        ';
        $db = new DB();
        $app_access_time_item=$db->query_array_obj($q);

        $q = '
            select security_app_access_time_id id 
            from u_group_security_app_access_time 
            where u_group_id = '.$db->escape($data['id']);
        
        $user_app_access_time = $db->query_array_obj($q);

        $day='';
        
        $accordion = null;
        $pane->form_group_add();
        $form_group = $pane->form_group_add();
        $form_group->label_add()->input_raw_add()->input_raw_set('type','checkbox')
                ->input_raw_set('id','app_access_time_check_all');
        $form_group->label_add()->label_set('value','Check All');
        foreach($app_access_time_item as $controller){
            if($day != $controller->day){
                $day = $controller->day;
                $accordion = $pane->accordion_add()
                    ->accordion_set('header',array('id'=>$day,'text'=>Tools::_date('2014-06-'.(Tools::_int($day)+1),'l')));
            }
            $prefix = 'app_access_time_';                
            $attrib = array();
            foreach($user_app_access_time as $item){
                if($item->id == $controller->id)
                    $attrib=array(
                        "checked"=>''
                    );
            }             
            $form_group=$accordion->form_group_add();
            $form_group->label_add()
                    ->input_raw_add()->input_raw_set('type','checkbox')
                    ->input_raw_set('attrib',$attrib)
                    ->input_raw_set('id',$prefix.$controller->id);
            $form_group->label_add()->label_set('value',Tools::_date($controller->hour_start.':'.$controller->min_start,'H:i').' - '.Tools::_date($controller->hour_end.':'.$controller->min_end,'H:i'));
        }
        
        if(Security_Engine::get_controller_permission(
                User_Info::get()['user_id'],'u_group','app_access_time_save')
        ){
            $pane->hr_add()->button_add()->button_set('value','Submit')
                ->button_set('icon','fa fa-save')
                ->button_set('id','app_access_time_save');
        }  
        //</editor-fold>
    }
    
    public static function controller_permission_render($pane,$data){
        get_instance()->load->helper('security/controller_engine');
        $q = '
            select id,method,name from security_controller order by name, method
        ';
        $db = new DB();
        $controller_item=$db->query_array_obj($q);

        $q = 'select security_controller_id id from u_group_security_controller where u_group_id = '.$db->escape($data['id']);
        $user_controller_item = $db->query_array_obj($q);

        $name='';
        $accordion = null;
        $pane->form_group_add();
        $form_group = $pane->form_group_add();
        $form_group->label_add()->input_raw_add()->input_raw_set('type','checkbox')
                ->input_raw_set('id','controller_check_all');
        $form_group->label_add()->label_set('value','Check All');
        foreach($controller_item as $controller){
            if($name != $controller->name){
                $name = $controller->name;
                $accordion = $pane->accordion_add()->accordion_set('header',array('id'=>$name,'text'=>$name));
            }
            $prefix = 'security_controller_';                
            $attrib = array();
            foreach($user_controller_item as $item){
                if($item->id == $controller->id)
                    $attrib=array(
                        "checked"=>''
                    );
            }             
            $form_group=$accordion->form_group_add();
            $form_group->label_add()
                    ->input_raw_add()->input_raw_set('type','checkbox')
                    ->input_raw_set('attrib',$attrib)
                    ->input_raw_set('id',$prefix.$controller->id);
            $form_group->label_add()->label_set('value',$controller->method);
        }

        get_instance()->load->helper('user/security_engine');

        if(Security_Engine::get_controller_permission(
                User_Info::get()['user_id'],'u_group','controller_save')
        ){
            $pane->hr_add()->button_add()->button_set('value','Submit')
                ->button_set('icon','fa fa-save')
                ->button_set('id','controller_save');
        }            
    }

    public static function component_permission_render($pane,$data){
        get_instance()->load->helper('security/security_component_engine');
        $q = '
            select id,module,comp_id from security_component order by module, comp_id
        ';
        $db = new DB();
        $component_item=$db->query_array_obj($q);

        $q = 'select security_component_id id from u_group_security_component where u_group_id = '.$db->escape($data['id']);
        $user_component_item = $db->query_array_obj($q);
        $prefix = 'security_component_';
        $module='';
        $accordion = null;
        $pane->form_group_add();
        $form_group = $pane->form_group_add();
        $form_group->label_add()->input_raw_add()->input_raw_set('type','checkbox')
                ->input_raw_set('id','component_check_all');
        $form_group->label_add()->label_set('value','Check All');
        foreach($component_item as $component){
            if($module != $component->module){
                $module = $component->module;
                $accordion = $pane->accordion_add()->accordion_set('header',array('id'=>$prefix.$module,'text'=>$module));
            }

            $attrib = array();
            foreach($user_component_item as $item){
                if($item->id == $component->id)
                    $attrib=array(
                        "checked"=>''
                    );
            }             
            $form_group=$accordion->form_group_add();
            $form_group->label_add()
                    ->input_raw_add()->input_raw_set('type','checkbox')
                    ->input_raw_set('attrib',$attrib)
                    ->input_raw_set('id',$prefix.$component->id);
            $form_group->label_add()->label_set('value',$component->comp_id);
        }

        get_instance()->load->helper('user/security_engine');

        if(Security_Engine::get_controller_permission(
                User_Info::get()['user_id'],'u_group','component_save')
        ){
            $pane->hr_add()->button_add()->button_set('value','Submit')
                ->button_set('icon','fa fa-save')
                ->button_set('id','component_save');
        }            
    }

    public static function helper_permission_render($pane,$data){
        get_instance()->load->helper('security/helper_engine');
        $q = '
            select id,method,name from security_helper order by name, method
        ';
        $db = new DB();
        $helper_item=$db->query_array_obj($q);

        $q = 'select security_helper_id id from u_group_security_helper where u_group_id = '.$db->escape($data['id']);
        $user_helper_item = $db->query_array_obj($q);

        $name='';
        $accordion = null;
        $pane->form_group_add();
        $form_group = $pane->form_group_add();
        $form_group->label_add()->input_raw_add()->input_raw_set('type','checkbox')
                ->input_raw_set('id','helper_check_all');
        $form_group->label_add()->label_set('value','Check All');
        foreach($helper_item as $helper){
            if($name != $helper->name){
                $name = $helper->name;
                $accordion = $pane->accordion_add()->accordion_set('header',array('id'=>$name,'text'=>$name));
            }
            $prefix = 'security_helper_';                
            $attrib = array();
            foreach($user_helper_item as $item){
                if($item->id == $helper->id)
                    $attrib=array(
                        "checked"=>''
                    );
            }             
            $form_group=$accordion->form_group_add();
            $form_group->label_add()
                    ->input_raw_add()->input_raw_set('type','checkbox')
                    ->input_raw_set('attrib',$attrib)
                    ->input_raw_set('id',$prefix.$helper->id);
            $form_group->label_add()->label_set('value',$helper->method);
        }

        get_instance()->load->helper('user/security_engine');

        if(Security_Engine::get_controller_permission(
                User_Info::get()['user_id'],'u_group','helper_save')
        ){
            $pane->hr_add()->button_add()->button_set('value','Submit')
                ->button_set('icon','fa fa-save')
                ->button_set('id','helper_save');
        }            
    }

    public static function app_access_time_save($data=array()){
        $result = array('success'=>1,"msg"=>array());

        $db = new DB();
        try{
            $db->trans_begin();

            $u_group_id = $data['u_group_id'];
            $tbl = 'u_group_security_app_access_time';
            $prefix = 'app_access_time_';


            $q_delete = 'delete from '.$tbl.' where u_group_id = '.$db->escape($u_group_id);
            if(!$db->query($q_delete)){
                $result['success'] = 0;
                $result['msg'][]=$db->_error_message();
            }


            if($result['success']!=0){
                foreach($data['app_access_time'] as $app_access_time){
                    $start = strlen($prefix);
                    $to = strlen($app_access_time)-strlen($prefix);
                    $app_access_time_id = substr($app_access_time,$start,$to);
                    $data_inserted = array(
                        "u_group_id"=>$u_group_id
                        ,"security_app_access_time_id"=>$app_access_time_id
                        ,'modid'=>User_Info::get()['user_id']
                        ,'moddate'=>Date('Y-m-d H:i:s')
                    );
                    if(!$db->insert($tbl,$data_inserted)){
                        $result['success'] = 0;
                        $result['msg'][]=$db->_error_message();
                        break;
                    }
                }
            }

            if($db->trans_status()){
                $db->trans_commit();
                $msg[] = 'Update data success';
            }
            else{
                $db->trans_rollback();
                $result['success'] = 0;
                $result['msg'][]=$db->_error_message();
            }
        }
        catch(Exception $e){
            $db->trans_rollback();
            $result['success'] = 0;
            $result['msg'][]=$e->message;
        }
        if($result['success'] === 1){
            Message::set('success',$msg);
        }
        
        return $result;
    }
    
    public static function controller_save($data=array()){
        $result = array('success'=>1,"msg"=>array());

        $db = new DB();
        try{
            $db->trans_begin();

            $u_group_id = $data['u_group_id'];
            $tbl = 'u_group_security_controller';
            $prefix = 'security_controller_';


            $q_delete = 'delete from '.$tbl.' where u_group_id = '.$db->escape($u_group_id);
            if(!$db->query($q_delete)){
                $result['success'] = 0;
                $result['msg'][]=$db->_error_message();
            }


            if($result['success']!=0){
                foreach($data['controller'] as $controller){
                    $start = strlen($prefix);
                    $to = strlen($controller)-strlen($prefix);
                    $controller_id = substr($controller,$start,$to);
                    $data_inserted = array(
                        "u_group_id"=>$u_group_id
                        ,"security_controller_id"=>$controller_id
                    );
                    if(!$db->insert($tbl,$data_inserted)){
                        $result['success'] = 0;
                        $result['msg'][]=$db->_error_message();
                        break;
                    }
                }
            }

            if($db->trans_status()){
                $db->trans_commit();
            }
            else{
                $db->trans_rollback();
                $result['success'] = 0;
                $result['msg'][]=$db->_error_message();
            }
        }
        catch(Exception $e){
            $db->trans_rollback();
            $result['success'] = 0;
            $result['msg'][]=$e->message;
        }
        return $result;
    }

    public static function component_save($data=array()){
        $result = array('success'=>1,"msg"=>array());

        $db = new DB();
        try{
            $db->trans_begin();

            $u_group_id = $data['u_group_id'];
            $tbl = 'u_group_security_component';
            $prefix = 'security_component_';


            $q_delete = 'delete from '.$tbl.' where u_group_id = '.$db->escape($u_group_id);
            if(!$db->query($q_delete)){
                $result['success'] = 0;
                $result['msg'][]=$db->_error_message();
            }


            if($result['success']!=0){
                foreach($data['component'] as $component){
                    $start = strlen($prefix);
                    $to = strlen($component)-strlen($prefix);
                    $component_id = substr($component,$start,$to);
                    $data_inserted = array(
                        "u_group_id"=>$u_group_id
                        ,"security_component_id"=>$component_id
                    );
                    if(!$db->insert($tbl,$data_inserted)){
                        $result['success'] = 0;
                        $result['msg'][]=$db->_error_message();
                        break;
                    }
                }
            }

            if($db->trans_status()){
                $db->trans_commit();
            }
            else{
                $db->trans_rollback();
                $result['success'] = 0;
                $result['msg'][]=$db->_error_message();
            }
        }
        catch(Exception $e){
            $db->trans_rollback();
            $result['success'] = 0;
            $result['msg'][]=$e->message;
        }
        return $result;
    }

    public static function helper_save($data=array()){
        $result = array('success'=>0,"msg"=>array());

        $db = new DB();
        try{
            $db->trans_begin();

            $u_group_id = $data['u_group_id'];
            $tbl = 'u_group_security_helper';
            $prefix = 'security_helper_';


            $q_delete = 'delete from '.$tbl.' where u_group_id = '.$db->escape($u_group_id);
            if(!$db->query($q_delete)){
                $result['success'] = 0;
                $result['msg'][]=$db->_error_message();
            }


            if($result['success']!=0){
                foreach($data['helper'] as $helper){
                    $start = strlen($prefix);
                    $to = strlen($helper)-strlen($prefix);
                    $helper_id = substr($helper,$start,$to);
                    $data_inserted = array(
                        "u_group_id"=>$u_group_id
                        ,"security_helper_id"=>$helper_id
                    );
                    if(!$db->insert($tbl,$data_inserted)){
                        $result['success'] = 0;
                        $result['msg'][]=$db->_error_message();
                        break;
                    }
                }
            }

            if($db->trans_status()){
                $db->trans_commit();
                $result['success'] = 1;
            }
            else{
                $db->trans_rollback();
                $result['success'] = 0;
                $result['msg'][]=$db->_error_message();
            }
        }
        catch(Exception $e){
            $db->trans_rollback();
            $result['success'] = 0;
            $result['msg'][]=$e->message;
        }
        return $result;
    }



}
?>
