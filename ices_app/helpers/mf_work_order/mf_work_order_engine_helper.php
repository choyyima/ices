<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mf_Work_Order_Engine {
    public static $prefix_id = 'mf_work_order';
    public static $prefix_method;
    public static $status_list;
    public static $module_type_list;

    public static function helper_init(){
        self::$prefix_method = self::$prefix_id;
        
        self::$status_list = array(
            //<editor-fold defaultstate="collapsed">
            array(
                'val'=>''
                ,'label'=>''
                , 'method'=>'mf_work_order_add'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Add')
                        ,array('val'=>Lang::get(array('Manufacturing - Work Order'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            array(//label name is used for method name
                'val'=>'initialized'
                ,'label'=>'INITIALIZED'
                ,'method'=>'mf_work_order_initialized'
                ,'next_allowed_status'=>array('approved','rejected','X')
                ,'default'=>true
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update')
                        ,array('val'=>Lang::get(array('Manufacturing - Work Order'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            array(//label name is used for method name
                'val'=>'approved'
                ,'label'=>'APPROVED'
                ,'method'=>'mf_work_order_approved'
                ,'next_allowed_status'=>array('X')
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update')
                        ,array('val'=>Lang::get(array('Manufacturing - Work Order'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            )
            ,array(
                'val'=>'rejected'
                ,'label'=>'REJECTED'
                ,'method'=>'mf_work_order_rejected'
                ,'next_allowed_status'=>array('X')
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update')
                        ,array('val'=>Lang::get(array('Manufacturing - Work Order'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            )  
            ,array(
                'val'=>'X'
                ,'label'=>'CANCELED'
                ,'method'=>'mf_work_order_canceled'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Cancel')
                        ,array('val'=>Lang::get(array('Manufacturing - Work Order'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ) 
            //</editor-fold>
        );
       
        self::$module_type_list = array(
            array('val'=>'normal','label'=>'Normal'),
            array('val'=>'good_stock_transform','label'=>'Good Stock Transformation'),
            array('val'=>'bad_stock_transform','label'=>'Bad Stock Transformation'),
        );
    }
    
    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'mf_work_order/'
            ,'mf_work_order_engine'=>'mf_work_order/mf_work_order_engine'
            ,'mf_work_order_data_support'=>'mf_work_order/mf_work_order_data_support'
            ,'mf_work_order_renderer' => 'mf_work_order/mf_work_order_renderer'
            ,'ajax_search'=>get_instance()->config->base_url().'mf_work_order/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'mf_work_order/data_support/'

        );

        return json_decode(json_encode($path));
    }

    public static function delete_all_related_table_records($db, $mf_work_order_id){
        //<editor-fold defaultstate="collapsed">
        $success = 1;
        $msg = array();
        $result = array('success'=>$success,'msg' => $msg);
        
        $q = 'delete from mf_work_order_component_product where mf_work_order_id = '.$db->escape($mf_work_order_id);
        if(!$db->query($q)){
            $success = 0;
            $msg[] = $db->_error_message();
            $db->trans_rollback();
        }
        
        if($success === 1){
            $q = 'delete from mf_work_order_result_product where mf_work_order_id = '.$db->escape($mf_work_order_id);
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
        get_instance()->load->helper('mf_work_order/mf_work_order_data_support');
        $result = array(
            "success"=>1
            ,"msg"=>array()
        );
        $success = 1;
        $msg = array();
        
        $mf_work_order = isset($data['mf_work_order'])?Tools::_arr($data['mf_work_order']):null;
        $mfwo_ordered_product = isset($data['mfwo_ordered_product'])?Tools::_arr($data['mfwo_ordered_product']):null;
        $mfwo_info = isset($data['mfwo_info'])?Tools::_arr($data['mfwo_info']):null;
        $mf_work_order_type = isset($mf_work_order['mf_work_order_type'])?Tools::_str($mf_work_order['mf_work_order_type']):'';
        $mf_work_order_id = $data['mf_work_order']['id'];
        $mf_work_order_db = Mf_Work_Order_Data_Support::mf_work_order_get($mf_work_order_id);
        $db = new DB();
        switch($action){
            case self::$prefix_method.'_add':
                $start_date_plan = isset($mfwo_info['start_date_plan'])?
                    Tools::_str($mfwo_info['start_date_plan']):'';
                $end_date_plan = isset($mfwo_info['end_date_plan'])?
                    Tools::_str($mfwo_info['end_date_plan']):'';
                $store_id = isset($mf_work_order['store_id'])?Tools::_str($mf_work_order['store_id']):'';
                //<editor-fold defaultstate="collapsed" desc="Major Validation">
                              
                if(!Store_Engine::store_exists($store_id)){
                    $success = 0;
                    $msg[] = Lang::get('Store').' '.Lang::get('empty',true,false);
                }
                
                if(!SI::type_match('Mf_Work_Order_Engine',$mf_work_order_type)){
                    $success = 0;
                    $msg[] = Lang::get(array('Module Type','invalid'),true,true,false,false,true);
                }
                
                if(strtotime($start_date_plan)<strtotime(Tools::_date(''))){
                    $success = 0;
                    $msg[] = Lang::get(array('Start','Date')).Lang::get(array(array('val'=>'is lower than','uc_first'=>false),Tools::_date('','F d Y, H:i:s','PT10M')),true,false,false,false,true);
                }
                
                if(strtotime($end_date_plan)<strtotime($start_date_plan)){
                    $success = 0;
                    $msg[] = Lang::get(array('End','Date')).Lang::get(array(array('val'=>'is lower than','uc_first'=>false),Tools::_date($start_date_plan,'F d Y, H:i:s')),true,false,false,false,true);
                }
                
                if($success !== 1) break;
                //</editor-fold>
                
                switch($mf_work_order_type){
                    case 'normal':
                        get_instance()->load->helper('bom/bom_data_support');
                        //<editor-fold defaultstate="collapsed">

                        $t_ordered_reg_prod = array();

                        //<editor-fold defaultstate="collapsed" desc="Prepare Ordered Prod">

                        foreach($mfwo_ordered_product as $i=>$row){
                            $product_type = isset($row['product_type'])?Tools::_str($row['product_type']):'';
                            if($product_type === 'registered_product'){
                                $t_ordered_reg_prod[] = array(
                                    'product_type'=>$product_type,
                                    'product_id'=>isset($row['product_id'])?Tools::_str($row['product_id']):'',
                                    'unit_id'=>isset($row['unit_id'])?Tools::_str($row['unit_id']):'',
                                    'qty'=>isset($row['qty'])?Tools::_str($row['qty']):'',
                                    'bom_id'=>isset($row['bom_id'])?Tools::_str($row['bom_id']):'',
                                );
                            }
                        }
                        //</editor-fold>
                        
                        
                        if(count($t_ordered_reg_prod)=== 0 ){
                            $success = 0;
                            $msg[] = lang::get(array('Ordered','Product')).Lang::get('empty',true,false,false,false,true);
                        }
                        
                        if(!BOM_Data_Support::product_unit_all_exists($mfwo_ordered_product)){
                            $success = 0;
                            $msg[] = lang::get(array('Ordered','Product')).Lang::get('invalid',true,false,false,false,true);
                        }
                        
                        //</editor-fold>
                        break;
                    case 'good_stock_transform':
                    case 'bad_stock_transform':
                        get_instance()->load->helper('product/product_data_support');
                        //<editor-fold defaultstate="collapsed">

                        $t_ordered_reg_prod = array();

                        //<editor-fold defaultstate="collapsed" desc="Prepare Ordered Prod">

                        foreach($mfwo_ordered_product as $i=>$row){
                            $product_type = isset($row['product_type'])?Tools::_str($row['product_type']):'';
                            if($product_type === 'registered_product'){
                                $t_ordered_reg_prod[] = array(
                                    'product_type'=>$product_type,
                                    'product_id'=>isset($row['product_id'])?Tools::_str($row['product_id']):'',
                                    'unit_id'=>isset($row['unit_id'])?Tools::_str($row['unit_id']):'',
                                    'qty'=>isset($row['qty'])?Tools::_str($row['qty']):'',
                                );
                            }
                        }
                        //</editor-fold>
                                                
                        if(count($t_ordered_reg_prod)=== 0 ){
                            $success = 0;
                            $msg[] = lang::get(array('Ordered','Product')).Lang::get('empty',true,false,false,false,true);
                        }
                        
                        if(!Product_Data_Support::product_unit_all_exists($mfwo_ordered_product,array('product_status'=>'active'))){
                            $success = 0;
                            $msg[] = lang::get(array('Ordered','Product')).Lang::get('invalid',true,false,false,false,true);
                        }
                        
                        //</editor-fold>
                        break;
                    case 'default':
                        $success = 0;
                        $msg[] = Lang::get('Module Type').'invalid';
                        break;
                }
                

                break;
            case self::$prefix_method.'_initialized':
                //<editor-fold defaultstate="collapsed">
                $temp_result = Validator::validate_on_update(
                        array(
                            'module'=>'mf_work_order',
                            'module_name'=>Lang::get('Manufacturing Work Order'),
                            'module_engine'=>'mf_work_order_engine',
                        ),
                        $mf_work_order
                    );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;
                //</editor-fold>
                break;
            case self::$prefix_method.'_approved':
                //<editor-fold defaultstate="collapsed">
                $approver = isset($mfwo_info['approver'])?Tools::_str($mfwo_info['approver']):'';
                $temp_result = Validator::validate_on_update(
                        array(
                            'module'=>'mf_work_order',
                            'module_name'=>Lang::get('Manufacturing Work Order'),
                            'module_engine'=>'mf_work_order_engine',
                        ),
                        $mf_work_order
                    );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;
                
                if($mf_work_order_db['mf_work_order_status'] === 'initialized'){
                    if(Tools::empty_to_null($approver) === null){
                        $success = 0;
                        $msg[] = Lang::get(array('Approver','empty'));
                    }
                }
                
                //</editor-fold>
                break;
            case self::$prefix_method.'_rejected':
                //<editor-fold defaultstate="collapsed">
                $rejector = isset($mfwo_info['rejector'])?Tools::_str($mfwo_info['rejector']):'';
                $temp_result = Validator::validate_on_update(
                        array(
                            'module'=>'mf_work_order',
                            'module_name'=>Lang::get('Manufacturing Work Order'),
                            'module_engine'=>'mf_work_order_engine',
                        ),
                        $mf_work_order
                    );
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];
                if($success !== 1) break;
                
                if($mf_work_order_db['mf_work_order_status'] === 'initialized'){
                    if(Tools::empty_to_null($rejector ) === null){
                        $success = 0;
                        $msg[] = Lang::get(array('Rejector','empty'));
                    }
                }
                //</editor-fold>
                break;
            case self::$prefix_method.'_canceled':
                //<editor-fold defaultstate="collapsed">
                $temp_result = Validator::validate_on_cancel(
                        array(
                            'module'=>'mf_work_order',
                            'module_name'=>Lang::get('Manufacturing Work Order'),
                            'module_engine'=>'mf_work_order_engine',
                        ),
                        $mf_work_order
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

        $mf_work_order_data = isset($data['mf_work_order'])?$data['mf_work_order']:array();
        $mfwo_ordered_product_data = isset($data['mfwo_ordered_product'])?Tools::_arr($data['mfwo_ordered_product']):array();
        $mfwo_info_data = isset($data['mfwo_info'])?Tools::_arr($data['mfwo_info']):array();
        $mf_work_order_db = Mf_Work_Order_Data_Support::mf_work_order_get($mf_work_order_data['id']);
        
        $modid = User_Info::get()['user_id'];
        $datetime_curr = Date('Y-m-d H:i:s');
        
        switch($method){
            case self::$prefix_method.'_add':
                //<editor-fold defaultstate="collapsed">
                $mf_work_order_type = Tools::_str($mf_work_order_data['mf_work_order_type']);
                
                $mf_work_order = array(
                    'store_id'=>  Tools::_str($mf_work_order_data['store_id']),
                    'mf_work_order_type' => Tools::_str($mf_work_order_data['mf_work_order_type']),
                    'mf_work_order_date'=> $datetime_curr,
                    'notes' => Tools::empty_to_null(Tools::_str($mf_work_order_data['notes'])),
                    'mf_work_order_status'=>SI::status_default_status_get('mf_work_order_engine')['val'],
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr
                );
                
                $mfwo_info = array(
                    'start_date_plan'=>Tools::_str($mfwo_info_data['start_date_plan']),
                    'end_date_plan'=>Tools::_str($mfwo_info_data['end_date_plan']),
                );

                $mfwo_ordered_product = array();
                foreach($mfwo_ordered_product_data as $i=>$row){
                    $mfwo_ordered_product[] = array(
                        'product_type'=>$row['product_type'],
                        'product_id'=>$row['product_id'],
                        'unit_id'=>$row['unit_id'],
                        'qty'=>$row['qty'],
                        'outstanding_qty'=>$row['qty'],
                    );
                    if($mf_work_order_type === 'normal'){
                        $mfwo_ordered_product[count($mfwo_ordered_product)-1]['bom_id'] = $row['bom_id'] ;
                    }
                }
                
                $result['mf_work_order'] = $mf_work_order;
                $result['mfwo_info'] = $mfwo_info;
                $result['mfwo_ordered_product'] = $mfwo_ordered_product;                
                
                //</editor-fold>
                break;
                
            case self::$prefix_method.'_initialized':
                //<editor-fold defaultstate="collapsed">
                $mf_work_order = array(
                    'notes' => isset($mf_work_order_data['notes'])?
                        Tools::empty_to_null(Tools::_str($mf_work_order_data['notes'])):null,
                    'mf_work_order_status'=>'initialized',
                    
                );
                //</editor-fold>
                $result['mf_work_order'] = $mf_work_order;
                break;
            case self::$prefix_method.'_approved':
                //<editor-fold defaultstate="collapsed">
                $mf_work_order = array(
                    'notes' => isset($mf_work_order_data['notes'])?
                        Tools::empty_to_null(Tools::_str($mf_work_order_data['notes'])):null,
                    'mf_work_order_status'=>'approved',                    
                );
                
                $result['mf_work_order'] = $mf_work_order;
                
                if($mf_work_order_db['mf_work_order_status'] !== 'approved'){
                    $mfwo_info = array(
                        'approver' => $mfwo_info_data['approver'],
                        'approved_date' => Tools::_date('')
                    );
                    
                    $result['mfwo_info'] = $mfwo_info;
                }
                //</editor-fold>
                break;
            case self::$prefix_method.'_rejected':
                //<editor-fold defaultstate="collapsed">
                $mf_work_order = array(
                    'notes' => isset($mf_work_order_data['notes'])?
                        Tools::empty_to_null(Tools::_str($mf_work_order_data['notes'])):null,
                    'mf_work_order_status'=>'rejected',                    
                );
                
                $result['mf_work_order'] = $mf_work_order;
                
                if($mf_work_order_db['mf_work_order_status'] !== 'approved'){
                    $mfwo_info = array(
                        'rejector' => $mfwo_info_data['rejector'],
                        'rejected_date' => Tools::_date('')
                    );
                    
                    $result['mfwo_info'] = $mfwo_info;
                }
                //</editor-fold>
                break;
            case self::$prefix_method.'_canceled':
                //<editor-fold defaultstate="collapsed">
                $mf_work_order = array();

                $mf_work_order = array(
                    'mf_work_order_status'=>'X',
                    'cancellation_reason'=>$mf_work_order_data['cancellation_reason'],
                    'notes'=>isset($mf_work_order_data['notes'])?
                        Tools::empty_to_null(Tools::_str($mf_work_order_data['notes'])):null,
                    'modid'=>$modid,
                    'moddate'=>$datetime_curr,
                );
                $result['mf_work_order'] = $mf_work_order;
                //</editor-fold>
                break;
                
        }        

        return $result;
        //</editor-fold>
    }

    public function mf_work_order_add($db,$final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $fmf_work_order = $final_data['mf_work_order'];
        $fmfwo_info = $final_data['mfwo_info'];
        $fmfwo_ordered_product = $final_data['mfwo_ordered_product'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        $store_id = $fmf_work_order['store_id'];        
        $mf_work_order_type = $fmf_work_order['mf_work_order_type'];        
        $mf_work_order_id = '';
        
        $fmf_work_order['code'] = SI::code_counter_store_get($db,$store_id,'mf_work_order');
        
        if(!$db->insert('mf_work_order',$fmf_work_order)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        $mf_work_order_code = $fmf_work_order['code'];

        if($success == 1){                                
            $mf_work_order_id = $db->fast_get('mf_work_order'
                    ,array('code'=>$mf_work_order_code))[0]['id'];
            $result['trans_id']=$mf_work_order_id; 
        }
        
        if($success == 1){
            $mf_work_order_status_log = array(
                'mf_work_order_id'=>$mf_work_order_id
                ,'mf_work_order_status'=>$fmf_work_order['mf_work_order_status']
                ,'modid'=>$modid
                ,'moddate'=>$moddate    
            );

            if(!$db->insert('mf_work_order_status_log',$mf_work_order_status_log)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
                
            }
        }
        
        if($success === 1){
            $fmfwo_info['mf_work_order_id'] = $mf_work_order_id;
            if(!$db->insert('mfwo_info',$fmfwo_info)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }
        
        if($success === 1){

            foreach($fmfwo_ordered_product as $i=>$row){
                $row['mf_work_order_id'] = $mf_work_order_id;
                if(!$db->insert('mfwo_ordered_product',$row)){
                    $success = 0;
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();
                    break;
                }
            }
        }
        

        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    
    function mf_work_order_initialized($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->engine('mf_work_order/mf_work_order_data_support');
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $fmf_work_order = $final_data['mf_work_order'];
        $fmfwo_info = isset($final_data['mfwo_info'])?Tools::_arr($final_data['mfwo_info']):array();
        
        $mf_work_order_id = $id;
        $mf_work_order_db = Mf_Work_Order_Data_Support::mf_work_order_get($mf_work_order_id);
        
        $mf_work_order_type = $mf_work_order_db['mf_work_order_type'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('mf_work_order',$fmf_work_order,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            $temp_result = SI::status_log_add($db,'mf_work_order',
                $id,$fmf_work_order['mf_work_order_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    function mf_work_order_approved($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('mf_work_order/mf_work_order_data_support');
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $fmf_work_order = $final_data['mf_work_order'];
        $fmfwo_info = isset($final_data['mfwo_info'])?Tools::_arr($final_data['mfwo_info']):array();
        
        $mf_work_order_id = $id;
        $mf_work_order_db = Mf_Work_Order_Data_Support::mf_work_order_get($mf_work_order_id);
        
        $mf_work_order_type = $mf_work_order_db['mf_work_order_type'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('mf_work_order',$fmf_work_order,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            $temp_result = SI::status_log_add($db,'mf_work_order',
                $id,$fmf_work_order['mf_work_order_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        
        if($success === 1){
            if($mf_work_order_db['mf_work_order_status'] !== 'approved'){
                if(!$db->update('mfwo_info',$fmfwo_info,array("mf_work_order_id"=>$id))){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                }
            }
        }
        
        
        
        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    function mf_work_order_rejected($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('mf_work_order/mf_work_order_data_support');
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $fmf_work_order = $final_data['mf_work_order'];
        $fmfwo_info = isset($final_data['mfwo_info'])?Tools::_arr($final_data['mfwo_info']):array();
        
        $mf_work_order_id = $id;
        $mf_work_order_db = Mf_Work_Order_Data_Support::mf_work_order_get($mf_work_order_id);
        
        $mf_work_order_type = $mf_work_order_db['mf_work_order_type'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('mf_work_order',$fmf_work_order,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            $temp_result = SI::status_log_add($db,'mf_work_order',
                $id,$fmf_work_order['mf_work_order_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        
        if($success === 1){
            if($mf_work_order_db['mf_work_order_status'] !== 'rejected'){
                if(!$db->update('mfwo_info',$fmfwo_info,array("mf_work_order_id"=>$id))){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                }
            }
        }
        
        
        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    function mf_work_order_canceled($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('mf_work_order/mf_work_order_data_support');
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $fmf_work_order = $final_data['mf_work_order'];
        $fmfwo_info = isset($final_data['mfwo_info'])?Tools::_arr($final_data['mfwo_info']):array();
        
        $mf_work_order_id = $id;
        $mf_work_order_db = Mf_Work_Order_Data_Support::mf_work_order_get($mf_work_order_id);
        
        $mf_work_order_type = $mf_work_order_db['mf_work_order_type'];
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('mf_work_order',$fmf_work_order,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }
        
        if($success == 1){
            $temp_result = SI::status_log_add($db,'mf_work_order',
                $id,$fmf_work_order['mf_work_order_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    public static function ordered_product_outstanding_qty_add($db, $mfwo_ordered_product_id, $qty){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();
        
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $q = '
            update mfwo_ordered_product
            set outstanding_qty = outstanding_qty + '.$db->escape($qty).'
            where mfwo_ordered_product.id = '.$db->escape($mfwo_ordered_product_id).'
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
