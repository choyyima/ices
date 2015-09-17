<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Refill_Checking_Result_Form_Engine {
    public static $prefix_id = 'rcrf';
    public static $prefix_method;
    public static $status_list;
    public static $product_condition;
    
    public function helper_init(){
        //<editor-fold desc="this function is called automatically in MY_Loader class" defaultstate="collapsed">
        
        self::$prefix_method = self::$prefix_id;
        self::$status_list = array(
        //<editor-fold defaultstate="collapsed">
            array(
                'val'=>''
                ,'label'=>''
                ,'method'=>self::$prefix_method.'_add'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Add Refill -'),array('val'=>'Checking Result Form'),array('val'=>'success')
                    )
                )
            ),
            array(
                'val'=>'done'
                ,'label'=>'DONE'
                ,'method'=>self::$prefix_method.'_done'
                ,'default'=>true
                ,'next_allowed_status'=>array('X')
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update Refill -'),array('val'=>'Checking Result Form'),array('val'=>'success')
                    )
                )
            )
            ,array(
                'val'=>'X'
                ,'label'=>'CANCELED'
                ,'method'=>self::$prefix_method.'_canceled'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Cancel Refill -'),array('val'=>'Checking Result Form'),array('val'=>'success')
                    )
                )
        )
        //</editor-fold>
        );
        self::$product_condition = array(
            array('val'=>'processed','label'=>'Telah Diproses'),
            array('val'=>'unable_to_process','label'=>'Tidak Dapat Diproses'),
            array('val'=>'add_preasure','label'=>'Tambah Tekanan'),
            array('val'=>'reprocess','label'=>'Re-Process'),
        );
        //</editor-fold>
    }
    
    public static function refill_checking_result_form_exists($id){
        //<editor-fold defaultstate="collapsed">
        $result = false;
        $db = new DB();
        $q = '
                select 1 
                from refill_checking_result_form 
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
            'index'=>get_instance()->config->base_url().'refill_checking_result_form/',
            'refill_checking_result_form_engine'=>'refill_checking_result_form/refill_checking_result_form_engine',
            'refill_checking_result_form_data_support' => 'refill_checking_result_form/refill_checking_result_form_data_support',
            'refill_checking_result_form_renderer' => 'refill_checking_result_form/refill_checking_result_form_renderer',
            'ajax_search'=>get_instance()->config->base_url().'refill_checking_result_form/ajax_search/',
            'data_support'=>get_instance()->config->base_url().'refill_checking_result_form/data_support/',
        );

        return json_decode(json_encode($path));
    }

    public static function validate($method,$data=array()){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('refill_checking_result_form/refill_checking_result_form_data_support');
        get_instance()->load->helper('refill_subcon/refill_subcon_data_support');
        get_instance()->load->helper('product/product_data_support');
        get_instance()->load->helper('product_stock_engine');
        get_instance()->load->helper('refill_work_order/refill_work_order_data_support');
        get_instance()->load->helper('refill_work_order/refill_work_order_engine');
        $result = array(
            "success"=>1
            ,"msg"=>array()

        );
        $success = 1;
        $msg = array();
        $rcrf = isset($data['rcrf'])?$data['rcrf']:array();
        $rcrfp = isset($data['rcrf_product'])?$data['rcrf_product']:array();

        $rcrf_id = isset($rcrf['id'])?Tools::_str($rcrf['id']):'';
        $rcrf_db = Refill_Checking_Result_Form_Data_Support::rcrf_get($rcrf_id);
        $rcrfp_db = Refill_Checking_Result_Form_Data_Support::rcrf_product_get($rcrf_id);
        
        switch($method){
            case self::$prefix_method.'_add':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $store_id = isset($rcrf['store_id'])? Tools::_str($rcrf['store_id']):'';
                $rcrf_date = isset($rcrf['refill_checking_result_form_date'])?
                    Tools::_str($rcrf['refill_checking_result_form_date']):'';
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
                
                if(count($rcrfp) === 0){
                    $success =0 ;
                    $msg[] = Lang::get('Product').' '.Lang::get('empty');
                }
                                
                if($success !== 1) break;
                //</editor-fold>
                
                $reg_p = array();
                $rwo_p = array();
                //<editor-fold defaultstate="collapsed" desc="Group Product into RWO Product and Registered Product">
                foreach($rcrfp as $idx=>$row){
                    switch($row['product_type']){
                        case 'registered_product':
                            $reg_p[] = $row;
                            break;
                        case 'refill_work_order_product':
                            $rwo_p[] = $row;
                            break;
                    }
                }
                //</editor-fold>
                
                $rwop = Refill_Work_Order_Data_Support::rwo_product_get_by_product_id(
                    $rwo_p,array('refill_work_order_status'=>'process')
                );
                
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
                
                //<editor-fold defaultstate="collapsed" desc="Validate RCRF Product">
                foreach($rcrfp as $idx=>$row){
                    $product_condition = Tools::_str(isset($row['product_condition'])?$row['product_condition']:'');
                    $product_recondition_cost = isset($row['product_recondition_cost'])?
                        Tools::_arr($row['product_recondition_cost']):array();
                    $product_sparepart_cost = isset($row['product_sparepart_cost'])?
                        Tools::_arr($row['product_sparepart_cost']):array();
                    $product_reference_type = Tools::_str(isset($row['reference_type'])?$row['reference_type']:'');
                    $product_reference_id = Tools::_str(isset($row['reference_id'])?$row['reference_id']:'');
                    $product_type = Tools::_str(isset($row['product_type'])?$row['product_type']:'');
                    $product_id = Tools::_str(isset($row['product_id'])?$row['product_id']:'');
                    $product_dependency_db = Refill_Checking_Result_Form_Data_Support::
                        product_marking_code_dependency_get($product_type, $product_id);
                    $product_sparepart_cost_db = $product_dependency_db['product_sparepart_cost'];
                    $product_sparepart_cost = Tools::_arr(
                        isset($row['product_sparepart_cost'])?$row['product_sparepart_cost']:array()
                    );
                    
                    //<editor-fold defaultstate="collapsed" desc="Product exists in DB">
                    $product_exists = false;
                    
                    switch($product_type){
                        case 'refill_work_order_product':
                            //<editor-fold defaultstate="collapsed">
                            foreach($rwop as $idx_rwop=> $row_rwop){
                                $rwop_product_id = $row_rwop['id'];
                                $rwop_product_reference_id = $row_rwop['id'];
                                $rwop_product_reference_type = 'refill_work_order_product';
                                
                                
                                if($rwop_product_reference_type === $product_reference_type
                                    &&$rwop_product_reference_id === $product_reference_id
                                    &&$rwop_product_id === $product_id
                                       
                                ){
                                    $product_exists = true;
                                    break;
                                }
                            }
                            //</editor-fold>
                            break;
                    }                    
                    
                    if(!$product_exists){ 
                        $success = 0;
                        $msg[] = 'Product invalid';
                    }
                    //</editor-fold>
                    
                    //<editor-fold defaultstate="collapsed" desc="Product Condition">
                    if(!SI::type_match('Refill_Checking_Result_Form_Engine', $product_condition,'$product_condition')){
                        $success = 0;
                        $msg[] = Lang::get('Product Condition').' '.Lang::get('invalid');
                    }
                    //</editor-fold>
                    
                    //<editor-fold defaultstate="collapsed" desc="Product Recondition Cost">
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
                            
                            if(Tools::_float($pr_amount)<Tools::_float('0')){
                                $success = 0;
                                $msg[] = Lang::get('Recondition Amount').' '.Lang::get('invalid');
                            }
                            
                            if($success !== 1) break;
                        }
                    }
                    //</editor-fold>
                    
                    //<editor-fold defaultstate="collapsed" desc="Product Sparepart Cost">
                    foreach($product_sparepart_cost as $idx_psc=>$row_psc){
                        $product_reference_type = isset($row_psc['reference_type'])?
                            Tools::_str($row_psc['reference_type']):'';
                        $product_reference_id = isset($row_psc['reference_id'])?
                            Tools::_str($row_psc['reference_id']):'';
                        
                        $product_sparepart_exists = false;
                        
                        foreach($product_sparepart_cost_db as $idx_psc_db=>$row_psc_db){
                            $product_reference_type_db = $row_psc_db['reference_type'];
                            $product_reference_id_db = $row_psc_db['reference_id'];
                            
                            if($product_reference_type === $product_reference_type_db
                                && $product_reference_id === $product_reference_id_db
                            ){
                                $product_sparepart_exists = true;
                                break;
                            }
                        }
                        if(!$product_sparepart_exists){
                            $success = 0;
                            $msg[] = 'Product Sparepart invalid';
                        }
                        
                        if($success !== 1) break;
                        
                    }
                    //</editor-fold>
                    
                    if($success !== 1) break;
                }
                //</editor-fold>
                
                break;
            
            case self::$prefix_method.'_done':
                //<editor-fold defaultstate="collapsed">
                $temp_result = Validator::validate_on_update(
                    array(
                        'module'=>'refill_checking_result_form',
                        'module_name'=>Lang::get('Checking Result Form'),
                        'module_engine'=>'Refill_Checking_Result_Form_Engine',
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
                        'module'=>'refill_checking_result_form',
                        'module_name'=>Lang::get('Checking Result Form'),
                        'module_engine'=>'Refill_Checking_Result_Form_Engine',
                    ),
                    $rcrf
                );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;
                
                if($rcrf_db['refill_checking_result_form_status'] === 'done'){
                    $rwo_db = Refill_Work_Order_Data_Support::rwo_get_by_product_id($rcrfp_db,array());
                    
                    //<editor-fold defaultstate="collapsed" desc="Check RWO status is PROCESS/DONE">
                    $row_valid_status = array('process','done');
                    foreach($rwo_db as $idx=>$row){
                        if(!in_array($row['refill_work_order_status'],$row_valid_status)){
                            $success = 0;
                            $msg[] = 'Refill - '.Lang::get('Work Order').' '.'Status'.' '.Lang::get('must be').' '.Lang::get('PROCESS / DONE');
                            break;
                        }
                    }
                    //</editor-fold>
                    
                    if($success === 1){
                        //<editor-fold defaultstate="collapsed" desc="IF RCRF Product is REPROCESS, check RWO Product Status still READY TO PROCESS">
                        $q_product_id = '';

                        foreach($rcrfp_db as $idx=>$row){
                            if($row['product_condition'] === 'reprocess'){
                                $q_product_id .= ($q_product_id === '')?
                                    ($row['product_id']):
                                    (','.$row['product_id']);

                            }

                        }

                        
                        $q = '
                            select 1
                            from refill_work_order_product
                            where id in ('.$q_product_id.')
                                and refill_work_order_product_status != "ready_to_process"
                        ';
                        if(count($db->query_array($q))>0){
                            $success = 0;
                            $msg[] = 'Product'.' '.Lang::get('in process');
                        }
                        //</editor-fold>                    
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
                    'refill_checking_result_form_date'=>Tools::_date('','Y-m-d H:i:s'),
                    'refill_checking_result_form_status'=>SI::status_default_status_get('Refill_Checking_Result_Form_Engine')['val'],
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
                    $amount = Tools::_float('0');
                    foreach($row['product_recondition_cost'] as $idx2=>$row2){
                        $rcrf_product_recondition_cost[] = array(
                            'product_recondition_name'=>Tools::_str($row2['product_recondition_name']),
                            'amount'=>Tools::_str($row2['amount']),
                        );
                        $amount+=Tools::_float($row2['amount']);
                    }
                    
                    $rcrf_product_sparepart_cost = array();
                    $prd_marking_code_dependency_db = Refill_Checking_Result_Form_Data_Support::
                        product_marking_code_dependency_get($row['product_type'], $row['product_id']);
                    $product_sparepart_cost_db = $prd_marking_code_dependency_db['product_sparepart_cost'];
                    
                    foreach($row['product_sparepart_cost'] as $idx2=>$row2){
                        $t_rcrf_product_sparepart_cost = array(
                            'reference_type'=>Tools::_str($row2['reference_type']),
                            'reference_id'=>Tools::_str($row2['reference_id']),
                            'product_type'=>null,
                            'product_id'=>null,
                            'unit_id'=>null,
                            'qty'=>null,
                            'amount'=>'0',
                        );
                        foreach($product_sparepart_cost_db as $idx3=>$row3){
                            if($t_rcrf_product_sparepart_cost['reference_type'] === $row3['reference_type']
                                && $t_rcrf_product_sparepart_cost['reference_id'] === $row3['reference_id']
                            ){
                                $t_rcrf_product_sparepart_cost['product_type'] = $row3['product_type'];
                                $t_rcrf_product_sparepart_cost['product_id'] = $row3['product_id'];
                                $t_rcrf_product_sparepart_cost['unit_id'] = $row3['unit_id'];
                                $t_rcrf_product_sparepart_cost['qty'] = $row3['sent_qty'];
                                $t_rcrf_product_sparepart_cost['amount'] = $row3['amount'];
                            }
                        }
                        $amount+=Tools::_float($row2['amount']);
                        $rcrf_product_sparepart_cost[] = $t_rcrf_product_sparepart_cost;
                    }
                    
                    
                    $t_rcrf_product = array(
                        'reference_type'=>Tools::_str($row['reference_type']),
                        'reference_id'=>Tools::_str($row['reference_id']),
                        'product_type'=>Tools::_str($row['product_type']),
                        'product_id'=>Tools::_str($row['product_id']),
                        'unit_id'=>Tools::_str($rwop_row['unit_id']),
                        'qty'=>Tools::_str($rwop_row['qty']),
                        'movement_outstanding_qty'=>Tools::_str($rwop_row['qty']),
                        'notes'=>Tools::empty_to_null(isset($row['notes'])?$row['notes']:''),
                        'rcrf_product_recondition_cost'=>$rcrf_product_recondition_cost,
                        'rcrf_product_sparepart_cost'=>$rcrf_product_sparepart_cost,
                        'amount'=>$amount,
                        'product_condition'=>Tools::_str($row['product_condition']),
                    );
                    
                    $rcrf_product[] = $t_rcrf_product;
                }
                                
                $result['refill_checking_result_form'] = $rcrf;
                $result['rcrf_product'] = $rcrf_product;
                
                
                //</editor-fold>
                break;
            case self::$prefix_method.'_done':
                $rcrf = array();

                $rcrf = array(
                    'notes'=>Tools::empty_to_null(isset($rcrf_data['notes'])?
                        Tools::_str($rcrf_data['notes']):''),
                    'refill_checking_result_form_status'=>'done',
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                $result['refill_checking_result_form'] = $rcrf;    
                break;
            case self::$prefix_method.'_canceled':
                $rcrf = array();

                $rcrf = array(
                    'refill_checking_result_form_status'=>'X',
                    'cancellation_reason'=>$rcrf_data['cancellation_reason'],
                    'notes'=>isset($rcrf_data['notes'])?
                        Tools::empty_to_null($rcrf_data['notes']):null,
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                $result['refill_checking_result_form'] = $rcrf;    
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

        $frefill_checking_result_form = $final_data['refill_checking_result_form'];
        $frcrf_product = $final_data['rcrf_product'];
        
        $store_id = $frefill_checking_result_form['store_id'];
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        $rcrf_id = '';       
        
        get_instance()->load->helper('refill_work_order/refill_work_order_engine');
        get_instance()->load->helper('refill_work_order/refill_work_order_data_support');
        
        $frefill_checking_result_form['code'] = SI::code_counter_store_get($db,$store_id, 'refill_checking_result_form');
        if(!$db->insert('refill_checking_result_form',$frefill_checking_result_form)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $rcrf_code = $frefill_checking_result_form['code'];

        if($success == 1){                                
            $rcrf_id = SI::get_trans_id($db,'refill_checking_result_form','code',$rcrf_code);
            $result['trans_id']=$rcrf_id; 
        }
        
        if($success === 1){
            //<editor-fold defaultstate="collapsed" desc="RCRF Product">
            foreach($frcrf_product as $idx=>$product){
                $product_type = $product['product_type'];
                $product_condition = $product['product_condition'];
                $rcrf_product_recondition_cost = $product['rcrf_product_recondition_cost'];
                unset($product['rcrf_product_recondition_cost']);
                $rcrf_product_sparepart_cost = $product['rcrf_product_sparepart_cost'];
                unset($product['rcrf_product_sparepart_cost']);
                $rcrf_product_id = '';
                $product['refill_checking_result_form_id'] = $rcrf_id;
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
                        where rcrf_product.refill_checking_result_form_id = '.$db->escape($rcrf_id).'
                    ';
                    $rs = $db->query_array($q);
                    if(!count($rs)>0){
                        $success = 0;
                        $msg[] = 'Unable to find Refill Checking Result Form Product ID';
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
                
                if($success === 1){
                    foreach($rcrf_product_sparepart_cost as $idx2=>$row2){
                        $row2['rcrf_product_id'] = $rcrf_product_id;
                        if(!$db->insert('rcrf_product_sparepart_cost',$row2)){
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
                    $rwo_product = Refill_Work_Order_Data_Support::refill_work_order_product_get($row['rwo_id']);
                    if(count($rwo_product)>0){
                        $all_is_done = true;
                        foreach($rwo_product as $idx2=>$row2){
                            if($row2['refill_work_order_product_status'] !== 'done'){
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
                'refill_checking_result_form',
                $rcrf_id,
                $frefill_checking_result_form['refill_checking_result_form_status']
            );

            $success = $temp_res['success'];
            if($success !== 1){
                $msg = array_merge($msg, $temp_res['msg']);
            }  
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

        $frefill_checking_result_form = $final_data['refill_checking_result_form'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('refill_checking_result_form',$frefill_checking_result_form,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        

        if($success == 1){
            $temp_result = SI::status_log_add($db,'refill_checking_result_form',
                $id,$frefill_checking_result_form['refill_checking_result_form_status']);
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
        get_instance()->load->helper('refill_checking_result_form/refill_checking_result_form_data_support');
        get_instance()->load->helper('refill_work_order/refill_work_order_engine');
        get_instance()->load->helper('refill_work_order/refill_work_order_data_support');
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();
        
        $refill_checking_result_form_id = $id;
        
        $frefill_checking_result_form = $final_data['refill_checking_result_form'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        $refill_checking_result_form = $db->fast_get('refill_checking_result_form',array('id'=>$refill_checking_result_form_id))[0];
        $rcrf_product_db = Refill_Checking_Result_Form_Data_Support::rcrf_product_get($refill_checking_result_form_id);
        //$pure_amount = Tools::_float($refill_checking_result_form['amount']) - Tools::_float($refill_checking_result_form['change_amount']);
        
        if(!$db->update('refill_checking_result_form',$frefill_checking_result_form,array("id"=>$refill_checking_result_form_id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'refill_checking_result_form',
                $refill_checking_result_form_id,$refill_checking_result_form['refill_checking_result_form_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
                
        if($success === 1){
            //<editor-fold defaultstate="collapsed" desc="RCRF Product">
            foreach($rcrf_product_db as $idx=>$product){
                $product_type = $product['product_type'];
                $product_condition = $product['product_condition'];
                $rcrf_product_recondition_cost = $product['rcrf_product_recondition_cost'];
                unset($product['rcrf_product_recondition_cost']);
                $rcrf_product_id = $product['id'];
                
                switch($product_condition){
                    case 'processed':
                    case 'unable_to_process':
                    case 'add_preasure':
                    case 'reprocess':
                        $temp_result = Refill_Work_Order_Engine::product_status_set(
                            $db, $product['product_id'], 'waiting_for_confirmation'
                        );
                        $success = $temp_result['success'];
                        $msg = array_merge($msg, $temp_result['msg']);
                        
                        break;
                }
                
                if($success !== 1) break;
            }
            //</editor-fold>
            
            //<editor-fold defaultstate="collapsed" desc="Update RWO Status">
            $rwo = Refill_Work_Order_Data_Support::rwo_get_by_product_id($rcrf_product_db, array('refill_work_order_status'=>'done'));
            foreach($rwo as $idx=>$row){
                $temp_result = Refill_Work_Order_Engine::rwo_status_set($db,$row['id'],'process');
            }
            //</editor-fold>
        }

        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    function movement_outstanding_qty_add($db,$tbl,$rcrf_id,$product_type,$product_id,$unit_id,$qty){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();
        
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $q = '
            update rcrf_'.$tbl.'
            set movement_outstanding_qty = movement_outstanding_qty + '.$db->escape($qty).'
            where refill_checking_result_form_id = '.$db->escape($rcrf_id).'
                and product_type = '.$db->escape($product_type).'
                and product_id = '.$db->escape($product_id).'
                and unit_id = '.$db->escape($unit_id).'
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