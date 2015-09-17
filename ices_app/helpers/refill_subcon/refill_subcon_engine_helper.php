<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Subcon_Engine {

    public static $status_list = array(
        //<editor-fold defaultstate="collapsed">
        array(
            'val'=>''
            ,'label'=>''
            , 'method'=>'refill_subcon_add'
            ,'next_allowed_status'=>array()
            ,'msg'=>array(
                'success'=>array(
                    array('val'=>'Add Refill - '),array('val'=>'Subcontractor'),array('val'=>'success')
                )
            )
        ),
        array(//label name is used for method name
            'val'=>'A'
            ,'label'=>'ACTIVE'
            ,'method'=>'refill_subcon_active'
            ,'default'=>true
            ,'next_allowed_status'=>array('I')
            ,'msg'=>array(
                'success'=>array(
                    array('val'=>'Update Refill - '),array('val'=>'Subcontractor'),array('val'=>'success')
                )
            )
        )
        ,array(
            'val'=>'I'
            ,'label'=>'INACTIVE'
            ,'method'=>'refill_subcon_inactive'
            ,'next_allowed_status'=>array('A')
            ,'msg'=>array(
                'success'=>array(
                    array('val'=>'Update Refill - '),array('val'=>'Subcontractor'),array('val'=>'success')
                )
            )
        )            
        //</editor-fold>
    );

    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'refill_subcon/'
            ,'refill_subcon_engine'=>'refill_subcon/refill_subcon_engine'
            ,'refill_subcon_data_support'=>'refill_subcon/refill_subcon_data_support'
            ,'refill_subcon_renderer' => 'refill_subcon/refill_subcon_renderer'
            ,'ajax_search'=>get_instance()->config->base_url().'refill_subcon/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'refill_subcon/data_support/'

        );

        return json_decode(json_encode($path));
    }

    public static function phone_exists_in_refill_subcon($id,$phone){
        //<editor-fold defaultstate="collapsed">
        $result = false;
        $db = new DB();
        $q = '
            select 1 
            from refill_subcon 
            where (
                    phone ='.$db->escape(preg_replace('/[^0-9]/','',$phone)).' 
                    or phone2 ='.$db->escape(preg_replace('/[^0-9]/','',$phone)).' 
                    or phone3 ='.$db->escape(preg_replace('/[^0-9]/','',$phone)).' 
                )
                and phone!="" 
                and id!='.$db->escape($id).'
        ';
        if(count($db->query_array_obj($q))>0){
            $result = true;
        }
        return $result;
        //</editor-fold>
    }

    public static function refill_subcon_get($id){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = null;
        $q = '
            select *
            , case refill_subcon_status when "A" then "ACTIVE"
                when "I" then "INACTIVE" end refill_subcon_status_name
            from refill_subcon
            where id = '.$db->escape($id).'
        ';
        $rs = $db->query_array_obj($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
        //</editor-fold>
    }

    public static function validate($action,$data=array()){
        //<editor-fold defaultstate="collapsed">
        $result = array(
            "success"=>1
            ,"msg"=>array()
        );

        $refill_subcon = isset($data['refill_subcon'])?$data['refill_subcon']:null;

        switch($action){
            case 'refill_subcon_add':
            case 'refill_subcon_active':
            case 'refill_subcon_inactive':

                $db = new DB();
                $refill_subcon_id = $data['refill_subcon']['id'];

                $refill_subcon_code = isset($refill_subcon['code'])?
                    Tools::_str($refill_subcon['code']):'';
                $refill_subcon_name = isset($refill_subcon['name'])?$refill_subcon['name']:'';
                $refill_subcon_name = str_replace(' ','',$refill_subcon_name);

                if(Tools::empty_to_null($refill_subcon_code) === null){
                    $result['success'] = 0;
                    $result['msg'][] = 'Code empty';
                }
                
                if(strlen($refill_subcon_name)==0){
                    $result['success'] = 0;
                    $result['msg'][] = "Name cannot be empty";
                }

                $phone = isset($refill_subcon['phone'])?
                        preg_replace('/[^0-9]/','',$refill_subcon['phone']):'';
                $phone2 = isset($refill_subcon['phone2'])?
                        preg_replace('/[^0-9]/','',$refill_subcon['phone2']):'';
                $phone3 = isset($refill_subcon['phone3'])?
                        preg_replace('/[^0-9]/','',$refill_subcon['phone3']):'';

                if($phone !== ''){
                    if(self::phone_exists_in_refill_subcon($refill_subcon_id,$phone)){
                        $result['success'] = 0;
                        $result['msg'][] = "Phone number already exists";
                    }
                    else{
                        if($phone == $phone2 || $phone == $phone3){
                            $result['success'] = 0;
                            $result['msg'][] = "Phone number already exists";
                        }
                    }
                }

                if($phone2 !== ''){

                    if(self::phone_exists_in_refill_subcon($refill_subcon_id,$phone2)){
                        $result['success'] = 0;
                        $result['msg'][] = "Phone 2 number already exists";
                    }
                    else{
                        if($phone2 == $phone || $phone2 == $phone3){
                            $result['success'] = 0;
                            $result['msg'][] = "Phone 2 number already exists";
                        }
                    }
                }

                if($phone3 !== ''){
                    if(self::phone_exists_in_refill_subcon($refill_subcon_id,$phone3)){
                        $result['success'] = 0;
                        $result['msg'][] = "Phone 3 number already exists";
                    }
                    else{
                        if($phone3 == $phone || $phone3 == $phone2){
                            $result['success'] = 0;
                            $result['msg'][] = "Phone 3 number already exists";
                        }
                    }
                }

                if(in_array($action,array('active','inactive'))){
                    $refill_subcon_id = isset($refill_subcon['id'])?$refill_subcon['id']:'';

                    $q = '
                        select * 
                        from refill_subcon 
                        where id = '.$db->escape($refill_subcon['id']).'
                    ';
                    $rs_refill_subcon = $db->query_array_obj($q);

                    if(count($rs_refill_subcon) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Refill - Subcontractor data is not available";
                        break;
                    }
                    else{
                        $rs_refill_subcon = $db->query_array_obj($q)[0];
                    }

                    //check receive product status is in list
                    $status_exists_in_list = false;
                    foreach (self::$refill_subcon_status_list as $status){
                        if($status['val'] === $refill_subcon['refill_subcon_status'])
                            $status_exists_in_list = true;
                    }
                    if(!$status_exists_in_list){
                        $result['success'] = 0;
                        $result['msg'][] = "Invalid Status";
                        break;
                    }

                }

                break;
            default:
                $result['success'] = 0;
                $result['msg'][] = 'Invalid Method';
                break;
        }


        return $result;
        //</editor-fold>
    }

    public static function adjust($method, $data=array()){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = array();

        $refill_subcon = isset($data['refill_subcon'])?$data['refill_subcon']:null;

        switch($method){
            case 'refill_subcon_add':
                $result['refill_subcon'] = array(
                    'code' => isset($refill_subcon['code'])?$refill_subcon['code']:'',
                    'name' => isset($refill_subcon['name'])?$refill_subcon['name']:'',
                    'address' => isset($refill_subcon['address'])?$refill_subcon['address']:'',
                    'notes' => isset($refill_subcon['notes'])?$refill_subcon['notes']:'',
                    'city' => isset($refill_subcon['city'])?$refill_subcon['city']:'',
                    'country' => isset($refill_subcon['country'])?$refill_subcon['country']:'',
                    'phone' => isset($refill_subcon['phone'])?
                        preg_replace('/[^0-9]/','',$refill_subcon['phone']):'',
                    'phone2' => isset($refill_subcon['phone2'])?
                        preg_replace('/[^0-9]/','',$refill_subcon['phone2']):'',
                    'phone3' => isset($refill_subcon['phone3'])?
                        preg_replace('/[^0-9]/','',$refill_subcon['phone3']):'',
                    'bb_pin'=>isset($refill_subcon['bb_pin'])?$refill_subcon['bb_pin']:'',
                    'email'=>isset($refill_subcon['email'])?$refill_subcon['email']:'',
                    'refill_subcon_status'=>SI::status_default_status_get('refill_subcon_engine')['val'],
                    
                );
                break;
            case 'refill_subcon_active':
            case 'refill_subcon_inactive':
                $result['refill_subcon'] = array(
                    'code' => isset($refill_subcon['code'])?$refill_subcon['code']:'',
                    'name' => isset($refill_subcon['name'])?$refill_subcon['name']:'',
                    'address' => isset($refill_subcon['address'])?$refill_subcon['address']:'',
                    'notes' => isset($refill_subcon['notes'])?$refill_subcon['notes']:'',
                    'city' => isset($refill_subcon['city'])?$refill_subcon['city']:'',
                    'country' => isset($refill_subcon['country'])?$refill_subcon['country']:'',
                    'phone' => isset($refill_subcon['phone'])?
                        preg_replace('/[^0-9]/','',$refill_subcon['phone']):'',
                    'phone2' => isset($refill_subcon['phone2'])?
                        preg_replace('/[^0-9]/','',$refill_subcon['phone2']):'',
                    'phone3' => isset($refill_subcon['phone3'])?
                        preg_replace('/[^0-9]/','',$refill_subcon['phone3']):'',
                    'bb_pin'=>isset($refill_subcon['bb_pin'])?$refill_subcon['bb_pin']:'',
                    'email'=>isset($refill_subcon['email'])?$refill_subcon['email']:'',
                    'refill_subcon_status'=>isset($refill_subcon['refill_subcon_status'])?
                        $refill_subcon['refill_subcon_status']:'',
                    
                );  
                break;
        }        

        return $result;
        //</editor-fold>
    }

    public function refill_subcon_add($db,$final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $frefill_subcon = $final_data['refill_subcon'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $refill_subcon_id = '';       
        
        if(!$db->insert('refill_subcon',$frefill_subcon)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        $refill_subcon_code = $frefill_subcon['code'];

        if($success == 1){                                
            $refill_subcon_id = $db->fast_get('refill_subcon'
                    ,array('code'=>$refill_subcon_code))[0]['id'];
            $result['trans_id']=$refill_subcon_id; 
        }

        if($success == 1){
            $refill_subcon_status_log = array(
                'refill_subcon_id'=>$refill_subcon_id
                ,'refill_subcon_status'=>$frefill_subcon['refill_subcon_status']
                ,'modid'=>$modid
                ,'moddate'=>$moddate    
            );

            if(!$db->insert('refill_subcon_status_log',$refill_subcon_status_log)){
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

    function refill_subcon_active($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $frefill_subcon = $final_data['refill_subcon'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('refill_subcon',$frefill_subcon,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            $temp_result = SI::status_log_add($db,'refill_subcon',
                $id,$frefill_subcon['refill_subcon_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    function refill_subcon_inactive($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        return Refill_Subcon_Engine::refill_subcon_active($db,$final_data,$id);
        //</editor-fold>
    }



}
?>
