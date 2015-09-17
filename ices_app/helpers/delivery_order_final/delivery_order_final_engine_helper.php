<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Delivery_Order_Final_Engine {

    public static $module_type_list = array(
        array('val'=>'sales_invoice','label'=>'Sales Invoice'),
    );

    public static $status_list = array(
        //<editor-fold defaultstate="collapsed">
        array(//label name is used for method name
            'val'=>'process'
            ,'label'=>'PROCESS'
            ,'method'=>'dof_process'
            ,'default'=>true
            ,'next_allowed_status'=>array('done','X')
        )
        ,array(
            'val'=>'done'
            ,'label'=>'DONE'
            ,'method'=>'delivery_order_final_done'
            ,'next_allowed_status'=>array('X')
        )
        ,array(
            'val'=>'confirmed'
            ,'label'=>'CONFIRMED'
            ,'method'=>'delivery_order_final_confirmed'
            ,'next_allowed_status'=>array('X')
        )
        ,array(
            'val'=>'X'
            ,'label'=>'CANCELED'
            ,'method'=>'delivery_order_final_canceled'
            ,'next_allowed_status'=>array()
        )
        //</editor-fold>
    );

    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'delivery_order_final/'
            ,'delivery_order_final_engine'=>'delivery_order_final/delivery_order_final_engine'
            ,'delivery_order_final_data_support' => 'delivery_order_final/delivery_order_final_data_support'
            ,'delivery_order_final_renderer' => 'delivery_order_final/delivery_order_final_renderer'                
            ,'ajax_search'=>get_instance()->config->base_url().'delivery_order_final/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'sales_prospect/data_support/'                
        );

        return json_decode(json_encode($path));
    }

    public static function delivery_order_final_exists($id){
        $result = false;
        $db = new DB();
        $q = '
            select 1 
            from delivery_order_final 
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
        if($method == 'add') $data['delivery_order_final']['id'] = '';
        else $data['delivery_order_final']['id'] = $id;

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
        get_instance()->load->helper('delivery_order_final/delivery_order_final_data_support');
        $result = array(
            "success"=>1
            ,"msg"=>array()

        );
        $delivery_order_final = isset($data['delivery_order_final'])?$data['delivery_order_final']:null;
        $delivery_order_final_id = isset($delivery_order_final['id'])?
            Tools::_str($delivery_order_final['id']):'';
        $warehouse_to = isset($data['warehouse_to'])?$data['warehouse_to']:null;
        $reference = isset($data['reference'])?Tools::_arr($data['reference']):array();
        $delivery_order = isset($data['delivery_order'])? Tools::_arr($data['delivery_order']):array();

        $db = new DB();
        switch($method){
            case 'delivery_order_final_add':
                //<editor-fold defaultstate="collapsed">
                $ref_id = Tools::empty_to_null(isset($reference['id'])?Tools::_str($reference['id']):'');
                $delivery_order_final_type = isset($delivery_order_final['delivery_order_final_type'])?
                    Tools::_str($delivery_order_final['delivery_order_final_type']):'';
                
                if(is_null($ref_id)){
                    $result['success'] = 0;
                    $result['msg'][] = 'Reference'.' '.Lang::get('empty',true,false);
                    break;
                }
                
                if(!SI::type_match('Delivery_Order_Final_Engine',$delivery_order_final_type)){
                    $result['success'] = 0;
                    $result['msg'][]='Mismatch Module Type';
                    break;
                }

                //check store is available
                $store_id = isset($delivery_order_final['store_id'])?$delivery_order_final['store_id']:'';
                $q = 'select 1 from store where status>0 and id ='.$db->escape($store_id);
                if(count($db->query_array_obj($q)) == 0){
                    $result['success'] = 0;
                    $result['msg'][] = Lang::get("Store").' '.Lang::get("Empty",true,false);
                }                   


                //check warehouse from is available
                $warehouse_id = isset($warehouse_to['id'])?
                        $warehouse_to['id']:'';
                $q = 'select 1 from warehouse where status>0 and id = '.$db->escape($warehouse_id).'';
                if(count($db->query_array_obj($q)) === 0){
                    $result['success'] = 0;
                    $result['msg'][] = Lang::get("Warehouse To").' '.Lang::get("Empty",true,false);
                    break;
                }

                //check delivery_order_final date
                $delivery_order_final_date = Tools::_date(isset($delivery_order_final['delivery_order_final_date'])?
                    $delivery_order_final['delivery_order_final_date']:'','Y-m-d H:i:s');
                if(strtotime($delivery_order_final_date) < strtotime(Tools::_date('','Y-m-d H:i:s'))){
                    $result['success'] = 0;
                    $result['msg'][] = "Delivery Order Final Date ".Lang::get("must be greater than",true,false,false,true).' '.Tools::_date('','F d, Y H:i:s');
                    break;
                }

                $ref_exists = false;
                if($delivery_order_final_type ==='sales_invoice'){
                    if(count($db->fast_get('sales_invoice', array('id'=>$ref_id,'sales_invoice_status'=>'invoiced')))> 0){
                        $ref_exists=true;
                    }
                }
                if(!$ref_exists){
                    $result['success'] = 0;
                    $result['msg'][] = 'Reference Empty';
                }

                $rd_expected = Delivery_Order_Final_Data_Support::reference_dependency_get($delivery_order_final_type, $ref_id);
                $product_total = array();// use to calculate total selected product between warehouse
                $delivery_order_exists = false; 
                foreach($delivery_order  as $do_idx=>$do){   
                    $delivery_order_exists = true;
                    $do_product = isset($do['product'])?$do['product']:array();
                    $product_exists = false;
                    $warehouse_id = isset($do['warehouse_from_id'])?$do['warehouse_from_id']:'';

                    for($i = 0;$i<count($do_product);$i++){
                        $p_reference_type = isset($do_product[$i]['reference_type'])?Tools::_str($do_product[$i]['reference_type']):'';
                        $p_reference_id = isset($do_product[$i]['reference_id'])?Tools::_str($do_product[$i]['reference_id']):'';
                        $product_id = isset($do_product[$i]['product_id'])?Tools::_str($do_product[$i]['product_id']):'';
                        $unit_id = isset($do_product[$i]['unit_id'])?Tools::_str($do_product[$i]['unit_id']):'';
                        $qty = isset($do_product[$i]['qty'])?Tools::_str($do_product[$i]['qty']):'';
                        $qty_stock_valid = false;
                        $qty_stock = 0;
                        $t_product_total  = Tools::array_extract($product_total,'index',
                            array('data'=>
                                array(array('product_id'=>$product_id,'unit_id'=>$unit_id))
                            )
                        );
                        
                        $product_ref_valid = false;
                        for($j = 0;$j<count($rd_expected['ref_product']);$j++){
                            if( $rd_expected['ref_product'][$j]['product_id'] == $product_id &&
                                $rd_expected['ref_product'][$j]['unit_id'] == $unit_id &&
                                $rd_expected['ref_product'][$j]['reference_type'] === $p_reference_type &&
                                $rd_expected['ref_product'][$j]['reference_id'] === $p_reference_id
                            ){ 
                                $product_ref_valid = true;                            
                                break;
                            }
                        }
                        
                        if(!$product_ref_valid){
                            $result['success'] = 0;
                            $result['msg'][] = 'Delivery Order Product Reference Invalid';
                        }                        
                        
                        if(count($t_product_total)>0){                            
                            $product_total[$t_product_total[0]]['qty']+=
                                Tools::_float($qty);
                        }
                        else{
                            $product_total[] = array('product_id'=>$product_id,'unit_id'=>$unit_id,'qty'=>$qty);
                        }

                        for($j = 0;$j<count($rd_expected['product_stock']);$j++){
                            if( $rd_expected['product_stock'][$j]['product_id'] == $product_id &&
                                $rd_expected['product_stock'][$j]['unit_id'] == $unit_id &&
                                $rd_expected['product_stock'][$j]['warehouse_id'] == $warehouse_id
                            ) 
                            $qty_stock = $rd_expected['product_stock'][$j]['qty'];
                        }
                        
                        $product_exists = true;
                        if(Tools::_float($qty)<=Tools::_float($qty_stock)){
                            $qty_stock_valid = true;
                        }

                        if(!$qty_stock_valid){
                            $result['success'] = 0;
                            $result['msg'][] = 'Delivery Order Product invalid';
                            
                        }
                        
                        if($result['success']!== 1) break;

                    }

                    if(!$product_exists){
                        $result['success'] = 0;
                        $result['msg'][] = Lang::get(array('Delivery Order','Product')).' '.Lang::get('empty',true,false);
                    }


                    if($result['success']!==1){
                        break;
                    }
                }
                if(!$delivery_order_exists){
                    $result['success'] = 0;
                    $result['msg'][] = 'Delivery Order '.Lang::get('empty',true,false);
                }
                if($result['success'] !== 1) break;
                
                foreach($product_total as $p_total_idx=>$p_total){
                    $dop_total_valid = false;
                    $temp_prod = Tools::array_extract($rd_expected['ref_product'],
                        array(),
                        array(
                            'data'=>array(
                                array('product_id'=>$p_total['product_id'],
                                    'unit_id'=>$p_total['unit_id'],
                                )
                            ),
                            'cfg'=>array('compare_sign'=>'===')
                        )
                    );
                    if(count($temp_prod)>0) {
                        $qty_outstanding = $temp_prod[0]['qty_outstanding'];
                        if(Tools::_float($p_total['qty'])===Tools::_float($qty_outstanding)){
                            $dop_total_valid = true;

                        }

                        $qty_stock = Product_Stock_Engine::stock_sum_get('stock_sales_available',
                            $p_total['product_id'],$p_total['unit_id'],Warehouse_Engine::BOS_get('id')
                        );

                        if(Tools::_float($p_total['qty'])< Tools::_float($qty_outstanding) &&
                            Tools::_float($p_total['qty']) === Tools::_float($qty_stock)
                        ){
                            $dop_total_valid = true;
                        }

                    }
                    if(!$dop_total_valid){
                        $result['success'] = 0;
                        $result['msg'][] = 'Delivery Order Product Total Invalid';
                        break;
                    }
                }
                if($result['success'] !== 1) break;

                $rs_warehouse_to = array();
                if($delivery_order_final_type==='sales_invoice'){
                    $si_info = $db->fast_get('sales_invoice_info',array('sales_invoice_id'=>$ref_id))[0];

                    if(is_null($si_info['expedition_id'])){
                        $q = '
                            select 1
                            from warehouse t1
                            where t1.code = "WC"
                                and t1.id = '.$db->escape($warehouse_to['id']).'
                        ';
                    }
                    else{
                        $q = '
                            select 1
                            from warehouse t1
                            where t1.code = "WE"
                                and t1.id = '.$db->escape($warehouse_to['id']).'
                        ';
                    }
                }                    
                $rs_warehouse_to = $db->query_array($q);                    
                if((!count($rs_warehouse_to)>0) || is_null($rs_warehouse_to)){
                    $result['success'] = 0;
                    $result['msg'][] = Lang::get('Warehouse To').' invalid';
                    break;
                }


                //</editor-fold>
                break;
            case 'delivery_order_final_process':
            case 'delivery_order_final_done':
                $temp_result = Validator::validate_on_update(
                    array(
                        'module'=>'delivery_order_final',
                        'module_name'=>'Delivery Order Final',
                        'module_engine'=>'Delivery_Order_Final_Engine',
                    ),
                    $delivery_order_final
                );
                $result['success'] = $temp_result['success'];
                $result['msg'] = array_merge($result['msg'],$temp_result['msg']);

                if($result['success']!==1) break;

                $delivery_order_db = $db->fast_get('delivery_order_final', array('id'=>$delivery_order_final_id));

                if($method==='delivery_order_final_done'){
                    $result['success'] = 0;
                    $result['msg'][] = 'Update Delivery Order Final '.Lang::get('failed',true,false).'. '
                        .Lang::get('use').' Delivery Order';
                }
                else{
                    $result['success'] = 0;
                    $result['msg'][] = 'Update Delivery Order Final '.Lang::get('failed',true,false).'. ';                        
                }

                break;
            case 'delivery_order_final_canceled':
                $temp_result = Validator::validate_on_cancel(
                    array(
                        'module'=>'delivery_order_final',
                        'module_name'=>'Delivery Order Final',
                        'module_engine'=>'Delivery_Order_Final_Engine',
                    ),
                    $delivery_order_final
                );
                $result['success'] = $temp_result['success'];
                $result['msg'] = array_merge($result['msg'],$temp_result['msg']);
                if($result['success'] !== 1) break;

                $q = '
                    select 1
                    from dof_dofc t1
                        inner join delivery_order_final_confirmation t2 
                            on t1.delivery_order_final_confirmation_id = t2.id
                    where t1.delivery_order_final_id = '.$db->escape($delivery_order_final_id).'
                        and t2.delivery_order_final_confirmation_status !="X"
                    limit 1
                ';

                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $result['success'] = 0;
                    $result['msg'] = Lang::get('Delivery Order Final Confirmation').' '.Lang::get('exists',true,false);
                }

                $id = isset($delivery_order_final['id'])?Tools::_str($delivery_order_final['id']):'';
                break;
            default:
                $result['success'] = 0;
                $result['msg'][] = 'Unknown Validation Method';
                break;

        }

        return $result;
    }

    public static function adjust($action,$data=array()){
        //<editor-fold defaultstate="collapsed">

        $db = new DB();
        $result = array();

        switch($action){
            case 'delivery_order_final_add':
                $delivery_order_final = $data['delivery_order_final'];
                $reference = $data['reference'];
                $warehouse_to  = $data['warehouse_to'];
                $delivery_order = $data['delivery_order'];

                $result['delivery_order_final'] = array(
                    'code'=>''
                    ,'store_id'=>$delivery_order_final['store_id']
                    ,'delivery_order_final_date'=>Tools::_date($delivery_order_final['delivery_order_final_date'],'Y-m-d H:i:s')
                    ,'delivery_order_final_type'=>$delivery_order_final['delivery_order_final_type']
                    ,'delivery_order_final_status'=>SI::status_default_status_get('Delivery_Order_Final_Engine')['val']
                );
                $result['dof_info'] = array('confirmation_required'=>0);
                if($warehouse_to['id'] === Warehouse_Engine::expedition_get()[0]['id']){
                    $result['dof_info']['confirmation_required'] = 1;
                }
                
                $result['sales_invoice_delivery_order_final'] = array(
                    'sales_invoice_id'=>$reference['id']
                );

                $temp_do = array();
                foreach($delivery_order as $do_idx=>$do){
                    $warehouse_from_id = $do['warehouse_from_id'];
                    if($warehouse_from_id !=='reserved_qty'){
                        $temp_do[] = array(
                            'data'=>array('delivery_order_status'=>SI::status_default_status_get('Delivery_Order_Engine')['val']),
                            'product'=>$do['product'],
                            'warehouse_from'=>array('id'=>$warehouse_from_id),
                            'warehouse_to'=>array(
                                'id'=>$warehouse_to['id'],
                                'contact_name'=>Tools::empty_to_null($warehouse_to['contact_name']),
                                'phone'=>Tools::empty_to_null(preg_replace('/[^0-9]/','',Tools::_str($warehouse_to['phone']))),
                                'address'=>Tools::empty_to_null($warehouse_to['address']),
                            ),
                        );
                    }
                }
                $result['delivery_order'] = $temp_do;


                break;
            case 'delivery_order_final_confirmed':
            case 'delivery_order_final_done':
            case 'dof_process':
                //<editor-fold defaultstate="collapsed">
                $delivery_order_final = $data['delivery_order_final']; 
                $delivery_order_final_status = '';

                switch($action){
                    case 'delivery_order_final_confirmed':
                        $delivery_order_final_status='confirmed';
                        break;
                    case 'delivery_order_final_done':
                        $delivery_order_final_status='done';
                        break;
                    case 'dof_process':
                        $delivery_order_final_status='process';
                        break;
                }
                $result['delivery_order_final'] = array(
                    'notes'=>isset($delivery_order_final['notes'])?$delivery_order_final['notes']:''
                    ,'delivery_order_final_status'=>$delivery_order_final_status
                );
                //</editor-fold>
                break;
            case 'delivery_order_final_canceled':
                //<editor-fold defaultstate="collapsed">
                $delivery_order_final = $data['delivery_order_final'];

                $result['delivery_order_final'] = array(
                    'cancellation_reason'=>isset($delivery_order_final['cancellation_reason'])?$delivery_order_final['cancellation_reason']:''
                    ,'delivery_order_final_status'=>'X'
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
        $delivery_order_final_data = $data['delivery_order_final'];
        $id = $delivery_order_final_data['id'];

        $method_list = array('delivery_order_final_add');
        foreach(SI::status_list_get('Delivery_Order_Final_Engine') as $status){
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
                case 'delivery_order_final_add':
                    try{ 
                        $db->trans_begin();
                        $temp_result = self::delivery_order_final_add($db, $final_data);
                        $success = $temp_result['success'];
                        $msg = array_merge($msg, $temp_result['msg']);
                        if($success === 1){
                            $result['trans_id']=$temp_result['trans_id']; // useful for view forwarder
                        }

                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = Lang::get(array('Add','Delivery Order Final','Success'),true,true,false,false,true);
                        }
                    }
                    catch(Exception $e){

                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    }
                    break;
                case 'dof_process':
                    try{
                        $db->trans_begin();
                        $temp_result = self::dof_process($db,$final_data,$id);
                        $success = $temp_result['success'];
                        $msg = array_merge($msg,$temp_result['msg']);

                        if($success == 1){
                            $result['trans_id'] = $temp_result['trans_id'];
                        }

                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = Lang::get(array('Update','Delivery Order Final','Success'),true,true,false,false,true);
                        }
                    }
                    catch(Exception $e){
                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    }  
                    break;
                case 'delivery_order_final_done':                        
                    try{
                        $db->trans_begin();
                        $temp_result = self::delivery_order_final_done($db,$final_data,$id);
                        $success = $temp_result['success'];
                        $msg = array_merge($msg,$temp_result['msg']);

                        if($success == 1){
                            $result['trans_id'] = $temp_result['trans_id'];
                        }

                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = Lang::get(array('Update','Delivery Order Final','Success'),true,true,false,false,true);
                        }
                    }
                    catch(Exception $e){
                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    }                        

                    break;
                case 'delivery_order_final_canceled':
                    try{
                        $db->trans_begin();
                        $temp_result = self::delivery_order_final_canceled($db,$final_data,$id);
                        $success = $temp_result['success'];
                        $msg = array_merge($temp_result['msg'],$msg);
                        if($success === 1){
                            $result['trans_id'] = $temp_result['trans_id'];
                        }
                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = Lang::get(array('Cancel','Delivery Order Final','Success'),true,true,false,false,true);
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

    function delivery_order_final_add($db, $final_data){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $fdelivery_order_final = $final_data['delivery_order_final'];
        $fdof_info = $final_data['dof_info'];

        $store_id = $fdelivery_order_final['store_id'];
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        $delivery_order_final_type = $fdelivery_order_final['delivery_order_final_type'];
        $delivery_order_final_date = $fdelivery_order_final['delivery_order_final_date'];

        $fdelivery_order_final['code'] = SI::code_counter_store_get($db,$store_id, 'delivery_order_final');
        if(!$db->insert('delivery_order_final',$fdelivery_order_final)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $delivery_order_final_code = $fdelivery_order_final['code'];

        if($success == 1){                                
            $delivery_order_final_id = $db->fast_get('delivery_order_final'
                    ,array('code'=>$delivery_order_final_code))[0]['id'];
            $result['trans_id']=$delivery_order_final_id; 
        }

        if($success === 1){
            $fdof_info['delivery_order_final_id'] = $delivery_order_final_id;
            if(!$db->insert('dof_info',$fdof_info)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }
        
        if($success == 1){
            $temp_res = SI::status_log_add($db,
                'delivery_order_final',
                $delivery_order_final_id,
                $fdelivery_order_final['delivery_order_final_status']
            );

            $success = $temp_res['success'];

            if($success !== 1){
                $msg = array_merge($msg, $temp_res['msg']);
            }                
        }

        if($success === 1){
            switch($delivery_order_final_type){
                case 'sales_invoice':
                    $fsales_invoice_delivery_order_final = $final_data['sales_invoice_delivery_order_final'];
                    $fsales_invoice_delivery_order_final['delivery_order_final_id']=$delivery_order_final_id;

                    if(!$db->insert('sales_invoice_delivery_order_final'
                        ,$fsales_invoice_delivery_order_final)){
                        $msg[] = $db->_error_message();
                        $db->trans_rollback();                                
                        $success = 0;
                    }
                    break;
            }
        }

        if($success === 1){
            //<editor-fold defaultstate="collapsed" desc="Delivery Order Add">
            $delivery_order_arr = isset($final_data['delivery_order'])?
                Tools::_arr($final_data['delivery_order']):array();
            foreach($delivery_order_arr as $do_idx=>$do){                    
                $do_data = isset($do['data'])?
                    Tools::_arr($do['data']):array();
                $do_product = $do['product']?
                    Tools::_arr($do['product']):array();
                $temp_delivery_order = array();                    
                $temp_delivery_order['delivery_order'] = array(
                    'delivery_order_date'=>$delivery_order_final_date,
                    'delivery_order_type'=>$delivery_order_final_type,
                    'store_id'=>$store_id,
                    'delivery_order_status'=>$do_data['delivery_order_status'],
                    'status'=>'1',
                    'modid'=>$modid,
                    'moddate'=>$moddate,
                );

                $temp_delivery_order['delivery_order_product']=array();
                foreach($do_product as $product_idx=>$product){
                    $temp_product = array(
                        'reference_type'=>$product['reference_type'],
                        'reference_id'=>$product['reference_id'],
                        'product_type'=>'registered_product',
                        'product_id'=>$product['product_id'],
                        'unit_id'=>$product['unit_id'],
                        'qty'=>$product['qty'],
                    );
                    $temp_delivery_order['delivery_order_product'][] = $temp_product;
                }

                $temp_delivery_order['delivery_order_warehouse_from'] = array(
                    'warehouse_id'=>$do['warehouse_from']['id']
                );

                $temp_delivery_order['delivery_order_warehouse_to'] = array(
                    'warehouse_id'=>$do['warehouse_to']['id'],
                    'contact_name'=>$do['warehouse_to']['contact_name'],
                    'address'=>$do['warehouse_to']['address'],
                    'phone'=>$do['warehouse_to']['phone'],
                );
                $temp_delivery_order['delivery_order_final_delivery_order'] = array(
                    'delivery_order_final_id'=>$delivery_order_final_id
                );

                $temp_result = Delivery_Order_Engine::delivery_order_add($db,$temp_delivery_order);

                $success = $temp_result['success'];
                $msg = $temp_result['msg'];

                if($success){

                }
            }
            //</editor-fold>
        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    function dof_process($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $fdelivery_order_final = $final_data['delivery_order_final'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('delivery_order_final',$fdelivery_order_final,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'delivery_order_final',
                $id,$fdelivery_order_final['delivery_order_final_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }
    
    function delivery_order_final_done($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $fdelivery_order_final = $final_data['delivery_order_final'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('delivery_order_final',$fdelivery_order_final,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'delivery_order_final',
                $id,$fdelivery_order_final['delivery_order_final_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    function delivery_order_final_confirmed($db, $final_data,$id){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $fdelivery_order_final = $final_data['delivery_order_final'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('delivery_order_final',$fdelivery_order_final,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'delivery_order_final',
                $id,$fdelivery_order_final['delivery_order_final_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }
    
    function delivery_order_final_canceled($db, $final_data,$delivery_order_final_id){
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$delivery_order_final_id);
        $success = 1;
        $msg = array();

        $fdelivery_order_final = $final_data['delivery_order_final'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('delivery_order_final',$fdelivery_order_final,array("id"=>$delivery_order_final_id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'delivery_order_final',
                $delivery_order_final_id,$fdelivery_order_final['delivery_order_final_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        if($success == 1){
            $q = '
                select t1.id delivery_order_id
                from delivery_order t1
                    inner join delivery_order_final_delivery_order t2
                        on t1.id = t2.delivery_order_id
                where t2.delivery_order_final_id = '.$db->escape($delivery_order_final_id).'
            ';
            $rs = $db->query_array($q);
            for($i = 0;$i<count($rs);$i++){
                $temp_param = array(
                    'delivery_order_status'=>'X',
                    'cancellation_reason'=>$fdelivery_order_final['cancellation_reason'],
                );
                $temp_result = Delivery_Order_Engine::delivery_order_canceled($db, array('delivery_order'=>$temp_param),$rs[$i]['delivery_order_id']);
                $success = $temp_result['success'];
                $msg = array_merge($msg,$temp_result['msg']);

                if($success !== 1) break;
            }

        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
    }
}
?>
