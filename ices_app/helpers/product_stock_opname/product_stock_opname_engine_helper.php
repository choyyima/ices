<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product_Stock_Opname_Engine {
    public static $prefix_id = 'pso';
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
                        array('val'=>'Add'),array('val'=>'Product Stock Opname'),array('val'=>'success')
                    )
                )
            ),
            array(
                'val'=>'process'
                ,'label'=>'PROCESS'
                ,'method'=>self::$prefix_method.'_process'
                ,'default'=>true
                ,'next_allowed_status'=>array('finalized')
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update'),array('val'=>'Product Stock Opname'),array('val'=>'success')
                    )
                )
            )
            ,array(
                'val'=>'finalized'
                ,'label'=>'FINALIZED'
                ,'method'=>self::$prefix_method.'_finalized'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update'),array('val'=>'Product Stock Opname'),array('val'=>'success')
                    )
                )
        )
        //</editor-fold>
        );
        
        //</editor-fold>
    }
    
    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'product_stock_opname/',
            'product_stock_opname_engine'=>'product_stock_opname/product_stock_opname_engine',
            'product_stock_opname_data_support' => 'product_stock_opname/product_stock_opname_data_support',
            'product_stock_opname_renderer' => 'product_stock_opname/product_stock_opname_renderer',
            'product_stock_opname_print' => 'product_stock_opname/product_stock_opname_print',
            'ajax_search'=>get_instance()->config->base_url().'product_stock_opname/ajax_search/',
            'data_support'=>get_instance()->config->base_url().'product_stock_opname/data_support/',
        );

        return json_decode(json_encode($path));
    }
    
    public static function validate($action,$data=array()){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('product_stock_opname/product_stock_opname_data_support');
        get_instance()->load->helper('product/product_data_support');
        
        $result = array(
            "success"=>1
            ,"msg"=>array()
        );
        $success = 1;
        $msg = array();
        
        $pso = isset($data['pso'])?Tools::_arr($data['pso']):null;
        $pso_product = isset($data['pso_product'])?Tools::_arr($data['pso_product']):array();
        $pso_id = Tools::empty_to_null(isset($pso['id'])?Tools::_str($pso['id']):'');
        $pso_db = Product_Stock_Opname_Data_Support::pso_get($pso_id);
        $pso_product_db = Product_Stock_Opname_Data_Support::pso_product_get($pso_id);

        $db = new DB();
        switch($action){
            case self::$prefix_method.'_add':
                //<editor-fold defaultstate="collapsed">
                $store_id = Tools::empty_to_null(isset($pso['store_id'])?Tools::_str($pso['store_id']):'');
                $checker = Tools::empty_to_null(isset($pso['checker'])?Tools::_str($pso['checker']):'');
                $warehouse_id = Tools::empty_to_null(isset($pso['warehouse_id'])?Tools::_str($pso['warehouse_id']):'');
                
                //<editor-fold defaultstate="collapsed" desc="Major Validation">
                              
                if(!Store_Engine::store_exists($store_id)){
                    $success = 0;
                    $msg[] = Lang::get('Store').' '.Lang::get('empty',true,false);
                }
                
                if(is_null($checker)){
                    $success = 0;
                    $msg[] = Lang::get('Checker').' '.Lang::get('empty',true,false);
                }
                
                if(!Warehouse_Engine::is_type('BOS',$warehouse_id)){
                    $success = 0;
                    $msg[] = Lang::get('Warehouse').' '.Lang::get('empty',true,false);
                }
                
                if(count($pso_product) === 0){
                    $success = 0;
                    $msg[] = Lang::get('Product').' '.Lang::get('empty',true,false);
                }
                
                
                if($success !== 1) break;
                //</editor-fold>

                //<editor-fold defaultstate="collapsed" desc="Product Validation">
                if(!Product_Data_Support::product_unit_all_exists($pso_product, array())){
                    $success = 0;
                    $msg[] = Lang::get('Product').' '.Lang::get('invalid',true,false);
                }
                else{
                    foreach($pso_product as $idx=>$row){
                        //<editor-fold defaultstate="collapsed">
                        $product_type = isset($row['product_type'])?Tools::_str($row['product_type']):'';
                        $product_id = isset($row['product_id'])?Tools::_str($row['product_id']):'';
                        $unit_id = isset($row['unit_id'])?Tools::_str($row['unit_id']):'';
                        $outstanding_qty = isset($row['outstanding_qty'])?Tools::_str($row['outstanding_qty']):'-1';
                        $floor_1_qty = isset($row['floor_1_qty'])?Tools::_str($row['floor_1_qty']):'-1';
                        $floor_2_qty = isset($row['floor_2_qty'])?Tools::_str($row['floor_2_qty']):'-1';
                        $floor_3_qty = isset($row['floor_3_qty'])?Tools::_str($row['floor_3_qty']):'-1';
                        $floor_4_qty = isset($row['floor_4_qty'])?Tools::_str($row['floor_4_qty']):'-1';
                        $stock_bad_qty = isset($row['stock_bad_qty'])?Tools::_str($row['stock_bad_qty']):'-1';
                        
                        $t_param = array(array('product_type'=>$product_type,'product_id'=>$product_id,'unit_id'=>$unit_id));
                        if(count(Tools::array_extract($pso_product,array(),array('data'=>$t_param,'cfg'=>array('data_conversion'=>'str'))))>1){
                            $success = 0;
                            $msg[] = 'Product duplicate';
                        }
                        if(Tools::_float($outstanding_qty)<Tools::_float('0')){
                            $success = 0;
                            $msg[] = 'Outstanding Qty'.' '.'invalid';
                        }
                        
                        if(Tools::_float($floor_1_qty)<Tools::_float('0')
                            || Tools::_float($floor_2_qty)<Tools::_float('0')
                            || Tools::_float($floor_3_qty)<Tools::_float('0')
                            || Tools::_float($floor_4_qty)<Tools::_float('0')
                        ){
                            $success = 0;
                            $msg[] = 'Floor Qty'.' '.'invalid';
                        }
                        
                        if(Tools::_float($stock_bad_qty)<Tools::_float('0')){
                            $success = 0;
                            $msg[] = 'Bad Stock Qty'.' '.'invalid';
                        }
                        
                        if($success !== 1) break;
                        //</editor-fold>
                    }
                    
                
                    
                    
                }
                //</editor-fold>
                
                
                //</editor-fold>
                break;
            case self::$prefix_method.'_process':
                //<editor-fold defaultstate="collapsed">
                $success = 0;
                $msg[] = 'Update'.' '.'Product Stock Opname'.' '.'invalid';
                if($success !== 1) break;
                //</editor-fold>
                break;
            case self::$prefix_method.'_finalized':
                //<editor-fold defaultstate="collapsed">
                $description = Tools::empty_to_null(isset($pso['description'])?Tools::_str($pso['description']):'');
                $temp_result = Validator::validate_on_update(
                    array(
                        'module'=>'product_stock_opname',
                        'module_name'=>Lang::get('Product Stock Opname'),
                        'module_engine'=>'product_stock_opname_engine',
                    ),
                    $pso
                );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                
                if($pso_db['product_stock_opname_status'] === 'finalized'){
                    $success = 0;
                    $msg[] = 'Update'.' '.'Product Stock Opname'.' '.'invalid';
                }
                
                if($success !== 1) break;
                
                if(is_null($description)){
                    $success = 0;
                    $msg[] = 'Description'.' '.Lang::get('empty',true,false);
                }
                
                if($pso_db['product_stock_opname_status'] === 'process'){
                    //<editor-fold defaultstate="collapsed">
                    foreach($pso_product_db as $idx=>$row){
                        //<editor-fold defaultstate="collapsed" desc="Product Qty Validation">
                        $product_id = $row['product_id'];
                        $unit_id = $row['unit_id'];
                        $product_type = $row['product_type'];
                        
                        $outstanding_qty = $row['outstanding_qty'];
                        $total_qty= $row['total_qty'];
                        $stock_bad_qty = $row['stock_bad_qty'];
                        
                        if(Tools::_float($outstanding_qty)< Tools::_float('0')){
                            $success = 0;
                            $msg[] = 'Outstanding Qty'.' '.Lang::get('is lower than',true,false,false,true).' 0.00';
                        }
                        
                        if(Tools::_float($total_qty)< Tools::_float('0')){
                            $success = 0;
                            $msg[] = 'Total Qty'.' '.Lang::get('is lower than',true,false,false,true).' 0.00';
                        }
                        
                        if(Tools::_float($stock_bad_qty)< Tools::_float('0')){
                            $success = 0;
                            $msg[] = 'Bad Stock Qty'.' '.Lang::get('is lower than',true,false,false,true).' 0.00';
                        }
                        
                        if($success !== 1) break;
                        //</editor-fold>
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

    public static function adjust($method, $data=array()){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = array();

        $pso_data = isset($data['pso'])?$data['pso']:array();        
        $pso_product_data = isset($data['pso_product'])?
            Tools::_arr($data['pso_product']):array();
        
        $pso_db = Product_Stock_Opname_Data_Support::pso_get($pso_data['id']);
        
        $modid = User_Info::get()['user_id'];
        $datetime_curr = Date('Y-m-d H:i:s');
        
        switch($method){
            case self::$prefix_method.'_add':
                //<editor-fold defaultstate="collapsed">
                $pso = array(
                    'store_id'=>  Tools::_str($pso_data['store_id']),
                    'warehouse_id'=>  Tools::_str($pso_data['warehouse_id']),
                    'checker'=>  Tools::_str($pso_data['checker']),
                    'product_stock_opname_date'=>Tools::_date('','Y-m-d H:i:s'),
                    'notes' => Tools::empty_to_null(isset($pso_data['notes'])?Tools::_str($pso_data['notes']):''),
                    'product_stock_opname_status'=>SI::type_default_type_get('product_stock_opname_engine','$status_list')['val'],
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                    'status'=>'1'
                );
                
                $pso_product = array();                
                foreach($pso_product_data as $i=>$row){
                    $outstanding_qty = Tools::_str($row['outstanding_qty']);
                    $floor_1_qty = $row['floor_1_qty'];
                    $floor_2_qty = $row['floor_2_qty'];
                    $floor_3_qty = $row['floor_3_qty'];
                    $floor_4_qty = $row['floor_4_qty'];
                    $stock_bad_qty = $row['stock_bad_qty'];
                    
                    $ssa_qty = Tools::_float($floor_1_qty)
                        +Tools::_float($floor_2_qty)
                        +Tools::_float($floor_3_qty)
                        +Tools::_float($floor_4_qty);
                    
                    $stock_qty = Tools::_float($ssa_qty+$outstanding_qty);
                    
                    $temp = array(
                        'product_type'=>$row['product_type'],
                        'product_id'=>$row['product_id'],
                        'unit_id'=>$row['unit_id'],
                        'outstanding_qty'=>$outstanding_qty,
                        'stock_qty'=>$stock_qty,
                        'stock_qty_old'=>null,
                        'ssa_floor_1_qty'=>$floor_1_qty,
                        'ssa_floor_2_qty'=>$floor_2_qty,
                        'ssa_floor_3_qty'=>$floor_3_qty,
                        'ssa_floor_4_qty'=>$floor_4_qty,
                        'stock_sales_available_qty'=>$ssa_qty,
                        'stock_sales_available_qty_old'=>null,
                        'stock_bad_qty'=>$stock_bad_qty,
                        'stock_bad_qty_old'=>null,
                    );
                    $pso_product[] = $temp;
                }
                
                $result['pso'] = $pso;
                $result['pso_product'] = $pso_product;
                
                //</editor-fold>
                break;
            case self::$prefix_method.'_finalized':
                //<editor-fold defaultstate="collapsed">
                $pso = array();

                $pso = array(
                    'product_stock_opname_status'=>'finalized',
                    'notes'=>isset($pso_data['notes'])?
                        Tools::empty_to_null(Tools::_str($pso_data['notes'])):null,
                    'description'=>Tools::_str($pso_data['description']),
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                $result['pso'] = $pso;
                //</editor-fold>
                break;
                
        }        

        return $result;
        //</editor-fold>
    }
    
    static function pso_add($db,$final_data,$id=''){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        $f_product_stock_opname = $final_data['pso'];
        $f_pso_product = $final_data['pso_product'];
        
        $store_id = $f_product_stock_opname['store_id'];
        
        $pso_id = '';
        $f_product_stock_opname['code'] = SI::code_counter_store_get($db,$store_id, 'product_stock_opname');
        if(!$db->insert('product_stock_opname',$f_product_stock_opname)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $pso_code = $f_product_stock_opname['code'];

        if($success == 1){                                
            $pso_id = $db->fast_get('product_stock_opname'
                    ,array('code'=>$pso_code))[0]['id'];
            $result['trans_id']=$pso_id; 

        }
        
        if($success === 1){
            foreach($f_pso_product as $idx=>$pso_product){
                $pso_product['product_stock_opname_id'] = $pso_id;
                if(!$db->insert('pso_product',$pso_product)){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                    break;
                }
            }
        }
        
        if($success === 1){
            $temp_res = SI::status_log_add($db,
                'product_stock_opname',
                $pso_id,
                $f_product_stock_opname['product_stock_opname_status']
            );

            $success = $temp_res['success'];
            $msg = array_merge($msg, $temp_res['msg']);

        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    static function pso_finalized($db, $final_data, $id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('product_stock_opname/product_stock_opname_data_support');
        get_instance()->load->helper('product_stock_engine');
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $fpso = $final_data['pso'];
        
        $pso_id = $id;
        $pso_db = Product_Stock_Opname_Data_Support::pso_get($pso_id);
        $pso_product_db = Product_Stock_Opname_Data_Support::pso_product_get($pso_id);
        $warehouse_id  = $pso_db['warehouse_id'];
        $product_stock_opname_status_curr = $pso_db['product_stock_opname_status'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('product_stock_opname',$fpso,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            $temp_result = SI::status_log_add($db,'product_stock_opname',
                $id,$fpso['product_stock_opname_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        if($product_stock_opname_status_curr === 'process'){
            
            foreach($pso_product_db as $idx=>$row){
                $pso_product_id = $row['id'];
                $product_id = $row['product_id'];
                $unit_id = $row['unit_id'];
                $product_type = $row['product_type'];
                $pso_product_param = array(
                    'outstanding_qty_old'=>$row['outstanding_qty_old'],
                    'stock_qty_old'=>$row['stock_qty_old'],
                    'stock_sales_available_qty_old'=>$row['stock_sales_available_qty_old'],
                    'stock_bad_qty_old'=>$row['stock_bad_qty_old'],
                );
                if(!$db->update('pso_product',$pso_product_param,array('id'=>$pso_product_id))){
                    $success = 0;
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();
                }
                
                if($success === 1){
                    switch($product_type){
                        case 'registered_product':
                            //<editor-fold defaultstate="collapsed">
                            
                            $delta_ssa_qty_diff = $row['total_qty_diff'];
                            $delta_stock_bad_qty_diff = $row['stock_bad_qty_diff'];
                            $delta_stock_qty_diff = Tools::_float($delta_ssa_qty_diff)+
                                Tools::_float($delta_stock_bad_qty_diff);
                            
                            if(Tools::_float($delta_stock_qty_diff) !== Tools::_float('0')){
                                //RUMUS: SELISIH STOCK QTY = STOCK QTY BARU - STOCK QTY LAMA
                                $stock_qty_old = $row['stock_qty_old'];
                                $stock_qty = $row['stock_qty'];
                                $stock_qty_diff = Tools::_float($stock_qty) - Tools::_float($stock_qty_old);
                                $temp_result = Product_Stock_Engine::stock_good_only_add(
                                    $db,
                                    $warehouse_id,
                                    $product_id,
                                    Tools::_float($delta_stock_qty_diff),
                                    $unit_id,
                                    "Product Stock Opname: ".$pso_db["code"]
                                        ." ".SI::status_get("Product_Stock_Opname_Engine",
                                        $fpso["product_stock_opname_status"])["label"],
                                    $moddate
                                );
                                
                                $success = $temp_result['success'];
                                $msg = array_merge($msg, $temp_result['msg']);
                                
                            }
                            
                            if($success === 1){
                                if(Tools::_float($delta_ssa_qty_diff) !== Tools::_float('0')){
                                    $temp_result = Product_Stock_Engine::stock_sales_available_only_add(
                                        $db,
                                        $warehouse_id,
                                        $product_id,
                                        Tools::_float($delta_ssa_qty_diff),
                                        $unit_id,
                                        "Product Stock Opname: ".$pso_db["code"]
                                            ." ".SI::status_get("Product_Stock_Opname_Engine",
                                            $fpso["product_stock_opname_status"])["label"],
                                        $moddate
                                    );
                                    
                                    $success = $temp_result['success'];
                                    $msg = array_merge($msg, $temp_result['msg']);
                                }
                            }
                            
                            if($success === 1){
                                if(Tools::_float($delta_stock_bad_qty_diff)!==Tools::_float('0')){
                                    $temp_result = Product_Stock_Engine::stock_bad_only_add(
                                        $db,
                                        $warehouse_id,
                                        $product_id,
                                        Tools::_float($delta_stock_bad_qty_diff),
                                        $unit_id,
                                        "Product Stock Opname: ".$pso_db["code"]
                                            ." ".SI::status_get("Product_Stock_Opname_Engine",
                                            $fpso["product_stock_opname_status"])["label"],
                                        $moddate
                                    );
                                    
                                    $success = $temp_result['success'];
                                    $msg = array_merge($msg, $temp_result['msg']);
                                }
                            }
                            //</editor-fold>
                            break;
                    }
                }
                
                if($success !== 1) break;
            }

            
        }
        
        
        
        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }
    
    static function pso_mail(){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('app_job/app_job_stream_context');
        $url = get_instance()->config->base_url().'app_job/';
        $data = array('password'=>'theappjob',
        'job'=>array(
                array('name'=>'product_stock_opname_mail','config'=>array())
        ));

        $sc = new App_Job_Stream_Context();
        $result = $sc->send($url,$data);

        //</editor-fold>
    }
    
}
?>