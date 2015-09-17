<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// It's delivery order final confirmation
class DOFC_Engine {

    public static $module_type_list = array(
        array('val'=>'sales_invoice','label'=>'Sales Invoice'),
    );

    public static $status_list = array(
        //<editor-fold defaultstate="collapsed">
        array(
            'val'=>'done'
            ,'label'=>'DONE'
            ,'method'=>'dofc_done'
            ,'default'=>true
            ,'next_allowed_status'=>array('X')
        ),
        array(
            'val'=>'X'
            ,'label'=>'CANCELED'
            ,'method'=>'dofc_canceled'
            ,'next_allowed_status'=>array()
        )
        //</editor-fold>
    );

    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'dofc/'
            ,'dofc_engine'=>'dofc/dofc_engine'
            ,'dofc_data_support' => 'dofc/dofc_data_support'
            ,'dofc_renderer' => 'dofc/dofc_renderer'
            ,'dofc_print' => 'dofc/dofc_print'
            ,'ajax_search'=>get_instance()->config->base_url().'dofc/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'sales_prospect/data_support/'                
        );

        return json_decode(json_encode($path));
    }

    public static function dofc_exists($id){
        $result = false;
        $db = new DB();
        $q = '
            select 1 
            from delivery_order_final_confirmation 
            where status > 0 && id = '.$db->escape($id).'
        ';
        $rs = $db->query_array_obj($q);
        if(count($rs)>0){
            $result = true;
        }
        return $result;
    }

    public static function submit($id,$method,$post){
        get_instance()->load->helper('delivery_order/delivery_order_engine');
        get_instance()->load->helper('product_stock_engine');
        $post = json_decode($post,TRUE);
        $data = $post;
        $ajax_post = false;                  
        $result = null;
        $cont = true;

        if(isset($post['ajax_post'])) $ajax_post = $post['ajax_post'];
        if($method == 'add') $data['dofc']['id'] = '';
        else $data['dofc']['id'] = $id;

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
    }

    public static function validate($method,$data=array()){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('dofc/dofc_data_support');
        $result = array(
            "success"=>1
            ,"msg"=>array()

        );
        $success = 1;
        $msg = array();
        $dofc = isset($data['dofc'])?$data['dofc']:null;
        $reference = isset($data['reference'])?Tools::_arr($data['reference']):array();
        $dofc_id = isset($dofc['id'])?
            Tools::_str($dofc['id']):'';

        $db = new DB();
        switch($method){
            case 'dofc_add':
                //<editor-fold defaultstate="collapsed">
                $ref_id = Tools::empty_to_null(isset($reference['id'])?Tools::_str($reference['id']):'');
                $dof = array();
                $dofc_date = Tools::_date(isset($dofc['dofc_date'])?
                    $dofc['dofc_date']:'','Y-m-d H:i:s');
                $dofc_type = isset($dofc['dofc_type'])?
                    Tools::_str($dofc['dofc_type']):'';

                if(is_null($ref_id)){
                    $success = 0;
                    $msg[] = 'Reference '.' '.Lang::get('empty',true,false);
                    break;
                }

                if(!SI::type_match('DOFC_Engine',$dofc_type)){
                    $success= 0;
                    $msg[]='Mismatch Module Type';
                    break;
                }


                $ref_exists = false;

                $q = '
                    select distinct dof.*
                    from delivery_order_final dof
                        inner join dof_info dofi
                            on dof.id = dofi.delivery_order_final_id
                    where dofi.confirmation_required = 1
                        and dof.delivery_order_final_status = "done"
                        and dof.status > 0
                        and dof.id = '.$db->escape($ref_id).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)> 0){
                    $dof = $rs[0];
                    $ref_exists = true;
                }

                if(!$ref_exists){
                    $success = 0;
                    $msg[] = 'Reference '.Lang::get('empty', true, false);
                }

                //check store is available
                $store_id = isset($dofc['store_id'])?$dofc['store_id']:'';
                $q = 'select 1 from store where status>0 and id ='.$db->escape($store_id);
                if(count($db->query_array_obj($q)) == 0){
                    $success = 0;
                    $msg[] = Lang::get("Store").' '.Lang::get("Empty",true,false);
                }                   

                //check dofc date
                if($success === 1){
                    if(strtotime($dofc_date) <strtotime(Tools::_date($dof['delivery_order_final_date'],'Y-m-d H:i:s'))){
                        $success = 0;
                        $msg[] = Lang::get(array("Delivery Order Final Confirmation","Date")).' '
                            .Lang::get('must be greater than').' '.Tools::_date($dof['delivery_order_final_date'],'F d, Y H:i:s');

                    }
                }

                $dofc_receipt_number = isset($dofc['receipt_number'])?
                    str_replace(' ','',Tools::_str($dofc['receipt_number'])):'';
                if(strlen($dofc_receipt_number) === 0){
                    $success = 0;
                    $msg[] = Lang::get("Receipt Number").' '.Lang::get('empty',true,false);

                }

                $dofc_receiver_name = isset($dofc['receiver_name'])?
                    str_replace(' ','',Tools::_str($dofc['receiver_name'])):'';
                if(strlen($dofc_receiver_name) === 0){
                    $success = 0;
                    $msg[] = "Receiver Name ".Lang::get('empty',true,false);

                }

                $dofc_expedition_name = isset($dofc['expedition_name'])?
                    str_replace(' ','',Tools::_str($dofc['expedition_name'])):'';
                if(strlen($dofc_expedition_name) === 0){
                    $success = 0;
                    $msg[] = "Expedition Name ".Lang::get('empty',true,false);

                }

                $dofc_driver_name = isset($dofc['driver_name'])?
                    str_replace(' ','',Tools::_str($dofc['driver_name'])):'';
                if(strlen($dofc_driver_name) === 0){
                    $success = 0;
                    $msg[] = "Driver Name ".Lang::get('empty',true,false);

                }

                $dofc_driver_assistant_name = isset($dofc['driver_assistant_name'])?
                    str_replace(' ','',Tools::_str($dofc['driver_assistant_name'])):'';
                if(strlen($dofc_driver_assistant_name) === 0){
                    $success = 0;
                    $msg[] = "Driver Assistant Name ".Lang::get('empty',true,false);

                }

                //</editor-fold>
                break;
            case 'dofc_done':
                $temp_result = Validator::validate_on_update(
                    array(
                        'module'=>'dofc',
                        'module_name'=>'Delivery Order Final Confirmation',
                        'module_engine'=>'DOFC_Engine',
                        'table'=>'delivery_order_final_confirmation',

                    ),
                    $dofc
                );
                $success = $temp_result['success'];
                $msg = array_merge($msg,$temp_result['msg']);

                if($success!==1) break;


                break;
            case 'dofc_canceled':
                $temp_result = Validator::validate_on_cancel(
                    array(
                        'module'=>'dofc',
                        'module_name'=>'Delivery Order Final Confirmation',
                        'module_engine'=>'DOFC_Engine',
                        'table'=>'delivery_order_final_confirmation',
                    ),
                    $dofc
                );
                $success = $temp_result['success'];
                $msg = array_merge($msg,$temp_result['msg']);

                if($success !== 1) break;

                $dofc = $db->fast_get('delivery_order_final_confirmation',array('id'=>$dofc['id']))[0];
                $dofc_type = $dofc['delivery_order_final_confirmation_type'];
                switch($dofc_type){
                    case 'sales_invoice':
                        get_instance()->load->helper('customer_deposit/customer_deposit_data_support');
                        get_instance()->load->helper('customer_bill/customer_bill_data_support');
                        $q = '
                            select t1.customer_deposit_id
                            from dofc_cd t1
                            where t1.delivery_order_final_confirmation_id = '.$db->escape($dofc_id).'
                        ';

                        $rs = $db->query_array($q);
                        if(count($rs)>0){
                            for($i = 0;$i<count($rs);$i++){
                                $t_customer_deposit = Customer_Deposit_Data_Support::customer_deposit_get($rs[$i]['customer_deposit_id']);
                                if(Tools::_float($t_customer_deposit['outstanding_amount']) !== Tools::_float($t_customer_deposit['amount']) ){
                                    $success = 0;
                                    $msg[] = 'Customer Deposit '.Lang::get('has been used',true,false);
                                    break;
                                }
                            }
                        }

                        $q = '
                            select t1.customer_bill_id
                            from dofc_cb t1
                            where t1.delivery_order_final_confirmation_id = '.$db->escape($dofc_id).'
                        ';
                        $rs = $db->query_array($q);
                        if(count($rs)>0){
                            for($i = 0;$i<count($rs);$i++){
                                $t_customer_bill = Customer_Bill_Data_Support::customer_bill_get($rs[$i]['customer_bill_id']);
                                if(Tools::_float($t_customer_bill['outstanding_amount'])!==Tools::_float($t_customer_bill['amount'])){
                                    $success = 0;
                                    $msg[] = 'Customer Bill '.Lang::get('has been paid',true,false);

                                    break;
                                }
                            }
                        }

                        break;
                }

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

        switch($action){
            case 'dofc_add':
                $dofc = $data['dofc'];
                $additional_cost = isset($data['additional_cost'])?
                    Tools::_arr($data['additional_cost']):array();
                $reference = $data['reference'];
                $result['dofc'] = array(
                    'store_id'=>Tools::_str($dofc['store_id']),
                    'code'=>'',
                    'delivery_order_final_confirmation_type'=>Tools::_str($dofc['dofc_type']),
                    'delivery_cost'=>Tools::_str($dofc['delivery_cost']),
                    'delivery_order_final_confirmation_date'=>Tools::_date($dofc['dofc_date'],'Y-m-d H:i:s'),
                    'additional_cost_total'=>'0',
                    'delivery_order_final_confirmation_status'=>SI::status_default_status_get('DOFC_Engine')['val'],
                    'status'=>'1'                        
                );
                $result['dofc_info'] = array(
                    'expedition_name'=>$dofc['expedition_name'],
                    'driver_name'=>$dofc['driver_name'],
                    'driver_assistant_name'=>$dofc['driver_assistant_name'],                        
                    'receiver_name'=>$dofc['receiver_name'],
                    'receipt_number'=>$dofc['receipt_number'],
                );
                $result['dof_dofc'] = array('delivery_order_final_id'=>$reference['id']);
                $result['dofc_additional_cost']=array();
                $add_cost_total = Tools::_float('0');
                for($i = 0;$i<count($additional_cost);$i++){
                    $add_cost_valid = true;
                    $add_description = isset($additional_cost[$i]['description'])?
                        Tools::_str($additional_cost[$i]['description']):'';
                    $add_amount = isset($additional_cost[$i]['amount'])?
                        Tools::_str($additional_cost[$i]['amount']):'0';
                    if(Tools::_float($add_amount)<=0) $add_cost_valid = false;
                    if($add_cost_valid){
                        $add_cost_total += Tools::_float($add_amount);
                        $result['dofc_additional_cost'][] = array(
                            'description'=>$add_description,
                            'amount'=>$add_amount,

                        );
                    }
                }
                $result['dofc']['additional_cost_total'] = $add_cost_total;

                break;

            case 'dofc_done':
                //<editor-fold defaultstate="collapsed">
                $dofc = $data['dofc']; 
                $dofc_status = '';

                switch($action){
                    case 'dofc_done':
                        $dofc_status = 'done';
                        break;
                }

                $result['dofc'] = array(
                    'notes'=>isset($dofc['notes'])?$dofc['notes']:''
                    ,'delivery_order_final_confirmation_status'=>$dofc_status
                );
                //</editor-fold>
                break;
            case 'dofc_canceled':
                //<editor-fold defaultstate="collapsed">
                $dofc = $data['dofc'];

                $result['dofc'] = array(
                    'cancellation_reason'=>isset($dofc['cancellation_reason'])?$dofc['cancellation_reason']:''
                    ,'delivery_order_final_confirmation_status'=>'X'
                );
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
        $dofc_data = $data['dofc'];
        $id = $dofc_data['id'];

        $method_list = array('dofc_add');
        foreach(SI::status_list_get('DOFC_Engine') as $status){
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
                case 'dofc_add':
                    try{ 
                        $db->trans_begin();
                        $temp_result = self::dofc_add($db, $final_data);
                        $success = $temp_result['success'];
                        $msg = array_merge($msg, $temp_result['msg']);
                        if($success === 1){
                            $result['trans_id']=$temp_result['trans_id']; // useful for view forwarder
                        }

                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = Lang::get(array('Add','Delivery Order Final Confirmation','Success'),true,true,false,false,true);
                        }
                    }
                    catch(Exception $e){

                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    }
                    break;
                case 'dofc_done':                        
                    try{
                        $db->trans_begin();
                        $temp_result = self::dofc_done($db,$final_data,$id);
                        $success = $temp_result['success'];
                        $msg = array_merge($msg,$temp_result['msg']);

                        if($success == 1){
                            $result['trans_id'] = $temp_result['trans_id'];
                        }

                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = Lang::get(array('Update','Delivery Order Final Confirmation','Success'),true,true,false,false,true);
                        }
                    }
                    catch(Exception $e){
                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    }                        

                    break;
                case 'dofc_canceled':
                    try{
                        $db->trans_begin();
                        $temp_result = self::dofc_canceled($db,$final_data,$id);
                        $success = $temp_result['success'];
                        $msg = array_merge($temp_result['msg'],$msg);
                        if($success === 1){
                            $result['trans_id'] = $temp_result['trans_id'];
                        }
                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = Lang::get(array('Cancel','Delivery Order Final Confirmation','Success'),true,true,false,false,true);
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

    function dofc_add($db, $final_data){
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $fdofc = $final_data['dofc'];
        $fdof_dofc = $final_data['dof_dofc'];
        $fdofc_info = $final_data['dofc_info'];
        $fdofc_additional_cost = $final_data['dofc_additional_cost'];

        $store_id = $fdofc['store_id'];
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        $dofc_type = $fdofc['delivery_order_final_confirmation_type'];
        $dofc_date = $fdofc['delivery_order_final_confirmation_date'];
        $dof_id = $fdof_dofc['delivery_order_final_id'];
        $q = '
            select t1.*
            from delivery_order_final t1
            where t1.id = '.$db->escape($dof_id).'
        ';
        $dof = $db->query_array($q)[0];

        $fdofc['code'] = SI::code_counter_store_get($db,$store_id, 'delivery_order_final_confirmation');
        if(!$db->insert('delivery_order_final_confirmation',$fdofc)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $dofc_code = $fdofc['code'];

        if($success == 1){                                
            $dofc_id = $db->fast_get('delivery_order_final_confirmation'
                    ,array('code'=>$dofc_code))[0]['id'];
            $result['trans_id']=$dofc_id; 
        }

        if($success === 1){
            $fdofc_info['delivery_order_final_confirmation_id'] = $dofc_id;
            if(!$db->insert('delivery_order_final_confirmation_info',$fdofc_info)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }


        if($success === 1){
            $fdof_dofc['delivery_order_final_confirmation_id'] = $dofc_id;
            if(!$db->insert('dof_dofc',$fdof_dofc)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }

        if($success === 1){
            for($i = 0;$i<count($fdofc_additional_cost);$i++){
                $fdofc_additional_cost[$i]['delivery_order_final_confirmation_id'] = $dofc_id;
                if(!$db->insert('delivery_order_final_confirmation_additional_cost',$fdofc_additional_cost[$i])){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                    break;
                }
            }
        }


        if($success == 1){
            $temp_res = SI::status_log_add($db,
                'delivery_order_final_confirmation',
                $dofc_id,
                $fdofc['delivery_order_final_confirmation_status']
            );

            $success = $temp_res['success'];

            if($success !== 1){
                $msg = array_merge($msg, $temp_res['msg']);
            }                
        }

        if($success == 1){

            $q = '
                update delivery_order_final
                set delivery_order_final_status = "confirmed"
                where id = '.$db->escape($dof_id).'
            ';

            if(!$db->query($q)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;

            }
        }

        if($success === 1){
            $temp_res = SI::status_log_add($db,
                'delivery_order_final',
                $dof_id,
                "confirmed"
            );

            $success = $temp_res['success'];
            if($success !== 1){
                $msg = array_merge($msg, $temp_res['msg']);
            }
        }

        if($success === 1){
            switch($dofc_type){
                case 'sales_invoice':
                    get_instance()->load->helper('sales_pos/sales_pos_data_support');

                    $q = '
                        select t2.id, t2.delivery_cost_estimation, t2.customer_id
                        from sales_invoice_delivery_order_final t1
                            inner join sales_invoice t2 on t1.sales_invoice_id = t2.id
                        where t1.delivery_order_final_id = '.$db->escape($dof_id).'

                    ';
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        $customer_id = $rs[0]['customer_id'];
                        $sales_invoice_id = $rs[0]['id'];
                        $delivery_cost_estimation = $rs[0]['delivery_cost_estimation'];   
                        if(count(Sales_Pos_Data_Support::product_movement_outstanding_get($sales_invoice_id))===0){
                            $all_confirmed = true;
                            $q = '
                                select 1
                                from sales_invoice_delivery_order_final t1
                                    inner join delivery_order_final t2 on t1.delivery_order_final_id = t2.id
                                where t1.sales_invoice_id = '.$sales_invoice_id.'
                                    and t2.delivery_order_final_status != "confirmed"
                                    and t2.delivery_order_final_status !="X"
                            ';
                            if(count($db->query_array($q))>0) $all_confirmed = false;
                            if($all_confirmed){
                                //<editor-fold defaultstate="collapsed" desc="cust deposit / bill">
                                $q = '
                                    select coalesce(sum(t3.delivery_cost),0) delivery_cost_total
                                    from sales_invoice_delivery_order_final t1
                                        inner join dof_dofc t2 on t1.delivery_order_final_id = t2.delivery_order_final_id
                                        inner join delivery_order_final_confirmation t3 
                                            on t2.delivery_order_final_confirmation_id = t3.id
                                    where     t3.delivery_order_final_confirmation_status !="X"
                                        and t1.sales_invoice_id = '.$db->escape($sales_invoice_id).'
                                ';
                                $rs = $db->query_array($q);
                                if(count($rs)>0){
                                    $delivery_cost_total = $rs[0]['delivery_cost_total'];
                                    $diff = Tools::_float($delivery_cost_total) - Tools::_float($delivery_cost_estimation);

                                    if($diff<0){
                                        get_instance()->load->helper('customer_deposit/customer_deposit_engine');

                                        $cust_dept_param = array(
                                            'customer_deposit_type'=>'delivery_order_final_confirmation',
                                            'store_id'=>$store_id,
                                            'customer_deposit_date'=>$moddate,
                                            'customer_id'=>$customer_id,
                                            'amount'=>abs($diff),
                                            'outstanding_amount'=>abs($diff),
                                            'customer_deposit_status'=>SI::status_default_status_get('Customer_Deposit_Engine')['val'],
                                            'status'=>'1',
                                            'modid'=>$modid,
                                            'moddate'=>$moddate,
                                        );

                                        $dofc_cd_param = array(
                                            'delivery_order_final_confirmation_id'=>$dofc_id,

                                        );

                                        $temp_result = Customer_Deposit_Engine::customer_deposit_add($db,
                                                array('customer_deposit'=>$cust_dept_param,
                                                    'dofc_cd'=>$dofc_cd_param
                                                )
                                        );

                                        $success =$temp_result['success'];
                                        $msg = array_merge($temp_result['msg'],$msg);
                                    }
                                    else if($diff>0){
                                        $diff = abs($diff);
                                        get_instance()->load->helper('customer_bill/customer_bill_engine');
                                        $cust_bill_param = array(
                                            'customer_bill_type'=>'delivery_order_final_confirmation',
                                            'store_id'=>$store_id,
                                            'customer_bill_date'=>$moddate,
                                            'customer_id'=>$customer_id,
                                            'amount'=>abs($diff),
                                            'outstanding_amount'=>$diff,
                                            'customer_bill_status'=>SI::status_default_status_get('Customer_Bill_Engine')['val'],
                                            'status'=>'1',
                                            'modid'=>$modid,
                                            'moddate'=>$moddate,
                                        );
                                        $dofc_cb = array(
                                            'delivery_order_final_confirmation_id'=>$dofc_id,
                                        );
                                        $temp_result = Customer_Bill_Engine::customer_bill_add($db,
                                                array('customer_bill'=>$cust_bill_param,
                                                    'dofc_cb'=>$dofc_cb,                                            
                                                )
                                        );
                                        $success =$temp_result['success'];
                                        $msg = array_merge($temp_result['msg'],$msg);

                                    }

                                }
                                //</editor-fold>
                            }
                        }
                    }

                    break;
            }
        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
    }

    function dofc_done($db, $final_data,$id){
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $fdofc = $final_data['dofc'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('delivery_order_final_confirmation',$fdofc,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'delivery_order_final_confirmation',
                $id,$fdofc['delivery_order_final_confirmation_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
    }

    function dofc_canceled($db, $final_data,$id){
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $fdofc = $final_data['dofc'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        $dofc_id = $id;

        $dof_id = $db->fast_get('dof_dofc',array('delivery_order_final_confirmation_id'=>$dofc_id))[0]['delivery_order_final_id'];
        $dof = $db->query_array('select * from delivery_order_final where id = '.$db->escape($dof_id))[0];
        $dofc = $db->query_array('select * from delivery_order_final_confirmation where id = '.$db->escape($dofc_id))[0];
        $dofc_type = $dofc['delivery_order_final_confirmation_type'];

        if(!$db->update('delivery_order_final_confirmation',$fdofc,array("id"=>$dofc_id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'delivery_order_final_confirmation',
                $dofc_id,$fdofc['delivery_order_final_confirmation_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        if($success === 1){
            if(!$db->update('delivery_order_final',array('delivery_order_final_status'=>'done'),array('id'=>$dof_id))){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }                
        }

        if($success === 1){
            $temp_res = SI::status_log_add($db,
                'delivery_order_final',
                $dof_id,
                "done"
            );

            $success = $temp_res['success'];
            if($success !== 1){
                $msg = array_merge($msg, $temp_res['msg']);
            }
        }

        if($success === 1){
            switch($dofc_type){
                case 'sales_invoice':

                    $q = '
                        select distinct t2.id
                        from dofc_cd t1
                            inner join customer_deposit t2 on t1.customer_deposit_id = t2.id
                        where t1.delivery_order_final_confirmation_id = '.$db->escape($dofc_id).'
                            and t2.customer_deposit_status !="X"
                    ';
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        get_instance()->load->helper('customer_deposit/customer_deposit_engine');
                        for($i = 0;$i<count($rs);$i++){
                            $cust_dep_id = $rs[$i]['id'];
                            $cust_dep_param = array(
                                'customer_deposit'=>array(
                                    'id'=>$cust_dep_id,
                                    'modid'=>$modid,
                                    'moddate'=>$moddate,
                                    'customer_deposit_status'=>'X',
                                    'cancellation_reason'=>$fdofc['cancellation_reason']
                                ),
                            );
                            $temp_result = Customer_Deposit_Engine::customer_deposit_canceled($db, $cust_dep_param, $cust_dep_id);
                            $success = $temp_result['success'];
                            $msg = array_merge($msg, $temp_result['msg']);

                            if($success !== 1) break;
                        }
                    }

                    $q = '
                        select distinct t2.id
                        from dofc_cb t1
                            inner join customer_bill t2 on t1.customer_bill_id = t2.id
                        where t1.delivery_order_final_confirmation_id = '.$db->escape($dofc_id).'
                            and t2.customer_bill_status !="X"
                    ';
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        get_instance()->load->helper('customer_bill/customer_bill_engine');
                        for($i = 0;$i<count($rs);$i++){
                            $cust_bill_id = $rs[$i]['id'];
                            $cust_bill_param = array(
                                'customer_bill'=>array(
                                    'id'=>$cust_bill_id,
                                    'modid'=>$modid,
                                    'moddate'=>$moddate,
                                    'customer_bill_status'=>'X',
                                    'cancellation_reason'=>$fdofc['cancellation_reason']
                                ),
                            );
                            $temp_result = Customer_Bill_Engine::customer_bill_canceled($db, $cust_bill_param,$cust_bill_id);
                            $success = $temp_result['success'];
                            $msg = array_merge($msg, $temp_result['msg']);
                            if($success !== 1) break;
                        }
                    }
                    break;
            }
        }


        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
    }
    
    static function dofc_mail($data){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('dofc/dofc_print');
        get_instance()->load->helper('dofc/dofc_data_support');
        get_instance()->load->library('email');
        $result = array('success'=>1,'msg'=>array());
        $success = 1;
        $msg = array();
        $dofc_id = isset($data['dofc_id'])?$data['dofc_id']:'';
        $file_location = 'pdf_file/dofc_'.Tools::_date('','Ymd').'.pdf';
        $dofc = DOFC_Data_Support::dofc_get($dofc_id);
        if(count($dofc)>0){
            
            if($dofc['delivery_order_final_confirmation_status']==='X'){
                $success = 0;
                $msg[] = 'Cannot mail Cancelled '.Lang::get('Delivery Order Final Confirmation');
            }
            if($success === 1){
                $temp_result = DOFC_Print::dofc_print($dofc_id,$file_location,'F');
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

                    $email_engine->initialize(array('code'=>'system'));
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
