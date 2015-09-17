<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer_Deposit_Engine {

    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'customer_deposit/'
            ,'customer_deposit_engine'=>'customer_deposit/customer_deposit_engine'
            ,'customer_deposit_renderer' => 'customer_deposit/customer_deposit_renderer'
            ,'ajax_search'=>get_instance()->config->base_url().'customer_deposit/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'customer_deposit/data_support/'

        );

        return json_decode(json_encode($path));
    }
    
    public static $module_type_list = array(
        array('val'=>'delivery_order_final_confirmation','label'=>'Delivery Order Final Confirmation'),
        array('val'=>'refill_work_order','label'=>'Refill Work Order'),
    );
    
    public static $status_list = array(
        //<editor-fold defaultstate="collapsed">
        array(//label name is used for method name
            'val'=>'invoiced'
            ,'label'=>'INVOICED'
            ,'method'=>'customer_deposit_invoiced'
            ,'default'=>true
            ,'next_allowed_status'=>array('X')
        )
        ,array(
            'val'=>'X'
            ,'label'=>'CANCELED'
            ,'method'=>'customer_deposit_canceled'
            ,'next_allowed_status'=>array()

        )            
        //</editor-fold>
    );
    
    public static function customer_deposit_exists($id){
            $result = false;
            $db = new DB();
            $q = '
                select 1 
                from customer_deposit
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
        if($method == 'add') $data['customer_deposit']['id'] = '';
        else $data['customer_deposit']['id'] = $id;

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

    public static function save($method,$data){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $success = 1;
        $msg = array();
        $action = $method;
        $result = array("success"=>0,"msg"=>array(),'trans_id'=>'');
        $customer_deposit_data = $data['customer_deposit'];
        $id = $customer_deposit_data['id'];

        $method_list = array('customer_deposit_add');
        foreach(SI::status_list_get('Customer_Deposit_Engine') as $status){
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
                case 'customer_deposit_add':
                    try{ 
                        $db->trans_begin();
                        $temp_result = self::customer_deposit_add($db,$final_data);
                        $success = $temp_result['success'];
                        $msg = array_merge($msg, $temp_result['msg']);
                        
                        if($success === 1){
                            $db->trans_commit();
                            $msg[] = 'Add Customer Deposit Success';
                            $result['trans_id'] = $temp_result['trans_id'];
                        }
                        
                    }
                    catch(Exception $e){

                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    }
                    break;
                case 'customer_deposit_invoiced':                        
                    try{
                        $db->trans_begin();
                        $temp_result = self::customer_deposit_invoiced($db,$final_data,$id);
                        $success = $temp_result['success'];
                        $msg = array_merge($msg,$temp_result['msg']);

                        if($success == 1){
                            $result['trans_id'] = $temp_result['trans_id'];
                        }

                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = 'Update Customer Deposit Success';
                        }

                    }
                    catch(Exception $e){
                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    }                        

                    break;
                case 'customer_deposit_canceled':
                    try{
                        $db->trans_begin();
                        $temp_result = self::customer_deposit_canceled($db,$final_data,$id);
                        $success = $temp_result['success'];
                        $msg = array_merge($msg,$temp_result['msg']);

                        if($success == 1){
                            $result['trans_id'] = $temp_result['trans_id'];
                        }

                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = 'Cancel Customer Deposit Success';
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
    
    public static function validate($method,$data=array()){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('customer_deposit/customer_deposit_data_support');
        get_instance()->load->helper('bos_bank_account/bos_bank_account_data_support');
        get_instance()->load->helper('payment_type/payment_type_data_support');
        $result = array(
            "success"=>1
            ,"msg"=>array()

        );
        $success = 1;
        $msg = array();
        $customer_deposit = isset($data['customer_deposit'])?$data['customer_deposit']:null;
        $reference = isset($data['reference'])?$data['reference']:null;
        $customer_deposit_id = isset($customer_deposit['id'])?
            Tools::_str($customer_deposit['id']):'';
        $customer_deposit_db = Customer_Deposit_Data_Support::customer_deposit_get($customer_deposit_id);
        $cd_type = '';
        $db = new DB();
        switch($method){
            case 'customer_deposit_add':
                //<editor-fold defaultstate="collapsed">
                
                $db = new DB();
                $cd_type = isset($customer_deposit['customer_deposit_type'])?
                        $customer_deposit['customer_deposit_type']:'';
                $reference_id = isset($reference['id'])?$reference['id']:'';
                $customer_id = isset($customer_deposit['customer_id'])?$customer_deposit['customer_id']:'';
                $store_id = isset($customer_deposit['store_id'])?
                    Tools::_str($customer_deposit['store_id']):'';
                $payment_type_id = isset($customer_deposit['payment_type_id'])?
                        Tools::_str($customer_deposit['payment_type_id']):'';
                $payment_type_code = Payment_Type_Data_Support::payment_type_code_get($payment_type_id);
                $bos_bank_account_id = isset($customer_deposit['bos_bank_account_id'])?
                        Tools::_str($customer_deposit['bos_bank_account_id']):'';
                //<editor-fold defaultstate="collapsed" desc="Major Validation">
                
                if(!SI::type_match('customer_deposit_engine', $cd_type)){
                    $success = 0;
                    $msg[] = 'Customer Deposit Type'.' '.Lang::get('invalid');
                }
                else if(!in_array($cd_type,array('refill_work_order'))){
                        $success = 0;
                        $msg[] = 'Customer Deposit Type invalid';
                    
                }
                if(!SI::record_exists('store', array('id'=>$store_id,'status'=>'1'))){
                    $success = 0;
                    $msg[] = Lang::get('Store').' '.Lang::get('empty',true,false);
                }

                if(!SI::record_exists('customer',array('id'=>$customer_id))){
                    $success = 0;
                    $msg[] = Lang::get('Customer').' '.Lang::get('empty',true,false);
                }
                
                $receipt_amount = isset($customer_deposit['amount'])?
                    Tools::_str($customer_deposit['amount']):'0';

                if(Tools::_float($receipt_amount)===floatval('0')){
                    $success = 0;
                    $msg[] = Lang::get('Amount').' '.Lang::get('empty');
                }

                
                $customer_bank_acc = isset($customer_deposit['customer_bank_acc'])?
                    str_replace(' ','',Tools::_str($customer_deposit['customer_bank_acc'])):'0';

                
                if($payment_type_code!=='CASH'
                       
                ){
                    //<editor-fold defaultstate="collapsed" desc="Bank Acc &, Name">
                    if($customer_bank_acc ===''){
                        $success = 0;
                        $msg[] = 'Customer Bank Acc. '.Lang::get('empty',true, false);
                    }
                    
                    if(is_null(Bos_Bank_Account_Data_Support::bos_bank_account_get($bos_bank_account_id))){
                        $success = 0;
                        $msg[] = 'BOS Bank Account'.' '.Lang::get('empty',true,false);
                    }
                    
                    //</editor-fold>
                }
                
                if($success !== 1) break;
                
                if($cd_type === 'refill_work_order'){
                    
                    get_instance()->load->helper('refill_work_order/refill_work_order_data_support');
                    
                    $payment_type_arr = Customer_Deposit_Data_Support::customer_payment_type_get($customer_id);                

                    if(!Tools::data_array_exists($payment_type_arr,array('id'=>$payment_type_id))){
                        $success = 0;
                        $msg[] = Lang::get('Payment Type').' '.Lang::get('empty',true,false);
                    }

                    $rwo = Refill_Work_Order_Data_Support::refill_work_order_get($reference_id);
                    if(count($rwo) === 0){
                        $success = 0;
                        $msg[] = 'Reference invalid';
                    }
                    else{
                        if(!in_array($rwo['refill_work_order_status'],array('process','done'))){
                            $success = 0;
                            $msg[] = 'Refill Work Order Status invalid';
                        }
                        
                        
                        if(Tools::_float($receipt_amount)> (
                            Tools::_float($rwo['total_estimated_amount']) - 
                            Tools::_float($rwo['total_deposit_amount'])
                            )
                        ){
                            $success = 0;
                            $msg[] = 'Amount too much';
                        }
                    }
                }
                
                
                //</editor-fold>
                break;
            case 'customer_deposit_invoiced':
                $cd_type = $customer_deposit_db['customer_deposit_type'];
                if($cd_type !== 'refill_work_order'){
                    $success = 0;
                    $msg[] = 'Update Customer Deposit '.Lang::get('failed',true,false);
                }

                break;
            case 'customer_deposit_canceled':
                //<editor-fold defaultstate="collapsed">
                $cd_type = $customer_deposit_db['customer_deposit_type'];
                if(!in_array($cd_type,array('refill_work_order'))){
                    $success = 0;
                    $msg[] = 'Cancel Customer Deposit '.Lang::get('failed',true,false);
                }
                if($success !== 1) break;
                
                if(Tools::_float($customer_deposit_db['outstanding_amount'])!== Tools::_float($customer_deposit_db['amount'])){
                    $success = 0;
                    $msg[] = 'Customer Deposit '.' '.Lang::get('has been used');
                }
                
                //</editor-fold>
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
        get_instance()->load->helper('payment_type/payment_type_data_support');
        get_instance()->load->helper('customer_deposit/customer_deposit_data_support');
        
        $db = new DB();
        $result = array();
        $customer_deposit_data = isset($data['customer_deposit'])?
            Tools::_arr($data['customer_deposit']):array();
        
        $reference_data = isset($data['reference'])?
            Tools::_arr($data['reference']):array();        
        $customer_deposit_type = '';
        $customer_deposit_id = $customer_deposit_data['id'];
        $customer_deposit_db = Customer_Deposit_Data_Support::customer_deposit_get($customer_deposit_id);
        
        $modid = User_Info::get()['user_id'];
        $datetime_curr = Date('Y-m-d H:i:s');
        
        switch($action){
            case 'customer_deposit_add':
                $customer_deposit_type = $customer_deposit_data['customer_deposit_type'];
                $customer_deposit = array(
                    'store_id'=>$customer_deposit_data['store_id'],
                    'payment_type_id'=>$customer_deposit_data['payment_type_id'],
                    'customer_deposit_type'=>$customer_deposit_data['customer_deposit_type'],
                    'customer_id'=>$customer_deposit_data['customer_id'],
                    'customer_bank_acc'=>Tools::empty_to_null($customer_deposit_data['customer_bank_acc']),
                    'bos_bank_account_id'=>null,
                    'amount'=>$customer_deposit_data['amount'],
                    'outstanding_amount'=>Tools::_float($customer_deposit_data['amount']),
                    'deposit_date'=>null,
                    'customer_deposit_date'=>$datetime_curr,
                    'customer_deposit_status'=>SI::status_default_status_get('Customer_Deposit_Engine')['val'],
                    'notes'=>isset($customer_deposit_data['notes'])?
                        Tools::_str($customer_deposit_data['notes']):'',
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                
                $payment_type_code = Payment_Type_Data_Support::payment_type_code_get($customer_deposit['payment_type_id']);
                if($payment_type_code !== 'CASH'){
                    $customer_deposit['bos_bank_account_id'] = $customer_deposit_data['bos_bank_account_id'];
                }
                
                $result['customer_deposit'] = $customer_deposit;
                
                switch($customer_deposit_type){
                    case 'refill_work_order':
                        $rwo_cd = array(
                            'refill_work_order_id'=>$reference_data['id']
                        );
                        $result['rwo_cd'] = $rwo_cd;
                        break;
                }
                
                
                break;

            case 'customer_deposit_invoiced':
                //<editor-fold defaultstate="collapsed">
                $customer_deposit = array();

                $customer_deposit = array(
                    'notes'=>isset($customer_deposit_data['notes'])?
                        Tools::_str($customer_deposit_data['notes']):'',
                    'customer_deposit_status'=>'invoiced',
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                
                if(is_null($customer_deposit_db['deposit_date']) 
                && (Payment_Type_Data_Support::payment_type_get($customer_deposit_db['payment_type_id'])['code'] === 'CASH')){
                    $customer_deposit['deposit_date'] = Tools::empty_to_null(
                        isset($customer_deposit_data['deposit_date'])?
                            (is_null($customer_deposit_data['deposit_date'])?
                                null:
                                Tools::_date($customer_deposit_data['deposit_date'],'Y-m-d H:i:s')
                            ):
                            null
                    );
                }
                
                $result['customer_deposit'] = $customer_deposit;
                //</editor-fold>
                break;
            case 'customer_deposit_canceled':
                //<editor-fold defaultstate="collapsed">
                $customer_deposit = array(
                    'customer_deposit_status'=>'X',
                    'notes'=>isset($customer_deposit_data['notes'])?
                        Tools::empty_to_null($customer_deposit_data['notes']):null,
                    'cancellation_reason'=>isset($customer_deposit_data['cancellation_reason'])?
                        Tools::empty_to_null($customer_deposit_data['cancellation_reason']):null,
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                $result['customer_deposit'] = $customer_deposit;
                //</editor-fold>
                break;
        }

        return $result;
        //</editor-fold>
    }
        
    public function customer_deposit_add($db,$final_data){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $fcustomer_deposit = $final_data['customer_deposit'];

        $store_id = $fcustomer_deposit['store_id'];
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $customer_deposit_id = '';    
        $cd_type = $fcustomer_deposit['customer_deposit_type'];
        
        $fcustomer_deposit['code'] = SI::code_counter_store_get($db,$store_id, 'customer_deposit');
        if(!$db->insert('customer_deposit',$fcustomer_deposit)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $customer_deposit_code = $fcustomer_deposit['code'];

        if($success == 1){                                
            $customer_deposit_id = $db->fast_get('customer_deposit'
                    ,array('code'=>$customer_deposit_code))[0]['id'];
            $result['trans_id']=$customer_deposit_id; 
        }

        if($success == 1){
            $customer_deposit_status_log = array(
                'customer_deposit_id'=>$customer_deposit_id
                ,'customer_deposit_status'=>$fcustomer_deposit['customer_deposit_status']
                ,'modid'=>$modid
                ,'moddate'=>$moddate    
            );

            if(!$db->insert('customer_deposit_status_log',$customer_deposit_status_log)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }
        
        if($success === 1){
            switch($cd_type){
                case 'delivery_order_final_confirmation':
                    $fdofc_cd = $final_data['dofc_cd'];
                    $fdofc_cd['customer_deposit_id'] = $customer_deposit_id;
                    
                    if(!$db->insert('dofc_cd',$fdofc_cd)){
                        $success = 0;
                        $msg[] = $db->_error_message();   
                        $db->trans_rollback(); 
                    }
                    
                    break;
                case 'refill_work_order':
                    
                    if($success == 1){
                        $frwo_cd = $final_data['rwo_cd'];
                        $frwo_cd['customer_deposit_id'] = $customer_deposit_id;
                        if(!$db->insert('rwo_cd',$frwo_cd)){
                            $success = 0;
                            $msg[] = $db->_error_message();   
                            $db->trans_rollback(); 
                            break;
                        }
                        
                    }
                    
                    if($success === 1){
                        get_instance()->load->helper('refill_work_order/refill_work_order_engine');
                        $temp_result = Refill_Work_Order_Engine::total_deposit_amount_add($db,
                                $fcustomer_deposit['amount'],
                                $frwo_cd['refill_work_order_id']);
                        $success = $temp_result['success'];
                        $msg = array_merge($temp_result['msg'],$msg);
                        if($success !== 1) break;
                    }
                    break;
            }
        }
        
        
        
        if($success === 1){
            get_instance()->load->helper('customer/customer_engine');
            $temp_result = Customer_Engine::customer_debit_add($db,$fcustomer_deposit['amount'],$fcustomer_deposit['customer_id']);
            $success = $temp_result['success'];
            $msg = array_merge($temp_result['msg'],$msg);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }

    function customer_deposit_invoiced($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $fcustomer_deposit = $final_data['customer_deposit'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('customer_deposit',$fcustomer_deposit,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        

        if($success == 1){
            $temp_result = SI::status_log_add($db,'customer_deposit',
                $id,$fcustomer_deposit['customer_deposit_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }
    
    public function customer_deposit_canceled($db,$final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();
        
        $customer_deposit_id = $id;
        
        $fcustomer_deposit = $final_data['customer_deposit'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        $customer_deposit = $db->fast_get('customer_deposit',array('id'=>$customer_deposit_id))[0];
        $cd_status_old = $customer_deposit['customer_deposit_status'];
        $cd_type = $customer_deposit['customer_deposit_type'];
        
        switch($cd_type){
            case 'refill_work_order':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('refill_work_order/refill_work_order_engine');
                //</editor-fold>
                break;
        }
        
        if(!$db->update('customer_deposit',$fcustomer_deposit,array("id"=>$customer_deposit_id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'customer_deposit',
                $customer_deposit_id,$customer_deposit['customer_deposit_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        if($success === 1){
            get_instance()->load->helper('customer/customer_engine');
            $temp_result = Customer_Engine::customer_debit_add($db,
                    -1*$customer_deposit['amount'],
                    $customer_deposit['customer_id']);
            $success = $temp_result['success'];
            $msg = array_merge($temp_result['msg'],$msg);
        }

        if($cd_status_old !=='X'){
            //<editor-fold defaultstate="collapsed">
            switch($cd_type){
                case 'refill_work_order':
                    //<editor-fold defaultstate="collapsed">
                    $q = '
                        select refill_work_order_id
                        from rwo_cd
                        where customer_deposit_id = '.$db->escape($customer_deposit_id).'
                    ';
                    $t_rs = $db->query_array($q);
                    if(count($t_rs)>0){
                        $temp_result = Refill_Work_Order_Engine::total_deposit_amount_add($db,
                                -1 * Tools::_float($customer_deposit['amount']),
                                $t_rs[0]['refill_work_order_id']);
                        $success = $temp_result['success'];
                        $msg = array_merge($temp_result['msg'],$msg);
                    }
                    if($success !== 1) break;
                    
                    //</editor-fold>
                    break;
            }
            //</editor-fold>
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }

}
?>
