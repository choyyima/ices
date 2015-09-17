<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Work_Order_Engine {

    public static $status_list = array(
        //<editor-fold defaultstate="collapsed">
        array(
            'val'=>''
            ,'label'=>''
            ,'method'=>'refill_work_order_add'
            ,'next_allowed_status'=>array()
            ,'msg'=>array(
                'success'=>array(
                    array('val'=>'Add Refill - '),array('val'=>'Work Order'),array('val'=>'success')
                )
            )
        ),
        array(//status awal tanpa product, hanya jumlah product
            'val'=>'initialized'
            ,'label'=>'INITIALIZED'
            ,'method'=>'refill_work_order_initialized'
            ,'default'=>true
            ,'next_allowed_status'=>array('process','X')
            ,'msg'=>array(
                'success'=>array(
                    array('val'=>'Update Refill - '),array('val'=>'Work Order'),array('val'=>'success')
                )
            )
        ),
        array(//status setelah product diisikan
            'val'=>'process'
            ,'label'=>'PROCESS'
            ,'method'=>'refill_work_order_process'
            ,'next_allowed_status'=>array('X')
            ,'msg'=>array(
                'success'=>array(
                    array('val'=>'Update Refill - '),array('val'=>'Work Order'),array('val'=>'success')
                )
            )
        ),
        array(//status setelah semua product telah keluar FHP
            'val'=>'done'
            ,'user_select_next_allowed_status'=>'false'
            ,'label'=>'DONE'
            ,'method'=>'refill_work_order_done'
                ,'next_allowed_status'=>array()
            ,'msg'=>array()
        ),
        array(//status setelah keluar invoice
            'val'=>'invoiced'
            ,'user_select_next_allowed_status'=>'false'
            ,'label'=>'INVOICED'
            ,'method'=>'refill_work_order_invoiced'
                ,'next_allowed_status'=>array()
            ,'msg'=>array()
        )
        ,array(
            'val'=>'X'
            ,'label'=>'CANCELED'
            ,'method'=>'refill_work_order_canceled'
            ,'next_allowed_status'=>array()
            ,'msg'=>array(
                'success'=>array(
                    array('val'=>'Cancel Refill - '),array('val'=>'Work Order'),array('val'=>'success')
                )
            )
        )
        //</editor-fold>
    );

    public static $product_condition = array(
        array('val'=>'product_condition_selang_nozzle','label'=>'Selang Nozzle'),
        array('val'=>'product_condition_roda','label'=>'Roda'),
        array('val'=>'product_condition_pvc_base','label'=>'PVC Base'),
        array('val'=>'product_condition_valve','label'=>'Valve'),
        array('val'=>'product_condition_safety_pin','label'=>'Safety Pin'),
        array('val'=>'product_condition_pg','label'=>'PG'),
        array('val'=>'product_condition_sabuk','label'=>'Sabuk')

    );
    
    public static $rwo_product_status = array(
        
    );
    
    function helper_init(){
        //<editor-fold defaultstate="collapsed">
        self::$rwo_product_status = array(
            //<editor-fold defaultstate="collapsed">
            array(// STATUS AWAL BARANG
                'val'=>'initialized'
                ,'label'=>'INITIALIZED'
                ,'default'=>true
            ),
            array(// STATUS SAAT BARANG SETELAH DIINPUT KE SYSTEM LEWAT REFILL SPK
                'val'=>'ready_to_process'
                ,'label'=>'READY TO PROCESS'
            ),
            array(// STATUS SAAT BARANG DIBUATKAN SUBCON SPK dan TAMBAHAN DELIVERY ORDER
                'val'=>'process'
                ,'label'=>'PROCESS'
            ),
            array(// STATUS BARANG SAAT ADA RECEIVE PRODUCT
                'val'=>'waiting_for_confirmation'
                ,'label'=>'WAITING FOR CONFIRMATION'
            ),
            array(// STATUS BARANG SAAT SUDAH KELUAR Form Hasil Pengecekan
                'val'=>'done'
                ,'label'=>'DONE'
            )
            ,array(
                'val'=>'X'
                ,'label'=>'CANCELED'
            )
            //</editor-fold>
        );
        //</editor-fold>
    }
    
    public static function product_marking_code_prefix_get(){
        //<editor-fold defaultstate="collapsed">
        $date_curr = Date('Ymd');
        $rwo_order = 1;
        $db = new DB();
        $q = '
            select count(1) total
            from refill_work_order_info t1
            where date(created_date) = date(now())
        ';
        $rs = $db->query_array($q);
        $rwo_order = Tools::_int($rs[0]['total'])+Tools::_int('1');
        $result = $date_curr.'/'.str_pad($rwo_order, 3, '0', STR_PAD_LEFT).'/';
        
        return $result;
        //</editor-fold>
    }
    
    public static function refill_work_order_active_get(){
        //<editor-fold defaultstate="collapsed">
        $result = null;
        $db = new DB();
        $user_id = User_Info::get()['user_id'];
        $q = '
            select t1.id
            from refill_work_order t1
                inner join refill_work_order_info t2 on t1.id = t2.refill_work_order_id
            where t1.status>0 and t1.refill_work_order_status ='.
                $db->escape(SI::status_default_status_get('Refill_Work_Order_Engine')['val']).'
                and t2.creator_id = '.$db->escape($user_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = $rs[0]['id'];
        return $result;
        //</editor-fold>
    }
    
    public static function refill_work_order_exists($id){
        //<editor-fold defaultstate="collapsed">
        $result = false;
        $db = new DB();
        $q = '
                select 1 
                from refill_work_order 
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
            'index'=>get_instance()->config->base_url().'refill_work_order/',
            'refill_work_order_engine'=>'refill_work_order/refill_work_order_engine',
            'refill_work_order_data_support' => 'refill_work_order/refill_work_order_data_support',
            'refill_work_order_renderer' => 'refill_work_order/refill_work_order_renderer',
            'refill_work_order_print' => 'refill_work_order/refill_work_order_print',
            'ajax_search'=>get_instance()->config->base_url().'refill_work_order/ajax_search/',
            'data_support'=>get_instance()->config->base_url().'refill_work_order/data_support/',
        );

        return json_decode(json_encode($path));
    }
    
    public static function validate($method,$data=array()){      
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('refill_work_order/refill_work_order_data_support');
        
        $result = array(
            "success"=>1
            ,"msg"=>array()

        );
        $success = 1;
        $msg = array();
        $refill_work_order = isset($data['refill_work_order'])?
                Tools::_arr($data['refill_work_order']):array();
        $rwo_info = isset($data['refill_work_order_info'])?
                Tools::_arr($data['refill_work_order_info']):array();
        $rwo_product = isset($data['refill_work_order_product'])?
                Tools::_arr($data['refill_work_order_product']):array();
        $rwo_id = $refill_work_order['id'];
        
        $rwo_product_db = Refill_Work_Order_Data_Support::refill_work_order_product_get($rwo_id);
        
        switch($method){
            case 'refill_work_order_add':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $customer_id = isset($refill_work_order['customer_id'])?$refill_work_order['customer_id']:'';
                $store_id = isset($refill_work_order['store_id'])?
                    Tools::_str($refill_work_order['store_id']):'';
                $number_of_product = isset($rwo_info['number_of_product'])?
                    Tools::_int($rwo_info['number_of_product']):0;

                //<editor-fold defaultstate="collapsed" desc="Major Validation">

                if(!SI::record_exists('store', array('id'=>$store_id,'status'=>'1'))){
                    $success = 0;
                    $msg[] = Lang::get('Store').' '.Lang::get('empty',true,false);
                }

                if(!SI::record_exists('customer',array('id'=>$customer_id))){
                    $success = 0;
                    $msg[] = Lang::get('Customer').' '.Lang::get('empty',true,false);
                }
                
                if(Tools::_int($number_of_product) < Tools::_int('1')){
                    $success = 0;
                    $mgs[] = 'Number of Product invalid';
                }
                
                if($success !== 1) break;
                //</editor-fold>
                
                
                //</editor-fold>
                break;
            case 'refill_work_order_initialized':
                //<editor-fold defaultstate="collapsed">
                $temp_result = Validator::validate_on_update(
                    array(
                        'module'=>'refill_work_order',
                        'module_name'=>Lang::get('Work Order'),
                        'module_engine'=>'Refill_Work_Order_Engine',
                    ),
                    $refill_work_order
                );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;
                
                //</editor-fold>
                break;
            case 'refill_work_order_process':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $temp_result = Validator::validate_on_update(
                    array(
                        'module'=>'refill_work_order',
                        'module_name'=>Lang::get('Work Order'),
                        'module_engine'=>'Refill_Work_Order_Engine',
                    ),
                    $refill_work_order
                );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;
                
                get_instance()->load->helper('refill_product_price_list/refill_product_price_list_data_support');
                
                //<editor-fold defaultstate="collapsed" desc="Refill Product Check">
                $rwo_product_db = Refill_Work_Order_Data_Support::refill_work_order_product_get($rwo_id);
                $rwo_db = Refill_Work_Order_Data_Support::refill_work_order_get($rwo_id);
                if(!Refill_Work_Order_Data_Support::rpc_rpm_cu_all_exist($rwo_product)){
                    $success = 0;
                    $msg[] = 'Refill Product invalid';
                }
                else{
                    if(count($rwo_product)!== count($rwo_product_db)){
                        $success = 0;
                        $msg[] = 'Refill Product '.Lang::get('incomplete');
                    }
                    
                    $rwo_product_id_list = '';
                    for($i = 0;$i<count($rwo_product);$i++){
                        //<editor-fold desc="Refill Product Loop">
                        $rwo_product_id = isset($rwo_product[$i]['id'])?
                                Tools::_str($rwo_product[$i]['id']):'';
                        $rpc_id = isset($rwo_product[$i]['rpc_id'])?
                                Tools::_str($rwo_product[$i]['rpc_id']):'';
                        $rpm_id = isset($rwo_product[$i]['rpm_id'])?
                                Tools::_str($rwo_product[$i]['rpm_id']):'';
                        $capacity_unit_id = isset($rwo_product[$i]['capacity_unit_id'])?
                                Tools::_str($rwo_product[$i]['capacity_unit_id']):'';
                        $capacity = isset($rwo_product[$i]['capacity'])?
                                Tools::_str($rwo_product[$i]['capacity']):'0';
                        $product_info_merk = isset($rwo_product[$i]['product_info_merk'])?
                                Tools::_str($rwo_product[$i]['product_info_merk']):'';
                        $product_info_type = isset($rwo_product[$i]['product_info_type'])?
                                Tools::_str($rwo_product[$i]['product_info_type']):'';
                        $estimated_amount = isset($rwo_product[$i]['estimated_amount'])?
                                Tools::_str($rwo_product[$i]['estimated_amount']):'';
                        $staff_checker = isset($rwo_product[$i]['staff_checker'])?
                                Tools::_str($rwo_product[$i]['staff_checker']):'';
                        
                        $rwo_product_id_list.=(($rwo_product_id_list==='')?'':',').$db->escape($rwo_product_id);
                        
                        if(preg_replace('/[ ]/','',$capacity) === '' 
                        ){
                            $success =0;
                            $msg[] = 'Product Capacity '.Lang::get('empty');
                        }
                        
                        if(preg_replace('/[ ]/','',$product_info_merk) === '' || 
                            preg_replace('/[ ]/','',$product_info_type) === ''
                        ){
                            $success =0;
                            $msg[] = 'Product Info '.Lang::get('empty');
                        }
                        
                        if(Tools::_float($estimated_amount) === floatval('0')){
                            $success = 0;
                            $msg[] = 'Product Estimated Amount zero';
                        }
                        
                        if(Tools::_float($estimated_amount) !== 
                            Tools::_float(Refill_Product_Price_List_Data_Support::
                                product_price_get($rwo_db['customer_id'],$rpc_id, $rpm_id, $capacity_unit_id, $capacity))){
                            $success = 0;
                            $msg[] = 'Product Estimated Amount invalid';
                            
                        }
                        
                        if(preg_replace('/[ ]/','',$staff_checker) === ''){
                            $success = 0;
                            $msg[] = 'ttd Staff '.Lang::get('empty');
                        }
                        
                        if($success !== 1) break;
                        //</editor-fold>
                    }
                    
                    //<editor-fold defaultstate="collapsed" desc="RWO Product ID">
                    if($success === 1 && count($rwo_product)>0 && 
                        count($rwo_product) === count($rwo_product_db)
                    ){
                        $q = '
                            select 1
                            from refill_work_order_product rwop
                            where rwop.refill_work_order_id = '.$db->escape($rwo_id).'
                                and rwop.id in ('.$rwo_product_id_list.')
                        ';
                        if(count($db->query_array($q))!== count($rwo_product)){
                            $success = 0;
                            $msg[] = 'Refill Work Order Product ID invalid';
                        }
                    }
                    //</editor-fold>
                }
                //</editor-fold>
                
                //</editor-fold>
                break;
            
            case 'refill_work_order_canceled':
                $db = new DB();
                $temp_result = Validator::validate_on_cancel(
                    array(
                        'module'=>'refill_work_order',
                        'module_name'=>'Refill - '.' '.Lang::get('Work Order'),
                        'module_engine'=>'Refill_Work_Order_Engine',
                    ),
                    $refill_work_order
                );
                
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;
                                
                $q = '
                    select 1
                    from customer_deposit cd
                        inner join rwo_cd on cd.id = rwo_cd.customer_deposit_id
                    where rwo_cd.refill_work_order_id = '.$db->escape($rwo_id).'
                        and cd.customer_deposit_status != "X"
                        and cd.status > 0
                ';
                if(count($db->query_array($q))>0){
                    $success = 0;
                    $msg[] = 'Customer Deposit'.' '.Lang::get('exists');
                }
                
                $q = '
                    select 1
                    from refill_work_order_product rwop
                    where rwop.refill_work_order_id = '.$db->escape($rwo_id).'
                        and refill_work_order_product_status not in ("initialized","ready_to_process")
                ';
                if(count($db->query_array($q))>0){
                    $success = 0;
                    $msg[] = 'Product '.' '.Lang::get('in process');
                }
                

                break;
            case 'refill_work_order_done':
            case 'refill_work_order_invoiced':
                $success = 0;
                $msg[] = 'Update '.'Refill - '.Lang::get('Work Order').' '.Lang::get('invalid');
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
        $db = new DB();
        $result = array();
        $refill_work_order_data = isset($data['refill_work_order'])?
            Tools::_arr($data['refill_work_order']):array();
        $refill_work_order_info_data = isset($data['refill_work_order_info'])?
            Tools::_arr($data['refill_work_order_info']):array();
        $rwo_product_data = isset($data['refill_work_order_product'])?
            Tools::_arr($data['refill_work_order_product']):array();
        $modid = User_Info::get()['user_id'];
        $datetime_curr = Date('Y-m-d H:i:s');
        switch($action){
            case 'refill_work_order_add':
                //<editor-fold defaultstate="collapsed">

                $refill_work_order = array();

                $refill_work_order = array(
                    'store_id'=>$refill_work_order_data['store_id'],
                    'customer_id'=>$refill_work_order_data['customer_id'],
                    'refill_work_order_date'=>$datetime_curr,
                    'refill_work_order_status'=>SI::status_default_status_get('Refill_Work_Order_Engine')['val'],
                    'notes'=>isset($refill_work_order_data['notes'])?
                        Tools::empty_to_null(Tools::_str($refill_work_order_data['notes'])):'',
                    'modid'=>$modid,
                    'status'=>'1',
                    'moddate'=>$datetime_curr,
                );

                $refill_work_order_info = array(
                    'number_of_product'=>$refill_work_order_info_data['number_of_product'],
                    'creator_id'=>User_Info::get()['user_id'],
                    'created_date'=>$datetime_curr,
                );
                
                $refill_work_order_product = array();
                $prefix_product_code = self::product_marking_code_prefix_get();
                
                $unit_id = $db->fast_get('unit', array('status'=>'1','code'=>'PCS'))[0]['id'];
                for($i = 0;$i<Tools::_int($refill_work_order_info['number_of_product']);$i++){
                    $refill_work_order_product[] = array(
                        'product_marking_code'=>$prefix_product_code.str_pad($i+1, 3, '0', STR_PAD_LEFT),
                        'qty'=>'1',
                        'qty_stock'=>'1',
                        'unit_id'=>$unit_id,
                        'refill_work_order_product_status'=>SI::type_default_type_get('refill_work_order_engine', '$rwo_product_status')['val']
                    );
                }
                $result['refill_work_order'] = $refill_work_order;                   
                $result['refill_work_order_info'] = $refill_work_order_info;                   
                $result['refill_work_order_product'] = $refill_work_order_product;
                //</editor-fold>
                break;
            case 'refill_work_order_initialized':
                //<editor-fold defaultstate="collapsed">
                $refill_work_order = array(
                    'notes'=>Tools::empty_to_null(isset($refill_work_order_data['notes'])?
                        Tools::_str($refill_work_order_data['notes']):''
                    ),
                    'refill_work_order_status'=>'initialized',
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr
                );
                $result['refill_work_order'] = $refill_work_order;
                //</editor-fold>
                break;
            case 'refill_work_order_process':                
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('refill_work_order/refill_work_order_engine');
                $refill_work_order = array();
                $rwo_product = array();
                
                //<editor-fold defaultstate="collapsed" desc="Refill Work Order">
                $refill_work_order = array(
                    'notes'=>isset($refill_work_order_data['notes'])?
                        Tools::empty_to_null(Tools::_str($refill_work_order_data['notes'])):null,
                    'refill_work_order_status'=>'process',
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                //</editor-fold>
                
                $total_estimated_amount = Tools::_float('0');
                
                //<editor-fold defaultstate="collapsed" desc="Refill Work Order Product">
                foreach($rwo_product_data as $idx=>$row){
                    $total_estimated_amount += Tools::_float($row['estimated_amount']);
                    $temp_product = array(
                        'id'=>$row['id'],
                        'refill_product_category_id'=>$row['rpc_id'],
                        'refill_product_medium_id'=>$row['rpm_id'],
                        'capacity_unit_id'=>$row['capacity_unit_id'],
                        'capacity'=>$row['capacity'],
                        'estimated_amount'=>$row['estimated_amount'],
                        'product_info_merk'=>$row['product_info_merk'],
                        'product_info_type'=>$row['product_info_type'],
                        'product_condition_description'=>isset($row['product_condition_description'])?
                            Tools::empty_to_null(Tools::_alpha_numeric($row['product_condition_description'])):null,
                        'staff_checker'=>$row['staff_checker'],
                        'refill_work_order_product_status'=>'ready_to_process',
                    );
                    
                    foreach(Refill_Work_Order_Engine::$product_condition as $idx=>$pdc){
                        $pdc_val = isset($row[$pdc['val']])? Tools::_str($row[$pdc['val']]) :'0';
                        if($pdc_val === '1') $temp_product[$pdc['val']] = '1';
                    }
                    
                    $rwo_product[] = $temp_product;
                }
                //</editor-fold>
                
                $refill_work_order['total_estimated_amount'] = $total_estimated_amount;
                
                $result['refill_work_order'] = $refill_work_order;  
                $result['refill_work_order_product'] = $rwo_product;
                
                //</editor-fold>
                break;
            case 'refill_work_order_canceled':
                $refill_work_order = array();

                $refill_work_order = array(
                    'refill_work_order_status'=>'X',
                    'cancellation_reason'=>$refill_work_order_data['cancellation_reason'],
                    'notes'=>isset($refill_work_order_data['notes'])?
                        Tools::_str($refill_work_order_data['notes']):'',
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                
                
                $result['refill_work_order'] = $refill_work_order;    
                break;
        }

        return $result;
        //</editor-fold>
    }

    public function refill_work_order_add($db,$final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $frefill_work_order = $final_data['refill_work_order'];
        $frefill_work_order_info = $final_data['refill_work_order_info'];
        $frefill_work_order_product = $final_data['refill_work_order_product'];

        $store_id = $frefill_work_order['store_id'];
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $refill_work_order_id = '';       
        $frefill_work_order['code'] = SI::code_counter_store_get($db,$store_id, 'refill_work_order');
        if(!$db->insert('refill_work_order',$frefill_work_order)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        $refill_work_order_code = $frefill_work_order['code'];

        if($success == 1){                                
            $refill_work_order_id = $db->fast_get('refill_work_order'
                    ,array('code'=>$refill_work_order_code))[0]['id'];
            $result['trans_id']=$refill_work_order_id; 
        }

        if($success === 1){
            $frefill_work_order_info['refill_work_order_id'] = $refill_work_order_id;
            if(!$db->insert('refill_work_order_info',$frefill_work_order_info)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }
        
        if($success === 1){
            foreach($frefill_work_order_product as $idx=>$product){
                $product['refill_work_order_id'] = $refill_work_order_id;
                if(!$db->insert('refill_work_order_product',$product)){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                    break;
                }
            }
        }
        
        if($success == 1){
            $refill_work_order_status_log = array(
                'refill_work_order_id'=>$refill_work_order_id
                ,'refill_work_order_status'=>$frefill_work_order['refill_work_order_status']
                ,'modid'=>$modid
                ,'moddate'=>$moddate    
            );

            if(!$db->insert('refill_work_order_status_log',$refill_work_order_status_log)){
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
    
    public static function refill_work_order_initialized($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $frefill_work_order = $final_data['refill_work_order'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $refill_work_order_id = $id;       
        
        if(!$db->update('refill_work_order',$frefill_work_order,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            $refill_work_order_status_log = array(
                'refill_work_order_id'=>$refill_work_order_id
                ,'refill_work_order_status'=>$frefill_work_order['refill_work_order_status']
                ,'modid'=>$modid
                ,'moddate'=>$moddate    
            );

            if(!$db->insert('refill_work_order_status_log',$refill_work_order_status_log)){
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
    
    public static function refill_work_order_process($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $frefill_work_order = $final_data['refill_work_order'];
        $frefill_work_order_product = $final_data['refill_work_order_product'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('refill_work_order',$frefill_work_order,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        foreach($frefill_work_order_product as $idx=>$rwo_product){
            if(!$db->update('refill_work_order_product',$rwo_product,array("id"=>$rwo_product['id']))){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
                break;
            }
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'refill_work_order',
                $id,$frefill_work_order['refill_work_order_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    public static function refill_work_order_done($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $frefill_work_order = $final_data['refill_work_order'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('refill_work_order',$frefill_work_order,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            $temp_result = SI::status_log_add($db,'refill_work_order',
                $id,$frefill_work_order['refill_work_order_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }
    
    function refill_work_order_canceled($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();
        
        $refill_work_order_id = $id;
        
        $frefill_work_order = $final_data['refill_work_order'];
        
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        $refill_work_order = $db->fast_get('refill_work_order',array('id'=>$refill_work_order_id))[0];
        
        if(!$db->update('refill_work_order',$frefill_work_order,array("id"=>$refill_work_order_id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if(!$db->update('refill_work_order_product',array('refill_work_order_product_status'=>'X'),array("refill_work_order_id"=>$refill_work_order_id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            $temp_result = SI::status_log_add($db,'refill_work_order',
                $refill_work_order_id,$refill_work_order['refill_work_order_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    public function total_deposit_amount_add($db, $amount,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();
        $refill_work_order_id = $id;
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $q = '
            update refill_work_order
            set total_deposit_amount = total_deposit_amount + '.$db->escape($amount).',
                modid = '.$db->escape($modid).',
                moddate = '.$db->escape($moddate).'
            where id = '.$db->escape($refill_work_order_id).'
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
    
    public function product_stock_add($db,$product_id,$qty){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();
        
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $q = '
            update refill_work_order_product
            set qty_stock = qty_stock + '.$db->escape($qty).'
            where id = '.$db->escape($product_id).'
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
    
    public static function product_status_set($db,$product_id,$status){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();
        
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $q = '
            update refill_work_order_product
            set refill_work_order_product_status = '.$db->escape($status).'
            where id = '.$db->escape($product_id).'
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
    
    public static function rwo_status_set($db,$rwo_id,$status){
        //<editor-fold defaultstate="collapsed" desc="Update RWO Status from outside module">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();
        
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $q = '
            update refill_work_order
            set refill_work_order_status = '.$db->escape($status).'
            where id = '.$db->escape($rwo_id).'
        ';
        
        if(!$db->query($q)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            $refill_work_order_status_log = array(
                'refill_work_order_id'=>$rwo_id
                ,'refill_work_order_status'=>$status
                ,'modid'=>$modid
                ,'moddate'=>$moddate    
            );

            if(!$db->insert('refill_work_order_status_log',$refill_work_order_status_log)){
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
}
?>