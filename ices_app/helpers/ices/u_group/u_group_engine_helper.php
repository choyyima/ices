<?php
class U_Group_Engine {
    public static $prefix_id = 'u_group';
    public static $prefix_method;
    public static $status_list; 

    public static function helper_init(){
        //<editor-fold defaultstate="collapsed">
        self::$prefix_method = self::$prefix_id;
        
        self::$status_list = array(
            //<editor-fold defaultstate="collapsed">
            array(
                'val'=>''
                ,'text'=>''
                ,'method'=>'u_group_add'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Add')
                        ,array('val'=>Lang::get(array('User Group'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            array(
                'val'=>'active'
                ,'text'=>'ACTIVE'
                ,'method'=>'u_group_active'
                ,'next_allowed_status'=>array('inactive')
                ,'default'=>true
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update')
                        ,array('val'=>Lang::get(array('User Group'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            array(
                'val'=>'inactive'
                ,'text'=>'INACTIVE'
                ,'method'=>'u_group_inactive'
                ,'next_allowed_status'=>array('active')
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update')
                        ,array('val'=>Lang::get(array('User Group'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            array(
                'val'=>''
                ,'text'=>''
                ,'method'=>'security_menu_save'
                ,'next_allowed_status'=>array()
                ,'user_select_next_allowed_status'=>'false'
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Save')
                        ,array('val'=>Lang::get(array('Security Menu'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            array(
                'val'=>''
                ,'text'=>''
                ,'method'=>'security_controller_save'
                ,'next_allowed_status'=>array()
                ,'user_select_next_allowed_status'=>'false'
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Save')
                        ,array('val'=>Lang::get(array('Security Controller'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            //</editor-fold>
        );
        
        //</editor-fold>
    }
    
    public static function path_get(){
        $app = SI::type_get('ICES_Engine','ices','$app_list');
        $path = array(
            'index'=>$app['app_base_url'].'u_group/'
            ,'u_group_engine'=>  $app['app_base_dir'].'u_group/u_group_engine'
            ,'u_group_data_support'=>$app['app_base_dir'].'u_group/u_group_data_support'
            ,'u_group_renderer' => $app['app_base_dir'].'u_group/u_group_renderer'
            ,'ajax_search'=>$app['app_base_url'].'u_group/ajax_search/'
            ,'data_support'=>$app['app_base_url'].'u_group/data_support/'

        );

        return json_decode(json_encode($path));
    }
    
    

    public static function validate($method,$data=array()){
        //<editor-fold defaultstate="collapsed">
        $path = U_Group_Engine::path_get();
        get_instance()->load->helper($path->u_group_data_support);
        
        $result = SI::result_format_get();
        
        $success = 1;
        $msg = array();
        
        $u_group = isset($data['u_group'])?Tools::_arr($data['u_group']):array();
        $u_group_id = Tools::_str(isset($u_group['id'])?$u_group['id']:'');
        $u_group_db = U_Group_Data_Support::u_group_get($u_group_id);
        
        $security_menu = Tools::_arr(isset($data['security_menu'])?
            $data['security_menu']:array());
        $security_controller = Tools::_arr(isset($data['security_controller'])?
            $data['security_controller']:array());
        
        $db = new DB();
        switch($method){
            case self::$prefix_method.'_add':
            case self::$prefix_method.'_active':
            case self::$prefix_method.'_inactive':
                //<editor-fold defaultstate="collapsed">
                $name = Tools::empty_to_null(isset($u_group['name'])?Tools::_str($u_group['name']):null);
                $app_name = Tools::empty_to_null(isset($u_group['app_name'])?Tools::_str($u_group['app_name']):null);
                //<editor-fold defaultstate="collapsed" desc="Major Validation">
                if(is_null($name) || is_null($app_name)){
                    $success = 0;
                    $msg[] = 'APP Name'.' '.Lang::get('or').' '.'Name'.' '.Lang::get('or').' '.Lang::get('empty',true,false);
                }
                if($success !== 1) break;
                //</editor-fold>
                
                $q = '
                    select 1
                    from u_group sc
                    where sc.status > 0
                        and sc.name = '.$db->escape($name).'
                        and sc.app_name = '.$db->escape($app_name).'
                        and sc.id <> '.$db->escape($u_group_id).'
                ';
                
                if(count($db->query_array($q))>0){
                    $success = 0;
                    $msg[] = 'APP Name and Method exists';
                }
                
                if(in_array($method,array(self::$prefix_method.'_active',self::$prefix_method.'_inactive'))){
                    //<editor-fold defaultstate="collapsed">
                    if(!count($u_group_db)>0){
                        $success = 0;
                        $msg[] = 'Invalid User Group';
                    }
                    
                    if($success === 1){
                        if($u_group_db['name'] === 'ROOT'){
                            $success =0;
                            $msg[] = 'Cannot update ROOT';
                        }
                    }
                    //</editor-fold>
                }
                
                //</editor-fold>
                break;
            case 'security_menu_save':
                if(!count($u_group_db)>0){
                    $success = 0;
                    $msg[] = 'User Group empty';
                }
                break;
            case 'security_controller_save':
                //<editor-fold defaultstate="collapsed">
                $controller = Tools::_arr(isset($security_controller['controller'])?
                    $security_controller['controller']:array()
                );
                $q_sec_cont = '';
                foreach($controller as $idx=>$row){
                    if($q_sec_cont === '') $q_sec_cont = $db->escape($row);
                    else $q_sec_cont.=','.$db->escape($row);
                }
                $q = '
                    select count(1) total_sc
                    from(
                        select distinct sc.id
                        from security_controller sc
                        where sc.id in ('.$q_sec_cont.')
                            and sc.status > 0
                            and sc.security_controller_status = "active"
                            and sc.app_name = '.$db->escape($u_group_db['app_name']).'
                    ) t1
                    
                ';
                $rs = $db->query_array($q);
                if($rs[0]['total_sc'] != count($controller)){
                    $success = 0;
                    $msg[] = 'Invalid Security Controller';
                }
                
                //</editor-fold>
                break;
            default:
                $success = 0;
                $msg[] = 'Invalid Method';
                break;
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        
        return $result;
        //</editor-fold>
    }

    public static function adjust($method, $data=array()){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper(self::path_get()->u_group_data_support);
        $db = new DB();
        $result = array();

        $u_group_data = Tools::_arr(isset($data['u_group'])?$data['u_group']:array());
        $security_menu_data = Tools::_arr(isset($data['security_menu'])?$data['security_menu']:array());
        $menu_data = Tools::_arr(isset($security_menu_data['menu'])?$security_menu_data['menu']:array());
        $security_controller_data = Tools::_arr(isset($data['security_controller'])?$data['security_controller']:array());
        $controller_data = Tools::_arr(isset($security_controller_data['controller'])?$security_controller_data['controller']:array());
        
        $u_group_db = U_Group_Data_Support::u_group_get($u_group_data['id']);
        
        $modid = User_Info::get()['user_id'];
        $datetime_curr = Date('Y-m-d H:i:s');
        
        switch($method){
            case self::$prefix_method.'_add':
            case self::$prefix_method.'_active':
            case self::$prefix_method.'_inactive':
                //<editor-fold defaultstate="collapsed">
                $u_group = array(
                    'app_name'=>$u_group_data['app_name'],
                    'name'=>$u_group_data['name'],
                    'status'=>1,
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                switch($method){
                    case self::$prefix_method.'_add':
                        $u_group['u_group_status'] = SI::type_default_type_get('U_Group_Engine','$status_list')['val'];
                        break;
                    case self::$prefix_method.'_active':
                        $u_group['u_group_status'] = 'active';
                        break;
                    case self::$prefix_method.'_inactive':
                        $u_group['u_group_status'] = 'inactive';
                        break;
                }
                
                $result['u_group'] = $u_group;
                
                //</editor-fold>
                break;            
            case 'security_menu_save':
                //<editor-fold defaultstate="collapsed">

                $u_group_security_menu = array();
                foreach($menu_data as $idx=>$row){
                    $u_group_security_menu[] = array(
                        'app_name'=>$u_group_db['app_name'],
                        'u_group_id'=>$u_group_db['id'],
                        'menu_id'=>$row,
                        'status'=>1,
                        'modid'=>$modid,
                        'moddate'=>$datetime_curr
                    );
                }
                
                $u_group = array(
                    'id'=>$u_group_data['id']
                );
                
                $result['u_group_security_menu'] = $u_group_security_menu;
                $result['u_group'] = $u_group;
                //</editor-fold>
                break;
            case 'security_controller_save':
                //<editor-fold defaultstate="collapsed">
                $u_group_security_controller = array();
                foreach($controller_data as $idx=>$row){
                    $u_group_security_controller[] = array(
                        'u_group_id'=>$u_group_db['id'],
                        'security_controller_id'=>$row,
                        'status'=>1,
                        'modid'=>$modid,
                        'moddate'=>$datetime_curr
                    );
                }
                
                $u_group = array(
                    'id'=>$u_group_data['id']
                );
                
                $result['u_group_security_controller'] = $u_group_security_controller;
                $result['u_group'] = $u_group;
                //</editor-fold>
                break;
        }
        
        
        
        
        
        return $result;
        //</editor-fold>
    }

    public function u_group_add($db,$final_data,$id = ''){
        //<editor-fold defaultstate="collapsed">
        $path = U_Group_Engine::path_get();
        get_instance()->load->helper($path->u_group_data_support);
        $result = DB::result_format_get();
        $success = 1;
        $msg = array();

        $fu_group = $final_data['u_group'];
                
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        if(!$db->insert('u_group',$fu_group)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            $u_group_id = $db->fast_get('u_group'
                ,array('name'=>$fu_group['name'],
                    'app_name'=>$fu_group['app_name'],'status'=>1
                )
            )[0]['id'];
            $result['trans_id'] = $u_group_id;
        }
        
        if($success == 1){
            $temp_result = SI::status_log_add($db,'u_group',
                $u_group_id,$fu_group['u_group_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    public function u_group_active($db,$final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $path = U_Group_Engine::path_get();
        get_instance()->load->helper($path->u_group_data_support);
        $result = DB::result_format_get();
        $success = 1;
        $msg = array();

        $fu_group = $final_data['u_group'];
                
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        $u_group_id = $id;
        
        $result['trans_id'] = $id;
        
        if(!$db->update('u_group',$fu_group,array('id'=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            $temp_result = SI::status_log_add($db,'u_group',
                $u_group_id,$fu_group['u_group_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }

    public function u_group_inactive($db,$final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result = self::u_group_active($db, $final_data, $id);
        return $result;
        //</editor-fold>
    }
    
    public function security_menu_save($db,$final_data,$id = ''){
        //<editor-fold defaultstate="collapsed">
        $path = U_Group_Engine::path_get();
        get_instance()->load->helper($path->u_group_data_support);
        $result = DB::result_format_get();
        $success = 1;
        $msg = array();

        $fu_group = $final_data['u_group'];
        $fu_group_security_menu = $final_data['u_group_security_menu'];
        $u_group_id = $fu_group['id'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        $result['trans_id'] = $u_group_id;
                
        $q = '
            delete from u_group_security_menu
            where u_group_id = '.$db->escape($u_group_id).'
        ';
        if(!$db->query($q)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            foreach($fu_group_security_menu as $idx=>$row){
                if(!$db->insert('u_group_security_menu',$row)){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                    break;
                }
            }
            
            
        }
                
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    public function security_controller_save($db,$final_data,$id = ''){
        //<editor-fold defaultstate="collapsed">
        $path = U_Group_Engine::path_get();
        get_instance()->load->helper($path->u_group_data_support);
        $result = DB::result_format_get();
        $success = 1;
        $msg = array();

        $fu_group = $final_data['u_group'];
        $fu_group_security_controller = $final_data['u_group_security_controller'];
        $u_group_id = $fu_group['id'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        $result['trans_id'] = $u_group_id;
                
        $q = '
            delete from u_group_security_controller
            where u_group_id = '.$db->escape($u_group_id).'
        ';
        if(!$db->query($q)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            foreach($fu_group_security_controller as $idx=>$row){
                if(!$db->insert('u_group_security_controller',$row)){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                    break;
                }
            }
            
            
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
}
?>
