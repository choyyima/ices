<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class U_Profile_Engine {
    public static $prefix_id = 'u_profile';
    public static $prefix_method;
    static $status_list;
    
    public static function helper_init(){
        //<editor-fold defaultstate="collapsed">
        self::$prefix_method = self::$prefix_id;
        self::$status_list = array(
        //<editor-fold defaultstate="collapsed">
            array(
                'val'=>''
                ,'label'=>''
                ,'method'=>self::$prefix_method.'_update'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update'),array('val'=>'User Profile'),array('val'=>'success')
                    )
                )
            ),
            
        //</editor-fold>
        );
        //</editor-fold>
    }
    
    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'u_profile/'
            ,'u_profile_engine'=>'u_profile/u_profile_engine'
            ,'u_profile_renderer' => 'u_profile/u_profile_renderer'
            ,'u_profile_data_support' => 'u_profile/u_profile_data_support'
            ,'ajax_search'=>get_instance()->config->base_url().'u_profile/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'u_profile/data_support/'

        );

        return json_decode(json_encode($path));
    }

    public static $module_list = array(
        array(
            'val'=>'refund_item_paid','label'=>'Paid Refund Item'            
        ),
    );
    
    public static function validate($method,$data=array()){  
        //<editor-fold defaultstate="collapsed">
        
        $result = array(
            "success"=>1
            ,"msg"=>array()

        );
        $success = 1;
        $msg = array();
        $u_profile = isset($data['u_profile'])?Tools::_arr($data['u_profile']):array();
        
        
        switch($method){
            
            case self::$prefix_method.'_update':
                $first_name = isset($u_profile['first_name'])?Tools::_str($u_profile['first_name']):'';
                $last_name = isset($u_profile['last_name'])?Tools::_str($u_profile['last_name']):'';
                $password = isset($u_profile['password'])?Tools::_str($u_profile['password']):'';
                
                if(strlen(str_replace(' ','',$first_name)) === 0){
                    $success = 0;
                    $msg[] = 'First Name empty';
                }
                
                
                if(strlen(str_replace(' ','',$password)) === 0){
                    $success = 0;
                    $msg[] = 'Password empty';
                }
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

    public static function adjust($action,$data=array()){
        //<editor-fold defaultstate="collpased">
        $db = new DB();
        $result = array();
        $u_profile_data = isset($data['u_profile'])?
            Tools::_arr($data['u_profile']):array();
        
        $modid = User_Info::get()['user_id'];
        $datetime_curr = Date('Y-m-d H:i:s');
        switch($action){
            case self::$prefix_method.'_update':                
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('security/user_login_engine');
                $user_login_db = User_Login_Engine::get($u_profile_data['id']);
                $old_password_md5 = $user_login_db['password'];
                $user_login = array();
                if ($old_password_md5 !== $u_profile_data['password']){
                    $user_login = array(
                        'first_name'=>Tools::_str($u_profile_data['first_name'])
                        ,'last_name'=>Tools::_str($u_profile_data['last_name'])
                        ,'password'=>md5(Tools::_str($u_profile_data['password']))
                        ,'modid'=>$modid
                        ,'moddate'=>$datetime_curr

                    );
                }
                else{
                    $user_login = array(
                        'first_name'=>Tools::_str($u_profile_data['first_name'])
                        ,'last_name'=>Tools::_str($u_profile_data['last_name'])
                        ,'modid'=>$modid
                        ,'moddate'=>$datetime_curr

                    );
                }
                
                $result['user_login'] = $user_login;
                
                
                
                //</editor-fold>  
                break;
        }

        return $result;
        //</editor-fold>
    }    
    
    function u_profile_update($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $fuser_login = $final_data['user_login'];
                
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('user_login',$fuser_login,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success === 1){
            User_Info::set($id);
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }
    



}
?>
