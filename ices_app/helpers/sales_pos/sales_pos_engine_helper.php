<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sales_Pos_Engine {

    public static $status_list; 

    public static $reference_type = array(
        '','sales_prospect'
    );
    
    public static function helper_init(){
        //<editor-fold defaultstate="collapsed">
        self::$status_list = array(
            //<editor-fold defaultstate="collapsed">
            array(
                'val'=>''
                ,'label'=>''
                , 'method'=>'sales_pos_add'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Add')
                        ,array('val'=>Lang::get(array('Point of Sale'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            array(//label name is used for method name
                'val'=>'invoiced'
                ,'label'=>'INVOICED'
                ,'method'=>''
                ,'default'=>true
                ,'next_allowed_status'=>array('X')
                ,'msg'=>array(
                    'success'=>array()
                )
            )
            ,array(
                'val'=>'X'
                ,'label'=>'CANCELED'
                ,'method'=>''
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array()
                )
            )
            //</editor-fold>
        );
        //</editor-fold>
    }
    
    public static function sales_pos_exists($id){
        $result = false;
        $db = new DB();
        $q = '
                select 1 
                from sales_invoice t1
                    inner join sales_invoice_info t2 on t1.id = t2.sales_invoice_id
                where t1.status > 0 && t1.id = '.$db->escape($id).'
                    and t2.sales_invoice_type="sales_invoice_pos"
            ';
        $rs = $db->query_array_obj($q);
        if(count($rs)>0){
            $result = true;
        }
        return $result;
    }

    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'sales_pos/',
            'sales_pos_engine'=>'sales_pos/sales_pos_engine',
            'sales_pos_data_support' => 'sales_pos/sales_pos_data_support',
            'sales_pos_print'=>'sales_pos/sales_pos_print',
            'sales_pos_renderer' => 'sales_pos/sales_pos_renderer',
            'ajax_search'=>get_instance()->config->base_url().'sales_pos/ajax_search/',
            'data_support'=>get_instance()->config->base_url().'sales_pos/data_support/',
            'product_stock_engine'=>'product_stock_engine',
        );

        return json_decode(json_encode($path));
    }

    public static function movement_product_diff_get($product_arr,$movement_arr){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        for($i = 0;$i<count($product_arr);$i++){
            $product_id = $product_arr[$i]['product_id'];
            $unit_id = $product_arr[$i]['unit_id'];
            $diff_qty = floatval($product_arr[$i]['qty']);

            for($j = 0;$j<count($movement_arr);$j++){
                for($k = 0;$k<count($movement_arr[$j]);$k++){
                    if($product_id === $movement_arr[$j][$k]['product_id'] &&
                        $unit_id === $movement_arr[$j][$k]['unit_id']
                    ){
                        $diff_qty -=floatval($movement_arr[$j][$k]['qty']);
                    }
                }
            }
            $result[] = array(
                'product_id'=>$product_id,
                'unit_id'=>$unit_id,
                'qty_diff'=>floatval($diff_qty),
            );
        }
        
        for($i = 0;$i<count($movement_arr);$i++){
            for($j = 0;$j<count($movement_arr[$i]);$j++){
                $product_id = $movement_arr[$i][$j]['product_id'];
                $unit_id = $movement_arr[$i][$j]['unit_id'];
                $product_unit_exists = false;
                for($k = 0;$k<count($product_arr);$k++){
                    if($product_arr[$k]['product_id'] === $product_id &&
                        $product_arr[$k]['unit_id'] === $unit_id
                    ){
                        $product_unit_exists = true;
                        break;
                    }
                }
                
                if(!$product_unit_exists){
                    $result[] = array(
                        'product_id'=>$product_id,
                        'unit_id'=>$unit_id,
                        'qty_diff'=>floatval(-1*$diff_qty),
                    );
                }
            }
        }
        
        return $result;
        //</editor-fold>
    }


    public function product_price_list_get($customer_id, $price_list_id, $product_arr){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = array();
        for($i = 0;$i<count($product_arr);$i++){
            $product_id = isset($product_arr[$i]['product_id'])?
                    Tools::_str($product_arr[$i]['product_id']):'';
            $unit_id = isset($product_arr[$i]['unit_id'])?
                    Tools::_str($product_arr[$i]['unit_id']):'';
            $qty = isset($product_arr[$i]['qty'])?
                    Tools::_str($product_arr[$i]['qty']):'';
            $amount = '0';

            $q = '
                select amount
                from product_price_list_product t1
                    inner join customer_type_product_price_list t2 
                        on t1.product_price_list_id = t2.product_price_list_id
                    inner join customer_customer_type t3
                        on t3.customer_type_id = t2.customer_type_id
                where t3.customer_id = '.$db->escape($customer_id).'
                    and t1.product_price_list_id = '.$db->escape($price_list_id).'
                    and t1.product_id = '.$db->escape($product_id).'
                    and t1.unit_id = '.$db->escape($unit_id).'
                    and t1.min_qty <= '.$db->escape($qty).'
                order by t1.min_qty desc
                limit 1
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0) $amount = $rs[0]['amount'];
            $result[] = array(
                'product_id'=>$product_id,
                'unit_id'=>$unit_id,
                'amount'=>$amount

            );
        }
        return $result;
        //</editor-fold>
    }

    public static function movement_outstanding_qty_add($db, $sales_invoice_id, $product){
        //<editor-fold defaultstate="collapsed">
        $result = array('success'=>1,'msg'=>[]);
        $success = 1;
        $msg = array();

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $reference_id = $product['reference_id'];
        $qty = $product['qty'];

        $q='
            update sales_invoice_product
            set movement_outstanding_qty = movement_outstanding_qty + '.$db->escape($qty).'
            where sales_invoice_id = '.$db->escape($sales_invoice_id).'
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
    
    public static function validate($method,$data=array()){            
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('sales_pos/sales_pos_data_support');
        get_instance()->load->helper('sales_prospect/sales_prospect_engine');
        get_instance()->load->helper('customer_deposit_allocation/customer_deposit_allocation_engine');
        get_instance()->load->helper('sales_receipt/sales_receipt_engine');
        get_instance()->load->helper('sales_receipt_allocation/sales_receipt_allocation_engine');
        get_instance()->load->helper('delivery_order_final/delivery_order_final_engine');
        get_instance()->load->helper('delivery_order/delivery_order_engine');
        get_instance()->load->helper('intake_final/intake_final_engine');
        get_instance()->load->helper('intake/intake_engine');
        get_instance()->load->helper('product_stock_engine');
        get_instance()->load->helper('bos_bank_account/bos_bank_account_data_support');
        get_instance()->load->helper('payment_type/payment_type_data_support');
        
        $result = array(
            "success"=>1
            ,"msg"=>array()

        );

        $sales_pos = isset($data['sales_pos'])?$data['sales_pos']:null;
        $product = isset($data['product'])? $data['product']: array();
        $final_movement_arr = isset($data['final_movement'])?$data['final_movement']:array();
        $customer_deposit = isset($data['customer_deposit'])?
                Tools::_arr($data['customer_deposit']):array();
        $receipt = isset($data['receipt'])?$data['receipt']:array();
        $additional_cost = isset($data['additional_cost'])?$data['additional_cost']:array();
        $receipt_change = isset($data['receipt_change'])?
                Tools::_str($data['receipt_change']):'0';
        switch($method){
            case 'sales_pos_add':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $customer_id = isset($sales_pos['customer_id'])?$sales_pos['customer_id']:'';
                $sales_inquiry_by_id = isset($sales_pos['sales_inquiry_by_id'])?$sales_pos['sales_inquiry_by_id']:'';
                $price_list_id = isset($sales_pos['price_list_id'])?$sales_pos['price_list_id']:'';
                $approval_id = isset($sales_pos['approval_id'])?Tools::empty_to_null(Tools::_str($sales_pos['approval_id'])):'';
                $is_delivery = isset($sales_pos['is_delivery'])?($sales_pos['is_delivery']?true:false):false;
                $reference_type = isset($sales_pos['reference_type'])?
                    Tools::_str($sales_pos['reference_type']):'';
                $reference_id = isset($sales_pos['reference_id'])?
                    Tools::empty_to_null(Tools::_str($sales_pos['reference_id'])):'';
                $store_id = isset($sales_pos['store_id'])?
                    Tools::_str($sales_pos['store_id']):'';

                //<editor-fold defaultstate="collapsed" desc="Major Validation">

                if(!SI::record_exists('store', array('id'=>$store_id,'status'=>'1'))){
                    $result['success'] = 0;
                    $result['msg'][] = 'Invalid Store';
                }

                if(!in_array($reference_type, self::$reference_type)){
                    $result['success'] = 0;
                    $result['msg'][] = 'Invalid Reference Type';
                }

                if($reference_type === 'sales_prospect'){
                    $sales_prospect_id = $reference_id;
                    if(!SI::record_exists('sales_prospect'
                        ,array('id'=>$sales_prospect_id,'status'=>'1'
                            ,'sales_prospect_status'=>'registered'
                        )
                    )){
                        $result['success'] = 0;
                        $result['msg'][] = 'Invalid Sales Prospect';
                    }

                    if(SI::record_exists('sales_pos_info',
                        array(
                            'reference_type'=>'sales_prospect'
                            ,'reference_id'=>$sales_prospect_id
                        )
                    )){
                        $result['success'] = 0;
                        $result['msg'][] = 'Sales Prospect already has POS';
                    }
                }

                if(!SI::record_exists('sales_inquiry_by',array('id'=>$sales_inquiry_by_id,'status'=>'1','sales_inquiry_by_status'=>'A'))){
                    $result['success'] = 0;
                    $result['msg'][] = "Sales Inquiry By ".Lang::get("Empty",true,false);   
                }

                if(!SI::record_exists('customer',array('id'=>$customer_id,'status'=>'1','customer_status'=>'A'))){
                    $result['success'] = 0;
                    $result['msg'][] = 'Customer '.Lang::get('Empty',true,false);   
                }

                $q = '
                    select 1
                    from customer_customer_type t1
                        inner join customer_type t2 on t1.customer_type_id = t2.id
                        inner join customer_type_product_price_list t3 on t2.id = t3.customer_type_id
                        inner join product_price_list t4 on t4.id = t3.product_price_list_id
                    where t1.customer_id ='.$db->escape($customer_id)
                        .' and t4.id = '.$db->escape($price_list_id).'
                ';
                if(count($db->query_array($q)) === 0){
                    $result['success'] = 0;
                    $result['msg'][] = "Price List is Empty";   
                }
                
                if(!is_null($approval_id)){
                    $q = '
                        select 1 
                        from approval t1
                            inner join approval_type t2 on t1.approval_type_id = t2.id
                        where t2.code = "SIP"
                            and t1.limit > t1.use
                            and t1.status > 0
                            and t1.id = '.$db->escape($approval_id).'
                    ';
                    if(count($db->query_array($q)) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Invalid Approval";
                    }
                }

                if(count(Tools::_arr($product)) === 0){
                    $result['success'] = 0;
                    $result['msg'][] = "Product is Empty";                        
                }

                if(count(Tools::_arr($final_movement_arr)) === 0){
                    $result['success'] = 0;
                    $result['msg'][] = "Movement is Empty";                        
                }

                if($result['success'] !== 1) break;
                //</editor-fold>

                $q = '
                    select * from customer where status>0 and id='.$db->escape($customer_id).'
                ';
                $rs = $db->query_array($q);
                $is_sales_receipt_outstanding = $rs[0]['is_sales_receipt_outstanding'];
                $is_credit = $rs[0]['is_credit'];

                //<editor-fold defaultstate="collapsed" desc="Price List Validation">
                if($approval_id === ''){


                    $ppl_req_arr = self::product_price_list_get($customer_id, $price_list_id, $product);

                    for($i = 0;$i<count($product);$i++){
                        $product_id = isset($product[$i]['product_id'])?
                                Tools::_str($product[$i]['product_id']):'';
                        $unit_id = isset($product[$i]['unit_id'])?
                                Tools::_str($product[$i]['unit_id']):'';
                        $qty = isset($product[$i]['qty'])?
                                Tools::_str($product[$i]['qty']):'';
                        $amount = isset($product[$i]['amount'])?
                                Tools::_str($product[$i]['amount']):'0';
                        $match = false;

                        foreach($ppl_req_arr as $idx=>$ppl){
                            if($ppl['product_id'] === $product_id && $ppl['unit_id'] === $unit_id){
                                if(Tools::_float($amount) === Tools::_float($ppl['amount'])){
                                    $match = true;
                                }
                                else{
                                    break;
                                }
                            }
                        }

                        if(!$match){
                            $result['success'] = 0;
                            $result['msg'][] = 'Invalid Price List Amount';
                            break;
                        }
                        
                        if(Tools::_float($amount)<floatval('0')){
                            $result['success'] = 0;
                            $result['msg'][] = 'Negative Amount '.Lang::get('invalid',true,false);
                            break;
                        }
                    }


                    if($result['success'] !== 1) break;
                }
                //</editor-fold>

                $total_product = 0;

                //<editor-fold defaultstate="collapsed" desc="Product Validation">

                for($i = 0;$i<count($product);$i++){
                    $product_id = isset($product[$i]['product_id'])?$product[$i]['product_id']:'';
                    $unit_id = isset($product[$i]['unit_id'])?$product[$i]['unit_id']:'';
                    $qty = isset($product[$i]['qty'])?$product[$i]['qty']:'';
                    $amount = isset($product[$i]['amount'])?$product[$i]['amount']:'';
                    
                    if(Tools::_float($qty)<floatval('0')||  Tools::_float($qty)<floatval('0')){
                        $result['success'] = 0;
                        $result['msg'][] = 'Negative Product Qty or Amount'.Lang::get('invalid',true,false);
                        break;
                    }
                    
                    $mult_qty = Sales_Pos_Data_Support::multiplication_qty_get($product_id, $unit_id);
                    $expected_amount = Sales_Pos_Data_Support::product_price_get($price_list_id, $product_id, $unit_id, $qty);
                    $subtotal = (floatval($qty) * floatval($amount));
                    $total_product += $subtotal;
                    $qty_stock = Product_Stock_Engine::stock_sum_get('stock_sales_available',
                            $product_id, $unit_id, Warehouse_Engine::BOS_get('id')
                    );

                    if($qty % $mult_qty !== 0){
                        $result['success'] = 0;
                        $result['msg'][] = 'Mismatch Product Multiplication Qty';                                
                    }
                    if($approval_id === ''){
                        if(floatval($expected_amount) !== floatval($amount)){
                            $result['success'] = 0;
                            $result['msg'][] = 'Mismatch Product Amount';                                
                        }
                    }
                    if(floatval($qty)<=0){
                        $result['success'] = 0;
                        $result['msg'][] = 'Qty must be higher than 0';                                
                    }
                    /* active when product qty is checked
                    if(floatval($qty)>floatval($qty_stock)){
                        $result['success'] = 0;
                        $result['msg'][] = 'Qty must be lower than Available Stock Sales';                                
                    }
                    */
                    if($result['success'] !== 1) break;
                }


                $extra_charge = $sales_pos['extra_charge'];
                $extra_charge_param  = array(
                    'price_list_id'=>$price_list_id
                    ,'products'=>array()
                    ,'delivery'=>$is_delivery
                );
                for($i = 0;$i<count($product);$i++){
                    $product_id = isset($product[$i]['product_id'])?$product[$i]['product_id']:'';
                    $unit_id = isset($product[$i]['unit_id'])?$product[$i]['unit_id']:'';
                    $qty = isset($product[$i]['qty'])?$product[$i]['qty']:'';
                    $extra_charge_param['products'][] = array(
                        'product_id'=>$product_id
                        ,'unit_id'=>$unit_id
                        ,'qty'=>$qty
                    );
                }
                $extra_charge_msg = Sales_Pos_Data_Support::extra_charge_message_get($extra_charge_param);
                $expected_extra_charge = preg_replace('/[^0-9.]/','', $extra_charge_msg['amount']);

                if(floatval($extra_charge) !== floatval($expected_extra_charge)){
                    $result['success'] = 0;
                    $result['msg'][] = 'Mismatch Extra Charge';
                }

                if($result['success'] !== 1){
                    break;
                }

                //</editor-fold>

                $discount = isset($sales_pos['discount'])?floatval($sales_pos['discount']):0;
                if(Tools::_float($discount)<floatval('0')){
                    $result['success'] = 0;
                    $result['msg'][] = 'Negative Discount '.Lang::get('invalid',true,false);
                }
                $total_additional_cost = floatval('0');
                for($i = 0;$i<count($additional_cost);$i++){
                    $amount = isset($additional_cost[$i]['amount'])?$additional_cost[$i]['amount']:'0.00';
                    $description = $additional_cost[$i]['description']?$additional_cost[$i]['description']:'';

                    if(floatval($amount)>0 && strlen(str_replace(' ','',$description)) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = 'Empty Additional Cost Description';
                    }
                    $total_additional_cost+=Tools::_float($amount);
                }
                $delivery_cost_estimation = floatval('0');

                if($is_delivery){
                    $delivery_cost_estimation= floatval($sales_pos['delivery_cost_estimation']);
                }

                $grand_total = $total_product
                        - $discount 
                        + $total_additional_cost
                        + $expected_extra_charge
                        + $delivery_cost_estimation
                    ;
                

                //<editor-fold defaultstate="collapsed" desc="Payment Validation">

                $total_payment = Tools::_float('0');

                $cust_dep_alloc_arr = Customer_Deposit_Allocation_Engine::cda_allocate_amount_get($customer_id, $grand_total);
                $remaining_amount = $grand_total;
                $cust_dep_alloc_total = Tools::_float('0');
                if(count($cust_dep_alloc_arr) !== count($customer_deposit)){
                    $result['success'] = 0;
                    $result['msg'][] = 'Invalid Customer Deposit Data';
                }
                foreach($cust_dep_alloc_arr as $idx=>$cust_dep_alloc){
                    $valid = false;
                    $allocated_amount_req = Tools::_float($cust_dep_alloc['allocated_amount']);
                    if($allocated_amount_req > $remaining_amount){
                        $allocated_amount_req = $remaining_amount;
                    }
                    for($i = 0;$i<count(Tools::_arr($customer_deposit));$i++){
                        if($cust_dep_alloc['customer_deposit_id'] === $customer_deposit[$i]['customer_deposit_id']){
                            $allocated_amount = isset($customer_deposit[$i]['allocated_amount'])?
                                    Tools::_float($customer_deposit[$i]['allocated_amount']):'0';

                            if($allocated_amount === $allocated_amount_req){
                                $valid = true;
                                $cust_dep_alloc_total+= $allocated_amount;
                            }
                            else{
                                $valid = false;
                                break;
                            }
                        }
                    }

                    $remaining_amount -=$allocated_amount_req;

                    if(!$valid){
                        $result['success'] = 0;
                        $result['msg'][] = 'Invalid Customer Deposit Data';
                        break;
                    }

                    if($remaining_amount <=0){
                        break;
                    }
                }

                $receipt_total_allocated_amount = 0;
                $payment_type_arr = Sales_Pos_Data_Support::payment_type_get($customer_id);
                for($i = 0;$i<count($receipt);$i++){
                    $payment_type_id = isset($receipt[$i]['payment_type_id'])?
                            $receipt[$i]['payment_type_id']:'';
                    $amount = isset($receipt[$i]['amount'])?$receipt[$i]['amount']:'0';
                    $allocated_amount = isset($receipt[$i]['allocated_amount'])?$receipt[$i]['allocated_amount']:'0';
                    $customer_bank_acc = Tools::empty_to_null(isset($receipt[$i]['customer_bank_acc'])?$receipt[$i]['customer_bank_acc']:'');
                    $bos_bank_account_id = Tools::empty_to_null(isset($receipt[$i]['bos_bank_account_id'])?$receipt[$i]['bos_bank_account_id']:'');
                    $payment_type_code = Payment_Type_Data_Support::payment_type_code_get($payment_type_id);
                    
                    if(Tools::_float($amount)<floatval('0')||  Tools::_float($allocated_amount)<floatval('0')){
                        $result['success'] = 0;
                        $result['msg'][] = 'Negative Sales Receipt Amount or Allocated Amount '.Lang::get('invalid',true,false);
                        break;
                    }
                    
                    $receipt_total_allocated_amount+=floatval($allocated_amount);

                    if(!Tools::data_array_exists($payment_type_arr,array('id'=>$payment_type_id))){
                        $result['success'] = 0;
                        $result['msg'][] = 'Invalid Payment Type';
                    }
                    if(floatval($amount)<=floatval('0')){
                        $result['success'] = 0;
                        $result['msg'][] = 'Receipt Amount must be higher than 0';
                    }
                    if(floatval($allocated_amount)<=floatval('0')){
                        $result['success'] = 0;
                        $result['msg'][] = 'Receipt Allocated Amount must be higher than 0';
                    }

                    if($payment_type_code === null){
                        $result['success'] = 0;
                        $result['msg'][] = 'Payment Type '.Lang::get('empty',true,false);
                    }
                    else{
                        if($payment_type_code === 'CASH'){
                            if($customer_bank_acc !== null){
                                $result['success'] = 0;
                                $result['msg'][] = 'Customer Bank Account '.Lang::get('invalid',true,false);
                            }
                            if($bos_bank_account_id !== null){
                                $result['success'] = 0;
                                $result['msg'][] = 'BOS Bank Account '.Lang::get('invalid',true,false);
                            }
                        }
                        else{
                            if($customer_bank_acc === null){
                                $result['success'] = 0;
                                $result['msg'][] = 'Customer Bank Account '.Lang::get('invalid',true,false);
                            }
                            if(is_null(Bos_Bank_Account_Data_Support::bos_bank_account_get($bos_bank_account_id))){
                                $result['success'] = 0;
                                $result['msg'][] = 'BOS Bank Account '.Lang::get('invalid',true,false);
                            }
                        }
                    }
                    
                    if($result['success'] !== 1){
                        break;
                    }
                }

                $outstanding_amount = $grand_total - ($cust_dep_alloc_total + $receipt_total_allocated_amount);
                if($result['success'] === 1){
                    if(!$is_sales_receipt_outstanding){
                        if($outstanding_amount > Tools::_float('0')){
                           $result['success'] = 0;
                           $result['msg'][] = 'Outstanding Sales POS '.Tools::thousand_separator($outstanding_amount,5);
                        }
                    }
                }
                
                
                
                if(Tools::_float($receipt_total_allocated_amount)>Tools::_float($grand_total)){
                    $receipt_change_expected = $outstanding_amount * -1;
                    if($receipt_change_expected !== Tools::_float($receipt_change)){
                        $result['success'] = 0;
                        $result['msg'][] = 'Invalid Receipt Change '.$receipt_change_expected;
                    }
                }                    
                if($result['success'] !== 1){
                    break;
                }

                //</editor-fold>

                //<editor-fold defaultstate="collapsed" desc="Movement Validation">
                $movement_product_total_arr = array();
                $mptq = array();//movement product total qty

                for($i = 0;$i<count($final_movement_arr);$i++){
                    $final_movement = $final_movement_arr[$i];
                    $final_movement_date = Tools::_date(isset($final_movement['final_movement_date'])?
                            Tools::_str($final_movement['final_movement_date']):'');
                    
                    if(strtotime($final_movement_date) < strtotime(Tools::_date('','Y-m-d H:i:s'))){
                        $result['success'] = 0;
                        $result['msg'][] = Lang::get(array(($is_delivery?'Delivery Order':'Product Intake'),'Date')).' '.Lang::get('must be greater than',true,false,false,true).' '.Tools::_date('','F d, Y H:i:s');
                        break;
                    }
                    $movement_arr = isset($final_movement['movement'])?
                        Tools::_arr($final_movement['movement']):array();
                    for($j = 0;$j<count($movement_arr);$j++){
                        $movement = $movement_arr[$j];
                        $mov_product = isset($movement['product'])?
                            Tools::_arr($movement['product']):array();
                        $warehouse_id = isset($movement['warehouse_id'])?
                                Tools::_str($movement['warehouse_id']):'';
                        $temp = array();
                        
                        for($k = 0;$k<count($mov_product);$k++){
                            $product_id = isset($mov_product[$k]['product_id'])?
                                    Tools::_str($mov_product[$k]['product_id']):'';
                            $unit_id = isset($mov_product[$k]['unit_id'])?
                                    Tools::_str($mov_product[$k]['unit_id']):'';
                            $qty = isset($mov_product[$k]['qty'])?
                                    Tools::_str($mov_product[$k]['qty']):'0';
                            
                            if(Tools::_float($qty)<floatval('0')){
                                $result['success'] = 0;
                                $result['msg'][] = 'Negative Movement Qty '.Lang::get('invalid',true,false);
                                break;
                            }
                            
                            

                            $qty_stock = Product_Stock_Engine::stock_sum_get('stock_sales_available',
                                $product_id, $unit_id, array($warehouse_id)
                            );
                            if(floatval($qty_stock)<$qty){
                                $result['success'] = 0;
                                $result['msg'][] = 'Movement Qty '.Lang::get('is greater than',true,false).' Available Sales Qty';
                            }
                            if($result['success'] !== 1) break;
                            
                            if(count(Tools::array_extract($movement_product_total_arr,array(),
                                array('data'=>array(array('product_id'=>$product_id,'unit_id'=>$unit_id)),
                                    )
                            ))>0){
                                for($mpti=0;$mpti<count($movement_product_total_arr);$mpti++){
                                    if($movement_product_total_arr[$mpti]['product_id'] === $product_id &&
                                        $movement_product_total_arr[$mpti]['unit_id'] === $unit_id
                                    ){
                                        $movement_product_total_arr[$mpti]['qty']+=Tools::_float($qty);
                                    }
                                }
                            }
                            else{
                                $temp = array(
                                    'product_id'=>$product_id
                                    ,'unit_id'=>$unit_id
                                    ,'qty'=>Tools::_float($qty)
                                );
                                $movement_product_total_arr[] = $temp; 
                            }
                        }   
                        
                        if($result['success'] !== 1) break;
                    }

                    if($result['success'] !== 1) break;
                }
                
                $product_diff_arr = self::movement_product_diff_get($product, array($movement_product_total_arr));
                
                foreach($product_diff_arr as $idx=>$product_diff){
                    if(Tools::_float($product_diff['qty_diff'])< Tools::_float('0')){
                        //check movement product not in sales pos
                        $result['success'] = 0;
                        $result['msg'][] = Lang::get('Movement Product Qty ').Lang::get('is too many',true,false);
                        break;
                    }
                }
                
                
                for($i = 0;$i<count($product);$i++){
                    $product_id = isset($product[$i]['product_id'])?Tools::_str($product[$i]['product_id']):'';
                    $unit_id = isset($product[$i]['unit_id'])?Tools::_str($product[$i]['unit_id']):'';
                    $ordered_qty = isset($product[$i]['qty'])?Tools::_str($product[$i]['qty']):'0';

                    $qty = '0';
                    $movement_product = Tools::array_extract($movement_product_total_arr,array(),array('data'=>array(array('product_id'=>$product_id,'unit_id'=>$unit_id)),'cfg'=>array('compare_sign'=>'===')));
                    if(count($movement_product)>0){
                        $qty = $movement_product[0]['qty'];
                    }
                    $stock_total = Product_Stock_Engine::stock_sum_get('stock_sales_available',$product_id, $unit_id,  Warehouse_Engine::BOS_get('id'));

                    if( Tools::_float($qty) < Tools::_float($stock_total) &&
                        Tools::_float($qty) < Tools::_float($ordered_qty)

                    ){
                        $result['success'] = 0;
                        $result['msg'][] = 'Reserved Qty '.Lang::get('invalid',true, false);
                        break;
                    }

                    if($result['success']!== 1) break;

                }
                
                //INTAKE RULE
                //1. Intake service only available in one day
                

                if(!$is_delivery){
                    if(count($final_movement_arr)>1){
                        $result['success'] = 0;
                        $result['msg'][] = Lang::get('Intake ').Lang::get('is more than').' '
                            .Lang::get('one',true,false);
                    }
                }
                
                    
                
                
                
                
                //</editor-fold>

                //</editor-fold>
                break;
            case 'sales_pos_canceled':
                $db = new DB();
                $success = 0;
                $msg[] = 'Cancel Sales POS invalid';
                break;
            default:
                $result['success'] = 0;
                $result['msg'][] = 'Invalid Method';
                break;


        }

        return $result;
        //</editor-fold>
    }

    public static function adjust($action,$data=array()){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = array();

        switch($action){
            case 'sales_pos_add':
                get_instance()->load->helper('sales_receipt/sales_receipt_engine');
                get_instance()->load->helper('sales_receipt_allocation/sales_receipt_allocation_engine');
                get_instance()->load->helper('customer_deposit_allocation/customer_deposit_allocation_engine');

                $sales_pos_data = isset($data['sales_pos'])?
                    Tools::_arr($data['sales_pos']):array();
                $product_data = isset($data['product'])?
                    Tools::_arr($data['product']):array();
                $additional_cost_data = isset($data['additional_cost'])?
                    Tools::_arr($data['additional_cost']):array();
                $receipt_data = isset($data['receipt'])?
                    Tools::_arr($data['receipt']):array();
                $customer_deposit_data = isset($data['customer_deposit'])?
                    Tools::_arr($data['customer_deposit']):array();
                $receipt_change_data = isset($data['receipt_change'])?
                    Tools::_str($data['receipt_change']):'0';
                $final_movement_data = isset($data['final_movement'])?
                    $data['final_movement']:array();

                $sales_date = Date('Y-m-d H:i:s');   
                $modid = User_Info::get()['user_id'];
                $moddate = Date('Y-m-d H:i:s');
                $expedition_id = isset($sales_pos_data['expedition_id'])?
                        Tools::_str($sales_pos_data['expedition_id']):'';
                $total_product = Tools::_float('0');
                $is_delivery = $sales_pos_data['is_delivery'];
                $delivery_cost_estimation = isset($sales_pos_data['delivery_cost_estimation'])?
                        $sales_pos_data['delivery_cost_estimation']:'0.00';
                $discount = $sales_pos_data['discount'];
                $price_list_id = $sales_pos_data['price_list_id'];
                $is_delivery = Tools::_bool($sales_pos_data['is_delivery']);
                $store_id = Tools::_str($sales_pos_data['store_id']);
                $customer_id = Tools::_str($sales_pos_data['customer_id']);

                $sales_invoice_product = array();
                $extra_charge_param  = array(
                    'price_list_id'=>$price_list_id
                    ,'products'=>array()
                    ,'delivery'=>$is_delivery
                );
                for($i=0;$i<count($product_data);$i++){
                    $product_id = isset($product_data[$i]['product_id'])?
                            Tools::_str($product_data[$i]['product_id']):'';
                    $unit_id = isset($product_data[$i]['unit_id'])?
                            Tools::_str($product_data[$i]['unit_id']):'';
                    $qty = isset($product_data[$i]['qty'])?
                            Tools::_str($product_data[$i]['qty']):'0';
                    $amount = isset($product_data[$i]['amount'])?
                            Tools::_str($product_data[$i]['amount']):'0';
                    $subtotal = Tools::_str(Tools::_float($qty) * Tools::_float($amount));

                    if($qty>0){
                         $extra_charge_param['products'][] = array(
                            'product_id'=>$product_id
                            ,'unit_id'=>$unit_id
                            ,'qty'=>$qty
                        );

                        $expedition_param = array(
                            'expedition_id'=>$expedition_id,
                            'product_id'=>$product_id,
                            'unit_id'=>$unit_id,
                            'qty'=>$qty                            
                        );

                        $expedition_weight_msg = Sales_Pos_Data_Support::expedition_weight_message_get($expedition_param);
                        $expedition_weight = $expedition_weight_msg['weight'];
                        $expedition_weight_unit_id = $expedition_weight_msg['unit_id'];                        


                        $sales_invoice_product[] = array(
                            'product_id'=>$product_id,
                            'unit_id'=>$unit_id,
                            'qty'=>$qty,
                            'movement_outstanding_qty'=>$qty,
                            'amount'=>$amount,
                            'subtotal'=>$subtotal,
                            'expedition_weight_qty'=>$expedition_weight,
                            'expedition_weight_unit_id'=>$expedition_weight_unit_id
                        );
                    }

                    $total_product+=Tools::_float($subtotal);
                }

                $extra_charge_msg = Sales_Pos_Data_Support::extra_charge_message_get($extra_charge_param);
                $extra_charge = preg_replace('/[^0-9.]/','', $extra_charge_msg['amount']);

                $additional_cost = array();
                $additional_cost_total = floatval('0');
                for($i = 0;$i<count($additional_cost_data);$i++){
                    $amount = $additional_cost_data[$i]['amount'];
                    $description = $additional_cost_data[$i]['description'];
                    if(floatval($amount) > 0){
                        $additional_cost[]=array(
                            'description'=>$description,
                            'amount'=>$amount
                        );
                        $additional_cost_total+=floatval($amount);
                    }
                }

                $grand_total = $total_product 
                        - floatval($discount) 
                        + floatval($delivery_cost_estimation)
                        + floatval($additional_cost_total)
                        + floatval($extra_charge)
                    ;


                $sales_invoice = array(
                    'store_id'=>$sales_pos_data['store_id'],
                    'customer_id'=>$sales_pos_data['customer_id'],
                    'sales_invoice_date'=>$sales_date,
                    'total_product'=>Tools::_str($total_product),
                    'discount'=>$discount,
                    'extra_charge'=>$extra_charge,
                    'delivery_cost_estimation'=>$delivery_cost_estimation,
                    'sales_invoice_status'=>SI::status_default_status_get('Sales_POS_Engine')['val'],
                    'grand_total'=>$grand_total,
                    'outstanding_amount'=>$grand_total,
                    'status'=>'1',
                    'modid'=>$modid,
                    'moddate'=>$moddate

                );

                $sales_invoice_info = array(
                    'sales_invoice_type'=>'sales_invoice_pos',
                    'sales_inquiry_by_id'=>Tools::empty_to_null($sales_pos_data['sales_inquiry_by_id']),
                    'reference_type'=>Tools::empty_to_null($sales_pos_data['reference_type']),
                    'reference_id'=>Tools::empty_to_null($sales_pos_data['reference_id']),
                    'approval_id'=>Tools::empty_to_null($sales_pos_data['approval_id']),
                    'product_price_list_id'=>$sales_pos_data['price_list_id'],
                    'expedition_id'=>Tools::empty_to_null(str_replace(' ','',$sales_pos_data['expedition_id'])),
                    'is_delivery'=>isset($sales_pos_data['is_delivery'])?
                        (Tools::_bool($sales_pos_data['is_delivery'])?'1':'0'):'0',
                    'extra_charge_amount'=>$extra_charge,
                    'extra_charge_msg'=>$extra_charge_msg['msg'],
                );


                $sales_receipt = array();
                for($i = 0;$i<count($receipt_data);$i++){
                    $payment_type_id = $receipt_data[$i]['payment_type_id'];
                    $sales_receipt[] = array(
                        'customer_id'=>$customer_id,
                        'sales_receipt_date'=>$sales_date,
                        'payment_type_id'=>$payment_type_id,
                        'customer_bank_acc'=>Tools::empty_to_null($receipt_data[$i]['customer_bank_acc']),
                        'bos_bank_account_id'=>Tools::empty_to_null($receipt_data[$i]['bos_bank_account_id']),
                        'amount'=>$receipt_data[$i]['amount'],
                        'change_amount'=>'0',
                        'outstanding_amount'=>$receipt_data[$i]['amount'],
                        'sales_receipt_status'=>SI::status_default_status_get('Sales_Receipt_Engine')['val'],
                        'modid'=>$modid,
                        'moddate'=>$moddate,
                        'store_id'=>$store_id
                    );
                    if($db->fast_get('payment_type',array('id'=>$payment_type_id))[0]['code'] 
                            === 'CASH'){
                        $sales_receipt[$i]['change_amount'] = $receipt_change_data;
                        $sales_receipt[$i]['outstanding_amount'] = $receipt_data[$i]['amount'] - $receipt_change_data;
                        $receipt_change_data = Tools::_float('0');
                    }


                }
                $customer_deposit_allocation = array();
                for($i = 0;$i<count($customer_deposit_data);$i++){
                    $customer_deposit_allocation[] = array(
                        'store_id'=>$store_id,
                        'customer_deposit_allocation_type'=>'sales_invoice',
                        'customer_deposit_id'=>$customer_deposit_data[$i]['customer_deposit_id'],
                        'allocated_amount'=>$customer_deposit_data[$i]['allocated_amount'],
                        'customer_deposit_allocation_status'=>SI::status_default_status_get('Customer_Deposit_Allocation_Engine')['val'],
                        'modid'=>$modid,
                        'moddate'=>$moddate,
                    );
                }                    

                $temp_fm_arr = array();


                foreach($final_movement_data as $fm_idx=>$fm){
                    $check_fm = $fm;
                    $temp_fm = $fm;
                    $temp_fm['final_movement_date'] = Tools::_date($temp_fm['final_movement_date'],'Y-m-d H:i:s');
                    $temp_fm['movement'] = array();
                    
                    foreach($fm['movement'] as $m_idx=>$m){ 
                        $check_m = $m;
                        $temp_m = $m;
                        $temp_m['product'] = array();
                        $warehouse_id = $m['warehouse_id'];

                        foreach($check_m['product'] as $p_idx=>$p){
                            $temp_product = null;
                            $qty = $p['qty'];
                            if(floatval($qty)>'0'){
                                $temp_product = $p;                                    
                            }
                            if($temp_product !== null){
                                $temp_m['product'][] = $temp_product;
                            }
                        }
                        if(count($temp_m['product'])>0){
                            $temp_fm['movement'][] = $temp_m;
                        }
                        
                    }    
                    if(count($temp_fm['movement'])>0){
                        $temp_fm_arr[] = $temp_fm;
                    }
                }
                $final_movement = $temp_fm_arr;

                $result['sales_invoice'] = $sales_invoice;
                $result['sales_invoice_product'] = $sales_invoice_product;
                $result['sales_invoice_additional_cost'] = $additional_cost;
                $result['sales_invoice_info'] = $sales_invoice_info;
                $result['sales_receipt'] = $sales_receipt;
                $result['customer_deposit_allocation'] = $customer_deposit_allocation;
                $result['final_movement'] = $final_movement;
                $result['change_amount'] = $receipt_change_data;

                break;
            case 'sales_pos_canceled':


                break;
        }

        return $result;
        //</editor-fold>
    }

    public function sales_pos_add($db, $final_data, $id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('product_stock_engine');
        get_instance()->load->helper('sales_pos/sales_pos_data_support');
        get_instance()->load->helper('sales_prospect/sales_prospect_engine');
        get_instance()->load->helper('customer_deposit_allocation/customer_deposit_allocation_engine');
        get_instance()->load->helper('sales_receipt/sales_receipt_engine');
        get_instance()->load->helper('sales_receipt_allocation/sales_receipt_allocation_engine');
        get_instance()->load->helper('delivery_order_final/delivery_order_final_engine');
        get_instance()->load->helper('delivery_order/delivery_order_engine');
        get_instance()->load->helper('intake_final/intake_final_engine');
        get_instance()->load->helper('intake/intake_engine');
        
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();
        
        $fsales_invoice = $final_data['sales_invoice'];
        $fsales_invoice_info = $final_data['sales_invoice_info'];
        $fsales_invoice_product = $final_data['sales_invoice_product'];
        $fsales_invoice_additional_cost = $final_data['sales_invoice_additional_cost'];
        $fcustomer_deposit_allocation = $final_data['customer_deposit_allocation'];
        $fsales_receipt = $final_data['sales_receipt'];
        $ffinal_movement = $final_data['final_movement'];
        $fchange_amount = $final_data['change_amount'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        $store_id = $fsales_invoice['store_id'];
        $customer_id = $fsales_invoice['customer_id'];
        $is_delivery = Tools::_bool($fsales_invoice_info['is_delivery']);
        $grand_total = $fsales_invoice['grand_total'];

        $sales_invoice_id = '';                            
        $fsales_invoice['code'] = SI::code_counter_store_get($db,$store_id, 'sales_invoice_pos');
        if(!$db->insert('sales_invoice',$fsales_invoice)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $sales_invoice_code = $fsales_invoice['code'];

        if($success == 1){

            $sales_invoice_id = $db->fast_get('sales_invoice'
                    ,array('code'=>$sales_invoice_code))[0]['id'];
            $result['trans_id']=$sales_invoice_id; 
        }

        if($success == 1){
            $fsales_invoice_info['sales_invoice_id'] = $sales_invoice_id;
            if(!$db->insert('sales_invoice_info',$fsales_invoice_info)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }

        if($success == 1){
            for($i = 0;$i<count($fsales_invoice_product) && $success == 1;$i++){
                $fsales_invoice_product[$i]['sales_invoice_id'] = $sales_invoice_id;
                if(!$db->insert('sales_invoice_product',$fsales_invoice_product[$i])){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                }
            }
        }

        if($success == 1){
            for($i = 0;$i<count($fsales_invoice_additional_cost);$i++){
                $fsales_invoice_additional_cost[$i]['sales_invoice_id'] = $sales_invoice_id;
                if(!$db->insert('sales_invoice_additional_cost',$fsales_invoice_additional_cost[$i])){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                }
            }
        }

        if($success == 1){
            $sales_invoice_status_log = array(
                'sales_invoice_id'=>$sales_invoice_id
                ,'sales_invoice_status'=>$fsales_invoice['sales_invoice_status']
                ,'modid'=>$modid
                ,'moddate'=>$moddate    
            );

            if(!$db->insert('sales_invoice_status_log',$sales_invoice_status_log)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }

        if($success === 1){
            get_instance()->load->helper('customer/customer_engine');
            $temp_result = Customer_Engine::customer_credit_add($db, $grand_total,
                $fsales_invoice['customer_id']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);

        }

        if($success === 1){
            if(!is_null($fsales_invoice_info['approval_id'])){
                get_instance()->load->helper('approval/approval_engine');

                $temp_result = Approval_Engine::approval_use($db,$fsales_invoice_info['approval_id']);
                $success = $temp_result['success'];
                $msg = array_merge($msg, $temp_result['msg']);
            }
        }

        if($success === 1){
            for($i = 0;$i<count($fcustomer_deposit_allocation);$i++){
                $fcustomer_deposit_allocation[$i]['sales_invoice_id'] = $sales_invoice_id;
                $temp_result = Customer_Deposit_Allocation_Engine::
                        customer_deposit_allocation_add(
                            $db,array('customer_deposit_allocation'=>$fcustomer_deposit_allocation[$i])
                        );
                    $success = $temp_result['success'];

                    if($success === 1){

                    }
                    else{
                        $msg = array_merge($msg, $temp_result['msg']);
                    }

            }
        }

        $sales_receipt_id = '';
        if($success === 1){
            //<editor-fold defaultstate="collapsed" desc="sales receipt">
            for($i = 0;$i<count($fsales_receipt);$i++){

                $sales_receipt_data = $fsales_receipt[$i];
                $temp_result = Sales_Receipt_Engine::sales_receipt_add($db,array('sales_receipt'=>$sales_receipt_data),'');
                $success = $temp_result['success'];

                if($success === 1){
                    $sales_receipt_id = $temp_result['trans_id'];
                }
                else{
                    $msg = array_merge($msg, $temp_result['msg']);
                }                                    

                if($success === 1){
                    $sales_receipt_allocation = array(
                        'store_id'=>$store_id,
                        'sales_receipt_allocation_type'=>'sales_invoice',
                        'allocated_amount'=>
                            Tools::_float($fsales_receipt[$i]['amount']) - Tools::_float($fsales_receipt[$i]['change_amount']),
                        'sales_receipt_allocation_status'
                            =>SI::status_default_status_get(
                                    'Sales_Receipt_Allocation_Engine')['val'],
                        'sales_receipt_id'=>$sales_receipt_id,
                        'sales_invoice_id'=>$sales_invoice_id,
                        'code'=>SI::code_counter_store_get($db,$store_id, 'sales_receipt_allocation'),
                        'modid'=>$modid,
                        'moddate'=>$moddate,
                    );
                    $payment_type_id = $fsales_receipt[$i]['payment_type_id'];

                    $temp_result = Sales_Receipt_Allocation_Engine::
                        sales_receipt_allocation_add(
                            $db,array('sales_receipt_allocation'=>$sales_receipt_allocation),''
                        );
                    $success = $temp_result['success'];

                    if($success === 1){

                    }
                    else{
                        $msg = array_merge($msg, $temp_result['msg']);
                    }

                }
                if($success !== 1) break;
            }
            //</editor-fold>
            
        }



        if($success == 1){
            //<editor-fold defaultstate="collapsed" desc="Movement">
            $movement_type = 'sales_invoice';

            foreach($ffinal_movement as $fm_idx=>$fm){
                $movement_date = $fm['final_movement_date'];                                    

                $movement_name = '';
                $movement_engine_name = '';
                if($is_delivery){
                    $movement_name = 'delivery_order';                                        
                    $movement_engine_name = 'Delivery_Order';
                }
                else{
                    $movement_name = 'intake';
                    $movement_engine_name = 'Intake';
                }

                $temp_mov_final = array(
                    $movement_name.'_final'=>array(
                        $movement_name.'_final_type'=>$movement_type,
                        $movement_name.'_final_date'=>$movement_date,
                        $movement_name.'_final_status'=>SI::
                            status_default_status_get($movement_engine_name.'_Final_Engine')['val'],
                        'store_id'=>$store_id,
                        'modid'=>User_Info::get()['user_id'],
                        'moddate'=>Date('Y-m-d H:i:s'),
                    ),
                    'sales_invoice_'.$movement_name.'_final'=>array('sales_invoice_id'=>$sales_invoice_id),
                    $movement_name=>array(),
                );
                if($is_delivery){
                    $temp_mov_final['dof_info'] = array(
                        'confirmation_required'=>0
                    );
                    if($fsales_invoice_info['expedition_id']!==null){
                        $temp_mov_final['dof_info']['confirmation_required'] = 1;
                    }
                    
                }
                foreach($fm['movement'] as $m_idx=>$m){
                    $temp_mov = array();

                    //--- new 
                    $expedition_id = $fsales_invoice_info['expedition_id'];
                    $movement_id = '';
                    $temp_mov = array();
                    $temp_mov['data'] = array(
                        $movement_name.'_status'=>SI::status_default_status_get($movement_engine_name.'_Engine')['val'],

                    );
                    $temp_mov['warehouse_from']=array('id'=>$m['warehouse_id']);

                    if($is_delivery){ 
                        //<editor-fold defaultstate="collapsed" desc="delivery order warehouse to">
                        $temp_mov['warehouse_to'] = array();
                           
                        if(strlen($expedition_id)>0) {
                            get_instance()->load->helper('expedition/expedition_data_support');
                            $t_expedition = Expedition_Data_Support::expedition_get($expedition_id);
                            $temp_mov['warehouse_to']['id'] = 
                                    Warehouse_Engine::expedition_get()[0]['id'];
                            $temp_mov['warehouse_to']['contact_name'] = 
                                    $t_expedition['name'];
                            $temp_mov['warehouse_to']['address'] = 
                                    $t_expedition['address'].' '.$t_expedition['city'];
                            $temp_mov['warehouse_to']['phone'] = 
                                    $t_expedition['phone'];
                        }
                        else{
                            get_instance()->load->helper('customer/customer_data_support');
                            $t_customer = Customer_Data_Support::customer_get($customer_id);
                            $temp_mov['warehouse_to']['id'] = 
                                Warehouse_Engine::customer_get()[0]['id'];
                            $temp_mov['warehouse_to']['contact_name'] = 
                                    $t_customer['name'];
                            $temp_mov['warehouse_to']['address'] = 
                                    $t_customer['address'].' '.$t_customer['city'];
                            $temp_mov['warehouse_to']['phone'] = 
                                    $t_customer['phone'];
                        }
                        //</editor-fold>
                    }

                    $temp_mov_product = array();
                    foreach($m['product'] as $p_idx=>$p){
                        $q = '
                            select id
                            from sales_invoice_product sip
                            where sales_invoice_id = '.$db->escape($sales_invoice_id).'
                                and product_id = '.$db->escape($p['product_id']).'
                        ';
                        $t_sip = $db->query_array($q);
                        if(count($t_sip) === 1){
                            $t_sip = $t_sip[0];
                        }
                        else{
                            $success = 0;
                            $msg[] = 'Unable to retreive Sales Invoice Product';
                        }
                        
                        if($success === 1){
                            $temp_mov_product[] = array(
                                'reference_type'=>'sales_invoice_product',
                                'reference_id'=>$t_sip['id'],
                                'product_id'=>$p['product_id'],
                                'unit_id'=>$p['unit_id'],
                                'qty'=>$p['qty'],
                            );
                        }
                        if($success !== 1) break;
                    }
                    
                    if($success === 1){
                        $temp_mov['product'] = $temp_mov_product;                                        

                        $temp_mov_final[$movement_name][] = $temp_mov;
                    }
                    
                    if($success !== 1) break;
                }
                
                if($success === 1){
                    $temp_result = eval('return '.$movement_engine_name.'_Final_Engine::'
                            .$movement_name.'_final_add($db,'
                            .'$temp_mov_final'
                        .');'
                    .'');

                    $success = $temp_result['success'];
                    $msg = $temp_result['msg'];
                }
                
                

                if($success !== 1) break;
            }
            //</editor-fold>
        }
        
        if($success === 1){
            if($fsales_invoice_info['reference_type'] ==='sales_prospect'){
                $temp_sales_prospect = array(
                    'sales_prospect_status'=>'done',
                );
                $sales_prospect_id = $fsales_invoice_info['reference_id'];
                $temp_result = Sales_Prospect_Engine::
                    sales_prospect_done($db, array('sales_prospect'=>$temp_sales_prospect),$sales_prospect_id);

                $success = $temp_result['success'];

                if($success === 1){

                }
                else{
                    $msg = array_merge($msg, $temp_result['msg']);
                }
            }
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        
        //</editor-fold>
    }
        
    public function sales_pos_canceled($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('product_stock_engine');
        get_instance()->load->helper('sales_pos/sales_pos_data_support');
        get_instance()->load->helper('sales_prospect/sales_prospect_engine');
        get_instance()->load->helper('customer_deposit_allocation/customer_deposit_allocation_engine');
        get_instance()->load->helper('sales_receipt/sales_receipt_engine');
        get_instance()->load->helper('sales_receipt_allocation/sales_receipt_allocation_engine');
        get_instance()->load->helper('delivery_order_final/delivery_order_final_engine');
        get_instance()->load->helper('delivery_order/delivery_order_engine');
        get_instance()->load->helper('intake_final/intake_final_engine');
        get_instance()->load->helper('intake/intake_engine');
        
        
        $result = array('success'=>1, 'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $fsales_pos = $final_data['sales_pos'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        $sales_pos_id = $id;
        
        
        $sales_invoice = Sales_Pos_Data_Support::sales_invoice_get($sales_pos_id);
        
        if(!$db->update('sales_invoice',$fsales_pos,array('id'=>$sales_pos_id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
         if($success == 1){
            $temp_result = SI::status_log_add($db,'sales_invoice',
                $sales_pos_id,$fsales_pos['sales_invoice_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        if($success === 1){
            get_instance()->load->helper('customer/customer_engine');
            $temp_result = Customer_Engine::customer_credit_add($db,-1*$sales_invoice['grand_total'],$sales_invoice['customer_id']);
            $success = $temp_result['success'];
            $msg = array_merge($temp_result['msg'],$msg);
        }
        
        $result['success'] = $success;
        $result['msg']= $msg;
        return $result;
        //</editor-fold>
    }

    static function sales_pos_mail($data){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('sales_pos/sales_pos_print');
        get_instance()->load->helper('sales_pos/sales_pos_data_support');
        get_instance()->load->library('email');
        $result = array('success'=>1,'msg'=>array());
        $success = 1;
        $msg = array();
        $sales_invoice_id = isset($data['sales_invoice_id'])?$data['sales_invoice_id']:'';
        $file_location = 'pdf_file/sales_invoice_'.Tools::_date('','Ymd').'.pdf';
        $sales_invoice = Sales_Pos_Data_Support::sales_invoice_get($sales_invoice_id);
        
        if(count($sales_invoice)>0){

            if($sales_invoice['sales_invoice_status']==='X'){
                $success = 0;
                $msg[] = 'Cannot mail Cancelled Sales Invoice';
            }
            if($success === 1){
                $print_param = array(
                    'file_location'=>$file_location,
                    'dest'=>'F',
                    'form_address'=>array('Customer')
                );
                $temp_result = Sales_Pos_Print::invoice_print($sales_invoice_id,$print_param);
                $success = $temp_result['success'];
                if($success !== 1){
                    $msg = $temp_result['msg'];
                }
            }
            
            if($success === 1){
                $mail_to = isset($data['mail_to'])?Tools::_str($data['mail_to']):'';
                $subject = isset($data['subject'])?Tools::_str($data['subject']):'';
                $message = isset($data['message'])?Tools::_str($data['message']):'';

                $email_engine = new Email_Engine();
                $email = $email_engine->email;

                try{

                    $email_engine->initialize(array('code'=>'mkt'));
                    $email_engine->to($mail_to);
                    $email_engine->subject($subject);
                    $email_engine->message_set($message);
                    $email_engine->attach($file_location);


                    if(!$email_engine->send()){
                        $success = 0;
                        $msg[] = $email_engine->error_msg_get();
                    }
                }
                catch(Exception $e){

                }

                unlink($file_location);

                if($success === 1){                    
                    $msg[] = 'Send Mail Success to '.$mail_to;
                    Message::set('success',$msg);
                }

            }

        }

        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
}
?>
