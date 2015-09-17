<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Stock_Transfer_Engine {

    public static $status_list = array(
        //<editor-fold defaultstate="collapsed">
        array(
            'val'=>''
            ,'label'=>''
            ,'method'=>'stock_transfer_add'
            ,'next_allowed_status'=>array()
            ,'msg'=>array(
                'success'=>array(
                    array('val'=>'Add Stock Transfer success')
                )
            )
        ),
        array(//label name is used for method name
            'val'=>'process'
            ,'label'=>'PROCESS'
            ,'method'=>'stock_transfer_process'
            ,'default'=>true
            ,'next_allowed_status'=>array('done','X')
            ,'msg'=>array(
                'success'=>array(
                    array('val'=>'Update Stock Transfer success')
                )
            )
        )
        ,array(
            'val'=>'done'
            ,'label'=>'DONE'
            ,'method'=>'stock_transfer_done'
            ,'next_allowed_status'=>array('X')
            ,'msg'=>array(
                'success'=>array(
                    array('val'=>'Update Stock Transfer success')
                )
            )

        )
        ,array(
            'val'=>'X'
            ,'label'=>'CANCELED'
            ,'method'=>'stock_transfer_canceled'
            ,'next_allowed_status'=>array()
            ,'msg'=>array(
                'success'=>array(
                    array('val'=>'Update Stock Transfer success')
                )
            )
        )
        //</editor-fold>
    );
        
    public static function stock_transfer_active_get(){
        //<editor-fold defaultstate="collapsed">
        $result = null;
        $db = new DB();
        $user_id = User_Info::get()['user_id'];
        $q = '
            select t1.id
            from stock_transfer t1
                inner join stock_transfer_info t2 on t1.id = t2.stock_transfer_id
            where t1.status>0 and t1.stock_transfer_status ='.
                $db->escape(SI::status_default_status_get('Stock_Transfer_Engine')['val']).'
                and t2.creator_id = '.$db->escape($user_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = $rs[0]['id'];
        return $result;
        //</editor-fold>
    }
    
    public static function stock_transfer_exists($id){
        //<editor-fold defaultstate="collapsed">
        $result = false;
        $db = new DB();
        $q = '
                select 1 
                from stock_transfer 
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
            'index'=>get_instance()->config->base_url().'stock_transfer/',
            'stock_transfer_engine'=>'stock_transfer/stock_transfer_engine',
            'stock_transfer_data_support' => 'stock_transfer/stock_transfer_data_support',
            'stock_transfer_renderer' => 'stock_transfer/stock_transfer_renderer',
            'stock_transfer_print' => 'stock_transfer/stock_transfer_print',
            'ajax_search'=>get_instance()->config->base_url().'stock_transfer/ajax_search/',
            'data_support'=>get_instance()->config->base_url().'stock_transfer/data_support/',
        );

        return json_decode(json_encode($path));
    }
    
    public static function validate($method,$data=array()){      
        get_instance()->load->helper('stock_transfer/stock_transfer_data_support');
        
        $result = array(
            "success"=>1
            ,"msg"=>array()

        );
        $success = 1;
        $msg = array();
        $stock_transfer = isset($data['stock_transfer'])?
                Tools::_arr($data['stock_transfer']):array();
        $stock_transfer_product = isset($data['stock_transfer_product'])?
                Tools::_arr($data['stock_transfer_product']):array();
        $stock_transfer_id = $stock_transfer['id'];
        
        switch($method){
            case 'stock_transfer_add':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $store_id = isset($stock_transfer['store_id'])?
                    Tools::_str($stock_transfer['store_id']):'';
                $requestor_name = isset($stock_transfer['requestor_name'])?
                    Tools::_str($stock_transfer['requestor_name']):'';
                $warehouse_from_id = isset($stock_transfer['warehouse_from_id'])?
                    Tools::_str($stock_transfer['warehouse_from_id']):'';
                $warehouse_to_id = isset($stock_transfer['warehouse_to_id'])?
                    Tools::_str($stock_transfer['warehouse_to_id']):'';
                $stock_transfer_date = isset($stock_transfer['stock_transfer_date'])?
                    Tools::_str($stock_transfer['stock_transfer_date']):'';

                //<editor-fold defaultstate="collapsed" desc="Major Validation">

                if(!SI::record_exists('store', array('id'=>$store_id,'status'=>'1'))){
                    $success = 0;
                    $msg[] = Lang::get('Store').' '.Lang::get('empty',true,false);
                }
                
                if(Tools::empty_to_null($requestor_name) === null){
                    $success = 0;
                    $msg[] = Lang::get(array('Requestor Name',array('val'=>'empty','uc_first'=>false)),true,true,false,false,true);
                }

                if(!Warehouse_Engine::is_type('BOS', $warehouse_from_id)){
                    $success = 0;
                    $msg[] = Lang::get('Warehouse From').' '.Lang::get('empty',true,false);
                }
                
                if(!Warehouse_Engine::is_type('BOS', $warehouse_to_id)){
                    $success = 0;
                    $msg[] = Lang::get('Warehouse To').' '.Lang::get('empty',true,false);
                }
                
                if(count($stock_transfer_product) === 0){
                    $success = 0;
                    $msg[] = Lang::get('Product').' '.Lang::get('empty',true,false);
                }
                
                if(strtotime(Tools::_date($stock_transfer_date,'Y-m-d H:i:s')) 
                    < strtotime(Tools::_date(null,'Y-m-d H:i:s','PT10M'))){
                    $success = 0;
                    $msg[] = Lang::get('Date invalid.');
                }
                
                if($success !== 1) break;
                //</editor-fold>
                
                    
                if($warehouse_from_id === $warehouse_to_id){
                    $success = 0;
                    $msg[] = Lang::get(array('Warehouse From',array('val'=>'and','uc_first'=>false),'Warehouse To','similar'),true,true,false,false,true);
                }
                
                get_instance()->load->helper('product_stock_engine');
                $product_stock_db = Product_Stock_Engine::stock_mass_get('stock_sales_available',$stock_transfer_product,array($warehouse_from_id));
                //<editor-fold defaultstate="collapsed" desc="Product Looop">
                foreach($stock_transfer_product as $idx=>$row){
                    $qty = isset($row['qty'])?Tools::_str($row['qty']):'';
                    foreach($product_stock_db as $idx2=>$row2){
                        $qty_stock = isset($row2['qty'])?Tools::_str($row2['qty']):'';
                        if(Tools::_float($qty)> $qty_stock){
                            $success = 0;
                            $msg[] = 'Product Qty '.Lang::get('higher than').' Product Stock';
                            break;
                        }
                    }
                    
                    if(Tools::_float($qty) === Tools::_float('0')){
                        $success = 0;
                        $msg[] = 'Product Qty '.Lang::get('empty',true,false);
                    }
                    
                    if($success !== 1) break;
                }
                //</editor-fold>
                
                
                //</editor-fold>
                break;
            case 'stock_transfer_process':
                //<editor-fold defaultstate="collapsed">
                $temp_result = Validator::validate_on_update(
                    array(
                        'module'=>'stock_transfer',
                        'module_name'=>Lang::get('Stock Transfer'),
                        'module_engine'=>'Stock_Transfer_Engine',
                    ),
                    $stock_transfer
                );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;
                
                //</editor-fold>
                break;
            case 'stock_transfer_done':
                //<editor-fold defaultstate="collapsed">
                $temp_result = Validator::validate_on_update(
                    array(
                        'module'=>'stock_transfer',
                        'module_name'=>Lang::get('Stock Transfer'),
                        'module_engine'=>'Stock_Transfer_Engine',
                    ),
                    $stock_transfer
                );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;
                
                //</editor-fold>
                break;
            
            case 'stock_transfer_canceled':
                $db = new DB();
                $temp_result = Validator::validate_on_cancel(
                        array(
                            'module'=>'stock_transfer',
                            'module_name'=>'Sales Receipt',
                            'module_engine'=>'Stock_Transfer_Engine',
                        ),
                        $stock_transfer
                    );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;

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
        $db = new DB();
        $result = array();
        $stock_transfer_data = isset($data['stock_transfer'])?
            Tools::_arr($data['stock_transfer']):array();        
        $stock_transfer_product_data = isset($data['stock_transfer_product'])?
            Tools::_arr($data['stock_transfer_product']):array();
        $modid = User_Info::get()['user_id'];
        $datetime_curr = Date('Y-m-d H:i:s');
        switch($action){
            case 'stock_transfer_add':
                //<editor-fold defaultstate="collapsed">

                $stock_transfer = array();

                $stock_transfer = array(
                    'store_id'=>$stock_transfer_data['store_id'],
                    'requestor_name'=>Tools::_str($stock_transfer_data['requestor_name']),
                    'stock_transfer_date'=>Tools::_date(Tools::_str($stock_transfer_data['stock_transfer_date']),'Y-m-d H:i:s'),
                    'warehouse_from_id'=>Tools::_str($stock_transfer_data['warehouse_from_id']),
                    'warehouse_to_id'=>Tools::_str($stock_transfer_data['warehouse_to_id']),
                    'stock_transfer_status'=>SI::status_default_status_get('stock_transfer_engine')['val'],
                    'notes'=>isset($stock_transfer_data['notes'])?
                        Tools::empty_to_null(Tools::_str($stock_transfer_data['notes'])):'',
                    'modid'=>$modid,
                    'status'=>'1',
                    'moddate'=>$datetime_curr,
                );

                $stock_transfer_product = array();
                foreach($stock_transfer_product_data as $idx=>$row){
                    $stock_transfer_product[] = array(
                        'product_id'=>Tools::_str($row['product_id'])
                        ,'unit_id'=>Tools::_str($row['unit_id'])
                        ,'qty'=>Tools::_str($row['qty'])
                        ,'stock_transfer_product_type'=>'registered_product'
                    );
                }
                
                $result['stock_transfer'] = $stock_transfer;                   
                $result['stock_transfer_product'] = $stock_transfer_product;                   
                //</editor-fold>
                break;
            case 'stock_transfer_process':                
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('stock_transfer/stock_transfer_engine');
                $stock_transfer = array();
                $rwo_product = array();
                
                //<editor-fold defaultstate="collapsed" desc="Stock Transfer">
                $stock_transfer = array(
                    'notes'=>isset($stock_transfer_data['notes'])?
                        Tools::empty_to_null(Tools::_str($stock_transfer_data['notes'])):null,
                    'stock_transfer_status'=>'process',
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                //</editor-fold>
                
                $result['stock_transfer'] = $stock_transfer;  
                
                //</editor-fold>
                break;
            case 'stock_transfer_done':                
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('stock_transfer/stock_transfer_engine');
                $stock_transfer = array();
                $rwo_product = array();
                
                //<editor-fold defaultstate="collapsed" desc="Stock Transfer">
                $stock_transfer = array(
                    'notes'=>isset($stock_transfer_data['notes'])?
                        Tools::empty_to_null(Tools::_str($stock_transfer_data['notes'])):null,
                    'stock_transfer_status'=>'done',
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                //</editor-fold>
                
                $result['stock_transfer'] = $stock_transfer;  
                
                //</editor-fold>
                break;
            case 'stock_transfer_canceled':
                $stock_transfer = array();

                $stock_transfer = array(
                    'stock_transfer_status'=>'X',
                    'cancellation_reason'=>$stock_transfer_data['cancellation_reason'],
                    'notes'=>isset($stock_transfer_data['notes'])?
                        Tools::_str($stock_transfer_data['notes']):'',
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                $result['stock_transfer'] = $stock_transfer;    
                break;
        }

        return $result;
    }

    public function stock_transfer_add($db,$final_data,$id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('product_stock_engine');
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $fstock_transfer = $final_data['stock_transfer'];
        $fstock_transfer_product = $final_data['stock_transfer_product'];

        $store_id = $fstock_transfer['store_id'];
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $stock_transfer_id = '';       
        $fstock_transfer['code'] = SI::code_counter_store_get($db,$store_id, 'stock_transfer');
        if(!$db->insert('stock_transfer',$fstock_transfer)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $stock_transfer_code = $fstock_transfer['code'];

        if($success == 1){                                
            $stock_transfer_id = $db->fast_get('stock_transfer'
                    ,array('code'=>$stock_transfer_code))[0]['id'];
            $result['trans_id']=$stock_transfer_id; 
        }
        
        if($success === 1){
            foreach($fstock_transfer_product as $idx=>$product){
                $product['stock_transfer_id'] = $stock_transfer_id;
                if(!$db->insert('stock_transfer_product',$product)){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                    break;
                }
            }
        }
        
        if($success == 1){
            $stock_transfer_status_log = array(
                'stock_transfer_id'=>$stock_transfer_id
                ,'stock_transfer_status'=>$fstock_transfer['stock_transfer_status']
                ,'modid'=>$modid
                ,'moddate'=>$moddate    
            );

            if(!$db->insert('stock_transfer_status_log',$stock_transfer_status_log)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
                
            }
        }

        if($success === 1){
            foreach($fstock_transfer_product as $idx=>$product){
                $temp_result = Product_Stock_Engine::stock_sales_available_only_add(
                    $db,
                    $fstock_transfer['warehouse_from_id'],
                    $product['product_id'],
                    -1*Tools::_float($product['qty']),
                    $product['unit_id'],
                    'Stock Transfer: '.$fstock_transfer['code'].' '.SI::status_get('stock_transfer_engine',
                        $fstock_transfer['stock_transfer_status'])['label'],
                    $moddate
                );
                $success = $temp_result['success'];
                $msg = array_merge($msg,$temp_result['msg']);
                if($success !== 1) break;
                
            }
            
        }
        
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    function stock_transfer_process($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $fstock_transfer = $final_data['stock_transfer'];
        

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('stock_transfer',$fstock_transfer,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            $temp_result = SI::status_log_add($db,'stock_transfer',
                $id,$fstock_transfer['stock_transfer_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    function stock_transfer_done($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('product_stock_engine');
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $fstock_transfer = $final_data['stock_transfer'];

        $stock_transfer_db = Stock_Transfer_Data_Support::stock_transfer_get($id);
        $stock_transfer_product_db = Stock_Transfer_Data_Support::stock_transfer_product_get($id);
        $old_status = $stock_transfer_db['stock_transfer_status'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('stock_transfer',$fstock_transfer,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            $temp_result = SI::status_log_add($db,'stock_transfer',
                $id,$fstock_transfer['stock_transfer_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        if($success === 1 && $old_status ==='process'){
            foreach($stock_transfer_product_db as $idx=>$product){
                if($success === 1){
                    $temp_result = Product_Stock_Engine::stock_good_only_add(
                        $db,
                        $stock_transfer_db['warehouse_from_id'],
                        $product['product_id'],
                        Tools::_float('-1')*Tools::_float($product['qty']),
                        $product['unit_id'],
                        'Stock Transfer: '.$stock_transfer_db['code'].' '.SI::status_get('stock_transfer_engine',
                            $stock_transfer_db['stock_transfer_status'])['label'],
                        $moddate
                    );
                    $success = $temp_result['success'];
                    $msg = array_merge($msg,$temp_result['msg']);
                }
                
                if($success === 1){
                    $temp_result = Product_Stock_Engine::stock_good_add(
                        $db,
                        $stock_transfer_db['warehouse_to_id'],
                        $product['product_id'],
                        Tools::_float($product['qty']),
                        $product['unit_id'],
                        'Stock Transfer: '.$stock_transfer_db['code'].' '.SI::status_get('stock_transfer_engine',
                            $stock_transfer_db['stock_transfer_status'])['label'],
                        $moddate
                    );
                    $success = $temp_result['success'];
                    $msg = array_merge($msg,$temp_result['msg']);
                }
                
                if($success !== 1) break;
                
            }
            
        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }
    
    function stock_transfer_canceled($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('product_stock_engine');
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();
        
        $stock_transfer_id = $id;
        
        $fstock_transfer = $final_data['stock_transfer'];
        
        $stock_transfer_db = Stock_Transfer_Data_Support::stock_transfer_get($id);
        $stock_transfer_product_db = Stock_Transfer_Data_Support::stock_transfer_product_get($id);
        $old_status = $stock_transfer_db['stock_transfer_status'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        $stock_transfer = $db->fast_get('stock_transfer',array('id'=>$stock_transfer_id))[0];
        
        if(!$db->update('stock_transfer',$fstock_transfer,array("id"=>$stock_transfer_id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'stock_transfer',
                $stock_transfer_id,$stock_transfer['stock_transfer_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        if($success === 1 && $old_status === 'process'){
            foreach($stock_transfer_product_db as $idx=>$product){
                
                $temp_result = Product_Stock_Engine::stock_sales_available_only_add(
                    $db,
                    $stock_transfer_db['warehouse_from_id'],
                    $product['product_id'],
                    Tools::_float($product['qty']),
                    $product['unit_id'],
                    'Stock Transfer: '.$stock_transfer_db['code'].' '.SI::status_get('stock_transfer_engine',
                        $stock_transfer_db['stock_transfer_status'])['label'],
                    $moddate
                );
                $success = $temp_result['success'];
                $msg = array_merge($msg,$temp_result['msg']);
                if($success !== 1) break;
                
            }
        }
        
        if($success === 1 && $old_status === 'done'){
            foreach($stock_transfer_product_db as $idx=>$product){
                if($success === 1){
                    $temp_result = Product_Stock_Engine::stock_good_add(
                        $db,
                        $stock_transfer_db['warehouse_from_id'],
                        $product['product_id'],
                        Tools::_float($product['qty']),
                        $product['unit_id'],
                        'Stock Transfer: '.$stock_transfer_db['code'].' '.SI::status_get('stock_transfer_engine',
                            $stock_transfer_db['stock_transfer_status'])['label'],
                        $moddate
                    );
                    $success = $temp_result['success'];
                    $msg = array_merge($msg,$temp_result['msg']);
                }
                
                if($success === 1){
                    $temp_result = Product_Stock_Engine::stock_good_add(
                        $db,
                        $stock_transfer_db['warehouse_to_id'],
                        $product['product_id'],
                        Tools::_float('-1')*Tools::_float($product['qty']),
                        $product['unit_id'],
                        'Stock Transfer: '.$stock_transfer_db['code'].' '.SI::status_get('stock_transfer_engine',
                            $stock_transfer_db['stock_transfer_status'])['label'],
                        $moddate
                    );
                    $success = $temp_result['success'];
                    $msg = array_merge($msg,$temp_result['msg']);
                }
                
                if($success !== 1) break;
                
            }
        }
        
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
     
    
}
?>