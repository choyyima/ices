<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Comp_Mail_Manager_Engine {
    public static $prefix_id = 'comp_mail_manager';
    public static $prefix_method;
    public static $status_list;
    public static $module_type_list;
    public static $stock_location_list;

    public static function helper_init(){
        //<editor-fold defaultstate="collapsed">
        self::$prefix_method = self::$prefix_id;
        
        self::$status_list = array(
            //<editor-fold defaultstate="collapsed">
            array(
                'val'=>''
                ,'label'=>''
                ,'method'=>'comp_mail_manager_save'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update')
                        ,array('val'=>Lang::get(array('Mail Manager'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
             
            //</editor-fold>
        );
       
        self::$module_type_list = array(
            //<editor-fold defaultstate="collapsed">
            
            //</editor-fold>
        );
        
        self::$stock_location_list = array(
            //<editor-fold defaultstate="collapsed">
            
            //</editor-fold>
        );
        
        //</editor-fold>
    }
    
    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'comp_mail_manager/'
            ,'comp_mail_manager_engine'=>'comp_mail_manager/comp_mail_manager_engine'
            ,'comp_mail_manager_data_support'=>'comp_mail_manager/comp_mail_manager_data_support'
            ,'comp_mail_manager_renderer' => 'comp_mail_manager/comp_mail_manager_renderer'
            ,'ajax_search'=>get_instance()->config->base_url().'comp_mail_manager/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'comp_mail_manager/data_support/'

        );

        return json_decode(json_encode($path));
    }

    public static function validate($action,$data=array()){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('comp_mail_manager/comp_mail_manager_data_support');
        
        
        $result = array(
            "success"=>1
            ,"msg"=>array()
        );
        $success = 1;
        $msg = array();
        
        $comp_mail_manager = isset($data['comp_mail_manager'])?Tools::_arr($data['comp_mail_manager']):null;
        $company_mail = isset($comp_mail_manager['company_mail'])?Tools::_arr($comp_mail_manager['company_mail']):array();
        $db = new DB();
        switch($action){
            case self::$prefix_method.'_save':
                //<editor-fold defaultstate="collapsed">
                if(!count($company_mail)>0){
                    $success = 0;
                    $msg[] = 'Company Mail'.' '.Lang::get('empty');
                    break;
                }
                
                foreach($company_mail as $idx=>$row){
                    $id = isset($row['id'])?Tools::empty_to_null(Tools::_str($row['id'])):null;
                    $username = isset($row['username'])?Tools::empty_to_null(Tools::_str($row['username'])):null;
                    $password = isset($row['password'])?Tools::empty_to_null(Tools::_str($row['password'])):null;
                    
                    if($username === null){
                        $success = 0;
                        $msg[] = 'ID'.' '.Lang::get('empty');
                    }
                    
                    if($username === null){
                        $success = 0;
                        $msg[] = 'Username'.' '.Lang::get('empty');
                    }
                    
                    if($password === null){
                        $success = 0;
                        $msg[] = 'Password'.' '.Lang::get('empty');
                    }
                    
                    if($success !== 1) break;
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

        $comp_mail_manager_data = isset($data['comp_mail_manager'])?$data['comp_mail_manager']:array();
        $company_mail_data = isset($comp_mail_manager_data['company_mail'])?Tools::_arr($comp_mail_manager_data['company_mail']):array();

        $modid = User_Info::get()['user_id'];
        $datetime_curr = Date('Y-m-d H:i:s');
        
        switch($method){
            case self::$prefix_method.'_save':
                //<editor-fold defaultstate="collapsed">
                foreach($company_mail_data as $idx=>$row){
                    $company_mail[] = array(
                        'id'=>$row['id'],
                        'username'=>$row['username'],
                        'password'=>$row['password'],
                        'modid'=>$modid,
                        'moddate'=>$datetime_curr
                    );
                }
                
                $result['company_mail'] = $company_mail;
                
                //</editor-fold>
                break;
                
        }        

        return $result;
        //</editor-fold>
    }

    public function comp_mail_manager_save($db,$final_data,$id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('comp_mail_manager/comp_mail_manager_engine');
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $fcompany_mail = $final_data['company_mail'];
        
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        foreach($fcompany_mail as $idx=>$row){
            if(!$db->update('company_mail',$row,array('id'=>$row['id']))){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
                break;
            }
        }
        
        if($success == 1){
            $result['trans_id']=''; 
        }
        

        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
}
?>
