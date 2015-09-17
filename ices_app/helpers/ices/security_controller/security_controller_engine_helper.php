<?php
class Security_Controller_Engine {
    public static $prefix_id = 'security_controller';
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
                ,'method'=>'security_controller_add'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Add')
                        ,array('val'=>Lang::get(array('Security Controller'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            array(
                'val'=>'active'
                ,'text'=>'ACTIVE'
                ,'method'=>'security_controller_active'
                ,'next_allowed_status'=>array('inactive')
                ,'default'=>true
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update')
                        ,array('val'=>Lang::get(array('Security Controller'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            array(
                'val'=>'inactive'
                ,'text'=>'INACTIVE'
                ,'method'=>'security_controller_inactive'
                ,'next_allowed_status'=>array('active')
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update')
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
        $path = array(
            'index'=>ICES_Engine::$app['app_base_url'].'security_controller/'
            ,'security_controller_engine'=>  ICES_Engine::$app['app_base_dir'].'security_controller/security_controller_engine'
            ,'security_controller_data_support'=>ICES_Engine::$app['app_base_dir'].'security_controller/security_controller_data_support'
            ,'security_controller_renderer' => ICES_Engine::$app['app_base_dir'].'security_controller/security_controller_renderer'
            ,'ajax_search'=>ICES_Engine::$app['app_base_url'].'security_controller/ajax_search/'
            ,'data_support'=>ICES_Engine::$app['app_base_url'].'security_controller/data_support/'

        );

        return json_decode(json_encode($path));
    }
    
    

    public static function validate($method,$data=array()){
        //<editor-fold defaultstate="collapsed">
        $path = Security_Controller_Engine::path_get();
        get_instance()->load->helper($path->security_controller_data_support);
        
        $result = SI::result_format_get();
        
        $success = 1;
        $msg = array();
        
        $security_controller = isset($data['security_controller'])?Tools::_arr($data['security_controller']):array();
        $security_controller_id = $security_controller['id'];
        $security_controller_db = Security_Controller_Data_Support::security_controller_get($security_controller_id);
        
        $db = new DB();
        switch($method){
            case self::$prefix_method.'_add':
            case self::$prefix_method.'_active':
            case self::$prefix_method.'_inactive':
                //<editor-fold defaultstate="collapsed">
                $name = Tools::empty_to_null(isset($security_controller['name'])?Tools::_str($security_controller['name']):null);
                $method_name = Tools::empty_to_null(isset($security_controller['method'])?Tools::_str($security_controller['method']):null);
                $app_name = Tools::empty_to_null(isset($security_controller['app_name'])?Tools::_str($security_controller['app_name']):null);
                //<editor-fold defaultstate="collapsed" desc="Major Validation">
                if(is_null($name) || is_null($method_name) || is_null($app_name)){
                    $success = 0;
                    $msg[] = 'APP Name'.' '.Lang::get('or').' '.'Name'.' '.Lang::get('or').' '.'Method '.' '.Lang::get('empty',true,false);
                }
                if($success !== 1) break;
                //</editor-fold>
                
                $q = '
                    select 1
                    from security_controller sc
                    where sc.status > 0
                        and sc.name = '.$db->escape($name).'
                        and sc.app_name = '.$db->escape($app_name).'
                        and sc.method = '.$db->escape($method_name).'
                        and sc.id <> '.$db->escape($security_controller_id).'
                ';
                
                if(count($db->query_array($q))>0){
                    $success = 0;
                    $msg[] = 'APP Name, Name, and Method exists';
                }
                
                if(in_array($method,array(self::$prefix_method.'_active',self::$prefix_method.'_inactive'))){
                    //<editor-fold defaultstate="collapsed">
                    if(!count($security_controller_db)>0){
                        $success = 0;
                        $msg[] = 'Invalid Security Controller';
                    }
                    //</editor-fold>
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
        $db = new DB();
        $result = array();

        $security_controller_data = isset($data['security_controller'])?$data['security_controller']:array();
                
        $security_controller_db = Security_Controller_Data_Support::security_controller_get($security_controller_data['id']);
        
        $modid = User_Info::get()['user_id'];
        $datetime_curr = Date('Y-m-d H:i:s');
        
        switch($method){
            case self::$prefix_method.'_add':
            case self::$prefix_method.'_active':
            case self::$prefix_method.'_inactive':
                //<editor-fold defaultstate="collapsed">
                $security_controller = array(
                    'app_name'=>$security_controller_data['app_name'],
                    'name'=>$security_controller_data['name'],
                    'method'=>$security_controller_data['method'],
                    'status'=>1,
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                switch($method){
                    case self::$prefix_method.'_add':
                        $security_controller['security_controller_status'] = SI::type_default_type_get('Security_Controller_Engine','$status_list')['val'];
                        break;
                    case self::$prefix_method.'_active':
                        $security_controller['security_controller_status'] = 'active';
                        break;
                    case self::$prefix_method.'_inactive':
                        $security_controller['security_controller_status'] = 'inactive';
                        break;
                }
                
                $result['security_controller'] = $security_controller;
                
                //</editor-fold>
                break;
        }
        
        
        
        
        
        return $result;
        //</editor-fold>
    }

    public function security_controller_add($db,$final_data,$id = ''){
        //<editor-fold defaultstate="collapsed">
        $path = Security_Controller_Engine::path_get();
        get_instance()->load->helper($path->security_controller_data_support);
        $result = DB::result_format_get();
        $success = 1;
        $msg = array();

        $fsecurity_controller = $final_data['security_controller'];
                
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        if(!$db->insert('security_controller',$fsecurity_controller)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            $security_controller_id = $db->fast_get('security_controller'
                ,array('name'=>$fsecurity_controller['name'],
                    'method'=>$fsecurity_controller['method'],'status'=>1
                )
            )[0]['id'];
            $result['trans_id'] = $security_controller_id; 
        }
        
        if($success == 1){
            $temp_result = SI::status_log_add($db,'security_controller',
                $security_controller_id,$fsecurity_controller['security_controller_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    public function security_controller_active($db,$final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $path = Security_Controller_Engine::path_get();
        get_instance()->load->helper($path->security_controller_data_support);
        $result = DB::result_format_get();
        $success = 1;
        $msg = array();

        $fsecurity_controller = $final_data['security_controller'];
                
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        $security_controller_id = $id;
        
        $result['trans_id'] = $id;
        
        if(!$db->update('security_controller',$fsecurity_controller,array('id'=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            $temp_result = SI::status_log_add($db,'security_controller',
                $security_controller_id,$fsecurity_controller['security_controller_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }

    public function security_controller_inactive($db,$final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result = self::security_controller_active($db, $final_data, $id);
        return $result;
        //</editor-fold>
    }
}
?>
