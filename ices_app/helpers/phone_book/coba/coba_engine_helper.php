<?php
class Coba_Engine {
    public static $prefix_id = 'coba';
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
                ,'method'=>'coba_add'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Add')
                        ,array('val'=>Lang::get(array('Coba Controller'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            array(
                'val'=>'active'
                ,'text'=>'ACTIVE'
                ,'method'=>'coba_active'
                ,'next_allowed_status'=>array('inactive')
                ,'default'=>true
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update')
                        ,array('val'=>Lang::get(array('Coba Controller'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            array(
                'val'=>'inactive'
                ,'text'=>'INACTIVE'
                ,'method'=>'coba_inactive'
                ,'next_allowed_status'=>array('active')
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update')
                        ,array('val'=>Lang::get(array('Coba Controller'),true,true,false,false,true))
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
            'index'=>ICES_Engine::$app['app_base_url'].'coba/'
            ,'coba_engine'=>  ICES_Engine::$app['app_base_dir'].'coba/coba_engine'
            ,'coba_data_support'=>ICES_Engine::$app['app_base_dir'].'coba/coba_data_support'
            ,'coba_renderer' => ICES_Engine::$app['app_base_dir'].'coba/coba_renderer'
            ,'ajax_search'=>ICES_Engine::$app['app_base_url'].'coba/ajax_search/'
            ,'data_support'=>ICES_Engine::$app['app_base_url'].'coba/data_support/'

        );

        return json_decode(json_encode($path));
    }
    
    

    public static function validate($method,$data=array()){
        //<editor-fold defaultstate="collapsed">
        $path = Coba_Engine::path_get();
        get_instance()->load->helper($path->coba_data_support);
        
        $result = SI::result_format_get();
        
        $success = 1;
        $msg = array();
        
        $coba = isset($data['coba'])?Tools::_arr($data['coba']):array();
        $coba_id = $coba['id'];
        $coba_db = Coba_Data_Support::coba_get($coba_id);
        
        $db = new DB();
        switch($method){
            case self::$prefix_method.'_add':
            case self::$prefix_method.'_active':
            case self::$prefix_method.'_inactive':
                //<editor-fold defaultstate="collapsed">
                $name = Tools::empty_to_null(isset($coba['name'])?Tools::_str($coba['name']):null);
                $method_name = Tools::empty_to_null(isset($coba['method'])?Tools::_str($coba['method']):null);
                $app_name = Tools::empty_to_null(isset($coba['app_name'])?Tools::_str($coba['app_name']):null);
                //<editor-fold defaultstate="collapsed" desc="Major Validation">
                if(is_null($name) || is_null($method_name) || is_null($app_name)){
                    $success = 0;
                    $msg[] = 'APP Name'.' '.Lang::get('or').' '.'Name'.' '.Lang::get('or').' '.'Method '.' '.Lang::get('empty',true,false);
                }
                if($success !== 1) break;
                //</editor-fold>
                
                $q = '
                    select 1
                    from coba sc
                    where sc.status > 0
                        and sc.name = '.$db->escape($name).'
                        and sc.app_name = '.$db->escape($app_name).'
                        and sc.method = '.$db->escape($method_name).'
                        and sc.id <> '.$db->escape($coba_id).'
                ';
                
                if(count($db->query_array($q))>0){
                    $success = 0;
                    $msg[] = 'APP Name, Name, and Method exists';
                }
                
                if(in_array($method,array(self::$prefix_method.'_active',self::$prefix_method.'_inactive'))){
                    //<editor-fold defaultstate="collapsed">
                    if(!count($coba_db)>0){
                        $success = 0;
                        $msg[] = 'Invalid Coba Controller';
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

        $coba_data = isset($data['coba'])?$data['coba']:array();
                
        $coba_db = Coba_Data_Support::coba_get($coba_data['id']);
        
        $modid = User_Info::get()['user_id'];
        $datetime_curr = Date('Y-m-d H:i:s');
        
        switch($method){
            case self::$prefix_method.'_add':
            case self::$prefix_method.'_active':
            case self::$prefix_method.'_inactive':
                //<editor-fold defaultstate="collapsed">
                $coba = array(
                    'app_name'=>$coba_data['app_name'],
                    'name'=>$coba_data['name'],
                    'method'=>$coba_data['method'],
                    'status'=>1,
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                switch($method){
                    case self::$prefix_method.'_add':
                        $coba['coba_status'] = SI::type_default_type_get('Coba_Engine','$status_list')['val'];
                        break;
                    case self::$prefix_method.'_active':
                        $coba['coba_status'] = 'active';
                        break;
                    case self::$prefix_method.'_inactive':
                        $coba['coba_status'] = 'inactive';
                        break;
                }
                
                $result['coba'] = $coba;
                
                //</editor-fold>
                break;
        }
        
        
        
        
        
        return $result;
        //</editor-fold>
    }

    public function coba_add($db,$final_data,$id = ''){
        //<editor-fold defaultstate="collapsed">
        $path = Coba_Engine::path_get();
        get_instance()->load->helper($path->coba_data_support);
        $result = DB::result_format_get();
        $success = 1;
        $msg = array();

        $fcoba = $final_data['coba'];
                
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        if(!$db->insert('coba',$fcoba)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            $coba_id = $db->fast_get('coba'
                ,array('name'=>$fcoba['name'],
                    'method'=>$fcoba['method'],'status'=>1
                )
            )[0]['id'];
            $result['trans_id'] = $coba_id; 
        }
        
        if($success == 1){
            $temp_result = SI::status_log_add($db,'coba',
                $coba_id,$fcoba['coba_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    public function coba_active($db,$final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $path = Coba_Engine::path_get();
        get_instance()->load->helper($path->coba_data_support);
        $result = DB::result_format_get();
        $success = 1;
        $msg = array();

        $fcoba = $final_data['coba'];
                
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        $coba_id = $id;
        
        $result['trans_id'] = $id;
        
        if(!$db->update('coba',$fcoba,array('id'=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            $temp_result = SI::status_log_add($db,'coba',
                $coba_id,$fcoba['coba_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }

    public function coba_inactive($db,$final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result = self::coba_active($db, $final_data, $id);
        return $result;
        //</editor-fold>
    }
}
?>
