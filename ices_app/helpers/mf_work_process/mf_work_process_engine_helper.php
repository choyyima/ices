<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mf_Work_Process_Engine {
    public static $prefix_id = 'mf_work_process';
    public static $prefix_method;
    public static $status_list;
    public static $module_type_list;
    public static $stock_location_list;

    public static function helper_init(){
        //<editor-fold defaultstate="collapsed">
        self::$prefix_method = self::$prefix_id;
        
        self::$status_list = array(
            //<editor-fold defaultstate="collapsed">
            array(
                'val'=>''
                ,'label'=>''
                ,'method'=>'mf_work_process_add'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Add')
                        ,array('val'=>Lang::get(array('Manufacturing - Work Process'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            array(//label name is used for method name
                'val'=>'process'
                ,'label'=>'PROCESS'
                ,'method'=>'mf_work_process_process'
                ,'next_allowed_status'=>array('done','X')
                ,'default'=>true
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update')
                        ,array('val'=>Lang::get(array('Manufacturing - Work Process'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            array(//label name is used for method name
                'val'=>'done'
                ,'label'=>'DONE'
                ,'method'=>'mf_work_process_done'
                ,'next_allowed_status'=>array('X')
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update')
                        ,array('val'=>Lang::get(array('Manufacturing - Work Process'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            )
            ,array(
                'val'=>'X'
                ,'label'=>'CANCELED'
                ,'method'=>'mf_work_process_canceled'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Cancel')
                        ,array('val'=>Lang::get(array('Manufacturing - Work Process'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ) 
            //</editor-fold>
        );
       
        self::$module_type_list = array(
            //<editor-fold defaultstate="collapsed">
            array(
                'val'=>'normal','label'=>'Normal',
                'default_component_product_stock_location'=>'good_stock',
                'default_result_product_stock_location'=>'good_stock'
            ),
            array(
                'val'=>'good_stock_transform','label'=>'Good Stock Transformation',
                'default_component_product_stock_location'=>'bad_stock',
                'default_result_product_stock_location'=>'good_stock'
            ),
            array(
                'val'=>'bad_stock_transform','label'=>'Bad Stock Transformation',
                'default_component_product_stock_location'=>'good_stock',
                'default_result_product_stock_location'=>'bad_stock'
            ),
            //</editor-fold>
        );
        
        self::$stock_location_list = array(
            array('val'=>'good_stock','label'=>'Good Stock','stock_db'=>array('stock_sales_available','stock_good')),
            array('val'=>'bad_stock','label'=>'Bad Stock','stock_db'=>array('stock_bad')),
        );
        
        //</editor-fold>
    }
    
    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'mf_work_process/'
            ,'mf_work_process_engine'=>'mf_work_process/mf_work_process_engine'
            ,'mf_work_process_data_support'=>'mf_work_process/mf_work_process_data_support'
            ,'mf_work_process_renderer' => 'mf_work_process/mf_work_process_renderer'
            ,'ajax_search'=>get_instance()->config->base_url().'mf_work_process/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'mf_work_process/data_support/'

        );

        return json_decode(json_encode($path));
    }

    public static function delete_all_related_table_records($db, $mf_work_process_id){
        //<editor-fold defaultstate="collapsed">
        $success = 1;
        $msg = array();
        $result = array('success'=>$success,'msg' => $msg);
        
        $q = 'delete from mf_work_process_component_product where mf_work_process_id = '.$db->escape($mf_work_process_id);
        if(!$db->query($q)){
            $success = 0;
            $msg[] = $db->_error_message();
            $db->trans_rollback();
        }
        
        if($success === 1){
            $q = 'delete from mf_work_process_result_product where mf_work_process_id = '.$db->escape($mf_work_process_id);
            if(!$db->query($q)){
                $success = 0;
                $msg[] = $db->_error_message();
                $db->trans_rollback();
            }
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    

    public static function validate($action,$data=array()){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('mf_work_process/mf_work_process_data_support');
        get_instance()->load->helper('product/product_data_support');
        
        $result = array(
            "success"=>1
            ,"msg"=>array()
        );
        $success = 1;
        $msg = array();
        
        $mf_work_process = isset($data['mf_work_process'])?Tools::_arr($data['mf_work_process']):null;
        $mfwp_info = isset($data['mfwp_info'])?Tools::_arr($data['mfwp_info']):array();
        $mfwp_worker = isset($data['mfwp_worker'])?Tools::_arr($data['mfwp_worker']):array();
        $mfwp_checker = isset($data['mfwp_checker'])?Tools::_arr($data['mfwp_checker']):array();
        $mfwp_expected_result_product = isset($data['mfwp_expected_result_product'])?Tools::_arr($data['mfwp_expected_result_product']):array();
        $mfwp_result_product = isset($data['mfwp_result_product'])?Tools::_arr($data['mfwp_result_product']):array();
        $mfwp_scrap_product = isset($data['mfwp_scrap_product'])?Tools::_arr($data['mfwp_scrap_product']):array();
        $mfwp_component_product = isset($data['mfwp_component_product'])?Tools::_arr($data['mfwp_component_product']):array();
        $mf_work_process_type = isset($mf_work_process['mf_work_process_type'])?Tools::_str($mf_work_process['mf_work_process_type']):'';
        $mf_work_process_id = $data['mf_work_process']['id'];
        $sir = isset($data['sir'])?Tools::_arr($data['sir']):array();
        $mf_work_process_db = Mf_Work_Process_Data_Support::mf_work_process_get($mf_work_process_id);
        $mfwp_info_db = Mf_Work_Process_Data_Support::mfwp_info_get($mf_work_process_id);
        $db = new DB();
        switch($action){
            case self::$prefix_method.'_add':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('mf_work_order/mf_work_order_data_support');
                $reference_id = isset($mf_work_process['reference_id'])?Tools::_str($mf_work_process['reference_id']):'';
                $store_id = isset($mf_work_process['store_id'])?Tools::_str($mf_work_process['store_id']):'';
                $warehouse_id = isset($mfwp_info['warehouse_id'])?Tools::_bool($mfwp_info['warehouse_id']):false;
                $sir_exists = isset($mfwp_info['sir_exists'])?Tools::_bool($mfwp_info['sir_exists']):false;
                $sir_creator = isset($sir['creator'])?
                    Tools::empty_to_null(Tools::_str($sir['creator'])):null;
                $sir_description = isset($sir['description'])?
                    Tools::empty_to_null(Tools::_str($sir['description'])):null;
                $available_expected_result_product = Mf_Work_Process_Data_Support::
                    available_expected_result_product_get($reference_id, $warehouse_id);
                $available_component_product = Mf_Work_Process_Data_Support::
                    available_component_product_get(
                        $mf_work_process_type,$warehouse_id,$mfwp_expected_result_product
                    );
                //<editor-fold defaultstate="collapsed" desc="Major Validation">
                              
                if(!Store_Engine::store_exists($store_id)){
                    $success = 0;
                    $msg[] = Lang::get('Store').' '.Lang::get('empty',true,false);
                }
                
                if(!Warehouse_Engine::is_type('BOS',$warehouse_id)){
                    $success = 0;
                    $msg[] = Lang::get('Warehouse').' '.Lang::get('empty',true,false);
                }
                
                if(!SI::type_match('Mf_Work_Process_Engine',$mf_work_process_type)){
                    $success = 0;
                    $msg[] = Lang::get(array('Module Type','invalid'),true,true,false,false,true);
                }
                
                if(in_array($mf_work_process_type, 
                    array('normal','bad_stock_transform','good_stock_transform')
                )){
                    if(!Mf_Work_Order_Data_Support::mf_work_order_exists($reference_id)){
                        $success = 0;
                        $msg[] = Lang::get('Reference').' '.Lang::get('empty',true,false);
                    }
                }
                else{
                    $success = 0;
                    $msg[] = Lang::get('Reference').' '.Lang::get('empty',true,false);
                }
                    
                if(!count($mfwp_component_product)>0){
                    $success = 0;
                    $msg[] = 'Product Component'.' '.Lang::get('empty',true,false);
                }
                
                if(!count($mfwp_expected_result_product)>0){
                    $success = 0;
                    $msg[] = Lang::get(array('Expected','Result','Product')).' '.Lang::get('empty');
                }
                
                if($success !== 1) break;
                //</editor-fold>

                if($sir_exists){
                    //<editor-fold defaultstate="collapsed">
                    if($sir_creator === null || $sir_description === null){
                        $success = 0;
                        $msg[] = Lang::get(array('System Investigation Report','-','Creator / Description','empty'),true,true,false,false,true);
                    }
                    //</editor-fold>
                }
                else{
                    
                    foreach($mfwp_expected_result_product as $i=>$row){
                        //<editor-fold defaultstate="collapsed" desc="Validate Expected Result Product">
                        $row_reference_type = isset($row['reference_type'])?Tools::_str($row['reference_type']):'';
                        $row_reference_id = isset($row['reference_id'])?Tools::_str($row['reference_id']):'';
                        $row_product_type = isset($row['product_type'])?Tools::_str($row['product_type']):'';
                        $row_product_id = isset($row['product_id'])?Tools::_str($row['product_id']):'';
                        $row_unit_id = isset($row['unit_id'])?Tools::_str($row['unit_id']):'';
                        $row_qty = isset($row['qty'])?Tools::_str($row['qty']):'0';

                        $reference_exists = false;

                        $available_reference_type = '';
                        $available_reference_id = '';
                        $available_product_type = '';
                        $available_product_id = '';
                        $available_unit_id = '';
                        $available_max_qty = '0';
                        
                        foreach($available_expected_result_product as $i2=>$row2){
                            $available_reference_type = $row2['reference_type'];
                            $available_reference_id = $row2['reference_id'];
                            $available_product_type = $row2['product_type'];
                            $available_product_id = $row2['product_id'];
                            $available_unit_id = $row2['unit_id'];
                            $available_max_qty = $row2['max_qty'];

                            if($row_reference_type === $available_reference_type 
                                && $row_reference_id === $available_reference_id
                                && $row_product_type === $available_product_type
                                && $row_product_id === $available_product_id
                                && $row_unit_id === $available_unit_id
                            ){
                                $reference_exists = true;
                                break;
                            }
                        }
                        if(!$reference_exists || Tools::_float($row_qty)>Tools::_float($available_max_qty)){
                            $success = 0;
                            $msg[] = Lang::get(array('Expected','Result','Product')).' '.Lang::get('invalid',true,false);
                            break;
                        }
                        else if (!Tools::_float($row_qty)>Tools::_float('0')){
                            $success = 0;
                            $msg[] = Lang::get(array('Expected','Result','Product','Qty')).' '.Lang::get('0');
                            break;
                        }
                        //</editor-fold>
                    }
                    if($success === 1){
                        //<editor-fold defaultstate="collapsed" desc="Validate Component Product">
                        
                        foreach($mfwp_component_product as $i=>$row){
                            $row_product_type = isset($row['product_type'])?
                                Tools::_str($row['product_type']):'';
                            $row_product_id = isset($row['product_id'])?
                                Tools::_str($row['product_id']):'';
                            $row_unit_id = isset($row['unit_id'])?
                                Tools::_str($row['unit_id']):'';
                            $row_qty = isset($row['qty'])?
                                Tools::_str($row['qty']):'0';
                            $row_stock_location = isset($row['stock_location'])?
                                Tools::_str($row['stock_location']):'';
                            $product_exists = false;
                            
                            foreach($available_component_product as $i2=>$row2){
                                $available_product_type = $row2['product_type'];
                                $available_product_id = $row2['product_id'];
                                $available_unit_id = $row2['unit_id'];
                                $available_stock_location = $row2['stock_location'];

                                if($row_product_type === $available_product_type
                                    && $row_product_id === $available_product_id
                                    && $row_unit_id === $available_unit_id
                                    && $row_stock_location === $available_stock_location
                                ){
                                    $product_exists = true;
                                    break;
                                }
                            }

                            if(!$product_exists){
                                $success = 0;
                                $msg[] = Lang::get(array('Component','Product')).' '.Lang::get('invalid',true,false);
                                break;
                            }
                        }
                        //</editor-fold>
                    }
                    
                    
                }
                
                //<editor-fold defaultstate="collapsed" desc="Validate Component Product">
                foreach($mfwp_component_product as $i=>$row){
                    $row_product_type = isset($row['product_type'])?
                        Tools::_str($row['product_type']):'';
                    $row_product_id = isset($row['product_id'])?
                        Tools::_str($row['product_id']):'';
                    $row_unit_id = isset($row['unit_id'])?
                        Tools::_str($row['unit_id']):'';
                    $row_qty = isset($row['qty'])?
                        Tools::_str($row['qty']):'0';
                    $row_stock_location = isset($row['stock_location'])?
                        Tools::_str($row['stock_location']):'';
                    $product_exists = false;
                    
                    $warehouse_stock_qty = 0;
                    switch($row_stock_location){
                        case 'good_stock':
                            $warehouse_stock_qty = Product_Stock_Engine::stock_sum_get('stock_sales_available', $row_product_id, $row_unit_id);
                            break;
                        case 'bad_stock':
                            $warehouse_stock_qty = Product_Stock_Engine::stock_sum_get('stock_bad', $row_product_id, $row_unit_id);                            
                            break;
                    }
                    
                    foreach($mfwp_component_product as $i2=>$row2){
                        $row_product_type2 = isset($row2['product_type'])?
                            Tools::_str($row2['product_type']):'';
                        $row_product_id2 = isset($row2['product_id'])?
                            Tools::_str($row2['product_id']):'';
                        $row_unit_id2 = isset($row2['unit_id'])?
                            Tools::_str($row2['unit_id']):'';
                        $row_qty2 = isset($row2['qty'])?
                            Tools::_str($row2['qty']):'0';
                        $row_stock_location2 = isset($row2['stock_location'])?
                            Tools::_str($row2['stock_location']):'';

                        if($i!== $i2 && (
                            $row_product_type2 === $row_product_type &&
                            $row_product_id2 === $row_product_id &&
                            $row_unit_id2 === $row_unit_id &&
                            $row_stock_location2 === $row_stock_location2
                        )){
                            $success = 0;
                            $msg[] = 'Product Component and Stock Location'.' '.'duplicated';
                            break;
                        }
                    }
                    if($success !== 1) break;
                    
                    if (!Tools::_float($row_qty)> Tools::_float('0')){
                        $success = 0;
                        $msg[] = Lang::get(array('Component','Product','Qty')).' '.Lang::get('0',true,false);
                        break;
                    }
                    else if (Tools::_float($row_qty)>Tools::_float($warehouse_stock_qty)){
                        $success = 0;
                        $msg[] = Lang::get(array('Component','Product','is greater than','stock qty'),true,true,false,false,true);
                        break;
                    }

                }
                //</editor-fold>
                
                if(!count($mfwp_worker) > 0){
                    $success = 0;
                    $msg[] = Lang::get('Worker').' '.Lang::get('empty');
                }
                else{
                    foreach($mfwp_worker as $i=>$row){
                        $worker_name = isset($row['name'])?
                            Tools::empty_to_null(Tools::_str($row['name'])):null;
                        if($worker_name === null){
                            $success = 0;
                            $msg[] = Lang::get(array('Worker','Name')).' '.Lang::get('empty',true,false);
                            break;
                        }
                    }
                }
                //</editor-fold>
                break;
            case self::$prefix_method.'_process':
                //<editor-fold defaultstate="collapsed">
                $temp_result = Validator::validate_on_update(
                    array(
                        'module'=>'mf_work_process',
                        'module_name'=>Lang::get('Manufacturing Work Process'),
                        'module_engine'=>'mf_work_process_engine',
                    ),
                    $mf_work_process
                );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;
                //</editor-fold>
                break;
            case self::$prefix_method.'_done':
                //<editor-fold defaultstate="collapsed">
                $sir_exists = isset($mfwp_info['sir_exists'])?Tools::_bool($mfwp_info['sir_exists']):false;
                $sir_creator = isset($sir['creator'])?
                    Tools::empty_to_null(Tools::_str($sir['creator'])):null;
                $sir_description = isset($sir['description'])?
                    Tools::empty_to_null(Tools::_str($sir['description'])):null;
                $checker_name = isset($mfwp_checker['name'])?
                    Tools::empty_to_null(Tools::_str($mfwp_checker['name'])):null;                
                $available_result_product = Mf_Work_Process_Data_Support::
                    available_result_product_get($mf_work_process_id);
                $available_scrap_product = Mf_Work_Process_Data_Support::
                    available_scrap_product_get($mf_work_process_id);
                
                $temp_result = Validator::validate_on_update(
                    array(
                        'module'=>'mf_work_process',
                        'module_name'=>Lang::get('Manufacturing Work Process'),
                        'module_engine'=>'mf_work_process_engine',
                    ),
                    $mf_work_process
                );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                
                if(!count($mfwp_result_product) > 0 ){
                    $success === 0;
                    $msg[] = Lang::get(array('Result','Product')).' '.Lang::get('empty',true,false);

                }
                
                if($success !== 1) break;

                if($mf_work_process_db['mf_work_process_status'] === 'process'){
                    if($checker_name === null){
                        $success = 0;
                        $msg[] = Lang::get(array('Checker')).' '.Lang::get('empty',true,false);
                    }
                    
                    if($mfwp_info_db['sir_exists'] === '1' && $sir_exists !== true){
                        $success = 0;
                        $msg[] = Lang::get(array('System Investigation Report')).' '.Lang::get('invalid',true,false);
                    }
                    
                    if(($mfwp_info_db['sir_exists'] !== '1') && $sir_exists === true){
                        if($sir_creator === null || $sir_description === null){
                            $success = 0;
                            $msg[] = Lang::get(array('System Investigation Report','-','Creator / Description','empty'),true,true,false,false,true);
                        }
                    }
                    
                    if($mfwp_info_db['sir_exists'] !== '1' && $sir_exists === false){
                        //<editor-fold defaultstate="collapsed" desc="Result Product">
                        
                        if( count($mfwp_result_product)!== count($available_result_product)){
                            $success === 0;
                            $msg[] = Lang::get(array('Result','Product','Total')).' '.Lang::get('invalid',true,false);
                        }
                        else{
                            foreach($mfwp_result_product as $i=>$row){
                                $row_product_type = isset($row['product_type'])?Tools::_str($row['product_type']):'';
                                $row_product_id = isset($row['product_id'])?Tools::_str($row['product_id']):'';
                                $row_unit_id = isset($row['unit_id'])?Tools::_str($row['unit_id']):'';
                                $row_qty = isset($row['qty'])?Tools::_str($row['qty']):'0';
                                $row_stock_location = isset($row['stock_location'])?Tools::_str($row['stock_location']):'';
                                $product_exists = false;                                
                                
                                $available_product_type = '';
                                $available_product_id = '';
                                $available_unit_id = '';
                                $available_qty = '0';
                                $available_stock_location = '';
                                
                                foreach($available_result_product as $i2=>$row2){
                                    $available_product_type = isset($row2['product_type'])?Tools::_str($row2['product_type']):'';
                                    $available_product_id = isset($row2['product_id'])?Tools::_str($row2['product_id']):'';
                                    $available_unit_id = isset($row2['unit_id'])?Tools::_str($row2['unit_id']):'';
                                    $available_qty = isset($row2['qty'])?Tools::_str($row2['qty']):'0';
                                    $available_stock_location = isset($row2['stock_location'])?Tools::_str($row2['stock_location']):'';
                                    
                                    if(
                                        $row_product_type === $available_product_type
                                        && $row_product_id === $available_product_id
                                        && $row_unit_id === $available_unit_id
                                        && Tools::_float($row_qty) === Tools::_float($available_qty)
                                        && $row_stock_location === $available_stock_location
                                    ){
                                        $product_exists = true;
                                        break;
                                    }
                                    
                                }
                                if(!$product_exists || Tools::_float($row_qty)!== Tools::_float($available_qty)){
                                    $success = 0;
                                    $msg[] = Lang::get(array('Result','Product')).' '.Lang::get('invalid',true,false);
                                    break;
                                }
                            }
                        }
                        //</editor-fold>
                        
                        //<editor-fold defaultstate="collapsed" desc="Scrap Product">
                        foreach($mfwp_scrap_product as $idx=>$row){
                            $row_product_type = isset($row['product_type'])?Tools::_str($row['product_type']):'';
                            $row_product_id = isset($row['product_id'])?Tools::_str($row['product_id']):'';
                            $row_unit_id = isset($row['unit_id'])?Tools::_str($row['unit_id']):'';
                            $row_qty = isset($row['qty'])?Tools::_str($row['qty']):'0';
                            $row_stock_location = isset($row['stock_location'])?Tools::_str($row['stock_location']):'';
                            
                            if(Tools::_float($row_qty)>Tools::_float('0')){
                                $success = 0;
                                $msg[] = Lang::get(array('Scrap Product','Qty'),true,true,false,false,true).' '.Lang::get('must be').' 0';
                            }
                        }
                        //</editor-fold>
                    
                    }
                    
                    //<editor-fold defaultstate="collapsed" desc="Result Product">

                    if(!Product_Data_Support::product_unit_all_exists($mfwp_result_product,array('product_status'=>'active'))){
                        $success = 0;
                        $msg[] = Lang::get(array('Result','Product')).' '.Lang::get('invalid',true,false);
                    }
                    else{
                        foreach($mfwp_result_product as $i=>$row){
                            $row_product_type = isset($row['product_type'])?Tools::_str($row['product_type']):'';
                            $row_product_id = isset($row['product_id'])?Tools::_str($row['product_id']):'';
                            $row_unit_id = isset($row['unit_id'])?Tools::_str($row['unit_id']):'';
                            $row_qty = isset($row['qty'])?Tools::_str($row['qty']):'0';
                            $row_stock_location = isset($row['stock_location'])?Tools::_str($row['stock_location']):'';
                            $product_exists = false;

                            foreach($mfwp_result_product as $i2=>$row2){
                                $row_product_type2 = isset($row2['product_type'])?Tools::_str($row2['product_type']):'';
                                $row_product_id2 = isset($row2['product_id'])?Tools::_str($row2['product_id']):'';
                                $row_unit_id2 = isset($row2['unit_id'])?Tools::_str($row2['unit_id']):'';
                                $row_qty2 = isset($row2['qty'])?Tools::_str($row2['qty']):'0';
                                $row_stock_location2 = isset($row2['stock_location'])?Tools::_str($row2['stock_location']):'';
                                
                                if($i !== $i2&&
                                    $row_product_type === $row_product_type2 &&
                                    $row_product_id === $row_product_id2 &&
                                    $row_unit_id === $row_unit_id2 &&
                                    $row_stock_location === $row_stock_location2
                                ){
                                    $success = 0;
                                    $msg[] = Lang::get('Product Result').' '.Lang::get('duplicated');
                                    break;
                                }
                            }
                            
                            if($success !== 1) break;
                            
                            if(! Tools::_float($row_qty)> Tools::_float('0')){
                                $success = 0;
                                $msg[] = Lang::get(array('Result','Product')).' '.Lang::get('invalid',true,false);
                                break;
                            }
                        }
                    }

                    //</editor-fold>
                    

                    //<editor-fold defaultstate="collapsed" desc="Scrap Product">
                    
                    if(!Product_Data_Support::product_unit_all_exists($mfwp_scrap_product,array('product_status'=>'active'))){
                        $success = 0;
                        $msg[] = Lang::get(array('Scrap','Product')).' '.Lang::get('invalid',true,false);
                    }
                    else{
                        foreach($mfwp_scrap_product as $i=>$row){
                            $row_product_type = isset($row['product_type'])?Tools::_str($row['product_type']):'';
                            $row_product_id = isset($row['product_id'])?Tools::_str($row['product_id']):'';
                            $row_unit_id = isset($row['unit_id'])?Tools::_str($row['unit_id']):'';
                            $row_qty = isset($row['qty'])?Tools::_str($row['qty']):'0';
                            $row_stock_location = isset($row['stock_location'])?Tools::_str($row['stock_location']):'';
                            $product_exists = false;
                            
                            foreach($mfwp_scrap_product as $i2=>$row2){
                                $row_product_type2 = isset($row2['product_type'])?Tools::_str($row2['product_type']):'';
                                $row_product_id2 = isset($row2['product_id'])?Tools::_str($row2['product_id']):'';
                                $row_unit_id2 = isset($row2['unit_id'])?Tools::_str($row2['unit_id']):'';
                                $row_qty2 = isset($row2['qty'])?Tools::_str($row2['qty']):'0';
                                $row_stock_location2 = isset($row2['stock_location'])?Tools::_str($row2['stock_location']):'';
                                
                                if($i !== $i2 && 
                                    $row_product_type === $row_product_type2 &&
                                    $row_product_id === $row_product_id2 &&
                                    $row_unit_id === $row_unit_id2 &&
                                    $row_stock_location === $row_stock_location2
                                ){
                                    $success = 0;
                                    $msg[] = Lang::get('Product Result').' '.Lang::get('duplicated');
                                    break;
                                }
                            }
                            if($success !== 1) break;
                            
                            foreach($available_scrap_product as $i2=>$row2){
                                $row2_product_type = isset($row2['product_type'])?Tools::_str($row2['product_type']):'';
                                $row2_product_id = isset($row2['product_id'])?Tools::_str($row2['product_id']):'';
                                $row2_unit_id = isset($row2['unit_id'])?Tools::_str($row2['unit_id']):'';
                                $row2_qty = isset($row2['qty'])?Tools::_str($row2['qty']):'0';
                                $row2_stock_location = isset($row2['stock_location'])?Tools::_str($row2['stock_location']):'';

                                if(
                                    $row_product_type === $row2_product_type
                                    && $row_product_id === $row2_product_id
                                    && $row_unit_id === $row2_unit_id
                                    && Tools::_float($row_qty) <= Tools::_float($row2_qty)
                                    && $row_stock_location === $row2_stock_location
                                ){
                                    $product_exists = true;
                                    break;
                                }

                            }
                            if(!$product_exists || Tools::_float($row_qty) > Tools::_float($row2_qty)){
                                $success = 0;
                                $msg[] = Lang::get(array('Scrap','Product')).' '.Lang::get('invalid',true,false);
                                break;
                            }
                        }
                    }
                    //</editor-fold>
                }
                
                //</editor-fold>
                break;
            case self::$prefix_method.'_canceled':
                //<editor-fold defaultstate="collapsed">
                $temp_result = Validator::validate_on_cancel(
                    array(
                        'module'=>'mf_work_process',
                        'module_name'=>Lang::get('Manufacturing Work Order'),
                        'module_engine'=>'mf_work_process_engine',
                    ),
                    $mf_work_process
                );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;
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

    public static function adjust($method, $data=array()){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = array();

        $mf_work_process_data = isset($data['mf_work_process'])?$data['mf_work_process']:array();
        $mfwp_info_data = isset($data['mfwp_info'])?Tools::_arr($data['mfwp_info']):array();
        $mfwp_worker_data = isset($data['mfwp_worker'])?Tools::_arr($data['mfwp_worker']):array();
        $mfwp_checker_data = isset($data['mfwp_checker'])?Tools::_arr($data['mfwp_checker']):array();
        $mfwp_expected_result_product_data = isset($data['mfwp_expected_result_product'])?
            Tools::_arr($data['mfwp_expected_result_product']):array();
        $mfwp_component_product_data = isset($data['mfwp_component_product'])?
            Tools::_arr($data['mfwp_component_product']):array();
        $mfwp_scrap_product_data = isset($data['mfwp_scrap_product'])?
            Tools::_arr($data['mfwp_scrap_product']):array();
        $mfwp_result_product_data = isset($data['mfwp_result_product'])?
            Tools::_arr($data['mfwp_result_product']):array();
        $sir_data = isset($data['sir'])?Tools::_arr($data['sir']):array();
        
        $mf_work_process_db = Mf_Work_Process_Data_Support::mf_work_process_get($mf_work_process_data['id']);
        $mfwp_info_db = Mf_Work_Process_Data_Support::mfwp_info_get($mf_work_process_data['id']);
        
        $modid = User_Info::get()['user_id'];
        $datetime_curr = Date('Y-m-d H:i:s');
        
        switch($method){
            case self::$prefix_method.'_add':
                //<editor-fold defaultstate="collapsed">
                $mf_work_process_type = Tools::_str($mf_work_process_data['mf_work_process_type']);
                
                $mf_work_process = array(
                    'store_id'=>  Tools::_str($mf_work_process_data['store_id']),
                    'mf_work_process_type' => Tools::_str($mf_work_process_data['mf_work_process_type']),
                    'reference_id'=> Tools::_str($mf_work_process_data['reference_id']),
                    'notes' => Tools::empty_to_null(Tools::_str($mf_work_process_data['notes'])),
                    'mf_work_process_status'=>SI::status_default_status_get('mf_work_process_engine')['val'],
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                    'status'=>'1'
                );
                
                $mfwp_info = array(
                    'start_date'=>$datetime_curr,
                    'sir_exists'=>Tools::_bool($mfwp_info_data['sir_exists'])?'1':'0',
                    'warehouse_id'=>Tools::_str($mfwp_info_data['warehouse_id']),                    
                );

                $mfwp_worker = array();
                foreach($mfwp_worker_data as $i=>$row){
                    $temp = array(
                        'name'=>Tools::_str($row['name']),
                    );
                    $mfwp_worker[] = $temp;
                }
                
                $mfwp_expected_result_product = array();                
                foreach($mfwp_expected_result_product_data as $i=>$row){
                    $temp = array(
                        'reference_type'=>$row['reference_type'],
                        'reference_id'=>$row['reference_id'],
                        'product_type'=>$row['product_type'],
                        'product_id'=>$row['product_id'],
                        'unit_id'=>$row['unit_id'],
                        'qty'=>$row['qty'],
                        'bom_id'=>isset($row['bom_id'])?Tools::_str($row['bom_id']):null,
                    );
                    $mfwp_expected_result_product[] = $temp;
                }
                
                $mfwp_component_product = array();
                foreach($mfwp_component_product_data as $i=>$row){
                    $temp = array(
                        'product_type'=>Tools::_str($row['product_type']),
                        'product_id'=>Tools::_str($row['product_id']),
                        'unit_id'=>Tools::_str($row['unit_id']),
                        'stock_location'=>Tools::_str($row['stock_location']),
                        'qty'=>Tools::_str($row['qty']),
                    );
                    $mfwp_component_product[] = $temp;
                }
                
                
                $sir = array();
                if($mfwp_info['sir_exists'] === '1'){
                    get_instance()->load->helper('sir/sir_engine');
                    $sir = array(
                        'store_id'=>Tools::_str($mf_work_process_data['store_id']),
                        'creator'=>Tools::_str($sir_data['creator']),
                        'description'=>Tools::_str($sir_data['description']),
                        'module_name'=>'mf_work_process',
                        'module_action'=>'free_rules',
                        'sir_date'=>$datetime_curr,
                        'sir_status'=>SI::status_default_status_get('sir_engine')['val'],
                        'modid'=>$modid,
                        'moddate'=>$datetime_curr
                    );
                }
                
                $result['mf_work_process'] = $mf_work_process;
                $result['mfwp_info'] = $mfwp_info;
                $result['mfwp_worker'] = $mfwp_worker;
                $result['mfwp_expected_result_product'] = $mfwp_expected_result_product;
                $result['mfwp_component_product'] = $mfwp_component_product;
                $result['mfwp_info'] = $mfwp_info;
                $result['sir'] = $sir;
                
                //</editor-fold>
                break;
                
            case self::$prefix_method.'_process':
                //<editor-fold defaultstate="collapsed">
                $mf_work_process = array(
                    'notes' => isset($mf_work_process_data['notes'])?
                        Tools::empty_to_null(Tools::_str($mf_work_process_data['notes'])):null,
                    'mf_work_process_status'=>'process',
                    
                );
                //</editor-fold>
                $result['mf_work_process'] = $mf_work_process;
                break;
            case self::$prefix_method.'_done':
                //<editor-fold defaultstate="collapsed">
                $mf_work_process = array(
                    'notes' => isset($mf_work_process_data['notes'])?
                        Tools::empty_to_null(Tools::_str($mf_work_process_data['notes'])):null,
                    'mf_work_process_status'=>'done',                    
                );
                
                $result['mf_work_process'] = $mf_work_process;
                
                if($mf_work_process_db['mf_work_process_status'] === 'process'){
                    //<editor-fold defaultstate="collapsed">
                    
                    $mfwp_info = array(
                        'end_date'=>$datetime_curr,
                        'sir_exists'=>Tools::_bool($mfwp_info_data['sir_exists'])?'1':'0',
                    );
                    
                    $mfwp_checker = array(
                        'mf_work_process_id'=>$mf_work_process_db['id'],
                        'name'=>$mfwp_checker_data['name'],
                    );
                    
                    $mfwp_result_product = array();
                    foreach($mfwp_result_product_data as $i=>$row){
                        $mfwp_result_product[] = array(
                            'mf_work_process_id'=>$mf_work_process_data['id'],
                            'product_type'=>Tools::_str($row['product_type']),
                            'product_id'=>Tools::_str($row['product_id']),
                            'unit_id'=>Tools::_str($row['unit_id']),
                            'qty'=>Tools::_str($row['qty']),
                            'stock_location'=>Tools::_str($row['stock_location']),
                        );
                    }
                    
                    $mfwp_scrap_product = array();
                    foreach($mfwp_scrap_product_data as $i=>$row){
                        $mfwp_scrap_product[] = array(
                            'mf_work_process_id'=>$mf_work_process_data['id'],
                            'product_type'=>Tools::_str($row['product_type']),
                            'product_id'=>Tools::_str($row['product_id']),
                            'unit_id'=>Tools::_str($row['unit_id']),
                            'qty'=>Tools::_str($row['qty']),
                            'stock_location'=>Tools::_str($row['stock_location']),
                        );
                    }
                    
                    $sir = array();
                    if($mfwp_info['sir_exists'] === '1'){
                        get_instance()->load->helper('sir/sir_engine');
                        $sir = array(
                            'store_id'=>Tools::_str($mf_work_process_db['store_id']),
                            'reference_id'=>$mf_work_process_db['id'],
                            'creator'=>Tools::_str($sir_data['creator']),
                            'description'=>Tools::_str($sir_data['description']),
                            'module_name'=>'mf_work_process',
                            'module_action'=>'free_rules',
                            'sir_date'=>$datetime_curr,
                            'sir_status'=>SI::status_default_status_get('sir_engine')['val'],
                            'modid'=>$modid,
                            'moddate'=>$datetime_curr
                        );
                    }
                    
                    $result['mfwp_checker'] = $mfwp_checker;
                    $result['mfwp_info'] = $mfwp_info;
                    $result['mfwp_scrap_product'] = $mfwp_scrap_product;
                    $result['mfwp_result_product'] = $mfwp_result_product;
                    $result['sir'] = $sir;
                    //</editor-fold>
                }
                
                //</editor-fold>
                break;
            case self::$prefix_method.'_canceled':
                //<editor-fold defaultstate="collapsed">
                $mf_work_process = array();

                $mf_work_process = array(
                    'mf_work_process_status'=>'X',
                    'cancellation_reason'=>$mf_work_process_data['cancellation_reason'],
                    'notes'=>isset($mf_work_process_data['notes'])?
                        Tools::empty_to_null(Tools::_str($mf_work_process_data['notes'])):null,
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                $result['mf_work_process'] = $mf_work_process;
                //</editor-fold>
                break;
                
        }        

        return $result;
        //</editor-fold>
    }

    public function mf_work_process_add($db,$final_data,$id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('product_stock_engine');
        get_instance()->load->helper('mf_work_order/mf_work_order_engine');
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $fmf_work_process = $final_data['mf_work_process'];
        $fmfwp_info = $final_data['mfwp_info'];
        $fmfwp_expected_result_product = $final_data['mfwp_expected_result_product'];
        $fmfwp_component_product = $final_data['mfwp_component_product'];
        $fmfwp_worker = $final_data['mfwp_worker'];
        $fsir = $final_data['sir'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        $store_id = $fmf_work_process['store_id'];        
        $mf_work_process_type = $fmf_work_process['mf_work_process_type'];        
        $mf_work_process_id = '';
        
        $fmf_work_process['code'] = SI::code_counter_store_get($db,$store_id,'mf_work_process');
        
        if(!$db->insert('mf_work_process',$fmf_work_process)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        $mf_work_process_code = $fmf_work_process['code'];

        if($success == 1){
            $mf_work_process_id = $db->fast_get('mf_work_process'
                    ,array('code'=>$mf_work_process_code))[0]['id'];
            $result['trans_id']=$mf_work_process_id; 
        }
        
        if($success == 1){
            $mf_work_process_status_log = array(
                'mf_work_process_id'=>$mf_work_process_id
                ,'mf_work_process_status'=>$fmf_work_process['mf_work_process_status']
                ,'modid'=>$modid
                ,'moddate'=>$moddate    
            );

            if(!$db->insert('mf_work_process_status_log',$mf_work_process_status_log)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
                
            }
        }
        
        if($success === 1){
            $fmfwp_info['mf_work_process_id'] = $mf_work_process_id;
            if(!$db->insert('mfwp_info',$fmfwp_info)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }
        
        if($success === 1){
            foreach($fmfwp_worker as $i=>$row){
                $row['mf_work_process_id'] = $mf_work_process_id;
                if(!$db->insert('mfwp_worker',$row)){
                    $success = 0;
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();
                    break;
                }
                
            }
        }
        
        if($success === 1){
            foreach($fmfwp_expected_result_product as $i=>$row){
                $row['mf_work_process_id'] = $mf_work_process_id;
                if(!$db->insert('mfwp_expected_result_product',$row)){
                    $success = 0;
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();
                    break;
                }
                
                if($success === 1){
                    if($row['reference_type'] === 'mfwo_ordered_product')
                    $temp_result = Mf_Work_Order_Engine::ordered_product_outstanding_qty_add(
                        $db
                        ,$row['reference_id']
                        ,-1 * Tools::_float($row['qty'])
                    );
                    
                    $success = $temp_result['success'];
                    if($success !== 1){
                        $msg = array_merge($msg,$temp_result['msg']);
                        break;
                    }
                    
                }
                
            }
        }
        
        if($success === 1){
            foreach($fmfwp_component_product as $i=>$row){
                $row['mf_work_process_id'] = $mf_work_process_id;
                if(!$db->insert('mfwp_component_product',$row)){
                    $success = 0;
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();
                    break;
                }
                                
                if($success === 1 && $row['product_type'] === 'registered_product'){
                    $stock_location = SI::type_get('mf_work_process_engine', $row['stock_location'], '$stock_location_list');
                    foreach($stock_location['stock_db'] as $i2=>$row2){
                        
                        $temp_result = eval('return Product_Stock_Engine::'.$row2.'_only_add(
                            $db,
                            $fmfwp_info["warehouse_id"],
                            $row["product_id"],
                            -1*Tools::_float($row["qty"]),
                            $row["unit_id"],
                            "Manufacturing Work Process - Component Product: ".$fmf_work_process["code"]
                                ." ".SI::status_get("Mf_Work_Process_Engine",
                                $fmf_work_process["mf_work_process_status"])["label"],
                            $moddate
                        );');
                        $success = $temp_result['success'];
                        if($success !== 1){
                            $msg = array_merge($msg,$temp_result['msg']);
                            break;
                        }
                    }
                }
            }
        }
        
        if($success === 1 && count($fsir)>0){
            $fsir['reference_id'] = $mf_work_process_id;
            $temp_result = SIR_Engine::sir_add($db,array('sir'=>$fsir),'mf_work_process_free_rules_add');
            $success = $temp_result['success'];
            if($success !== 1){
                $msg = array_merge($msg, $temp_result['mgs']);
            }
        }
        

        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    
    function mf_work_process_process($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->engine('mf_work_process/mf_work_process_data_support');
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $fmf_work_process = $final_data['mf_work_process'];
        $fmfwp_info = isset($final_data['mfwp_info'])?Tools::_arr($final_data['mfwp_info']):array();
        
        $mf_work_process_id = $id;
        $mf_work_process_db = Mf_Work_Process_Data_Support::mf_work_process_get($mf_work_process_id);
        
        $mf_work_process_type = $mf_work_process_db['mf_work_process_type'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('mf_work_process',$fmf_work_process,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            $temp_result = SI::status_log_add($db,'mf_work_process',
                $id,$fmf_work_process['mf_work_process_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    function mf_work_process_done($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('mf_work_process/mf_work_process_data_support');
        get_instance()->load->helper('product_stock_engine');
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $fmf_work_process = $final_data['mf_work_process'];
        $fmfwp_info = isset($final_data['mfwp_info'])?Tools::_arr($final_data['mfwp_info']):array();
        $fmfwp_checker = isset($final_data['mfwp_checker'])?Tools::_arr($final_data['mfwp_checker']):array();
        $fmfwp_result_product = isset($final_data['mfwp_result_product'])?
            Tools::_arr($final_data['mfwp_result_product']):array();
        $fmfwp_scrap_product = isset($final_data['mfwp_scrap_product'])?
            Tools::_arr($final_data['mfwp_scrap_product']):array();
        $fsir = isset($final_data['sir'])?Tools::_arr($final_data['sir']):array();
        
        $mf_work_process_id = $id;
        $mf_work_process_db = Mf_Work_Process_Data_Support::mf_work_process_get($mf_work_process_id);
        $mfwp_info_db = Mf_Work_Process_Data_Support::mfwp_info_get($mf_work_process_id);
        
        $mf_work_process_type = $mf_work_process_db['mf_work_process_type'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('mf_work_process',$fmf_work_process,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            $temp_result = SI::status_log_add($db,'mf_work_process',
                $id,$fmf_work_process['mf_work_process_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        if($mf_work_process_db['mf_work_process_status'] === 'process'){
            if($success === 1 && count($fmfwp_info)>0 ){
                if(!$db->update('mfwp_info',$fmfwp_info,array("mf_work_process_id"=>$id))){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                }
            }

            if($success === 1){
                if(!$db->insert('mfwp_checker',$fmfwp_checker)){
                    $success = 0;
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();
                }                
            
            }
            
            if($success === 1 && count($fsir)>0){
                $temp_result = SIR_Engine::sir_add($db,array('sir'=>$fsir),'mf_work_process_free_rules_add');
                $success = $temp_result['success'];
                if($success !== 1){
                    $msg = array_merge($msg, $temp_result['mgs']);
                }
            }
            
            
            if($success === 1){
                foreach($fmfwp_result_product as $i=>$row){
                    if(!$db->insert('mfwp_result_product',$row)){
                        $success = 0;
                        $msg[] = $db->_error_message();
                        $db->trans_rollback();
                        break;
                    }

                    if($success === 1 && $row['product_type'] === 'registered_product'){
                        $stock_location = SI::type_get('mf_work_process_engine', $row['stock_location'], '$stock_location_list');
                        foreach($stock_location['stock_db'] as $i2=>$row2){

                            $temp_result = eval('return Product_Stock_Engine::'.$row2.'_only_add(
                                $db,
                                $mfwp_info_db["warehouse_id"],
                                $row["product_id"],
                                Tools::_float($row["qty"]),
                                $row["unit_id"],
                                "Manufacturing Work Process - Result Product: ".$mf_work_process_db["code"]
                                    ." ".SI::status_get("Mf_Work_Process_Engine",
                                    $fmf_work_process["mf_work_process_status"])["label"],
                                $moddate
                            );');
                            $success = $temp_result['success'];
                            if($success !== 1){
                                $msg = array_merge($msg,$temp_result['msg']);
                                break;
                            }
                        }
                    }
                }
            }
            
            if($success === 1){
                foreach($fmfwp_scrap_product as $i=>$row){
                    if(!$db->insert('mfwp_scrap_product',$row)){
                        $success = 0;
                        $msg[] = $db->_error_message();
                        $db->trans_rollback();
                        break;
                    }

                    if($success === 1 && $row['product_type'] === 'registered_product'){
                        $stock_location = SI::type_get('mf_work_process_engine', $row['stock_location'], '$stock_location_list');
                        foreach($stock_location['stock_db'] as $i2=>$row2){

                            $temp_result = eval('return Product_Stock_Engine::'.$row2.'_only_add(
                                $db,
                                $mfwp_info_db["warehouse_id"],
                                $row["product_id"],
                                Tools::_float($row["qty"]),
                                $row["unit_id"],
                                "Manufacturing Work Process - Scrap Product: ".$mf_work_process_db["code"]
                                    ." ".SI::status_get("Mf_Work_Process_Engine",
                                    $fmf_work_process["mf_work_process_status"])["label"],
                                $moddate
                            );');
                            $success = $temp_result['success'];
                            if($success !== 1){
                                $msg = array_merge($msg,$temp_result['msg']);
                                break;
                            }
                        }
                    }
                }
            }
            
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    function mf_work_process_canceled($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('mf_work_process/mf_work_process_data_support');
        get_instance()->load->helper('mf_work_order/mf_work_order_data_support');
        get_instance()->load->helper('mf_work_order/mf_work_order_engine');
        get_instance()->load->helper('product_stock_engine');
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $fmf_work_process = $final_data['mf_work_process'];
        $fmfwp_info = isset($final_data['mfwp_info'])?Tools::_arr($final_data['mfwp_info']):array();
        
        $mf_work_process_id = $id;
        $mf_work_process_db = Mf_Work_Process_Data_Support::mf_work_process_get($mf_work_process_id);
        $mfwp_info_db = Mf_Work_Process_Data_Support::mfwp_info_get($mf_work_process_id);
        $mfwp_expected_result_product_db = Mf_Work_Process_Data_Support::mfwp_expected_result_product_get($mf_work_process_id);
        $mfwp_result_product_db = Mf_Work_Process_Data_Support::mfwp_result_product_get($mf_work_process_id);
        $mfwp_component_product_db = Mf_Work_Process_Data_Support::mfwp_component_product_get($mf_work_process_id);
        $mfwp_scrap_product_db = Mf_Work_Process_Data_Support::mfwp_scrap_product_get($mf_work_process_id);
        
        $mf_work_order_id = $mf_work_process_db['reference_id'];
        $mfwo_ordered_product_db = Mf_Work_Order_Data_Support::mfwo_ordered_product_get($mf_work_order_id);
        
        
        $mf_work_process_type = $mf_work_process_db['mf_work_process_type'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('mf_work_process',$fmf_work_process,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            $temp_result = SI::status_log_add($db,'mf_work_process',
                $id,$fmf_work_process['mf_work_process_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        if($success === 1 ){
            foreach($mfwp_expected_result_product_db as $i=>$row){
                if($row['reference_type'] === 'mfwo_ordered_product'){
                    $temp_result = Mf_Work_Order_Engine::ordered_product_outstanding_qty_add(
                        $db,
                        $row['reference_id'],
                        $row['qty']
                    );
                    $success = $temp_result['success'];
                    if($success !== 1){
                        $msg[] = array_merge($msg, $temp_result['msg']);
                        break;
                    }
                }
            }
        }
        
        if($success === 1){
            foreach($mfwp_component_product_db as $i=>$row){
                if($row['product_type'] === 'registered_product'){
                    $stock_location = SI::type_get('mf_work_process_engine', $row['stock_location'],'$stock_location_list');
                    foreach($stock_location['stock_db'] as $i2=>$row2){
                        $temp_result = eval('return Product_Stock_Engine::'.$row2.'_only_add(
                            $db,
                            $mfwp_info_db["warehouse_id"],
                            $row["product_id"],
                            Tools::_float($row["qty"]),
                            $row["unit_id"],
                            "Manufacturing Work Process - Component Product: ".$mf_work_process_db["code"]
                                ." ".SI::status_get("Mf_Work_Process_Engine",
                                $fmf_work_process["mf_work_process_status"])["label"],
                            $moddate
                        );');
                        $success = $temp_result['success'];
                        if($success !== 1){
                            $msg = array_merge($msg,$temp_result['msg']);
                            break;
                        }
                    }
                }
            }
        }
        
        if($success === 1){
            foreach($mfwp_result_product_db as $i=>$row){
                if($row['product_type'] === 'registered_product'){
                    $stock_location = SI::type_get('mf_work_process_engine', $row['stock_location'],'$stock_location_list');
                    foreach($stock_location['stock_db'] as $i2=>$row2){
                        $temp_result = eval('return Product_Stock_Engine::'.$row2.'_only_add(
                            $db,
                            $mfwp_info_db["warehouse_id"],
                            $row["product_id"],
                            -1 * Tools::_float($row["qty"]),
                            $row["unit_id"],
                            "Manufacturing Work Process - Result Product: ".$mf_work_process_db["code"]
                                ." ".SI::status_get("Mf_Work_Process_Engine",
                                $fmf_work_process["mf_work_process_status"])["label"],
                            $moddate
                        );');
                        $success = $temp_result['success'];
                        if($success !== 1){
                            $msg = array_merge($msg,$temp_result['msg']);
                            break;
                        }
                    }
                }
            }
        }
        
        if($success === 1){
            foreach($mfwp_scrap_product_db as $i=>$row){
                if($row['product_type'] === 'registered_product'){
                    $stock_location = SI::type_get('mf_work_process_engine', $row['stock_location'], '$stock_location_list');
                    foreach($stock_location['stock_db'] as $i2=>$row2){

                        $temp_result = eval('return Product_Stock_Engine::'.$row2.'_only_add(
                            $db,
                            $mfwp_info_db["warehouse_id"],
                            $row["product_id"],
                            -1 * Tools::_float($row["qty"]),
                            $row["unit_id"],
                            "Manufacturing Work Process - Scrap Product: ".$mf_work_process_db["code"]
                                ." ".SI::status_get("Mf_Work_Process_Engine",
                                $fmf_work_process["mf_work_process_status"])["label"],
                            $moddate
                        );');
                        $success = $temp_result['success'];
                        if($success !== 1){
                            $msg = array_merge($msg,$temp_result['msg']);
                            break;
                        }
                    }
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
