<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

define('PREFIX_ID', 'rswo');

class Refill_Subcon_Work_Order_Engine {
    public static $prefix_id = 'rswo';
    public static $prefix_method;
    public static $status_list;
    
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
                        array('val'=>'Add Refill -'),array('val'=>'Subcon Work Order'),array('val'=>'success')
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
                        array('val'=>'Update Refill -'),array('val'=>'Subcon Work Order'),array('val'=>'success')
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
                        array('val'=>'Cancel Refill -'),array('val'=>'Subcon Work Order'),array('val'=>'success')
                    )
                )
        )
        //</editor-fold>
        );
        //</editor-fold>
    }
    
    public static function refill_subcon_work_order_exists($id){
        //<editor-fold defaultstate="collapsed">
        $result = false;
        $db = new DB();
        $q = '
                select 1 
                from refill_subcon_work_order 
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
            'index'=>get_instance()->config->base_url().'refill_subcon_work_order/',
            'refill_subcon_work_order_engine'=>'refill_subcon_work_order/refill_subcon_work_order_engine',
            'refill_subcon_work_order_data_support' => 'refill_subcon_work_order/refill_subcon_work_order_data_support',
            'refill_subcon_work_order_renderer' => 'refill_subcon_work_order/refill_subcon_work_order_renderer',
            'ajax_search'=>get_instance()->config->base_url().'refill_subcon_work_order/ajax_search/',
            'data_support'=>get_instance()->config->base_url().'refill_subcon_work_order/data_support/',
        );

        return json_decode(json_encode($path));
    }

    public static function validate($method,$data=array()){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('refill_subcon_work_order/refill_subcon_work_order_data_support');
        get_instance()->load->helper('refill_subcon/refill_subcon_data_support');
        get_instance()->load->helper('refill_work_order/refill_work_order_data_support');
        get_instance()->load->helper('product/product_data_support');
        get_instance()->load->helper('product_stock_engine');
        $result = array(
            "success"=>1
            ,"msg"=>array()

        );
        $success = 1;
        $msg = array();
        $rswo = isset($data['rswo'])?$data['rswo']:array();
        $rswop = isset($data['rswo_product'])?$data['rswo_product']:array();
        $rswoepr = isset($data['rswo_expected_product_result'])?$data['rswo_expected_product_result']:array();

        $rswo_id = isset($rswo['id'])?Tools::_str($rswo['id']):'';
        
        switch($method){
            case self::$prefix_method.'_add':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                
                //<editor-fold defaultstate="collapsed" desc="Major Validation">
                $store_id = isset($rswo['store_id'])? Tools::_str($rswo['store_id']):'';
                $refill_subcon_id = isset($rswo['refill_subcon_id'])? Tools::_str($rswo['refill_subcon_id']):'';
                $rswo_date = isset($rswo['refill_subcon_work_order_date'])?
                    Tools::_str($rswo['refill_subcon_work_order_date']):'';
                if(!Store_Engine::store_exists($store_id)){
                    $success = 0;
                    $msg[] = Lang::get('Store').' '.Lang::get('empty',true,false);
                }

                if(!Refill_Subcon_Data_Support::refill_subcon_exists($refill_subcon_id)){
                    $success = 0;
                    $msg[] = Lang::get('Refill Subcon').' '.Lang::get('empty',true,false);
                }
                
                if(strtotime(Tools::_date($rswo_date,'Y-m-d H:i:s'))<strtotime(Tools::_date(null,'Y-m-d H:i:s'))){
                    $success =0 ;
                    $msg[] = Lang::get('Date ').' '.Lang::get('is lower than',true,false).' '.Lang::get(Tools::_date('','F d, Y H:i'));
                }
                
                if(count($rswop) === 0){
                    $success =0 ;
                    $msg[] = Lang::get('Product').' '.Lang::get('empty');
                }
                                
                if($success !== 1) break;
                //</editor-fold>
                
                $reg_p_arr = array();
                $rwo_p_arr = array();
                $reg_epr_arr = array();
                $rwo_epr_arr = array();
                $rwop_db = Refill_Work_Order_Data_Support::rwo_product_get_by_product_id($rswop, array('refill_work_order_status'=>'process'));
                
                //<editor-fold defaultstate="collapsed" desc="Validate RWO Product DB">
                foreach($rwop_db as $idx=>$row){
                    if($row['refill_work_order_product_status'] !== 'ready_to_process'){
                        $success = 0;
                        $msg[] = Lang::get('Work Order').' '.'Product Status'.' '.'invalid';
                        break;
                    }
                }
                //</editor-fold>
                
                //<editor-fold defaultstate="collapsed" desc="Product Validation">
                if($success === 1){
                    
                    foreach( array('rswop'=>$rswop,'rswoepr'=>$rswoepr) as $key=>$p){
                        //<editor-fold defaultstate="collapsed" desc="group product to registered or rwo product">
                        foreach($p as $idx=>$row){
                            $product_reference_type = Tools::empty_to_null(isset($row['product_reference_type'])?Tools::_str($row['product_reference_type']):'');
                            $product_reference_id = Tools::empty_to_null(isset($row['product_reference_id'])?Tools::_str($row['product_reference_id']):'');
                            $product_type = isset($row['product_type'])?Tools::_str($row['product_type']):'';
                            $product_id = isset($row['product_id'])?Tools::_str($row['product_id']):'';
                            $unit_id = isset($row['unit_id'])?Tools::_str($row['unit_id']):'';
                            $qty = isset($row['qty'])?Tools::_str($row['qty']):'0';

                            if(Tools::_float($qty)===Tools::_float('0')){
                                $success = 0;
                                $msg[] = 'Qty 0';
                            }

                            switch($product_type){
                                case 'registered_product':
                                    if($key === 'rswop')
                                        $reg_p_arr[] = array('product_reference_type'=>$product_reference_type,'product_reference_id'=>$product_reference_id, 'product_id'=>$product_id,'unit_id'=>$unit_id,'qty'=>$qty);
                                    else if ($key === 'rswoepr')
                                        $reg_epr_arr[] = array('product_reference_type'=>$product_reference_type,'product_reference_id'=>$product_reference_id,'product_id'=>$product_id,'unit_id'=>$unit_id,'qty'=>$qty);
                                    break;
                                case 'refill_work_order_product':
                                    if($key === 'rswop')
                                        $rwo_p_arr[] = array('product_reference_type'=>$product_reference_type,'product_reference_id'=>$product_reference_id,'product_id'=>$product_id,'unit_id'=>$unit_id,'qty'=>$qty);
                                    else if ($key === 'rswoepr')
                                        $rwo_epr_arr[] = array('product_reference_type'=>$product_reference_type,'product_reference_id'=>$product_reference_id,'product_id'=>$product_id,'unit_id'=>$unit_id,'qty'=>$qty);
                                    break;
                                default:
                                    $success = 0;
                                    $msg[] = 'Product Type invalid';
                                    break;
                            }
                            if($success !== 1) break;

                        }
                        //</editor-fold>
                    }
                    
                    //<editor-fold defaultstate="collapsed" desc="validate duplicate product">
                    $product_duplicate = false;
                    foreach(array($rwo_p_arr,$rwo_epr_arr,$reg_epr_arr) as $p){
                        for($i = 0;$i<count($p);$i++){
                            $product_id_i = $p[$i]['product_id'];
                            $unit_id_i = $p[$i]['unit_id'];
                            for($j = 0;$j<count($p);$j++){
                                $product_id_j = $p[$j]['product_id'];
                                $unit_id_j = $p[$j]['unit_id'];

                                if($i !== $j){
                                    if($product_id_i === $product_id_j && $unit_id_i === $unit_id_j){
                                        $product_duplicate = true;
                                        $success = 0;
                                        $msg[] = 'Product duplicate';
                                        break;
                                    }
                                }
                            }
                            if($success !== 1) break;
                        }
                        
                    }
                    
                    if(!$product_duplicate){
                        foreach($reg_p_arr as $idx=>$p){
                            $product_id = $p['product_id'];
                            $unit_id = $p['unit_id'];
                            $product_reference_type = $p['product_reference_type'];
                            $product_reference_id = $p['product_reference_id'];
                            foreach($reg_p_arr as $idx2=>$p2){
                                $product_id2 = $p2['product_id'];
                                $unit_id2 = $p2['unit_id'];
                                $product_reference_type2 = $p2['product_reference_type'];
                                $product_reference_id2 = $p2['product_reference_id'];
                                if($idx !== $idx2){
                                    if($product_id === $product_id2 && $unit_id === $unit_id2
                                        && $product_reference_id === $product_reference_id2
                                        && $product_reference_type === $product_reference_type2
                                    ){
                                        $product_duplicate = true;
                                        $success = 0;
                                        $msg[] = 'Product duplicate';
                                        break;
                                    }
                                }
                            }
                            if($success !== 1) break;
                        }
                    }    
                    
                    //</editor-fold>
                    
                    
                    //<editor-fold defaultstate="collapsed" desc="validate product is in database">
                    $t_product = array_merge($reg_p_arr,$reg_epr_arr);
                    if(!Product_Data_Support::product_unit_all_exists($t_product,array('product_status'=>'active'))){
                        $success = 0;
                        $msg[] = 'Product / Unit invalid';
                        
                    }
                    
                    if($success === 1){
                        $t_product = array_merge($rwo_p_arr,$rwo_epr_arr);
                        
                        if(!Refill_Subcon_Data_Support::product_unit_all_exists($t_product)){
                            $success = 0;
                            $msg[] = 'Product / Unit invalid';
                        }
                    }
                    //</editor-fold>
                    
                    //<editor-fold defaultstate="collapsed" desc="Validate Product Reference">
                    if($success === 1){
                        $product_cfg_db = Product_Data_Support::product_cfg_get($reg_p_arr, array());
                        
                        //<editor-fold defaultstate="collapsed" desc="Product Reference Required & Product Reference Type">
                        foreach($reg_p_arr as $idx=>$row){
                            $product_id = isset($row['product_id'])?Tools::_str($row['product_id']):'';
                            $unit_id = isset($row['unit_id'])?Tools::_str($row['unit_id']):'';
                            $product_reference_type = Tools::empty_to_null(isset($row['product_reference_type'])?
                                Tools::_str($row['product_reference_type']):'');
                            $product_reference_id = Tools::empty_to_null(isset($row['product_reference_id'])?
                                Tools::_str($row['product_reference_id']):'');
                            
                            $product_reference_req_valid = false;
                            
                            foreach($product_cfg_db as $idx2=>$row2){
                                $product_id_db = $row2['product_id'];
                                
                                if($product_id === $product_id_db){
                                    $rswo_product_reference_req = $row2['rswo_product_reference_req'];
                                    
                                    if($rswo_product_reference_req === 'yes'
                                        && !is_null($product_reference_type)
                                        && !is_null($product_reference_id)
                                    ){
                                        $product_reference_req_valid = true;
                                        break;
                                    }
                                    else if ($rswo_product_reference_req === 'no'
                                        && is_null($product_reference_type)
                                        && is_null($product_reference_id)
                                    ){
                                        $product_reference_req_valid = true;
                                        break;
                                    }
                                }
                            }
                            
                            if(!$product_reference_req_valid){
                                $success = 0;
                                $msg[] = 'Product Reference Required invalid';                                
                            }
                            
                            
                            
                            if($success !== 1) break;
                        }
                        //</editor-fold>
                    
                        //<editor-fold defaultstate="collapsed" desc="Product Reference">
                        if($success === 1){
                            //<editor-fold defaultstate="collapsed" desc="Refill Work Order Product">
                            $q_product = 'select -1 product_id';
                            $t_total_product_ref = Tools::_float('0');
                            $t_p_arr = array();
                            foreach($reg_p_arr as $idx=>$row){
                                $product_reference_type = isset($row['product_reference_type'])?
                                    Tools::_str($row['product_reference_type']):'';
                                $product_reference_id = isset($row['product_reference_id'])?
                                    Tools::_str($row['product_reference_id']):'';

                                if( $product_reference_type === "refill_work_order_product"
                                ){
                                    $t_total_product_ref+=Tools::_float('1');
                                    $q_product.=' union all select '.$db->escape($product_reference_id);
                                }
                            }
                            $q = '
                                select distinct dop.product_type, dop.product_id
                                from refill_subcon_work_order rswo
                                inner join rswo_do on rswo.id = rswo_do.refill_subcon_work_order_id
                                inner join delivery_order do on rswo_do.delivery_order_id = do.id
                                inner join delivery_order_product dop 
                                    on do.id = dop.delivery_order_id
                                inner join ('.$q_product.') tf 
                                    on tf.product_id = dop.product_id
                                    and dop.product_type = "refill_work_order_product"
                                where rswo.refill_subcon_work_order_status = "process"
                                    and do.delivery_order_status = "done"
                                    and do.status > 0
                            ';
                            $rs = $db->query_array($q);

                            foreach($t_p_arr as $idx=>$row){
                                $product_reference_type = isset($row['product_reference_type'])?
                                    Tools::_str($row['product_reference_type']):'';
                                $product_reference_id = isset($row['product_reference_id'])?
                                    Tools::_str($row['product_reference_id']):'';

                                $product_exists = false;
                                
                                foreach($rs as $idx2=>$row2){
                                    $product_type = $row2['product_type'];
                                    $product_id = $row2['product_id'];
                                    
                                    if($product_reference_type === $product_type 
                                        && $product_reference_id === $product_id
                                    ){
                                        $product_exists = true;
                                        break;
                                    }
                                }
                                
                                if(!$product_exists){
                                    $success = 0;
                                    $msg[] = 'Product Reference invalid';
                                }

                                if($success !== 1) break;
                            }
                            //</editor-fold>
                        }
                        //</editor-fold>
                    }
                    //</editor-fold>
                    
                    
                    //<editor-fold desc="validate product qty" defaultstate="collapsed">
                    foreach(
                        array(
                            array('type'=>'registered','data'=>$reg_p_arr),
                            array('type'=>'refill_work_order','data'=>$rwo_p_arr),
                        ) 
                        as $idx=>$p
                    ){
                        
                        if($p['type'] === 'registered'){
                            foreach($p['data'] as $idx=>$row){
                                $product_id = $row['product_id'];
                                $unit_id = $row['unit_id'];
                                $qty = $row['qty'];
                                $total_stock = Product_Stock_Engine::stock_sum_get('stock_sales_available',$product_id,$unit_id);

                                if(Tools::_float($qty)>Tools::_float($total_stock)){
                                    $success = 0;
                                    $msg[] = 'Product Qty invalid';
                                    break;
                                }
                            }
                        }
                        else if($p['type'] === 'refill_work_order'){
                            foreach($p['data'] as $idx=>$row){
                                $product_id = $row['product_id'];
                                $unit_id = $row['unit_id'];
                                $qty = $row['qty'];

                                $total_stock = '0';
                                $temp_rs = $db->fast_get('refill_work_order_product',array('id'=>$product_id,'unit_id'=>$unit_id));
                                if(count($temp_rs)>0) $total_stock = $temp_rs[0]['qty_stock'];

                                if(Tools::_float($qty)>Tools::_float($total_stock)){
                                    $success = 0;
                                    $msg[] = 'Product Qty invalid';
                                    break;
                                }
                            }
                        }
                        if($success !== 1) break;
                        
                    }
                    //</editor-fold>

                    //<editor-fold desc="validate mismatch SPK product & expected SPK product result" defaultstate="collapsed">
                    foreach(
                        array(
                            array('src'=>$rwo_p_arr,'dst'=>$rwo_epr_arr),
                            array('src'=>$rwo_epr_arr,'dst'=>$rwo_p_arr),
                        )
                        as $idx=>$row
                    ){
                        $src = $row['src'];
                        $dst = $row['dst'];
                        foreach($src as $idx=>$row){
                            $exists = false;
                            foreach($dst as $idx2=>$row2){
                                if($row['product_id'] === $row2['product_id'] && $row['unit_id'] === $row['unit_id']){
                                    $exists = true;
                                }
                            }
                            if(!$exists){
                                $success = 0;
                                $msg[] = Lang::get('Product SPK').' '.Lang::get('and',true,false).' '.Lang::get(array('Product Result','Expectation')).' SPK '.Lang::get('mismatch',true,false);

                            }
                            if($success !== 1) break;
                        }
                        if($success !== 1) break;
                        
                    }
                    //</editor-fold>
                    
                }
                //</editor-fold>
                
                //</editor-fold>
                break;
            
            case self::$prefix_method.'_done':
                $temp_result = Validator::validate_on_update(
                    array(
                        'module'=>'refill_subcon_work_order',
                        'module_name'=>Lang::get('Subcon Work Order'),
                        'module_engine'=>'Refill_Subcon_Work_Order_Engine',
                    ),
                    $rswo
                );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;
                    
                break;
            
            case self::$prefix_method.'_canceled':
                $db = new DB();
                $temp_result = Validator::validate_on_cancel(
                    array(
                        'module'=>'refill_subcon_work_order',
                        'module_name'=>Lang::get('Subcon Work Order'),
                        'module_engine'=>'Refill_Subcon_Work_Order_Engine',
                    ),
                    $rswo
                );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;
                
                $q = '
                    select 1
                    from rswo_do 
                        inner join delivery_order do on rswo_do.delivery_order_id = do.id
                    where rswo_do.refill_subcon_work_order_id = '.$db->escape($rswo_id).'
                        and do.delivery_order_status != "X"
                ';
                if(count($db->query_array($q))>0){
                    $success = 0;
                    $msg[] = Lang::get('Delivery Order').' '.Lang::get('exists');
                }
                
                $q = '
                    select 1
                    from rswo_rp 
                        inner join receive_product rp on rswo_rp.receive_product_id = rp.id
                    where rswo_rp.refill_subcon_work_order_id = '.$db->escape($rswo_id).'
                        and rp.receive_product_status != "X"
                ';
                if(count($db->query_array($q))>0){
                    $success = 0;
                    $msg[] = Lang::get('Receive Product').' '.Lang::get('exists');
                }
                
                //<editor-fold defaultstate="collapsed" desc="Check Product is being refered to another RSWO">
                $q = '
                    select distinct rswo.code
                    from rswo_product rswop
                    inner join refill_subcon_work_order rswo 
                        on rswo.id = rswop.refill_subcon_work_order_id
                    inner join (
                        select distinct rswop.product_id
                        from rswo_product rswop
                        inner join refill_subcon_work_order rswo 
                            on rswo.id = rswop.refill_subcon_work_order_id
                            and rswop.refill_subcon_work_order_id = '.$db->escape($rswo_id).'
                            and rswop.product_type = "refill_work_order_product"
                    ) t1 
                        on rswop.product_reference_type = "refill_work_order_product"
                        and rswop.product_reference_id = t1.product_id
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $success = 0;
                    foreach($rs as $idx=>$row){
                        $msg[] = Lang::get('Product ').' '.Lang::get('is being refered to',true,false).' '.Lang::get($rs['code']);
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
        $rswo_data = isset($data['rswo'])?
            Tools::_arr($data['rswo']):array();
        $rswo_product_data = isset($data['rswo_product'])?
            Tools::_arr($data['rswo_product']):array();
        $rswo_expected_product_result_data = isset($data['rswo_expected_product_result'])?
            Tools::_arr($data['rswo_expected_product_result']):array();
        $modid = User_Info::get()['user_id'];
        $datetime_curr = Date('Y-m-d H:i:s');
        switch($action){
            case self::$prefix_method.'_add':
                //<editor-fold defaultstate="collapsed">

                $rswo = array(
                    'store_id'=>$rswo_data['store_id'],
                    'refill_subcon_id'=>$rswo_data['refill_subcon_id'],
                    'refill_subcon_work_order_date'=>$rswo_data['refill_subcon_work_order_date'],
                    'refill_subcon_work_order_status'=>SI::status_default_status_get('Refill_Subcon_Work_Order_Engine')['val'],
                    'notes'=>isset($rswo_data['notes'])?
                        Tools::empty_to_null(Tools::_str($rswo_data['notes'])):null,
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );

                $rswo_product = array();
                foreach($rswo_product_data as $idx=>$row){
                    $t_rswo_product = array(
                        'product_type'=>Tools::_str($row['product_type']),
                        'product_id'=>Tools::_str($row['product_id']),
                        'unit_id'=>Tools::_str($row['unit_id']),
                        'qty'=>Tools::_str($row['qty']),
                        'movement_outstanding_qty'=>Tools::_str($row['qty']),
                        'product_reference_type'=>null,
                        'product_reference_id'=>null,
                    );
                    $t_product_reference_type = Tools::empty_to_null(Tools::_str($row['product_reference_type']));
                    if(!is_null($t_product_reference_type)){
                        $t_rswo_product['product_reference_type'] = $row['product_reference_type'];
                        $t_rswo_product['product_reference_id'] = $row['product_reference_id'];
                    }
                    
                    $rswo_product[] = $t_rswo_product;
                }
                
                $rswo_expected_product_result = array();
                foreach($rswo_expected_product_result_data as $idx=>$row){
                    $rswo_expected_product_result[] = array(
                        'product_type'=>Tools::_str($row['product_type']),
                        'product_id'=>Tools::_str($row['product_id']),
                        'unit_id'=>Tools::_str($row['unit_id']),
                        'qty'=>Tools::_str($row['qty']),
                        'movement_outstanding_qty'=>Tools::_str($row['qty']),
                    );
                }
                
                $result['refill_subcon_work_order'] = $rswo;
                $result['rswo_product'] = $rswo_product;
                $result['rswo_expected_product_result'] = $rswo_expected_product_result;
                
                //</editor-fold>
                break;
            case self::$prefix_method.'_done':                
                $rswo = array();

                $rswo = array(
                    'notes'=>isset($rswo_data['notes'])?
                        Tools::_str($rswo_data['notes']):'',
                    'refill_subcon_work_order_status'=>'done',
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                $result['refill_subcon_work_order'] = $rswo;    
                break;
            case self::$prefix_method.'_canceled':
                $rswo = array();

                $rswo = array(
                    'refill_subcon_work_order_status'=>'X',
                    'cancellation_reason'=>$rswo_data['cancellation_reason'],
                    'notes'=>isset($rswo_data['notes'])?
                        Tools::empty_to_null($rswo_data['notes']):null,
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                $result['refill_subcon_work_order'] = $rswo;    
                break;
        }

        return $result;
        //</editor-fold>
    }

    public function rswo_add($db,$final_data){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $frefill_subcon_work_order = $final_data['refill_subcon_work_order'];
        $frswo_product = $final_data['rswo_product'];
        $frswo_expected_product_result = $final_data['rswo_expected_product_result'];

        $store_id = $frefill_subcon_work_order['store_id'];
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        get_instance()->load->helper('refill_work_order/refill_work_order_engine');
        
        $rswo_id = '';       
        $frefill_subcon_work_order['code'] = SI::code_counter_store_get($db,$store_id, 'refill_subcon_work_order');
        if(!$db->insert('refill_subcon_work_order',$frefill_subcon_work_order)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $rswo_code = $frefill_subcon_work_order['code'];

        if($success == 1){                                
            $rswo_id = SI::get_trans_id($db,'refill_subcon_work_order','code',$rswo_code);
            $result['trans_id']=$rswo_id; 
        }
        
        
        if($success == 1){
            $refill_subcon_work_order_status_log = array(
                'refill_subcon_work_order_id'=>$rswo_id
                ,'refill_subcon_work_order_status'=>$frefill_subcon_work_order['refill_subcon_work_order_status']
                ,'modid'=>$modid
                ,'moddate'=>$moddate    
            );

            if(!$db->insert('refill_subcon_work_order_status_log',$refill_subcon_work_order_status_log)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }
        
        if($success === 1){
            //<editor-fold defaultstate="collapsed" desc="RSWO Product & RWO Product">
            foreach($frswo_product as $idx=>$product){
                $product_type = $product['product_type'];
                $product['refill_subcon_work_order_id'] = $rswo_id;
                if(!$db->insert('rswo_product',$product)){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                    break;
                }
                
                if($product_type === 'refill_work_order_product'){
                    $temp_result = Refill_Work_Order_Engine::product_status_set($db, $product['product_id'], 'process');
                    $success = $temp_result['success'];
                    $msg = array_merge($msg, $temp_result['msg']);
                    
                    if($success !== 1) break;
                }
            }
            //</editor-fold>
        }

        if($success === 1){
            //<editor-fold defaultstate="collapsed" desc="RSWO Expected Product Result">
            foreach($frswo_expected_product_result as $idx=>$product){
                $product['refill_subcon_work_order_id'] = $rswo_id;
                if(!$db->insert('rswo_expected_product_result',$product)){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                    break;
                }
            }
            //</editor-fold>
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    function rswo_done($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $frefill_subcon_work_order = $final_data['refill_subcon_work_order'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('refill_subcon_work_order',$frefill_subcon_work_order,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        

        if($success == 1){
            $temp_result = SI::status_log_add($db,'refill_subcon_work_order',
                $id,$frefill_subcon_work_order['refill_subcon_work_order_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    function rswo_canceled($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('refill_subcon_work_order/refill_subcon_work_order_data_support');
        get_instance()->load->helper('refill_work_order/refill_work_order_engine');
        get_instance()->load->helper('refill_work_order/refill_work_order_data_support');
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();
        
        $refill_subcon_work_order_id = $id;
        
        $frefill_subcon_work_order = $final_data['refill_subcon_work_order'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        $refill_subcon_work_order = $db->fast_get('refill_subcon_work_order',array('id'=>$refill_subcon_work_order_id))[0];
        $rswo_product_db = Refill_Subcon_Work_Order_Data_Support::rswo_product_get($refill_subcon_work_order_id);
        //$pure_amount = Tools::_float($refill_subcon_work_order['amount']) - Tools::_float($refill_subcon_work_order['change_amount']);
        
        if(!$db->update('refill_subcon_work_order',$frefill_subcon_work_order,array("id"=>$refill_subcon_work_order_id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'refill_subcon_work_order',
                $refill_subcon_work_order_id,$refill_subcon_work_order['refill_subcon_work_order_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
                
        if($success === 1){
            $rwo_product_status = 'ready_to_process';
            foreach($rswo_product_db as $idx=>$row){
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
    
    function movement_outstanding_qty_add($db,$tbl,$rswo_id,$reference_id,$qty){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();
        
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $q = '
            update rswo_'.$tbl.'
            set movement_outstanding_qty = movement_outstanding_qty + '.$db->escape($qty).'
            where refill_subcon_work_order_id = '.$db->escape($rswo_id).'
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