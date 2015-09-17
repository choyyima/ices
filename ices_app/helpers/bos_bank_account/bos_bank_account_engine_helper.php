<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bos_Bank_Account_Engine {
    public static $prefix_id = 'bba';
    static $status_list;
    
    public static function helper_init(){
        //<editor-fold defaultstate="collapsed">
        self::$status_list = array(
            array(
                'val'=>''
                ,'label'=>''
                , 'method'=>'bba_add'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Add')
                        ,array('val'=>Lang::get(array('BOS Bank Account'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            array(
                'val'=>'active'
                ,'label'=>'ACTIVE'
                ,'method'=>'bba_active'
                ,'default'=>true
                ,'next_allowed_status'=>array('I')
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update')
                        ,array('val'=>Lang::get(array('BOS Bank Account'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            )
            ,array(
                'val'=>'inactive'
                ,'label'=>'INACTIVE'
                ,'method'=>'bba_inactive'
                ,'next_allowed_status'=>array('A')
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update')
                        ,array('val'=>Lang::get(array('BOS Bank Account'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            )

        );
                
        //</editor-fold>
    }
    
    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'bos_bank_account/'
            ,'bos_bank_account_engine'=>'bos_bank_account/bos_bank_account_engine'
            ,'bos_bank_account_data_support'=>'bos_bank_account/bos_bank_account_data_support'
            ,'bos_bank_account_renderer' => 'bos_bank_account/bos_bank_account_renderer'
            ,'ajax_search'=>get_instance()->config->base_url().'bos_bank_account/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'bos_bank_account/data_support/'

        );

        return json_decode(json_encode($path));
    }

    public static function validate($action,$data=array()){
        get_instance()->load->helper('bos_bank_account/bos_bank_account_data_support');
        $result = array(
            "success"=>1
            ,"msg"=>array()
        );
        
        switch($action){
            case 'bba_add':
            case 'bba_active':
            case 'bba_inactive':
                $bos_bank_account = isset($data['bos_bank_account'])?$data['bos_bank_account']:null;
                $db = new DB();
                $bos_bank_account_id = $data['bos_bank_account']['id'];

                $code = Tools::empty_to_null(isset($data['bos_bank_account']['code'])?$data['bos_bank_account']['code']:'');
                $bank_name = Tools::empty_to_null(isset($data['bos_bank_account']['bank_name'])?$data['bos_bank_account']['bank_name']:'');
                $account_number = Tools::empty_to_null(isset($data['bos_bank_account']['bank_name'])?$data['bos_bank_account']['account_number']:'');
                
                if(is_null($code)){
                    $result['success'] = 0;
                    $result['msg'][] = Lang::get("Code").' '.Lang::get('empty',true,false,false,true);
                }

                if(is_null($bank_name)){
                    $result['success'] = 0;
                    $result['msg'][] = Lang::get("Bank Name").' '.Lang::get('empty',true,false,false,true);
                }
                
                if(is_null($account_number)){
                    $result['success'] = 0;
                    $result['msg'][] = Lang::get("Account Number").' '.Lang::get('empty',true,false,false,true);
                }
                
                $q = '
                    select *
                    from bos_bank_account bba
                    where bba.code = '.$db->escape($code).'
                        and bba.id <> '.$db->escape($bos_bank_account_id).'
                ';
                
                if(count($db->query_array($q))>0){
                    $result['success'] = 0;
                    $result['msg'][] = Lang::get('Code').' '.Lang::get('exists',true,false);
                }
                break;
            
            default:
                $result['success'] = 0;
                $result['msg'][] = 'Invalid Method';
                break;
        }


        return $result;
    }

    public static function adjust($method, $data=array()){
        $db = new DB();
        $result = array();

        $bos_bank_account = isset($data['bos_bank_account'])?$data['bos_bank_account']:null;

        switch($method){
            case 'bba_add':
            case 'bba_active':
            case 'bba_inactive':
                $result['bos_bank_account'] = array(
                    'code' =>Tools::_str($bos_bank_account['code']),
                    'bank_name' =>Tools::_str($bos_bank_account['bank_name']),
                    'account_number' =>Tools::_str($bos_bank_account['account_number']),
                    'notes' => Tools::empty_to_null(isset($bos_bank_account['notes'])?$bos_bank_account['notes']:''),
                );
                break;            
        }        
        
        switch($method){
            case 'bba_add':
                $result['bos_bank_account']['bos_bank_account_status'] = SI::type_default_type_get('bos_bank_account_engine', '$status_list')['val'];
                break;            
            case 'bba_active':
                $result['bos_bank_account']['bos_bank_account_status'] = 'active';
                break;
            case 'bba_inactive':
                $result['bos_bank_account']['bos_bank_account_status'] = 'inactive';
                break;            
        }   
        
        return $result;
    }

    public function bba_add($db, $final_data, $id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();
        
        $fbos_bank_account = $final_data['bos_bank_account'];
        $bos_bank_account_id = '';
        $db->trans_begin();
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        $fbos_bank_account = array_merge($fbos_bank_account,array("modid"=>$modid,"moddate"=>$moddate));
        if(!$db->insert('bos_bank_account',$fbos_bank_account)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success){
            $result['trans_id'] = SI::get_trans_id($db,'bos_bank_account','code',$fbos_bank_account['code']);
            if($result['trans_id'] === null){
                $msg[] = 'Unable to get trans id';
                $db->trans_rollback();                                
                $success = 0;
            }
            $bos_bank_account_id = $result['trans_id'];
        }

        if($success == 1){
            $bos_bank_account_status_log = array(
                'bos_bank_account_id'=>$bos_bank_account_id
                ,'bos_bank_account_status'=>$fbos_bank_account['bos_bank_account_status']
                ,'modid'=>$modid
                ,'moddate'=>$moddate    
            );

            if(!$db->insert('bos_bank_account_status_log',$bos_bank_account_status_log)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }

        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        
        //</editor-fold>
    }
    
    public function bba_active($db, $final_data, $id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();
        
        $fbos_bank_account = $final_data['bos_bank_account'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        $bos_bank_account_id = $id;
        
        if(!$db->update('bos_bank_account',$fbos_bank_account,array("id"=>$bos_bank_account_id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }                            
        $result['trans_id']=$id;

        if($success == 1){
            $bos_bank_account_status_log = array(
                'bos_bank_account_id'=>$bos_bank_account_id
                ,'bos_bank_account_status'=>$fbos_bank_account['bos_bank_account_status']
                ,'modid'=>$modid
                ,'moddate'=>$moddate    
            );

            if(!$db->insert('bos_bank_account_status_log',$bos_bank_account_status_log)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    public function bba_inactive($db, $final_data, $id){
        //<editor-fold defaultstate="collapsed">
        return Bos_Bank_Account_Engine::bba_active($db,$final_data,$id);
        //</editor-fold>
    }
    

        
    }
?>
