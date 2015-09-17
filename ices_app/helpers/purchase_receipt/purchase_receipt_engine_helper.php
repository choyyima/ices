<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Purchase_Receipt_Engine {

    public static $status_list = array(
        //<editor-fold defaultstate="collapsed">
        array(//label name is used for method name
            'val'=>'invoiced'
            ,'label'=>'INVOICED'
            ,'method'=>'purchase_receipt_invoiced'
            ,'default'=>true
            ,'next_allowed_status'=>array('X')
        )
        ,array(
            'val'=>'X'
            ,'label'=>'CANCELED'
            ,'method'=>'purchase_receipt_canceled'
            ,'next_allowed_status'=>array()
        )
        //</editor-fold>
    );

    public static function purchase_receipt_exists($id){
        //<editor-fold defaultstate="collapsed">
        $result = false;
        $db = new DB();
        $q = '
                select 1 
                from purchase_receipt 
                where status > 0 && id = '.$db->escape($id).'
            ';
        $rs = $db->query_array_obj($q);
        if(count($rs)>0){
            $result = true;
        }
        return $result;
        //</editor-fold>
    }

    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'purchase_receipt/',
            'purchase_receipt_engine'=>'purchase_receipt/purchase_receipt_engine',
            'purchase_receipt_data_support' => 'purchase_receipt/purchase_receipt_data_support',
            'purchase_receipt_renderer' => 'purchase_receipt/purchase_receipt_renderer',
            'ajax_search'=>get_instance()->config->base_url().'purchase_receipt/ajax_search/',
            'data_support'=>get_instance()->config->base_url().'purchase_receipt/data_support/',
        );

        return json_decode(json_encode($path));
    }

    public static function submit($id,$method,$post){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper(self::path_get()->purchase_receipt_data_support);

        $post = json_decode($post,TRUE);
        $data = $post;
        $ajax_post = false;                  
        $result = null;
        $cont = true;

        if(isset($post['ajax_post'])) $ajax_post = $post['ajax_post'];
        if($method == 'purchase_receipt_add') $data['purchase_receipt']['id'] = '';
        else $data['purchase_receipt']['id'] = $id;

        if($cont){
            $result = self::save($method,$data);
        }

        if(!$ajax_post){

        }            
        else{
            echo json_encode($result);
            die();
        }
        //</editor-fold>
    }

    public static function validate($method,$data=array()){            
        get_instance()->load->helper('purchase_receipt/purchase_receipt_data_support');
        get_instance()->load->helper('payment_type/payment_type_data_support');
        $result = array(
            "success"=>1
            ,"msg"=>array()

        );
        $success = 1;
        $msg = array();
        $purchase_receipt = isset($data['purchase_receipt'])?$data['purchase_receipt']:null;

        switch($method){
            case 'purchase_receipt_add':
                $db = new DB();
                $supplier_id = isset($purchase_receipt['supplier_id'])?$purchase_receipt['supplier_id']:'';
                $store_id = isset($purchase_receipt['store_id'])?
                    Tools::_str($purchase_receipt['store_id']):'';
                $payment_type_id = isset($purchase_receipt['payment_type_id'])?
                        Tools::_str($purchase_receipt['payment_type_id']):'';
                $payment_type = Payment_Type_Data_Support::payment_type_get($payment_type_id);
                $payment_type_code = isset($payment_type['code'])?$payment_type['code']:null;
                //<editor-fold defaultstate="collapsed" desc="Major Validation">

                if(!SI::record_exists('store', array('id'=>$store_id,'status'=>'1'))){
                    $success = 0;
                    $msg[] = Lang::get('Store').' '.Lang::get('empty',true,false);
                }

                if(!SI::record_exists('supplier',array('id'=>$supplier_id))){
                    $success = 0;
                    $msg[] = Lang::get('Supplier').' '.Lang::get('empty',true,false);
                }
                
                $payment_type_arr = Purchase_Receipt_Data_Support::supplier_payment_type_get($supplier_id);                

                if(!Tools::data_array_exists($payment_type_arr,array('id'=>$payment_type_id))){
                    $success = 0;
                    $msg[] = Lang::get('Payment Type').' '.Lang::get('empty',true,false);
                }
                
                $receipt_amount = isset($purchase_receipt['amount'])?
                    Tools::_str($purchase_receipt['amount']):'0';

                if(Tools::_float($receipt_amount)===floatval('0')){
                    $success = 0;
                    $msg[] = Lang::get('Amount').' '.Lang::get('empty');
                }

                $change_amount = isset($purchase_receipt['change_amount'])?
                    Tools::_str($purchase_receipt['change_amount']):'0';

                if(Tools::_float($change_amount)>=floatval($receipt_amount)){
                    $success = 0;
                    $msg[] = Lang::get('Change Amount').' '.Lang::get('is greater than',true,false).' '.Lang::get('or',true,false).' '.Lang::get('the same as',true,false).' '.Lang::get('Amount');
                }
                
                if(Tools::_float($change_amount)>floatval('0') && $payment_type_code !=="CASH"){
                    $success = 0;
                    $msg[] = "Change only available on CASH";
                }
                
                $supplier_bank_acc = isset($purchase_receipt['supplier_bank_acc'])?
                    str_replace(' ','',Tools::_str($purchase_receipt['supplier_bank_acc'])):'0';

                $bos_bank_name = isset($purchase_receipt['bos_bank_name'])?
                    str_replace(' ','',Tools::_str($purchase_receipt['bos_bank_name'])):'0';

                $bos_bank_acc = isset($purchase_receipt['bos_bank_acc'])?
                    str_replace(' ','',Tools::_str($purchase_receipt['bos_bank_acc'])):'0';

                
                if($payment_type_code !== 'CASH'
                       
                ){
                    if($supplier_bank_acc ===''){
                        $success = 0;
                        $msg[] = 'Supplier Bank Acc. '.Lang::get('empty',true, false);
                    }
                    if($bos_bank_name ===''){
                        $success = 0;
                        $msg[] = 'Supplier Bank Name '.Lang::get('empty',true, false);
                    }
                    if($bos_bank_acc ===''){
                        $success = 0;
                        $msg[] = 'BOS Bank Acc. '.Lang::get('empty',true, false);
                    }
                }
                
                if($success !== 1) break;
                //</editor-fold>

                break;
            
            case 'purchase_receipt_invoiced':
                $temp_result = Validator::validate_on_update(
                    array(
                        'module'=>'purchase_receipt',
                        'module_name'=>'Purchase Receipt',
                        'module_engine'=>'Purchase_Receipt_Engine',
                    ),
                    $purchase_receipt
                );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;
                
                
                
                    
                break;
            
            case 'purchase_receipt_canceled':
                $db = new DB();
                $temp_result = Validator::validate_on_cancel(
                        array(
                            'module'=>'purchase_receipt',
                            'module_name'=>'Purchase Receipt',
                            'module_engine'=>'Purchase_Receipt_Engine',
                        ),
                        $purchase_receipt
                    );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;

                if(Purchase_Receipt_Data_Support::purchase_receipt_is_allocated($purchase_receipt['id'])){
                    $success = 0;
                    $msg[] = 'Purchase Receipt Allocation '.Lang::get('exists',true,false).'. Cancel Purchase Receipt Allocation';
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
    }

    public static function adjust($action,$data=array()){
        $db = new DB();
        $result = array();
        $purchase_receipt_data = isset($data['purchase_receipt'])?
            Tools::_arr($data['purchase_receipt']):array();
        $modid = User_Info::get()['user_id'];
        $datetime_curr = Date('Y-m-d H:i:s');
        switch($action){
            case 'purchase_receipt_add':
                

                $purchase_receipt = array();

                $purchase_receipt = array(
                    'store_id'=>$purchase_receipt_data['store_id'],
                    'payment_type_id'=>$purchase_receipt_data['payment_type_id'],
                    'supplier_id'=>$purchase_receipt_data['supplier_id'],
                    'supplier_bank_acc'=>Tools::empty_to_null($purchase_receipt_data['supplier_bank_acc']),
                    'bos_bank_name'=>Tools::empty_to_null($purchase_receipt_data['bos_bank_name']),
                    'bos_bank_acc'=>Tools::empty_to_null($purchase_receipt_data['bos_bank_acc']),
                    'amount'=>$purchase_receipt_data['amount'],
                    'outstanding_amount'=>Tools::_float($purchase_receipt_data['amount']) - Tools::_float($purchase_receipt_data['change_amount']),
                    'change_amount'=>$purchase_receipt_data['change_amount'],
                    'purchase_receipt_date'=>$datetime_curr,
                    'purchase_receipt_status'=>SI::status_default_status_get('Purchase_Receipt_Engine')['val'],
                    'notes'=>isset($purchase_receipt_data['notes'])?
                        Tools::_str($purchase_receipt_data['notes']):'',
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );

                $result['purchase_receipt'] = $purchase_receipt;                   

                break;
            case 'purchase_receipt_invoiced':                
                $purchase_receipt = array();

                $purchase_receipt = array(
                    'notes'=>isset($purchase_receipt_data['notes'])?
                        Tools::_str($purchase_receipt_data['notes']):'',
                    'purchase_receipt_status'=>'invoiced',
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                $result['purchase_receipt'] = $purchase_receipt;    
                break;
            case 'purchase_receipt_canceled':
                $purchase_receipt = array();

                $purchase_receipt = array(
                    'purchase_receipt_status'=>'X',
                    'cancellation_reason'=>$purchase_receipt_data['cancellation_reason'],
                    'notes'=>isset($purchase_receipt_data['notes'])?
                        Tools::_str($purchase_receipt_data['notes']):'',
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                $result['purchase_receipt'] = $purchase_receipt;    
                break;
        }

        return $result;
    }

    public static function save($method,$data){
        $db = new DB();
        $success = 1;
        $msg = array();
        $action = $method;
        $result = array("success"=>0,"msg"=>array(),'trans_id'=>'');
        $purchase_receipt_data = $data['purchase_receipt'];
        $id = $purchase_receipt_data['id'];

        $method_list = array('purchase_receipt_add');
        foreach(self::$status_list as $status){
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
                case 'purchase_receipt_add':
                    try{ 
                        $db->trans_begin();
                        $temp_result = self::purchase_receipt_add($db,$final_data);
                        $success = $temp_result['success'];
                        $msg = array_merge($msg, $temp_result['msg']);
                        
                        if($success === 1){
                            $db->trans_commit();
                            $msg[] = 'Add Purchase Receipt Success';
                            $result['trans_id'] = $temp_result['trans_id'];
                        }
                        
                    }
                    catch(Exception $e){

                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    }
                    break;
                case 'purchase_receipt_invoiced':
                    try{
                        $db->trans_begin();
                        $temp_result = self::purchase_receipt_invoiced($db,$final_data,$id);
                        $success = $temp_result['success'];
                        $msg = array_merge($msg,$temp_result['msg']);

                        if($success == 1){
                            $result['trans_id'] = $temp_result['trans_id'];
                        }

                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = 'Update Purchase Receipt Success';
                        }
                    }
                    catch(Exception $e){
                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    }  
                    break;
                case 'purchase_receipt_canceled':
                    try{
                        $db->trans_begin();
                        $temp_result = self::purchase_receipt_canceled($db,$final_data,$id);
                        $success = $temp_result['success'];
                        $msg = array_merge($msg,$temp_result['msg']);

                        if($success == 1){
                            $result['trans_id'] = $temp_result['trans_id'];
                        }

                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = 'Cancel Purchase Receipt Success';
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
    }

    public function purchase_receipt_add($db,$final_data){
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $fpurchase_receipt = $final_data['purchase_receipt'];

        $store_id = $fpurchase_receipt['store_id'];
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $purchase_receipt_id = '';       
        $pure_amount = Tools::_float($fpurchase_receipt['amount']) - Tools::_float($fpurchase_receipt['change_amount']);
        $fpurchase_receipt['code'] = SI::code_counter_store_get($db,$store_id, 'purchase_receipt');
        if(!$db->insert('purchase_receipt',$fpurchase_receipt)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $purchase_receipt_code = $fpurchase_receipt['code'];

        if($success == 1){                                
            $purchase_receipt_id = $db->fast_get('purchase_receipt'
                    ,array('code'=>$purchase_receipt_code))[0]['id'];
            $result['trans_id']=$purchase_receipt_id; 
        }

        if($success == 1){
            $purchase_receipt_status_log = array(
                'purchase_receipt_id'=>$purchase_receipt_id
                ,'purchase_receipt_status'=>$fpurchase_receipt['purchase_receipt_status']
                ,'modid'=>$modid
                ,'moddate'=>$moddate    
            );

            if(!$db->insert('purchase_receipt_status_log',$purchase_receipt_status_log)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }


        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
    }
    
    function purchase_receipt_invoiced($db, $final_data,$id){
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $fpurchase_receipt = $final_data['purchase_receipt'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('purchase_receipt',$fpurchase_receipt,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        

        if($success == 1){
            $temp_result = SI::status_log_add($db,'purchase_receipt',
                $id,$fpurchase_receipt['purchase_receipt_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
    }

    function purchase_receipt_canceled($db, $final_data,$id){
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();
        
        $purchase_receipt_id = $id;
        
        $fpurchase_receipt = $final_data['purchase_receipt'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        $purchase_receipt = $db->fast_get('purchase_receipt',array('id'=>$purchase_receipt_id))[0];
        $pure_amount = Tools::_float($purchase_receipt['amount']) - Tools::_float($purchase_receipt['change_amount']);
        
        if(!$db->update('purchase_receipt',$fpurchase_receipt,array("id"=>$purchase_receipt_id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'purchase_receipt',
                $purchase_receipt_id,$purchase_receipt['purchase_receipt_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
    }
}
?>