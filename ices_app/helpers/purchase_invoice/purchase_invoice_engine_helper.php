<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Purchase_Invoice_Engine {

    public static $status_list = array(
        //<editor-fold defaultstate="collapsed">
        array(//label name is used for method name
            'val'=>'invoiced'
            ,'label'=>'INVOICED'
            ,'method'=>'purchase_invoice_invoiced'
            ,'default'=>true
            ,'next_allowed_status'=>array('X')
        )
        ,array(
            'val'=>'X'
            ,'label'=>'CANCELED'
            ,'method'=>'purchase_invoice_canceled'
            ,'next_allowed_status'=>array()
        )
        //</editor-fold>
    );

    public static function purchase_invoice_exists($id){
        //<editor-fold defaultstate="collapsed">
        $result = false;
        $db = new DB();
        $q = '
                select 1 
                from purchase_invoice 
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
            'index'=>get_instance()->config->base_url().'purchase_invoice/',
            'purchase_invoice_engine'=>'purchase_invoice/purchase_invoice_engine',
            'purchase_invoice_data_support' => 'purchase_invoice/purchase_invoice_data_support',
            'purchase_invoice_renderer' => 'purchase_invoice/purchase_invoice_renderer',
            'ajax_search'=>get_instance()->config->base_url().'purchase_invoice/ajax_search/',
            'data_support'=>get_instance()->config->base_url().'purchase_invoice/data_support/',
        );

        return json_decode(json_encode($path));
    }

    public static function submit($id,$method,$post){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper(self::path_get()->purchase_invoice_data_support);

        $post = json_decode($post,TRUE);
        $data = $post;
        $ajax_post = false;                  
        $result = null;
        $cont = true;

        if(isset($post['ajax_post'])) $ajax_post = $post['ajax_post'];
        if($method == 'purchase_invoice_add') $data['purchase_invoice']['id'] = '';
        else $data['purchase_invoice']['id'] = $id;

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
        get_instance()->load->helper('purchase_invoice/purchase_invoice_data_support');
        get_instance()->load->helper('product/product_engine');
        get_instance()->load->helper('product/product_data_support');
        $result = array(
            "success"=>1
            ,"msg"=>array()

        );
        $success = 1;
        $msg = array();
        $ipurchase_invoice = isset($data['purchase_invoice'])?$data['purchase_invoice']:null;
        $iproduct_arr = isset($data['product'])?$data['product']:array();
        $iexpense_arr = isset($data['expense'])?$data['expense']:array();
        $iinfo = isset($data['info'])?Tools::_arr($data['info']):null;
        
        $purchase_invoice_id = $ipurchase_invoice['id'];
        
        switch($method){
            case 'purchase_invoice_add':
                $db = new DB();
                $supplier_id = isset($ipurchase_invoice['supplier_id'])?$ipurchase_invoice['supplier_id']:'';
                $store_id = isset($ipurchase_invoice['store_id'])?
                    Tools::_str($ipurchase_invoice['store_id']):'';

                //<editor-fold defaultstate="collapsed" desc="Major Validation">

                if(!SI::record_exists('store', array('id'=>$store_id,'status'=>'1'))){
                    $success = 0;
                    $msg[] = Lang::get('Store').' '.Lang::get('empty',true,false);
                }

                if(!SI::record_exists('supplier',array('id'=>$supplier_id))){
                    $success = 0;
                    $msg[] = Lang::get('Supplier').' '.Lang::get('empty',true,false);
                }
                
                $product_arrival_date = isset($iinfo['product_arrival_date'])?
                        Tools::_str($iinfo['product_arrival_date']):'';
                
                $curr_date = new DateTime();
                //$curr_date->add(new DateInterval('PT30M'));

                if(strtotime($product_arrival_date) <=strtotime($curr_date->format('Y-m-d H:i:s'))){
                    $success = 0;
                    $msg[] = 'Product Arrival Date'.' '.Lang::get('invalid',true,false);
                }
                
                if(count($iproduct_arr)===0){
                    $success = 0;
                    $msg[] = 'Porduct'.' '.Lang::get('empty',true,false);
                }
                
                $product_arr = array();
                foreach($iproduct_arr as $idx=>$iproduct){
                    $product_id = isset($iproduct['product_id'])?Tools::_str($iproduct['product_id']):'';
                    $unit_id = isset($iproduct['unit_id'])?Tools::_str($iproduct['unit_id']):'';
                    $qty = isset($iproduct['qty'])?Tools::_str($iproduct['qty']):'';
                    $amount = isset($iproduct['amount'])?Tools::_str($iproduct['amount']):'';
                    $product_arr[] = array('product_id'=>$product_id,'unit_id'=>$unit_id);
                    if(Tools::_float($amount)<floatval('0') || Tools::_float($qty)<floatval('0')){
                        $success = 0;
                        $msg[]='Amount or Qty'.' '.Lang::get('invalid');
                    }
                }
                
                if(!Product_Data_Support::product_unit_all_exists($product_arr,array('product_status'=>'active'))){
                    $success = 0;
                    $msg[] = 'Product'.' '.Lang::get('invalid',true,false);
                }
                
                foreach($iexpense_arr as $expense_idx=>$expense){
                    $description = isset($expense['description'])?Tools::_str($expense['description']):'';
                    $amount = isset($expense['amount'])?Tools::_str($expense['amount']):'0';
                    if(floatval($amount)>0 && preg_replace('/[ ]/','',$description)===''){
                        $success = 0;
                        $msg[] = 'Description'.' '.Lang::get('empty',true,false);
                        break;
                    }
                }
                
                //</editor-fold>

                break;
            
            case 'purchase_invoice_invoiced':
                $temp_result = Validator::validate_on_update(
                    array(
                        'module'=>'purchase_invoice',
                        'module_name'=>'Purchase Invoice',
                        'module_engine'=>'Purchase_Invoice_Engine',
                    ),
                    $ipurchase_invoice
                );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;
                    
                break;
            
            case 'purchase_invoice_canceled':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $temp_result = Validator::validate_on_cancel(
                        array(
                            'module'=>'purchase_invoice',
                            'module_name'=>'Purchase Invoice',
                            'module_engine'=>'Purchase_Invoice_Engine',
                        ),
                        $ipurchase_invoice
                    );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;
                
                $pra = Purchase_Invoice_Data_Support::purchase_receipt_allocation_get($purchase_invoice_id);
                foreach($pra as $i=>$row){
                    if($row['purchase_receipt_allocation_status']!== 'X'){
                        $success = 0;
                        $msg[] = 'Cancel'.' '.'Purchase Receipt Allocation';
                        break;
                    }
                }
                $receive_product = Purchase_Invoice_Data_Support::receive_product_get($purchase_invoice_id);
                foreach($receive_product as $i=>$row){
                    if($row['receive_product_status']!== 'X'){
                        $success = 0;
                        $msg[] = 'Cancel'.' '.'Receive Product';
                        break;
                    }
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
    }

    public static function adjust($action,$data=array()){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = array();
        $purchase_invoice_data = isset($data['purchase_invoice'])?
            Tools::_arr($data['purchase_invoice']):array();
        $info_data = isset($data['info'])?
            Tools::_arr($data['info']):array();
        $product_data = isset($data['product'])?
            Tools::_arr($data['product']):array();
        $expense_data = isset($data['expense'])?
            Tools::_arr($data['expense']):array();
        $modid = User_Info::get()['user_id'];
        $datetime_curr = Date('Y-m-d H:i:s');
        switch($action){
            case 'purchase_invoice_add':
                

                $purchase_invoice = array();

                $purchase_invoice_product = array();
                $product_total = floatval('0');
                $expense_total = floatval('0');
                $grand_total = floatval('0');
                foreach($product_data as $idx=>$product){
                    $product_subtotal = Tools::_float($product['qty']) * Tools::_float($product['amount']);
                    
                    $purchase_invoice_product[] = array(
                        'product_id' => $product['product_id'],
                        'unit_id' => $product['unit_id'],
                        'qty' => $product['qty'],
                        'amount' => $product['amount'],
                        'movement_outstanding_qty'=>$product['qty'],
                        'subtotal'=>$product_subtotal,
                    );
                    $product_total+=$product_subtotal;
                }
                
                
                
                $purchase_invoice_expense = array();
                
                foreach($expense_data as $idx=>$expense){

                    $purchase_invoice_expense[] = array(
                        
                        'description' => $expense['description'],
                        'amount' => $expense['amount'],
                    );
                    $expense_total+=Tools::_float($expense['amount']);
                }
                
                $grand_total = $product_total+$expense_total;
                
                $purchase_invoice_info = array(
                    'product_arrival_date'=>Tools::_date($info_data['product_arrival_date']),
                    'reference_code'=>Tools::_str($info_data['reference_code']),
                );
                
                $purchase_invoice = array(
                    'store_id'=>$purchase_invoice_data['store_id'],
                    'supplier_id'=>$purchase_invoice_data['supplier_id'],
                    'purchase_invoice_date'=>$datetime_curr,
                    
                    'total_product'=>$product_total,
                    'total_expense'=>$expense_total,
                    'grand_total'=>$grand_total,
                    'outstanding_amount'=>$grand_total,
                    'purchase_invoice_status'=>SI::status_default_status_get('Purchase_Invoice_Engine')['val'],
                    'notes'=>isset($purchase_invoice_data['notes'])?
                        Tools::empty_to_null(Tools::_str($purchase_invoice_data['notes'])):'',
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                    
                );
                
                $result['purchase_invoice_info'] = $purchase_invoice_info;
                $result['purchase_invoice'] = $purchase_invoice;                   
                $result['purchase_invoice_product'] = $purchase_invoice_product;
                $result['purchase_invoice_expense'] = $purchase_invoice_expense;
                
                break;
            case 'purchase_invoice_invoiced':                
                $purchase_invoice = array();

                $purchase_invoice = array(
                    'notes'=>isset($purchase_invoice_data['notes'])?
                        Tools::_str($purchase_invoice_data['notes']):'',
                    'purchase_invoice_status'=>'invoiced',
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                $result['purchase_invoice'] = $purchase_invoice;    
                break;
            case 'purchase_invoice_canceled':
                $purchase_invoice = array();

                $purchase_invoice = array(
                    'purchase_invoice_status'=>'X',
                    'cancellation_reason'=>$purchase_invoice_data['cancellation_reason'],
                    'notes'=>isset($purchase_invoice_data['notes'])?
                        Tools::_str($purchase_invoice_data['notes']):'',
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                $result['purchase_invoice'] = $purchase_invoice;    
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
        $purchase_invoice_data = $data['purchase_invoice'];
        $id = $purchase_invoice_data['id'];

        $method_list = array('purchase_invoice_add');
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
                case 'purchase_invoice_add':
                    try{ 
                        $db->trans_begin();
                        $temp_result = self::purchase_invoice_add($db,$final_data);
                        $success = $temp_result['success'];
                        $msg = $temp_result['msg'];
                        
                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = 'Add Purchase Invoice Success';
                            $result['trans_id'] = $temp_result['trans_id'];
                        }
                        else{
                            $msg = array_merge($msg, $temp_result['msg']);
                        }
                    }
                    catch(Exception $e){

                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    }
                    break;
                case 'purchase_invoice_invoiced':
                    try{
                        $db->trans_begin();
                        $temp_result = self::purchase_invoice_invoiced($db,$final_data,$id);
                        $success = $temp_result['success'];
                        $msg = array_merge($msg,$temp_result['msg']);

                        if($success == 1){
                            $result['trans_id'] = $temp_result['trans_id'];
                        }

                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = 'Update Purchase Invoice Success';
                        }
                    }
                    catch(Exception $e){
                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    }  
                    break;
                case 'purchase_invoice_canceled':
                    try{
                        $db->trans_begin();
                        $temp_result = self::purchase_invoice_canceled($db,$final_data,$id);
                        $success = $temp_result['success'];
                        $msg = array_merge($msg,$temp_result['msg']);

                        if($success == 1){
                            $result['trans_id'] = $temp_result['trans_id'];
                        }

                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = 'Cancel Purchase Invoice Success';
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

    public function purchase_invoice_add($db,$final_data){
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $fpurchase_invoice = $final_data['purchase_invoice'];
        $fpurchase_invoice_info = $final_data['purchase_invoice_info'];
        $fpurchase_invoice_product = $final_data['purchase_invoice_product'];
        $fpurchase_invoice_expense = $final_data['purchase_invoice_expense'];
        

        $store_id = $fpurchase_invoice['store_id'];
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $purchase_invoice_id = '';       

        $fpurchase_invoice['code'] = SI::code_counter_store_get($db,$store_id, 'purchase_invoice');
        if(!$db->insert('purchase_invoice',$fpurchase_invoice)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $purchase_invoice_code = $fpurchase_invoice['code'];

        if($success == 1){                                
            $purchase_invoice_id = $db->fast_get('purchase_invoice'
                    ,array('code'=>$purchase_invoice_code))[0]['id'];
            $result['trans_id']=$purchase_invoice_id; 
        }

        if($success == 1){
            $fpurchase_invoice_info['purchase_invoice_id'] = $purchase_invoice_id;
            if(!$db->insert('purchase_invoice_info',$fpurchase_invoice_info)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }
        
        if($success === 1){
            foreach($fpurchase_invoice_product as $idx=>$product){
                $product['purchase_invoice_id'] = $purchase_invoice_id;
                if(!$db->insert('purchase_invoice_product',$product)){
                    $success = 0;
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();    
                    break;
                }
            }
        }
        
        if($success === 1){
            foreach($fpurchase_invoice_expense as $idx=>$expense){
                $expense['purchase_invoice_id'] = $purchase_invoice_id;
                if(!$db->insert('purchase_invoice_expense',$expense)){
                    $success = 0;
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();  
                    break;
                }
            }
        }
        
        if($success == 1){
            $temp_result = SI::status_log_add($db,'purchase_invoice',
                    $purchase_invoice_id,$fpurchase_invoice['purchase_invoice_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
    }
    
    function purchase_invoice_invoiced($db, $final_data,$id){
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $fpurchase_invoice = $final_data['purchase_invoice'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('purchase_invoice',$fpurchase_invoice,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        

        if($success == 1){
            $temp_result = SI::status_log_add($db,'purchase_invoice',
                $id,$fpurchase_invoice['purchase_invoice_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
    }

    function purchase_invoice_canceled($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();
        
        $purchase_invoice_id = $id;
        
        $fpurchase_invoice = $final_data['purchase_invoice'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        $purchase_invoice = $db->fast_get('purchase_invoice',array('id'=>$purchase_invoice_id))[0];
        
        if(!$db->update('purchase_invoice',$fpurchase_invoice,array("id"=>$purchase_invoice_id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'purchase_invoice',
                $purchase_invoice_id,$fpurchase_invoice['purchase_invoice_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    public static function movement_outstanding_qty_add($db, $purchase_invoice_id, $product){
        //<editor-fold defaultstate="collapsed">
        $result = array('success'=>1,'msg'=>[]);
        $success = 1;
        $msg = array();

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $reference_id = $product['reference_id'];
        $qty = $product['qty'];
        

        $q='
            update purchase_invoice_product
            set movement_outstanding_qty = movement_outstanding_qty + '.$db->escape($qty).'
            where purchase_invoice_id = '.$db->escape($purchase_invoice_id).'
                and id = '.$db->escape($reference_id).'
        ';

        if(!$db->query($q)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
}
?>