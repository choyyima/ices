<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer_Refund_Engine {

    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'customer_refund/'
            ,'customer_refund_engine'=>'customer_refund/customer_refund_engine'
            ,'customer_refund_renderer' => 'customer_refund/customer_refund_renderer'
            ,'ajax_search'=>get_instance()->config->base_url().'customer_refund/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'customer_refund/data_support/'

        );

        return json_decode(json_encode($path));
    }
    
    public static $module_type_list = array(
        array('val'=>'customer_deposit','label'=>'Customer Deposit'),
    );
    
    public static $status_list = array(
        //<editor-fold defaultstate="collapsed">
        array(//label name is used for method name
            'val'=>'invoiced'
            ,'label'=>'INVOICED'
            ,'method'=>'customer_refund_invoiced'
            ,'default'=>true
            ,'next_allowed_status'=>array('X')
        )
        ,array(
            'val'=>'X'
            ,'label'=>'CANCELED'
            ,'method'=>'customer_refund_canceled'
            ,'next_allowed_status'=>array()

        )            
        //</editor-fold>
    );
    
    public static function customer_refund_exists($id){
            $result = false;
            $db = new DB();
            $q = '
                select 1 
                from customer_refund
                where status > 0 && id = '.$db->escape($id).'
            ';
            $rs = $db->query_array_obj($q);
            if(count($rs)>0){
                $result = true;
            }
            return $result;
        }

    public static function submit($id,$method,$post){
        //<editor-fold defaultstate="collapsed">
        $post = json_decode($post,TRUE);
        $data = $post;
        $ajax_post = false;                  
        $result = null;
        $cont = true;

        if(isset($post['ajax_post'])) $ajax_post = $post['ajax_post'];
        if($method == 'add') $data['customer_refund']['id'] = '';
        else $data['customer_refund']['id'] = $id;

        if($cont){
            $result = self::save($method,$data);
        }

        if(!$ajax_post){
            echo json_encode($result);
            die();
        }            
        else{
            echo json_encode($result);
            die();
        }
        //</editor-fold>
    }

    public static function validate($method,$data=array()){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('customer_refund/customer_refund_data_support');
        $result = array(
            "success"=>1
            ,"msg"=>array()

        );
        $success = 1;
        $msg = array();
        $customer_refund = isset($data['customer_refund'])?$data['customer_refund']:null;
        $customer_refund_id = isset($customer_refund['id'])?
            Tools::_str($customer_refund['id']):'';
        
        $db = new DB();
        switch($method){
            case 'customer_refund_add':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                    
                $cr_type = isset($customer_refund['customer_refund_type'])?
                        $customer_refund['customer_refund_type']:'';
                $reference_id = isset($data['reference_id'])?Tools::_str($data['reference_id']):'';
                $cr_amount = isset($customer_refund['amount'])?Tools::_str($customer_refund['amount']):'0';
                
                if(!SI::type_match('Customer_Refund_Engine',$cr_type)){
                    $success = 0;
                    $msg[]='Mismatch Module Type';
                    break;
                }
                
                $store_id = isset($customer_refund['store_id'])?
                    Tools::_str($customer_refund['store_id']):'';                    

                if(!SI::record_exists('store', array('id'=>$store_id,'status'=>'1'))){
                    $success = 0;
                    $msg[] = Lang::get('Store').' '.Lang::get('empty',true,false);
                }
                               
                
                if(floatval($cr_amount)<=floatval('0')){
                    $success = 0;
                    $msg[] = 'Amount 0';

                }
                
                $reference_outstanding_amount = 0;
                $reference_exists = false;
                $reference_customer_id = '';
                $q = '';
                switch($cr_type){
                    case 'customer_deposit':
                        $q = '
                            select outstanding_amount, customer_id
                            from customer_deposit t1
                            where t1.id = '.$reference_id.' and t1.outstanding_amount >0
                                and t1.outstanding_amount >= '.$db->escape($cr_amount).'
                                and t1.status>0
                                and t1.customer_deposit_type in( "delivery_order_final_confirmation","refill_work_order")
                                and t1.customer_deposit_status = "invoiced"
                        ';
                        break;
                    
                }
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $reference_exists = true;
                    $reference_outstanding_amount = $rs[0]['outstanding_amount'];
                }

                if(!$reference_exists){
                    $success = 0;
                    $msg[] = 'Reference '.Lang::get('empty',true,false);
                }                   

                if($success !== 1) break;
                //</editor-fold>
                break;
            case 'customer_refund_invoiced':
                $temp_result = Validator::validate_on_update(
                    array(
                        'module'=>'customer_refund',
                        'module_name'=>'Customer Refund',
                        'module_engine'=>'Customer_Refund_Engine',
                    ),
                    $customer_refund
                );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;

                break;
            case 'customer_refund_canceled':
                $temp_result = Validator::validate_on_cancel(
                        array(
                            'module'=>'customer_refund',
                            'module_name'=>'Customer Refund',
                            'module_engine'=>'Customer_Refund_Engine',
                        ),
                        $customer_refund
                    );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;
                
                break;
            default:
                $success = 0;
                $msg[] = 'Unknown Validation Method';
                break;

        }
        $result['msg'] = $msg;
        $result['success'] = $success;
        return $result;
        //</editor-fold>
    }

    public static function adjust($action,$data=array()){
        //<editor-fold defaultstate="collapsed">

        $db = new DB();
        $result = array();

        $cr_data = isset($data['customer_refund'])?
            Tools::_arr($data['customer_refund']):array();        
        $reference_id = isset($data['reference_id'])?
            Tools::_str($data['reference_id']):'';
        
        $modid = User_Info::get()['user_id'];
        $datetime_curr = Date('Y-m-d H:i:s');
        
        switch($action){
            case 'customer_refund_add':
                //<editor-fold defaultstate="collapsed">
                
                $cr_type = $cr_data['customer_refund_type'];

                $customer_refund = array(
                    'store_id'=>$cr_data['store_id'],
                    'customer_refund_type'=>$cr_type,
                    'customer_refund_date'=>Tools::_date(''),
                    'amount'=>$cr_data['amount'],
                    'customer_refund_status'=>
                            SI::status_default_status_get('Customer_Refund_Engine')['val'],
                    'notes'=>isset($cr_data['notes'])?Tools::empty_to_null(Tools::_str($cr_data['notes'])):null,
                    'status'=>'1',
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                    
                );
                
                $result['customer_refund'] = $customer_refund;
                
                switch($cr_type){
                    case 'customer_deposit':
                        $result['customer_deposit_customer_refund'] = array(
                            'customer_deposit_id'=>$reference_id
                        );
                        break;
                }
                    
                //</editor-fold>
                break;

            case 'customer_refund_invoiced':
                //<editor-fold defaultstate="collapsed">
                $customer_refund = array();

                $customer_refund = array(
                    'notes'=>isset($cr_data['notes'])?
                        Tools::_str($cr_data['notes']):'',
                    'customer_refund_status'=>'invoiced',
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                $result['customer_refund'] = $customer_refund;  
                //</editor-fold>
                break;
            case 'customer_refund_canceled':
                //<editor-fold defaultstate="collapsed">
                $customer_refund = array();

                $customer_refund = array(
                    'customer_refund_status'=>'X',
                    'cancellation_reason'=>$cr_data['cancellation_reason'],
                    'notes'=>isset($cr_data['notes'])?
                        Tools::empty_to_null(Tools::_str($cr_data['notes'])):'',
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                $result['customer_refund'] = $customer_refund;   
                //</editor-fold>
                break;
        }

        return $result;
        //</editor-fold>
    }

    public static function save($method,$data){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $success = 1;
        $msg = array();
        $action = $method;
        $result = array("success"=>0,"msg"=>array(),'trans_id'=>'');
        $customer_refund_data = $data['customer_refund'];
        $id = $customer_refund_data['id'];

        $method_list = array('customer_refund_add');
        foreach(SI::status_list_get('Customer_Refund_Engine') as $status){
            $method_list[] = strtolower($status['method']);
        }

        if(in_array($action,$method_list)){
            $validation_res = self::validate($action,$data);
            $success = $validation_res['success']; 
            $msg = $validation_res['msg'];
        }
        else{
            $success = 0;
            $msg[] = 'Unknown method';
        }

        if($success == 1){
            $final_data = self::adjust($action,$data);
            $modid = User_Info::get()['user_id'];
            $moddate = date("Y-m-d H:i:s");

            switch($action){                    
                case 'customer_refund_add':
                    try{ 
                        $db->trans_begin();
                        
                        $temp_result = self::customer_refund_add($db,$final_data);
                        $success = $temp_result['success'];
                        $msg = array_merge($msg, $temp_result['msg']);
                        
                        if($success === 1){
                            $db->trans_commit();
                            $msg[] = 'Add Customer Refund Success';
                            $result['trans_id'] = $temp_result['trans_id'];
                        }
                    }
                    catch(Exception $e){

                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    }
                    break;
                case 'customer_refund_invoiced':                        
                    try{
                        $db->trans_begin();
                        
                        $temp_result = self::customer_refund_invoiced($db,$final_data,$id);
                        $success = $temp_result['success'];
                        $msg = array_merge($msg, $temp_result['msg']);
                        
                        if($success === 1){
                            $db->trans_commit();
                            $msg[] = 'Update Customer Refund Success';
                            $result['trans_id'] = $temp_result['trans_id'];
                        }
                    }
                    catch(Exception $e){
                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    }                        

                    break;
                case 'customer_refund_canceled':
                    try{
                        $db->trans_begin();
                        $temp_result = self::customer_refund_canceled($db,$final_data,$id);
                        $success = $temp_result['success'];
                        $msg = array_merge($msg, $temp_result['msg']);
                        
                        if($success === 1){
                            $db->trans_commit();
                            $msg[] = 'Cancel Customer Refund Success';
                            $result['trans_id'] = $temp_result['trans_id'];
                        }
                    }
                    catch(Exception $e){
                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    } 
                    break;
            }
        }

        if($success == 1){
            Message::set('success',$msg);
        }            

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }
        
    public function customer_refund_add($db,$final_data){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $fcustomer_refund = $final_data['customer_refund'];

        $store_id = $fcustomer_refund['store_id'];
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $customer_refund_id = '';    
        $cr_type = $fcustomer_refund['customer_refund_type'];
        $cr_amount = $fcustomer_refund['amount'];

        $reference_id = '';
        $customer_id = '';
        $reference = array();
        
        switch($cr_type){
            case 'customer_deposit':
                get_instance()->load->helper('customer_deposit/customer_deposit_data_support');
                $reference_id = $final_data['customer_deposit_customer_refund']['customer_deposit_id'];
                $reference = Customer_Deposit_Data_Support::customer_deposit_get($reference_id);
                break;
        }
        
        $customer_id = $reference['customer_id'];
        
        $fcustomer_refund['code'] = SI::code_counter_store_get($db,$store_id, 'customer_refund');
        if(!$db->insert('customer_refund',$fcustomer_refund)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $customer_refund_code = $fcustomer_refund['code'];

        if($success == 1){                                
            $customer_refund_id = $db->fast_get('customer_refund'
                    ,array('code'=>$customer_refund_code))[0]['id'];
            $result['trans_id']=$customer_refund_id; 
        }

        if($success == 1){
            $customer_refund_status_log = array(
                'customer_refund_id'=>$customer_refund_id
                ,'customer_refund_status'=>$fcustomer_refund['customer_refund_status']
                ,'modid'=>$modid
                ,'moddate'=>$moddate    
            );

            if(!$db->insert('customer_refund_status_log',$customer_refund_status_log)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }
        
        if($success === 1){
            switch($cr_type){
                case 'customer_deposit':
                    //<editor-fold defaultstate="collapsed">
                    $fcustomer_deposit_customer_refund = $final_data['customer_deposit_customer_refund'];
                    $fcustomer_deposit_customer_refund['customer_refund_id'] = $customer_refund_id;
                    
                    if($success === 1){
                        if(!$db->insert('customer_deposit_customer_refund',$fcustomer_deposit_customer_refund)){
                            $success = 0;
                            $msg[] = $db->_error_message();   
                            $db->trans_rollback(); 
                        }                    
                    }
                    
                    if($success === 1){
                        $q = '
                            update customer_deposit 
                            set outstanding_amount = outstanding_amount - '.$db->escape($cr_amount).'
                                ,modid='.$db->escape($modid).'
                                ,moddate='.$db->escape($moddate).'
                            where id = '.$db->escape($reference['id']).'
                        ';
                        
                        if(!$db->query($q)){
                            $success = 0;
                            $msg[] = $db->_error_message();   
                            $db->trans_rollback(); 
                        }                    
                    }
                    
                    
                    
                    //</editor-fold>
                    break;
            }
        }
        
        if($success === 1){
            get_instance()->load->helper('customer/customer_engine');
            $temp_result = Customer_Engine::customer_debit_add($db,-1*Tools::_float($fcustomer_refund['amount']),$customer_id);
            $success = $temp_result['success'];
            $msg = array_merge($temp_result['msg'],$msg);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }

    function customer_refund_invoiced($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $fcustomer_refund = $final_data['customer_refund'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        $customer_refund_id = $id;

        if(!$db->update('customer_refund',$fcustomer_refund,array("id"=>$customer_refund_id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        

        if($success == 1){
            $temp_result = SI::status_log_add($db,'customer_refund',
                $customer_refund_id,$fcustomer_refund['customer_refund_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }
    
    public function customer_refund_canceled($db,$final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();
        
        $customer_refund_id = $id;
        
        $fcustomer_refund = $final_data['customer_refund'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        
        $customer_refund = $db->fast_get('customer_refund',array('id'=>$customer_refund_id))[0];
        
        $cr_type = $customer_refund['customer_refund_type'];
        $cr_amount = $customer_refund['amount'];
        
        $reference_id = '';
        $customer_id = '';
        $reference = array();
        
        switch($cr_type){
            case 'customer_deposit':
                get_instance()->load->helper('customer_deposit/customer_deposit_data_support');
                $reference_id = $db->fast_get('customer_deposit_customer_refund',
                    array('customer_refund_id'=>$customer_refund_id))[0]['customer_deposit_id'];
                $reference = Customer_Deposit_Data_Support::customer_deposit_get($reference_id);
                $customer_id = $reference['customer_id'];
                break;
        }
        
        if(!$db->update('customer_refund',$fcustomer_refund,array("id"=>$customer_refund_id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'customer_refund',
                $customer_refund_id,$customer_refund['customer_refund_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        if($success === 1){
            switch($cr_type){
                case 'customer_deposit':
                    //<editor-fold defaultstate="collapsed">

                    if($success === 1){
                        $q = '
                            update customer_deposit 
                            set outstanding_amount = outstanding_amount + '.$db->escape($cr_amount).'
                                ,modid='.$db->escape($modid).'
                                ,moddate='.$db->escape($moddate).'
                            where id = '.$db->escape($reference_id).'
                        ';
                        
                        if(!$db->query($q)){
                            $success = 0;
                            $msg[] = $db->_error_message();   
                            $db->trans_rollback(); 
                        }                    
                    }
                    
                    
                    
                    //</editor-fold>
                    break;
            }
        }
        
        
        if($success === 1){
            get_instance()->load->helper('customer/customer_engine');
            $temp_result = Customer_Engine::customer_debit_add($db,
                    $customer_refund['amount'],
                    $customer_id);
            $success = $temp_result['success'];
            $msg = array_merge($temp_result['msg'],$msg);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }

}
?>
