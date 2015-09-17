<?php
class User_Login_Engine {
    
    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'user_login/'
            ,'user_login_engine'=>'security/user_login_engine'
            ,'ajax_search'=>get_instance()->config->base_url().'user_login/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'user_login/data_support/'

        );

        return json_decode(json_encode($path));
    }
    
    public static function save($user_login_data){
        $result = array('success'=>1, 'msg'=>array(),'trans_id'=>'');
        $db = new DB();
        $success = 1;
        $msg = array();
        $action = "";
        $trans_id = '';

        if(strlen($user_login_data['id'])==0){
            unset($user_login_data['id']);
            $action = "insert";
        }
        else{
            $action = "update";
            if(isset($user_login_data['status'])){
                if($user_login_data['status'] == 0) $action = "delete";
            }

        }
        
        if(in_array($action,array("insert","update"))){
            $validation_res = self::validate($user_login_data);
            $success = $validation_res['success']; 
            $msg = $validation_res['msg'];
        }
        
        if($success == 1){
            $user_login_data = self::adjust($user_login_data);
            switch($action){                    
                case 'insert':
                    $new_id = $db->query_array_obj('select max(id)+1 new_id from user_login')[0]->new_id;
                    $trans_id = $new_id;
                    $user_login_data['id']=$new_id;
                    $user_data = array(
                        "id"=>$new_id
                        ,'name'=>$user_login_data['name']
                        ,'password'=>md5($user_login_data['password'])
                        ,'first_name'=>$user_login_data['first_name']
                        ,'last_name'=>$user_login_data['last_name']
                        ,'default_store_id'=>$user_login_data['default_store_id']
                        ,'modid'=>User_Info::get()['user_id']
                    );

                    $group_data = array(
                        "user_login_id"=>$new_id
                        ,"u_group_id"=>$user_login_data['u_group_id']
                        ,'modid'=> User_Info::get()['user_id']
                        ,'moddate'=>Date('Y-m-d H:i:s')
                    );
                    
                    $user_login_store_data = $user_login_data['user_login_store'];
                    
                    $db->trans_begin();

                    if(!$db->insert('user_login',$user_data)){
                        $success =0;
                        $msg[] = $db->_error_message();
                        break;
                    }

                    if($success === 1){
                        if(!$db->insert('user_login_u_group',$group_data)){
                            $success =0;
                            $msg[] = $db->_error_message();
                            break;
                        }
                    }

                    if($success === 1){
                        if(!$db->query('delete from user_login_store where user_login_id = '.$user_login_data['id'])){
                            $success =0;
                            $msg[] = $db->_error_message();
                            break;
                        }
                    }
                    
                    if($success === 1){
                        foreach($user_login_store_data as $idx=>$row){
                            $row['user_login_id'] = $user_login_data['id'];
                            
                            if(!$db->insert('user_login_store',$row)){
                                $success =0;
                                $msg[] = $db->_error_message();
                                break;
                            }
                        }
                    }
                    
                    if($success === 1){
                        if(!$db->insert('user_login_u_group_log',$group_data)){
                            $success =0;
                            $msg[] = $db->_error_message();
                            break;
                        }
                    }

                    if($success == 1){                            
                        $db->trans_commit();
                    }

                    $msg[] = 'Add User Login Success';
                    break;
                case 'update':
                    $id = $user_login_data['id'];
                    $trans_id = $id;
                    $pwd = $user_login_data['password'];
                    if(count($db->query_array_obj('select password from user_login where id = '.$db->escape($id).' and password='.$db->escape($pwd).'')) === 0){
                        $pwd = md5($pwd);
                    }
                    $user_data = array(
                        "id"=>$user_login_data['id']
                        ,'name'=>$user_login_data['name']
                        ,'password'=>$pwd
                        ,'first_name'=>$user_login_data['first_name']
                        ,'last_name'=>$user_login_data['last_name']
                        ,'default_store_id'=>$user_login_data['default_store_id']
                        ,'modid'=>User_Info::get()['user_id']
                    );

                    $group_data = array(
                        "user_login_id"=>$user_login_data['id']
                        ,"u_group_id"=>$user_login_data['u_group_id']
                        ,'modid'=> User_Info::get()['user_id']
                        ,'moddate'=>Date('Y-m-d H:i:s')
                    );
                    
                    $user_login_store_data = $user_login_data['user_login_store'];
                    
                    $db->trans_begin();

                    if(!$db->update('user_login',$user_data,array("id"=>$user_login_data['id']))){
                        $success =0;
                        $msg[] = $db->_error_message();
                        break;
                    }
                    if(!$db->query('delete from user_login_u_group where user_login_id = '.$user_login_data['id'])){
                        $success =0;
                        $msg[] = $db->_error_message();
                        break;
                    }
                    if(!$db->insert('user_login_u_group',$group_data)){
                        $success =0;
                        $msg[] = $db->_error_message();
                        break;
                    }
                    
                    if($success === 1){
                        if(!$db->query('delete from user_login_store where user_login_id = '.$user_login_data['id'])){
                            $success =0;
                            $msg[] = $db->_error_message();
                            break;
                        }
                    }
                    
                    if($success === 1){
                        foreach($user_login_store_data as $idx=>$row){
                            $row['user_login_id'] = $user_login_data['id'];
                            
                            if(!$db->insert('user_login_store',$row)){
                                $success =0;
                                $msg[] = $db->_error_message();
                                break;
                            }
                        }
                    }
                    
                    if($success === 1){
                        if(!$db->insert('user_login_u_group_log',$group_data)){
                            $success =0;
                            $msg[] = $db->_error_message();
                            break;
                        }
                    }

                    if($success == 1){
                        $db->trans_commit();
                    }


                    $msg[] = "Update User Login Success";
                    break;
                case 'delete':
                    $db->update('user_login',array("status"=>0),array("id"=>$user_login_data['id']));
                    $msg[] = "Delete User Login Success";
                    break;
            }  
        }

        if($success==1){
            Message::set('success',$msg);
        }
        else{
            Message::set('error',$msg);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['trans_id'] = $trans_id;
        return $result;
    }

    public static function validate($data=array()){
        $default_store_id = isset($data['default_store_id'])?
            Tools::empty_to_null(Tools::_str($data['default_store_id'])):null;
        
        $result = array(
            "success"=>1
            ,"msg"=>array()
        );
        if(strlen($data['name'])==0){
            $result['success'] = 0;
            $result['msg'][] = "Username cannot be empty";
        }
         if(strlen($data['password'])==0){
            $result['success'] = 0;
            $result['msg'][] = "Password cannot be empty";
        }
         if(strlen($data['first_name'])==0){
            $result['success'] = 0;
            $result['msg'][] = "First Name cannot be empty";
        }
         if(strlen($data['u_group_id'])==0){
            $result['success'] = 0;
            $result['msg'][] = "User Group cannot be empty";
        }

        if(strlen(str_replace(' ','',$data['default_store_id'])) === 0){
            $result['success'] = 0;
            $result['msg'][] = 'Default Store empty';
        }

        $db = new DB();
        $user_id = isset($data['id'])?$db->escape($data['id']):$db->escape('');
        $q = 'select 1 from user_login where status>0 and name = '.$db->escape($data['name'])
                .' and id !='.$user_id;
        $rs = $db->query_array_obj($q);

        if(count($rs)>0){
            $result['success'] = 0;
            $result['msg'][] = "Username already exists";
        }

        $q = '
            select 1
            from u_group
            where id = '.$db->escape($data['u_group_id']).' 
                and lower(name) ="root"
        ';
        if(count($db->query_array($q))>0){
            $result['success'] = 0;
            $result['msg'][] = 'Unable to add ROOT as user group';
        }

        $q = '
            select 1
            from user_login
            where id = '.$user_id.'
                and is_system = "1"
        ';
        if(count($db->query_array($q))>0){
            $result['success'] = 0;
            $result['msg'][] = 'Unable to update user system';
        }

        if(!count($data['store'])>0){
            $result['success'] = 0;
            $result['msg'][] = 'Store empty';
        }
        else{
            $default_store_id_exists = false;
            foreach($data['store'] as $idx=>$row){
                $store_id = isset($row['id'])?Tools::_str($row['id']):null;
                if(!is_null($store_id) && $store_id === $default_store_id){
                    $default_store_id_exists = true;
                }
            }
            if(!$default_store_id_exists){
                $result['success'] = 0;
                $result['msg'][] = 'Default Store does not exists in Store list';
            }
        }
        
        return $result;
    }

    public static function adjust($data=array()){
        $data['user_login_store'] = array();
        if(isset($data['store'])){
            foreach($data['store'] as $idx=>$row){
                $data['user_login_store'][] = array(
                    'store_id'=>$row['id']
                );
            }
        }
        unset($data['store']);
        $result = $data;
        
        return $result;
    }

    public static function get($id=""){
        $db = get_instance()->db;
        $q = "
            select t1.*, t3.name u_group_name, t4.name default_store_name
            from user_login t1
                left outer join user_login_u_group t2 on t1.id = t2.user_login_id
                left outer join u_group t3 on t3.id = t2.u_group_id
                left outer join store t4 on t4.id = t1.default_store_id and t4.status>0
            where t1.status>0 and t1.id = ".$db->escape($id);
        $rs = $db->query($q)->result_array();
        if(count($rs)>0) $rs = $rs[0];
        else $rs = null;
        return $rs;
    }

    public static function detail_render($pane,$data){
        //<editor-fold defaultstate="collapsed">
        $user_login = self::get($data['id']);
        $user_login = json_decode(json_encode($user_login));
        $pane->div_add()->div_set("class","form-group");
        $first_row = $pane->div_add()->div_set("class","form-group");
        $first_row->label_add()->label_set("value",'Code: ');
        $first_row->span_add()->span_set("value",$user_login->name);

        if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'user_login','edit')){
            $first_row->button_add()->button_set('value','Edit')
                    ->button_set('style','margin-left:20px')
                    ->button_set('icon','fa fa-pencil-square-o')
                    ->button_set('href',get_instance()->config->base_url().'user_login/edit/'.$user_login->id);
        }
        if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'user_login','delete')){
            $first_row->button_add()->button_set('value','Delete')
                    ->button_set('icon','fa fa-cut')
                    ->button_set('class','btn btn-danger')
                    ->button_set('confirmation',true)
                    ->button_set('confirmation msg','Are you sure want to delete '.$user_login->first_name.'?')
                    ->button_set('href',get_instance()->config->base_url().'user_login/delete/'.$data['id']);
        }


        $pane->label_span_add()->label_span_set("value",array('label'=>"Password: ","span"=>$user_login->password));
        $pane->label_span_add()->label_span_set("value",array('label'=>"First Name: ","span"=>$user_login->first_name));
        $pane->label_span_add()->label_span_set("value",array('label'=>"Last Name: ","span"=>$user_login->last_name));
        $pane->label_span_add()->label_span_set("value",array('label'=>"User Group: ","span"=>$user_login->u_group_name));
        $pane->label_span_add()->label_span_set("value",array('label'=>"Default Store: ","span"=>$user_login->default_store_name));

        $pane->hr_add();
        $pane->button_add()->button_set('value','BACK')
            ->button_set('icon','fa fa-arrow-left')
            ->button_set('class','btn btn-default')
            ->button_set('href',get_instance()->config->base_url().'user_login/index');
        //</editor-fold>
    }

    public static function u_group_log_render($pane, $data){
        //<editor-fold defaultstate="collapsed">
        $log_data = array();
        $db  = new DB();
        $q = '
            select
                ulug.*,
                ug.name ug_name,
                ul.name user_name
            from user_login_u_group_log ulug
                inner join u_group ug on ulug.u_group_id = ug.id
                inner join user_login ul on ulug.modid = ul.id 
            where ulug.user_login_id = '.$db->escape($data['id']).'
            order by ulug.id desc
            limit 20
        ';
        $rs = $db->query_array($q);

        if(count($rs)>0){
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                $rs[$i]['moddate'] = Tools::_date($rs[$i]['moddate'],'F d, Y H:i:s');

            }
            $log_data = $rs;
        }

        $config=array(
            'module_name'=>'user_login_u_group',
            'field_name'=>array(
                array('val'=>'row_num','label'=>'#'),
                array('val'=>'moddate','label'=>'Modified Date'),
                array('val'=>'ug_name','label'=>'User Group'),
                array('val'=>'user_name','label'=>'User Name'),
            ),
        );
        SI::form_renderer()->log_tab_render($pane, $config,$log_data);

        //</editor-fold>
    }

    public static function add_edit_render($path, $data, $form){
        //<editor-fold defaultstate="collapsed">
        $id = $data['id'];
        $db = new DB();
        $path = self::path_get();
        if(strlen($id)>0 ){
            $q = '
                select t1.id,t1.first_name,t1.last_name,t1.name,t1.password,t3.id u_group_id
                    ,t1.default_store_id
                from user_login t1
                left outer join user_login_u_group t2 on t2.user_login_id = t1.id
                left outer join u_group t3 on t2.u_group_id = t3.id
                where t1.status>0
                    and t1.id = '.$db->escape($id).'
            ';
            $rs = $db->query_array_obj($q);

            foreach($rs as $row){
                $data['id'] = $row->id;
                $data['first_name'] = $row->first_name;
                $data['last_name'] = $row->last_name;
                $data['name'] = $row->name;
                $data['password'] = $row->password;
                $data['u_group_id'] = $row->u_group_id;
                $data['default_store_id'] = $row->default_store_id;
            }
            
            $q = '
                select s.id, s.code, s.name
                from user_login_store uls
                    inner join store s on uls.store_id = s.id
                where uls.user_login_id='.$db->escape($id).'
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0){
                foreach($rs as $idx=>$row){
                    $data['store'][] = array(
                        'id'=>$row['id'],
                    );
                            
                }
            }
        }


        $form->input_add()->input_set('label','Username')->input_set('name','name')->input_set('icon','fa fa-user')->input_set('value',$data['name']);
        $form->input_add()->input_set('label','Password')->input_set('name','password')->input_set('icon','fa fa-key')->input_set('value',$data['password']);
        $form->input_add()->input_set('label','First Name')->input_set('name','first_name')->input_set('icon','fa fa-info')->input_set('value',$data['first_name']);
        $form->input_add()->input_set('label','Last Name')->input_set('name','last_name')->input_set('icon','fa fa-info')->input_set('value',$data['last_name']);

        get_instance()->load->helper('security/u_group_engine');
        $u_group = u_group_Engine::get($data['u_group_id']);

        $selected_u_group=array("id"=>"","data"=>"");
        $selected_default_store=array("id"=>"","data"=>"");

        if(count($u_group)>0){                    
            $selected_u_group['id'] = $u_group->id;
            $selected_u_group['data'] = $u_group->name;
        }

        $u_group_search = $form->input_select_add();
        $u_group_search->input_select_set('name','u_group_id')
                ->input_select_set('id','u_group')
                ->input_select_set('label','User Group')
                ->input_select_set('icon','fa fa-info')
                ->input_select_set('min_length','1')
                ->input_select_set('ajax_url',$path->index.'/ajax/u_group_search')
                ->input_select_set('value',$selected_u_group)
                ;

        $store_list = $db->query_array('select id id, name data from store where status>0');

        $q = 'select * from store where id = '.$db->escape($data['default_store_id']);
            $rs_default_store = $db->query_array_obj($q);
            if(count($rs_default_store)>0){
                $selected_default_store['id'] = $rs_default_store[0]->id;
                $selected_default_store['data'] = $rs_default_store[0]->name;
            }

        $default_store_search = $form->input_select_add();
        $default_store_search->input_select_set('name','default_store_id')
                ->input_select_set('id','default_store')
                ->input_select_set('label','Default Store')
                ->input_select_set('icon','fa fa-info')
                ->input_select_set('min_length','0')
                ->input_select_set('data_add',$store_list)
                ->input_select_set('value',$selected_default_store)
                ;
        
        $store_list = array();
        $q ='
            select id id,code, name
            from store
            where status>0
        ';
        
        $rs = $db->query_array($q);
        
        foreach($rs as $idx=>$row){
            $store_list[] = array(
                'id'=>$row['id'],
                'data'=>SI::html_tag('strong',$row['code']).' '.$row['name']
            );
        }
        
        $store_column = array(
            array(
                "name"=>"store_text"
                ,"label"=>"Store"
            )
        );
        
        $selected_store = array();
        foreach($data['store'] as $idx=>$row){
            $selected_store[] = $row['id'];
        }
        
        $store_ist = $form->input_select_table_add();
        $store_ist->input_select_set('name','store_id')
                ->input_select_set('id','input_select_store')
                ->input_select_set('label','Store')
                ->input_select_set('icon',APP_Icon::store())
                ->input_select_set('min_length','1')
                ->input_select_set('data_add',$store_list)
                ->input_select_set('value',array("id"=>"","data"=>""))
                ->table_set('columns',$store_column)
                ->table_set('id',"store_table")
                ->table_set('ajax_url',$path->index.'ajax/store_detail_get')
                ->table_set('column_key','id')
                ->table_set('allow_duplicate_id',false)
                ->table_set('selected_value',$selected_store);
                ;
        
        
        
        
        //</editor-fold>
    }
}
?>
