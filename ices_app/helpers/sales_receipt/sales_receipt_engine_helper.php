<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sales_Receipt_Engine {
    public static $prefix_id = 'sales_receipt';
    public static $prefix_method;
    public static $status_list;

    public static function helper_init(){
        //<editor-fold defaultstate="collapsed">
        
        self::$prefix_method = self::$prefix_id;
        
        self::$status_list = array(
            //<editor-fold defaultstate="collapsed">
            array(
                'val'=>''
                ,'label'=>''
                ,'method'=>'sales_receipt_add'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Add')
                        ,array('val'=>Lang::get(array('Sales Receipt'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            array(
                'val'=>'invoiced'
                ,'label'=>'INVOICED'
                ,'default'=>true
                ,'method'=>'sales_receipt_invoiced'
                ,'next_allowed_status'=>array('X')
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update')
                        ,array('val'=>Lang::get(array('Sales Receipt'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            )
            ,array(
                'val'=>'X'
                ,'label'=>'CANCELED'
                ,'method'=>'sales_receipt_canceled'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Cancel')
                        ,array('val'=>Lang::get(array('Sales Receipt'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            )
            //</editor-fold>
        );
        //</editor-fold>
    }
    
    public static function path_get(){
        //<editor-fold defaultstate="collapsed">
        $path = array(
            'index'=>get_instance()->config->base_url().'sales_receipt/',
            'sales_receipt_engine'=>'sales_receipt/sales_receipt_engine',
            'sales_receipt_data_support' => 'sales_receipt/sales_receipt_data_support',
            'sales_receipt_renderer' => 'sales_receipt/sales_receipt_renderer',
            'ajax_search'=>get_instance()->config->base_url().'sales_receipt/ajax_search/',
            'data_support'=>get_instance()->config->base_url().'sales_receipt/data_support/',
        );

        return json_decode(json_encode($path));
        //</editor-fold>
    }

    public static function validate($method,$data=array()){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('sales_receipt/sales_receipt_data_support');
        get_instance()->load->helper('bos_bank_account/bos_bank_account_data_support');
        get_instance()->load->helper('payment_type/payment_type_data_support');
        $result = array(
            "success"=>1
            ,"msg"=>array()

        );
        $success = 1;
        $msg = array();
        $sales_receipt = isset($data['sales_receipt'])?$data['sales_receipt']:null;

        switch($method){
            case 'sales_receipt_add':
                $db = new DB();
                $customer_id = isset($sales_receipt['customer_id'])?$sales_receipt['customer_id']:'';
                $store_id = isset($sales_receipt['store_id'])?
                    Tools::_str($sales_receipt['store_id']):'';
                $payment_type_id = isset($sales_receipt['payment_type_id'])?
                        Tools::_str($sales_receipt['payment_type_id']):'';
                $payment_type_code = Payment_Type_Data_Support::payment_type_code_get($payment_type_id);
                $bos_bank_account_id = isset($sales_receipt['bos_bank_account_id'])?
                        Tools::_str($sales_receipt['bos_bank_account_id']):'';
                //<editor-fold defaultstate="collapsed" desc="Major Validation">

                if(!SI::record_exists('store', array('id'=>$store_id,'status'=>'1'))){
                    $success = 0;
                    $msg[] = Lang::get('Store').' '.Lang::get('empty',true,false);
                }

                if(!SI::record_exists('customer',array('id'=>$customer_id))){
                    $success = 0;
                    $msg[] = Lang::get('Customer').' '.Lang::get('empty',true,false);
                }
                
                $payment_type_arr = Sales_Receipt_Data_Support::customer_payment_type_get($customer_id);                

                if(!Tools::data_array_exists($payment_type_arr,array('id'=>$payment_type_id))){
                    $success = 0;
                    $msg[] = Lang::get('Payment Type').' '.Lang::get('empty',true,false);
                }
                
                $receipt_amount = isset($sales_receipt['amount'])?
                    Tools::_str($sales_receipt['amount']):'0';

                if(Tools::_float($receipt_amount)===floatval('0')){
                    $success = 0;
                    $msg[] = Lang::get('Amount').' '.Lang::get('empty');
                }

                $change_amount = isset($sales_receipt['change_amount'])?
                    Tools::_str($sales_receipt['change_amount']):'0';

                if(Tools::_float($change_amount)>=floatval($receipt_amount)){
                    $success = 0;
                    $msg[] = Lang::get('Change Amount').' '.Lang::get('is greater than',true,false).' '.Lang::get('or',true,false).' '.Lang::get('the same as',true,false).' '.Lang::get('Amount');
                }
                
                if(Tools::_float($change_amount)>floatval('0') && Payment_Type_Data_Support::payment_type_code_get($payment_type_id) !=="CASH"){
                    $success = 0;
                    $msg[] = "Change only available on CASH";
                }
                
                $customer_bank_acc = isset($sales_receipt['customer_bank_acc'])?
                    str_replace(' ','',Tools::_str($sales_receipt['customer_bank_acc'])):'0';

                if($payment_type_code!=='CASH'
                       
                ){
                    if($customer_bank_acc ===''){
                        $success = 0;
                        $msg[] = 'Customer Bank Acc. '.Lang::get('empty',true, false);
                    }
                    
                    if(is_null(Bos_Bank_Account_Data_Support::bos_bank_account_get($bos_bank_account_id))){
                        $success = 0;
                        $msg[] = 'BOS Bank Account'.' '.Lang::get('empty',true,false);
                    }
                    
                }
                
                
                
                
                if($success !== 1) break;
                //</editor-fold>

                break;
            
            case 'sales_receipt_invoiced':
                $temp_result = Validator::validate_on_update(
                    array(
                        'module'=>'sales_receipt',
                        'module_name'=>'Sales Receipt',
                        'module_engine'=>'Sales_Receipt_Engine',
                    ),
                    $sales_receipt
                );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;
                
                
                
                    
                break;
            
            case 'sales_receipt_canceled':
                $db = new DB();
                $temp_result = Validator::validate_on_cancel(
                        array(
                            'module'=>'sales_receipt',
                            'module_name'=>'Sales Receipt',
                            'module_engine'=>'Sales_Receipt_Engine',
                        ),
                        $sales_receipt
                    );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;

                if(Sales_Receipt_Data_Support::sales_receipt_is_allocated($sales_receipt['id'])){
                    $success = 0;
                    $msg[] = 'Sales Receipt Allocation '.Lang::get('exists',true,false).'. Cancel Sales Receipt Allocation';
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
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('payment_type/payment_type_data_support');
        get_instance()->load->helper('sales_receipt/sales_receipt_data_support');
        $db = new DB();
        $result = array();
        $sales_receipt_data = isset($data['sales_receipt'])?
            Tools::_arr($data['sales_receipt']):array();
        $sales_receipt_id = $sales_receipt_data['id'];
        $sales_receipt_db = Sales_Receipt_Data_Support::sales_receipt_get($sales_receipt_id);
        $modid = User_Info::get()['user_id'];
        $datetime_curr = Date('Y-m-d H:i:s');
        
        switch($action){
            case 'sales_receipt_add':
                

                $sales_receipt = array();

                $sales_receipt = array(
                    'store_id'=>$sales_receipt_data['store_id'],
                    'payment_type_id'=>$sales_receipt_data['payment_type_id'],
                    'customer_id'=>$sales_receipt_data['customer_id'],
                    'customer_bank_acc'=>Tools::empty_to_null($sales_receipt_data['customer_bank_acc']),
                    'bos_bank_account_id'=>null,
                    'amount'=>$sales_receipt_data['amount'],
                    'outstanding_amount'=>Tools::_float($sales_receipt_data['amount']) - Tools::_float($sales_receipt_data['change_amount']),
                    'change_amount'=>$sales_receipt_data['change_amount'],
                    'sales_receipt_date'=>$datetime_curr,
                    'sales_receipt_status'=>SI::status_default_status_get('Sales_Receipt_Engine')['val'],
                    'deposit_date'=>null,
                    'notes'=>isset($sales_receipt_data['notes'])?
                        Tools::_str($sales_receipt_data['notes']):'',
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                
                $payment_type_code = Payment_Type_Data_Support::payment_type_code_get($sales_receipt['payment_type_id']);
                if($payment_type_code !== 'CASH'){
                    $sales_receipt['bos_bank_account_id'] = $sales_receipt_data['bos_bank_account_id'];
                }
                
                $result['sales_receipt'] = $sales_receipt;                   

                break;
            case 'sales_receipt_invoiced':                
                $sales_receipt = array();

                $sales_receipt = array(
                    'notes'=>isset($sales_receipt_data['notes'])?
                        Tools::_str($sales_receipt_data['notes']):'',
                    'sales_receipt_status'=>'invoiced',
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                
                if(is_null($sales_receipt_db['deposit_date']) 
                && (Payment_Type_Data_Support::payment_type_get($sales_receipt_db['payment_type_id'])['code'] === 'CASH')){
                        $sales_receipt['deposit_date'] = Tools::empty_to_null(
                            isset($sales_receipt_data['deposit_date'])?
                                (is_null($sales_receipt_data['deposit_date'])?
                                    null:
                                    Tools::_date($sales_receipt_data['deposit_date'],'Y-m-d H:i:s')
                                ):
                                null
                        );
                }
                
                
                $result['sales_receipt'] = $sales_receipt;    
                break;
            case 'sales_receipt_canceled':
                $sales_receipt = array();

                $sales_receipt = array(
                    'sales_receipt_status'=>'X',
                    'cancellation_reason'=>$sales_receipt_data['cancellation_reason'],
                    'notes'=>isset($sales_receipt_data['notes'])?
                        Tools::_str($sales_receipt_data['notes']):'',
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                $result['sales_receipt'] = $sales_receipt;    
                break;
        }

        return $result;
        //</editor-fold>
    }

    public function sales_receipt_add($db,$final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $fsales_receipt = $final_data['sales_receipt'];

        $store_id = $fsales_receipt['store_id'];
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $sales_receipt_id = '';       
        $pure_amount = Tools::_float($fsales_receipt['amount']) - Tools::_float($fsales_receipt['change_amount']);
        $fsales_receipt['code'] = SI::code_counter_store_get($db,$store_id, 'sales_receipt');
        if(!$db->insert('sales_receipt',$fsales_receipt)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $sales_receipt_code = $fsales_receipt['code'];

        if($success == 1){                                
            $sales_receipt_id = $db->fast_get('sales_receipt'
                    ,array('code'=>$sales_receipt_code))[0]['id'];
            $result['trans_id']=$sales_receipt_id; 
        }

        if($success == 1){
            $sales_receipt_status_log = array(
                'sales_receipt_id'=>$sales_receipt_id
                ,'sales_receipt_status'=>$fsales_receipt['sales_receipt_status']
                ,'modid'=>$modid
                ,'moddate'=>$moddate    
            );

            if(!$db->insert('sales_receipt_status_log',$sales_receipt_status_log)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }

        if($success === 1){
            get_instance()->load->helper('customer/customer_engine');
            $temp_result = Customer_Engine::customer_debit_add($db, $pure_amount,
                $fsales_receipt['customer_id']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);

        }

        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    function sales_receipt_invoiced($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $fsales_receipt = $final_data['sales_receipt'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('sales_receipt',$fsales_receipt,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        

        if($success == 1){
            $temp_result = SI::status_log_add($db,'sales_receipt',
                $id,$fsales_receipt['sales_receipt_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    function sales_receipt_canceled($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();
        
        $sales_receipt_id = $id;
        
        $fsales_receipt = $final_data['sales_receipt'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        $sales_receipt = $db->fast_get('sales_receipt',array('id'=>$sales_receipt_id))[0];
        $pure_amount = Tools::_float($sales_receipt['amount']) - Tools::_float($sales_receipt['change_amount']);
        
        if(!$db->update('sales_receipt',$fsales_receipt,array("id"=>$sales_receipt_id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'sales_receipt',
                $sales_receipt_id,$sales_receipt['sales_receipt_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        if($success === 1){
            get_instance()->load->helper('customer/customer_engine');
            $temp_result = Customer_Engine::customer_debit_add($db,
                    -1*$pure_amount,
                    $sales_receipt['customer_id']);
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