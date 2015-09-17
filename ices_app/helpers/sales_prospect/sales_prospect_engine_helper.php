<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sales_Prospect_Engine {

    public static $status_list; 

    public static function helper_init(){
        //<editor-fold defaultstate="collapsed">
        self::$status_list = array(
            //<editor-fold defaultstate="collapsed">
             array(
                'val'=>''
                ,'label'=>''
                , 'method'=>'sales_prospect_add'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Add')
                        ,array('val'=>Lang::get(array('Sales Prospect'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            array(
                'val'=>'registered'
                ,'label'=>'REGISTERED'
                ,'method'=>''
                ,'default'=>true
                ,'next_allowed_status'=>array('canceled')
                ,'msg'=>array(
                    'success'=>array()
                )
            )
            ,array(
                'val'=>'done'
                ,'label'=>'DONE'
                ,'method'=>''
                ,'next_allowed_status'=>array('canceled')
                ,'msg'=>array(
                    'success'=>array()
                )

            )
            ,array(
                'val'=>'X'
                ,'label'=>'CANCELED'
                ,'method'=>'sales_prospect_canceled'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Cancel')
                        ,array('val'=>Lang::get(array('Sales Prospect'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            )
            //</editor-fold>
        );
        //</editor-fold>
    }

    public static function sales_prospect_exists($id){
        $result = false;
        $db = new DB();
        $q = '
                select 1 
                from sales_prospect 
                where status > 0 && id = '.$db->escape($id).'
            ';
        $rs = $db->query_array_obj($q);
        if(count($rs)>0){
            $result = true;
        }
        return $result;
    }

    public static function sales_prospect_active($id){
        $result = false;
        $db = new DB();
        $q = '
                select 1 
                from sales_prospect 
                where status > 0 && id = '.$db->escape($id).' and sales_prospect_status !="X"
            ';
        $rs = $db->query_array_obj($q);
        if(count($rs)>0){
            $result = true;
        }
        return $result;
    }

    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'sales_prospect/'
            ,'sales_prospect_engine'=>'sales_prospect/sales_prospect_engine'
            ,'sales_prospect_data_support' => 'sales_prospect/sales_prospect_data_support'
            ,'sales_prospect_print'=>'sales_prospect/sales_prospect_print'
            ,'sales_prospect_renderer' => 'sales_prospect/sales_prospect_renderer'                
            ,'ajax_search'=>get_instance()->config->base_url().'sales_prospect/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'sales_prospect/data_support/'

        );

        return json_decode(json_encode($path));
    }

    public static function validate($method,$data=array()){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('sales_pos/sales_pos_data_support');
        $result = array(
            "success"=>1
            ,"msg"=>array()

        );

        $sales_prospect = isset($data['sales_prospect'])?$data['sales_prospect']:null;
        $product = isset($data['product'])? 
            (is_array($data['product'])?$data['product']:array()): array();
        $additional_cost = isset($data['additional_cost'])?
            (is_array($data['additional_cost'])?$data['additional_cost']:array()):array();

        switch($method){
            case 'sales_prospect_add':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $sales_inquiry_by_id = isset($sales_prospect['sales_inquiry_by_id'])?$sales_prospect['sales_inquiry_by_id']:'';
                $customer_id = isset($sales_prospect['customer_id'])?$sales_prospect['customer_id']:'';
                $price_list_id = isset($sales_prospect['price_list_id'])?$sales_prospect['price_list_id']:'';
                $is_delivery = isset($sales_prospect['is_delivery'])?($sales_prospect['is_delivery']?true:false):false;

                //<editor-fold defaultstate="collapsed" desc="Major Validation">
                if(!SI::record_exists('sales_inquiry_by',array('id'=>$sales_inquiry_by_id,'status'=>'1','sales_inquiry_by_status'=>'A'))){
                    $result['success'] = 0;
                    $result['msg'][] = "Sales Inquiry By is Empty";   
                }

                if(!SI::record_exists('customer',array('id'=>$customer_id,'status'=>'1','customer_status'=>'A'))){
                    $result['success'] = 0;
                    $result['msg'][] = "Customer is Empty";   
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

                if(!is_array($product)){
                    $result['success'] = 0;
                    $result['msg'][] = "Product is Empty";                        
                }
                else if (count($product)===0){
                    $result['success'] = 0;
                    $result['msg'][] = "Product is Empty";
                }

                if($result['success'] !== 1) break;
                //</editor-fold>

                $q = '
                    select * from customer where status>0 and id='.$db->escape($customer_id).'
                ';
                $rs = $db->query_array($q);
                $is_sales_receipt_outstanding = $rs[0]['is_sales_receipt_outstanding'];
                $is_credit = $rs[0]['is_credit'];

                $total = 0;
                //<editor-fold defaultstate="collapsed" desc="Product Validation">
                for($i = 0;$i<count($product);$i++){
                    $product_id = isset($product[$i]['product_id'])?$product[$i]['product_id']:'';
                    $unit_id = isset($product[$i]['unit_id'])?$product[$i]['unit_id']:'';
                    $qty = isset($product[$i]['qty'])?$product[$i]['qty']:'';
                    $amount = isset($product[$i]['amount'])?$product[$i]['amount']:'';
                    $mult_qty = Sales_Pos_Data_Support::multiplication_qty_get($product_id, $unit_id);
                    $expected_amount = Sales_Pos_Data_Support::product_price_get($price_list_id, $product_id, $unit_id, $qty);
                    $subtotal = (floatval($qty) * floatval($expected_amount));
                    $total += $subtotal;

                    if($qty % $mult_qty !== 0){
                        $result['success'] = 0;
                        $result['msg'][] = 'Mismatch Product Multiplication Qty';                                
                    }

                    if(floatval($expected_amount) !== floatval($amount)){
                        $result['success'] = 0;
                        $result['msg'][] = 'Mismatch Product Amount';                                
                    }

                    if(floatval($qty)<=0){
                        $result['success'] = 0;
                        $result['msg'][] = 'Qty must be higher than 0';                                
                    }

                    if($result['success'] !== 1){
                        break;
                    }
                }

                $discount = isset($sales_prospect['discount'])?floatval($sales_prospect['discount']):0;
                if($total < $discount){
                    $result['success'] = 0;
                    $result['msg'][] = 'Discount is higher than Total';
                }


                if($result['success'] !== 1){
                    break;
                }

                //</editor-fold>


                for($i = 0;$i<count($additional_cost);$i++){
                    $amount = isset($additional_cost[$i]['amount'])?$additional_cost[$i]['amount']:'0.00';
                    $description = $additional_cost[$i]['description']?$additional_cost[$i]['description']:'';

                    if(floatval($amount)>0 && strlen(str_replace(' ','',$description)) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = 'Empty Additional Cost Description';
                    }
                }


                //</editor-fold>
                break;
            case 'sales_prospect_canceled':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $cancellation_reason = isset($sales_prospect['cancellation_reason'])?
                        $sales_prospect['cancellation_reason']:'';

                if(SI::record_exists('sales_prospect'
                        ,array('id'=>$sales_prospect['id'],'sales_prospect_status'=>'X'))){
                    $result['success'] = 0;
                    $result['msg'][] = 'Data already been canceled';                        
                    break;
                }

                if(preg_replace('/[ ]/','', $cancellation_reason) === ''){
                   $result['success'] = 0;
                   $result['msg'][] = 'Empty Cancellation Reason';
                }

                $sales_prospect_id = isset($sales_prospect['id'])?
                    Tools::_str($sales_prospect['id']):'';

                $db = new DB();

                $q = '
                    select 1 
                    from sales_prospect 
                    where sales_prospect_status = "done"
                    and id = '.$db->escape($sales_prospect_id).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $result['success'] = 0;
                    $result['msg'][] = 'Sales Prospect Status done';
                }

                //</editor-fold>
                break;
            default:
                $result['success'] = 0;
                $result['msg'][] = 'Method Invalid';
                break;

        }

        return $result;
        //</editor-fold>
    }

    public static function adjust($action,$data=array()){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = array();
        $sales_prospect = isset($data['sales_prospect'])?$data['sales_prospect']:null;
        $product = isset($data['product'])?$data['product']:array();
        $additional_cost = isset($data['additional_cost'])?
                (is_array($data['additional_cost'])?$data['additional_cost']:array()):array();
        switch($action){
            case 'sales_prospect_add':
                $result['sales_prospect']=array();
                $result['sales_prospect_product'] = array();
                $result['sales_prospect_additional_cost'] = array();
                $result['sales_prospect_info'] = array();                   

                $delivery_cost_estimation = isset($sales_prospect['delivery_cost_estimation'])?
                        $sales_prospect['delivery_cost_estimation']:'0.00';
                $discount = isset($sales_prospect['discount'])?
                        $sales_prospect['discount']:'0.00';
                $expedition_id = isset($sales_prospect['expedition_id'])?
                        $sales_prospect['expedition_id']:'';
                $price_list_id = $sales_prospect['price_list_id'];
                $is_delivery = $sales_prospect['is_delivery'];

                $result['sales_prospect_info']['sales_inquiry_by_id']=$sales_prospect['sales_inquiry_by_id'];;
                $result['sales_prospect_info']['expedition_id']=$expedition_id;
                $result['sales_prospect_info']['is_delivery']=$is_delivery?'1':'0';
                $result['sales_prospect_info']['product_price_list_id'] = $price_list_id;


                $extra_charge_param  = array(
                    'price_list_id'=>$price_list_id
                    ,'products'=>array()
                    ,'delivery'=>$is_delivery
                );
                $product_total = floatval('0');

                for($i = 0;$i<count($product);$i++){
                    $product_id = isset($product[$i]['product_id'])?$product[$i]['product_id']:'';
                    $unit_id = isset($product[$i]['unit_id'])?$product[$i]['unit_id']:'';
                    $qty = isset($product[$i]['qty'])?$product[$i]['qty']:'';
                    $amount = Sales_Pos_Data_Support::product_price_get($price_list_id, $product_id, $unit_id, $qty);;
                    $subtotal = floatval($amount) * floatval($qty);
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

                    $result['sales_prospect_product'][] = array(
                        'product_id'=>$product_id,
                        'unit_id'=>$unit_id,
                        'qty'=>$qty,
                        'amount'=>$amount,
                        'subtotal'=>$subtotal,
                        'expedition_weight_qty'=>$expedition_weight,
                        'expedition_weight_unit_id'=>$expedition_weight_unit_id
                    );

                    $product_total +=$subtotal;
                }

                $extra_charge_msg = Sales_Pos_Data_Support::extra_charge_message_get($extra_charge_param);
                $extra_charge = preg_replace('/[^0-9.]/','', $extra_charge_msg['amount']);

                $result['sales_prospect_info']['extra_charge_amount'] = $extra_charge;
                $result['sales_prospect_info']['extra_charge_msg'] = $extra_charge_msg['msg'];

                $additional_cost_total = floatval('0');
                for($i = 0;$i<count($additional_cost);$i++){
                    $amount = $additional_cost[$i]['amount'];
                    $description = $additional_cost[$i]['description'];
                    if(floatval($amount) > 0){
                        $result['sales_prospect_additional_cost'][]=array(
                            'description'=>$description,
                            'amount'=>$amount
                        );
                        $additional_cost_total+=floatval($amount);
                    }
                }


                $grand_total = $product_total 
                        - floatval($discount) 
                        + floatval($delivery_cost_estimation)
                        + floatval($additional_cost_total)
                        + floatval($extra_charge)
                    ;

                $result['sales_prospect']['customer_id'] = $sales_prospect['customer_id'] ;
                $result['sales_prospect']['sales_prospect_date'] = Date('Y-m-d H:i:s') ;
                $result['sales_prospect']['sales_prospect_status'] = SI::status_default_status_get('Sales_Prospect_Engine')['val'];
                $result['sales_prospect']['delivery_cost_estimation'] = $delivery_cost_estimation;
                $result['sales_prospect']['discount'] = $discount;
                $result['sales_prospect']['total_additional_cost'] = $additional_cost_total;
                $result['sales_prospect']['extra_charge'] = $extra_charge;
                $result['sales_prospect']['total_product'] = $product_total;
                $result['sales_prospect']['grand_total'] = $grand_total;


                break;


            case 'sales_prospect_canceled':
                $result['sales_prospect']['cancellation_reason'] = $sales_prospect['cancellation_reason'];
                $result['sales_prospect']['sales_prospect_status'] = 'X';

                break;
        }

        return $result;
        //</editor-fold>
    }

    function sales_prospect_add($db, $final_data, $id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        
        $fsales_prospect = array_merge($final_data['sales_prospect'],array("modid"=>$modid,"moddate"=>$moddate));
        $fsales_prospect_product = $final_data['sales_prospect_product'];
        $fsales_prospect_info = $final_data['sales_prospect_info'];
        $fsales_prospect_additional_cost = $final_data['sales_prospect_additional_cost'];


        $sales_prospect_id = '';
        $rs = $db->query_array_obj('select func_code_counter("sales_prospect") code');
        $fsales_prospect['code'] = $rs[0]->code;
        if(!$db->insert('sales_prospect',$fsales_prospect)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $q = '
                select id 
                from sales_prospect
                where status>0 
                    and sales_prospect_status = '.$db->escape(SI::status_default_status_get('Sales_Prospect_Engine')['val']).' 
                    and code = '.$db->escape($fsales_prospect['code']).'
            ';
            $rs_sales_prospect = $db->query_array_obj($q);
            $sales_prospect_id = $rs_sales_prospect[0]->id;
            $result['trans_id']=$sales_prospect_id; // useful for view forwarder
        }


        if($success == 1){
            $sales_prospect_status_log = array(
                'sales_prospect_id'=>$sales_prospect_id
                ,'sales_prospect_status'=>SI::status_default_status_get('Sales_Prospect_Engine')['val']
                ,'modid'=>$modid
                ,'moddate'=>$moddate    
            );

            if(!$db->insert('sales_prospect_status_log',$sales_prospect_status_log)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }

        if($success == 1){

            for($i = 0;$i<count($fsales_prospect_product);$i++){
                $fsales_prospect_product[$i]['sales_prospect_id'] = $sales_prospect_id;
                if(!$db->insert('sales_prospect_product',$fsales_prospect_product[$i])){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                    break;
                }
            }

        }

        if($success == 1){

            for($i = 0;$i<count($fsales_prospect_additional_cost);$i++){
                $fsales_prospect_additional_cost[$i]['sales_prospect_id'] = $sales_prospect_id;
                if(!$db->insert('sales_prospect_additional_cost',$fsales_prospect_additional_cost[$i])){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                    break;
                }
            }

        }

        if($success == 1){
            $fsales_prospect_info['sales_prospect_id'] = $sales_prospect_id;
            if(!$db->insert('sales_prospect_info',$fsales_prospect_info)){
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

    static function sales_prospect_registered($db, $final_data, $id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $fsales_prospect = $final_data['sales_prospect'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $fsales_prospect['modid']=$modid;
        $fsales_prospect['moddate']=$moddate;

        if(!$db->update('sales_prospect',$fsales_prospect,array('id'=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $sales_prospect_id = $id;
        if($success == 1){
            $result['trans_id']=$sales_prospect_id; 
        }

        if($success == 1){
            $sales_prospect_status_log = array(
                'sales_prospect_id'=>$sales_prospect_id
                ,'sales_prospect_status'=>$fsales_prospect['sales_prospect_status']
                ,'modid'=>$modid
                ,'moddate'=>$moddate    
            );

            if(!$db->insert('sales_prospect_status_log',$sales_prospect_status_log)){
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
    
    static function sales_prospect_done($db, $final_data, $id){
        //<editor-fold defaultstate="collapsed">        
        return self::sales_prospect_registered($db, $final_data, $id);
        //</editor-fold>
    }

    function sales_prospect_canceled($db, $final_data, $id){
        //<editor-fold defaultstate="collapsed">        
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        
        $fsales_prospect = array_merge($final_data['sales_prospect'],array("modid"=>$modid,"moddate"=>$moddate));
        
        if(!$db->update('sales_prospect',$fsales_prospect,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        $result['trans_id']=$id;
        if($success == 1){
            $sales_prospect_status_log = array(
                'sales_prospect_id'=>$id
                ,'sales_prospect_status'=>$fsales_prospect['sales_prospect_status']
                ,'modid'=>$modid
                ,'moddate'=>$moddate    
            );

            if(!$db->insert('sales_prospect_status_log',$sales_prospect_status_log)){
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
    
    static function sales_prospect_mail($data){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('sales_prospect/sales_prospect_print');
        get_instance()->load->library('email');
        $result = array('success'=>1,'msg'=>array());
        $success = 1;
        $msg = array();
        $sales_prospect_id = isset($data['sales_prospect_id'])?$data['sales_prospect_id']:'';
        $file_location = 'pdf_file/sales_prospect_'.Tools::_date('','Ymd').'.pdf';
        if(!self::sales_prospect_active($sales_prospect_id)){
            $success = 0;
            $msg[] = 'Cannot mail Cancelled Sales Prospect';
        }
        if($success === 1){
            $temp_result = Sales_Prospect_Print::prospect_print($sales_prospect_id,$file_location,'F');
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



        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }

}
?>
