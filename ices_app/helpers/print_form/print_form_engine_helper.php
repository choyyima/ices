<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Print_Form_Engine {
    public static $prefix_id = 'print_form';
    public static $prefix_method;
    public static $form_type;
    
    public function helper_init(){
        //<editor-fold desc="this function is called automatically in MY_Loader class" defaultstate="collapsed">
        
        self::$prefix_method = self::$prefix_id;
        self::$form_type = array(
            array('val'=>'stock_opname','label'=>'Stock Opname')
        );
        //</editor-fold>
    }
    
    public static function print_form_exists($id){
        //<editor-fold defaultstate="collapsed">
        $result = false;
        $db = new DB();
        $q = '
                select 1 
                from print_form 
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
            'index'=>get_instance()->config->base_url().'print_form/',
            'print_form_engine'=>'print_form/print_form_engine',
            'print_form_data_support' => 'print_form/print_form_data_support',
            'print_form_renderer' => 'print_form/print_form_renderer',
            'print_form_print' => 'print_form/print_form_print',
            'ajax_search'=>get_instance()->config->base_url().'print_form/ajax_search/',
            'data_support'=>get_instance()->config->base_url().'print_form/data_support/',
        );

        return json_decode(json_encode($path));
    }

    public static function validate($method,$data=array()){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('print_form/print_form_data_support');
        get_instance()->load->helper('refill_subcon/refill_subcon_data_support');
        get_instance()->load->helper('product/product_data_support');
        get_instance()->load->helper('product_stock_engine');
        get_instance()->load->helper('refill_work_order/refill_work_order_data_support');
        $result = array(
            "success"=>1
            ,"msg"=>array()

        );
        $success = 1;
        $msg = array();
        $rcrf = isset($data['rcrf'])?$data['rcrf']:array();
        $rcrfp = isset($data['rcrf_product'])?$data['rcrf_product']:array();

        $rcrf_id = isset($rcrf['id'])?Tools::_str($rcrf['id']):'';
        $rcrf_db = Print_Form_Data_Support::rcrf_get($rcrf_id);
        $rcrfp_db = Print_Form_Data_Support::rcrf_product_get($rcrf_id);
        
        switch($method){
            case self::$prefix_method.'_add':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $store_id = isset($rcrf['store_id'])? Tools::_str($rcrf['store_id']):'';
                $rcrf_date = isset($rcrf['print_form_date'])?
                    Tools::_str($rcrf['print_form_date']):'';
                $checker = isset($rcrf['checker'])?
                    Tools::empty_to_null($rcrf['checker']):nulll;
                
                //<editor-fold defaultstate="collapsed" desc="Major Validation">
                if(!Store_Engine::store_exists($store_id)){
                    $success = 0;
                    $msg[] = Lang::get('Store').' '.Lang::get('empty',true,false);
                }

                if(is_null($checker)){
                    $success = 0;
                    $msg[] = Lang::get('Checker').' '.Lang::get('empty',true,false);
                }
                
                if(strtotime(Tools::_date($rcrf_date,'Y-m-d H:i:s'))<strtotime(Tools::_date(null,'Y-m-d H:i:s'))){
                    $success =0 ;
                    $msg[] = Lang::get('Date ').' '.Lang::get('is lower than',true,false).' '.Lang::get(Tools::_date('','F d, Y H:i'));
                }
                
                if(count($rcrfp) === 0){
                    $success =0 ;
                    $msg[] = Lang::get('Product').' '.Lang::get('empty');
                }
                                
                if($success !== 1) break;
                //</editor-fold>
                
                $rwop = Refill_Work_Order_Data_Support::rwo_product_get_by_product_id($rcrfp,array('refill_work_order_status'=>'process'));
                
                //<editor-fold desc="Validate RWO Product" defaultstate="collapsed">
                if(count($rwop)!= count($rcrfp)){
                    $success = 0;
                    $msg[] = 'Product Invalid';
                }
                else{
                    foreach($rwop as $idx=>$row){
                        if($row['refill_work_order_product_status'] !== 'waiting_for_confirmation'){
                            $success = 0;
                            $msg[] = Lang::get('Product Status').' '.Lang::get('invalid');
                            break;
                        }
                    }                    
                }
                //</editor-fold>
                
                //<editor-fold defaultstate="Validate RCRF Product">
                foreach($rcrfp as $idx=>$row){
                    $product_condition = Tools::_str(isset($row['product_condition'])?$row['product_condition']:'');
                    
                    if(!SI::type_match('Print_Form_Engine', '$product_condition', $product_condition)){
                        $success = 0;
                        $msg[] = Lang::get('Product Condition').' '.Lang::get('invalid');
                    }
                    
                    $product_recondition_cost = isset($row['product_recondition_cost'])?
                        Tools::_arr($row['product_recondition_cost']):array();

                    if(count($product_recondition_cost) === 0){
                        $success = 0;
                        $msg[] = Lang::get('Product Recondition').' '.Lang::get('empty');
                    }
                    else{
                        foreach($product_recondition_cost as $idx2=>$row2){
                            $pr_name = isset($row2['product_recondition_name'])?
                                Tools::empty_to_null($row2['product_recondition_name']):null;
                            $pr_amount = isset($row2['amount'])?
                                Tools::_str($row2['amount']):'0';
                            
                            if(is_null($pr_name)){
                                $success = 0;
                                $msg[] = Lang::get('Recondition').' '.Lang::get('empty');
                            }
                            
                            if(!Tools::_float($pr_amount)> Tools::_float('0')){
                                $success = 0;
                                $msg[] = Lang::get('Amount').' '.Lang::get('0');
                            }
                            
                            if($success !== 1) break;
                        }
                    }
                    

                    if($success !== 1) break;
                }
                //</editor-fold>
                
                
                //</editor-fold>
                break;
            
            case self::$prefix_method.'_done':
                //<editor-fold defaultstate="collapsed">
                $temp_result = Validator::validate_on_update(
                    array(
                        'module'=>'print_form',
                        'module_name'=>Lang::get('Checking Result Form'),
                        'module_engine'=>'Print_Form_Engine',
                    ),
                    $rcrf
                );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;
                //</editor-fold>
                break;
            
            case self::$prefix_method.'_canceled':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $temp_result = Validator::validate_on_cancel(
                    array(
                        'module'=>'print_form',
                        'module_name'=>Lang::get('Checking Result Form'),
                        'module_engine'=>'Print_Form_Engine',
                    ),
                    $rcrf
                );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;
                
                if($rcrf_db['print_form_status'] === 'done'){
                    //<editor-fold defaultstate="collapsed" desc="Check RWO Product Status still READY TO PROCESS">
                    $q_product_id = '';
                    foreach($rcrfp_db as $idx=>$row){
                        if($row['product_condition'] === 'reprocess'){
                            if($q_product_id === ''){
                                $q_product_id.=$row['product_id'];
                            }
                            else{
                                $q_product_id.=','.$row['product_id'];
                            }
                        }
                    }
                    $def_status = SI::type_default_type_get('Refill_Work_Order_Engine', '$rwo_product_status')['val'];
                    $q = '
                        select 1
                        from refill_work_order_product
                        where id in ('.$q_product_id.')
                            and refill_work_order_product_status != '.$db->escape($def_status).'
                    ';
                    if(count($db->query_array($def_status))>0){
                        $success = 0;
                        $msg[] = 'Product'.' '.Lang::get('in process');
                    }
                    
                    //</editor-fold>                    
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
        //</editor-fold>
    }

    public static function adjust($action,$data=array()){
        //<editor-fold defaultstate="collpased">
        $db = new DB();
        $result = array();
        $rcrf_data = isset($data['rcrf'])?
            Tools::_arr($data['rcrf']):array();
        $rcrf_product_data = isset($data['rcrf_product'])?
            Tools::_arr($data['rcrf_product']):array();
        
        $rwop = Refill_Work_Order_Data_Support::rwo_product_get_by_product_id($rcrf_product_data,array('refill_work_order_status'=>'process'));
        
        $modid = User_Info::get()['user_id'];
        $datetime_curr = Date('Y-m-d H:i:s');
        switch($action){
            case self::$prefix_method.'_add':
                //<editor-fold defaultstate="collapsed">

                $rcrf = array(
                    'store_id'=>$rcrf_data['store_id'],
                    'print_form_date'=>$rcrf_data['print_form_date'],
                    'print_form_status'=>SI::status_default_status_get('Print_Form_Engine')['val'],
                    'notes'=>isset($rcrf_data['notes'])?
                        Tools::empty_to_null(Tools::_str($rcrf_data['notes'])):null,
                    'checker'=>Tools::empty_to_null(isset($rcrf_data['checker'])?
                        Tools::_str($rcrf_data['checker']):''),
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                
                
                
                $rcrf_product = array();
                foreach($rcrf_product_data as $idx=>$row){
                    $rwop_row = array();
                    foreach($rwop as $idx2=>$row2){
                        if($row['product_id'] === $row2['id']){
                            $rwop_row = $row2;
                        }
                    }
                    
                    $rcrf_product_recondition_cost = array();
                    $subtotal_amount = Tools::_float('0');
                    foreach($row['product_recondition_cost'] as $idx2=>$row2){
                        $rcrf_product_recondition_cost[] = array(
                            'product_recondition_name'=>Tools::_str($row2['product_recondition_name']),
                            'amount'=>Tools::_str($row2['amount']),
                        );
                        $subtotal_amount+=Tools::_float($row2['amount']);
                    }
                    
                    $t_rcrf_product = array(
                        'product_type'=>"refill_work_order_product",
                        'product_id'=>Tools::_str($row['product_id']),
                        'unit_id'=>Tools::_str($rwop_row['unit_id']),
                        'qty'=>Tools::_str($rwop_row['qty']),
                        'movement_outstanding_qty'=>Tools::_str($rwop_row['qty']),
                        'notes'=>Tools::empty_to_null(isset($row['notes'])?$row['notes']:''),
                        'rcrf_product_recondition_cost'=>$rcrf_product_recondition_cost,
                        'subtotal_amount'=>$subtotal_amount,
                        'product_condition'=>Tools::_str($rwop_row['product_condition']),
                    );
                    
                    $rcrf_product[] = $t_rcrf_product;
                }
                                
                $result['print_form'] = $rcrf;
                $result['rcrf_product'] = $rcrf_product;
                
                
                //</editor-fold>
                break;
            case self::$prefix_method.'_process':                
                $rcrf = array();

                $rcrf = array(
                    'notes'=>isset($rcrf_data['notes'])?
                        Tools::_str($rcrf_data['notes']):'',
                    'print_form_status'=>'process',
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                $result['print_form'] = $rcrf;    
                break;
            case self::$prefix_method.'_canceled':
                $rcrf = array();

                $rcrf = array(
                    'print_form_status'=>'X',
                    'cancellation_reason'=>$rcrf_data['cancellation_reason'],
                    'notes'=>isset($rcrf_data['notes'])?
                        Tools::empty_to_null($rcrf_data['notes']):null,
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                $result['print_form'] = $rcrf;    
                break;
        }

        return $result;
        //</editor-fold>
    }    

    public function rcrf_add($db,$final_data){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $fprint_form = $final_data['print_form'];
        $frcrf_product = $final_data['rcrf_product'];
        
        $store_id = $fprint_form['store_id'];
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        $rcrf_id = '';       
        
        get_instance()->load->helper('refill_work_order/refill_work_order_engine');
        get_instance()->load->helper('refill_work_order/refill_work_order_data_support_engine');
        
        $fprint_form['code'] = SI::code_counter_store_get($db,$store_id, 'print_form');
        if(!$db->insert('print_form',$fprint_form)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $rcrf_code = $fprint_form['code'];

        if($success == 1){                                
            $rcrf_id = SI::get_trans_id($db,'print_form','code',$rcrf_code);
            $result['trans_id']=$rcrf_id; 
        }
        
        if($success === 1){
            //<editor-fold defaultstate="collapsed" desc="RWO">            
            $rwo_id_arr = array();
            
            //<editor-fold defaultstate="collapsed" desc="Get All RWO ID where rwo product status is done">
            $q_product_id = '';
            foreach($frcrf_product as $idx=>$row){
                $product_id = isset($row['product_id'])?Tools::_str($row['product_id']):'';
                $q_product_id.= (($q_product_id === '')?'':',').$db->escape($product_id);
            }
            
            $q = '
                select distinct rwo.id rwo_id
                from refill_work_order_product rwop
                    inner join refill_work_order rwo on rwop.refill_work_order_id = rwo.id
                where rwop.id in ('.$q_product_id.')
                    and rwo.status > 0
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0){
                foreach($rs as $idx=>$row){
                    $rwo_product = self::refill_work_order_product_get($row['rwo_id']);
                    if(count($rwo_product)>0){
                        $all_is_done = true;
                        foreach($rwo_product as $idx2=>$row2){
                            if($rwo2['refill_work_order_product_status'] !== 'done'){
                                $all_is_done = false;break;
                            }
                        }
                        if($all_is_done) $rwo_id_arr[] = $row['rwo_id'];
                    }
                }            
            }
            //</editor-fold>
            
            //<editor-fold defaultstate="collapsed" desc="Update RWO to DONE">
            foreach($rwo_id_arr as $idx=>$row){
                $rwo_id = $row;
                $t_rwo_param = array(
                    'refill_work_order'=>array(
                        'id'=>$rwo_id,
                        'refill_work_order_status'=>'done',
                        'modid'=>$modid,
                        'moddate'=>$moddate
                    )
                );
                $temp_result = Refill_Work_Order_Engine::refill_work_order_done($db, $t_rwo_param, $rwo_id);
                $success = $temp_result['success'];
                $msg = array_merge($msg,$temp_result['msg']);
                
                if($success !== 1) break;
            }
            //</editor-fold>
            
            //</editor-fold>
        }
        
        if($success == 1){
            $temp_res = SI::status_log_add($db,
                'print_form',
                $rcrf_id,
                $fprint_form['print_form_status']
            );

            $success = $temp_res['success'];
            if($success !== 1){
                $msg = array_merge($msg, $temp_res['msg']);
            }  
        }
        
        if($success === 1){
            //<editor-fold defaultstate="collapsed" desc="RCRF Product">
            foreach($frcrf_product as $idx=>$product){
                $product_type = $product['product_type'];
                $product_condition = $product['product_condition'];
                $rcrf_product_recondition_cost = $product['rcrf_product_recondition_cost'];
                unset($product['rcrf_product_recondition_cost']);
                $rcrf_product_id = '';
                $product['print_form_id'] = $rcrf_id;
                if(!$db->insert('rcrf_product',$product)){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                    break;
                }
                
                switch($product_condition){
                    case 'processed':
                    case 'unable_to_process':
                    case 'add_preasure':
                        $temp_result = Refill_Work_Order_Engine::product_status_set(
                            $db, $product['product_id'], 'done'
                        );
                        $success = $temp_result['success'];
                        $msg = array_merge($msg, $temp_result['msg']);
                        
                        break;
                    case 'reprocess':
                        $temp_result = Refill_Work_Order_Engine::product_status_set(
                            $db, $product['product_id'], 'ready_to_process'
                        );
                        $success = $temp_result['success'];
                        $msg = array_merge($msg, $temp_result['msg']);
                        break;
                }
                
                if($success === 1){
                    $q = '
                        select max(id) id
                        from rcrf_product
                        where rcrf_product.print_form_id = '.$db->escape($rcrf_id).'
                    ';
                    $rs = $db->query_array($q);
                    if(!count($rs)>0){
                        $success = 0;
                        $msg[] = 'Unable to find Refill Checking Result Form ID';
                        break;
                    }
                    else{
                        $rcrf_product_id = $rs[0]['id'];
                    }
                }
                
                if($success === 1){
                    foreach($rcrf_product_recondition_cost as $idx2=>$row2){
                        $row2['rcrf_product_id'] = $rcrf_product_id;
                        if(!$db->insert('rcrf_product_recondition_cost',$row2)){
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();                                
                            $success = 0;
                            break;
                        }
                    }
                }
                
                if($success !== 1) break;
            }
            //</editor-fold>
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    function rcrf_done($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $fprint_form = $final_data['print_form'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('print_form',$fprint_form,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        

        if($success == 1){
            $temp_result = SI::status_log_add($db,'print_form',
                $id,$fprint_form['print_form_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    function rcrf_canceled($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('print_form/print_form_data_support');
        get_instance()->load->helper('refill_work_order/refill_work_order_engine');
        get_instance()->load->helper('refill_work_order/refill_work_order_data_support');
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();
        
        $print_form_id = $id;
        
        $fprint_form = $final_data['print_form'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        $print_form = $db->fast_get('print_form',array('id'=>$print_form_id))[0];
        $rcrf_product_db = Print_Form_Data_Support::rcrf_product_get($print_form_id);
        //$pure_amount = Tools::_float($print_form['amount']) - Tools::_float($print_form['change_amount']);
        
        if(!$db->update('print_form',$fprint_form,array("id"=>$print_form_id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'print_form',
                $print_form_id,$print_form['print_form_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
                
        if($success === 1){
            $rwo_product_status = SI::type_default_type_get('refill_work_order_engine', '$rwo_product_status')['val'];
            foreach($rcrf_product_db as $idx=>$row){
                $q = '
                    update refill_work_order_product
                    set refill_work_order_product_status = '.$db->escape($rwo_product_status).'
                    where refill_work_order_product.id = '.$db->escape($row['product_id']).'
                ';
                if(!$db->query($q)){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();             
                    $success = 0;
                    break;
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