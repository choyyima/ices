<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Intake_Final_Engine {

    public static $module_type_list = array(
        array('val'=>'sales_invoice','label'=>'Sales Invoice'),
    );

    public static $status_list = array(
        //<editor-fold defaultstate="collapsed">
        array(//label name is used for method name
            'val'=>'process'
            ,'label'=>'PROCESS'
            ,'method'=>'intake_final_process'
            ,'default'=>true
            ,'next_allowed_status'=>array('done','X')
        )
        ,array(
            'val'=>'done'
            ,'label'=>'DONE'
            ,'method'=>'intake_final_done'
            ,'next_allowed_status'=>array('X')
        )
        ,array(
            'val'=>'X'
            ,'label'=>'CANCELED'
            ,'method'=>'intake_final_canceled'
            ,'next_allowed_status'=>array()
        )
        //</editor-fold>
    );

    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'intake_final/'
            ,'intake_final_engine'=>'intake_final/intake_final_engine'
            ,'intake_final_data_support' => 'intake_final/intake_final_data_support'
            ,'intake_final_renderer' => 'intake_final/intake_final_renderer'                
            ,'ajax_search'=>get_instance()->config->base_url().'intake_final/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'sales_prospect/data_support/'                
        );

        return json_decode(json_encode($path));
    }

    public static function intake_final_exists($id){
        $result = false;
        $db = new DB();
        $q = '
            select 1 
            from intake_final 
            where status > 0 && id = '.$db->escape($id).'
        ';
        $rs = $db->query_array_obj($q);
        if(count($rs)>0){
            $result = true;
        }
        return $result;
    }

    public static function submit($id,$method,$post){
        get_instance()->load->helper('intake/intake_engine');
        get_instance()->load->helper('product_stock_engine');
        $post = json_decode($post,TRUE);
        $data = $post;
        $ajax_post = false;                  
        $result = null;
        $cont = true;

        if(isset($post['ajax_post'])) $ajax_post = $post['ajax_post'];
        if($method == 'add') $data['intake_final']['id'] = '';
        else $data['intake_final']['id'] = $id;

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
        get_instance()->load->helper('intake_final/intake_final_data_support');
        $result = array(
            "success"=>1
            ,"msg"=>array()

        );
        $intake_final = isset($data['intake_final'])?$data['intake_final']:null;
        $intake_final_id = isset($intake_final['id'])?
            Tools::_str($intake_final['id']):'';
        $reference = isset($data['reference'])?Tools::_arr($data['reference']):array();
        $intake = isset($data['intake'])? Tools::_arr($data['intake']):array();

        $db = new DB();
        switch($method){
            case 'intake_final_add':
                //<editor-fold defaultstate="collapsed">
                $ref_id = Tools::empty_to_null(isset($reference['id'])?Tools::_str($reference['id']):'');
                $intake_final_type = isset($intake_final['intake_final_type'])?
                    Tools::_str($intake_final['intake_final_type']):'';
                
                if(is_null($ref_id)){
                    $result['success'] = 0;
                    $result['msg'][] = 'Reference'.' '.Lang::get('empty',true,false);
                    break;
                }
                
                if(!SI::type_match('Intake_Final_Engine',$intake_final_type)){
                    $result['success'] = 0;
                    $result['msg'][]='Mismatch Module Type';
                    break;
                }

                //check store is available
                $store_id = isset($intake_final['store_id'])?$intake_final['store_id']:'';
                $q = 'select 1 from store where status>0 and id ='.$db->escape($store_id);
                if(count($db->query_array_obj($q)) == 0){
                    $result['success'] = 0;
                    $result['msg'][] = Lang::get("Store").' '.Lang::get("Empty",true,false);
                }                   

                //check intake_final date
                
                $intake_final_date = Tools::_date(isset($intake_final['intake_final_date'])?
                    $intake_final['intake_final_date']:'','Y-m-d H:i:s');
                if(strtotime($intake_final_date) < strtotime(Tools::_date('','Y-m-d H:i:s'))){
                    $result['success'] = 0;
                    $result['msg'][] = Lang::get(array('Product Intake Final','Date')).' '.Lang::get('must be greater than',true,false,false,true).' '.Tools::_date('','F d, Y H:i:s');
                    break;
                }

                $ref_exists = false;
                if($intake_final_type ==='sales_invoice'){
                    if(count($db->fast_get('sales_invoice', array('id'=>$ref_id,'sales_invoice_status'=>'invoiced')))> 0){
                        $ref_exists=true;
                    }
                }
                if(!$ref_exists){
                    $result['success'] = 0;
                    $result['msg'][] = 'Reference Empty';
                }

                $rd_expected = Intake_Final_Data_Support::reference_dependency_get($intake_final_type, $ref_id);
                $product_total = array();// use to calculate total selected product between warehouse
                $intake_exists = false;
                foreach($intake  as $do_idx=>$do){   

                    $do_product = isset($do['product'])?$do['product']:array();
                    $product_exists = false;
                    $warehouse_id = isset($do['warehouse_from_id'])?$do['warehouse_from_id']:'';
                    $intake_exists = true;
                    for($i = 0;$i<count($do_product);$i++){
                        $p_reference_type = isset($do_product[$i]['reference_type'])?Tools::_str($do_product[$i]['reference_type']):'';
                        $p_reference_id = isset($do_product[$i]['reference_id'])?Tools::_str($do_product[$i]['reference_id']):'';
                        $product_id = isset($do_product[$i]['product_id'])?Tools::_str($do_product[$i]['product_id']):'';
                        $unit_id = isset($do_product[$i]['unit_id'])?Tools::_str($do_product[$i]['unit_id']):'';
                        $qty = isset($do_product[$i]['qty'])?Tools::_str($do_product[$i]['qty']):'';
                        $qty_stock_valid = false;
                        $qty_stock = 0;
                        $product_total_idx  = Tools::array_extract($product_total,'index',
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
                            $result['msg'][] = 'Product Intake Product Reference Invalid';
                        }
                        
                        if(count($product_total_idx)>0){
                            $product_total[$product_total_idx[0]]['qty']+=
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
                            $result['msg'][] = 'Intake Product invalid';
                            break;
                        }


                    }

                    if(!$product_exists){
                        $result['success'] = 0;
                        $result['msg'][] = 'Intake has no product';
                    }

                    if($result['success']!==1){
                        break;
                    }
                }
                if(!$intake_exists){
                    $result['success'] = 0;
                    $result['msg'][] = 'Intake'.' '.Lang::get('empty',true, false, true);
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
                        $result['msg'][] = 'Product Qty invalid';
                        break;
                    }
                }
                if($result['success'] !== 1) break;

                //</editor-fold>
                break;
            case 'intake_final_process':
            case 'intake_final_done':
                $temp_result = Validator::validate_on_update(
                    array(
                        'module'=>'intake_final',
                        'module_name'=>'Intake Final',
                        'module_engine'=>'Intake_Final_Engine',
                    ),
                    $intake_final
                );
                $result['success'] = $temp_result['success'];
                $result['msg'] = array_merge($result['msg'],$temp_result['msg']);

                if($result['success']!==1) break;

                $intake_db = $db->fast_get('intake_final', array('id'=>$intake_final_id));

                if($method==='intake_final_done'){
                    $result['success'] = 0;
                    $result['msg'][] = 'Update '.Lang::get('Product Intake Final').' '.Lang::get('failed',true,false).'. '
                        .Lang::get('use').' '.Lang::get('Product Intake');
                }
                else{
                    $result['success'] = 0;
                    $result['msg'][] = 'Update '.Lang::get('Product Intake Final').' '.Lang::get('failed',true,false).'. ';                        
                }

                break;
            case 'intake_final_canceled':
                $temp_result = Validator::validate_on_cancel(
                    array(
                        'module'=>'intake_final',
                        'module_name'=>'Intake Final',
                        'module_engine'=>'Intake_Final_Engine',
                    ),
                    $intake_final
                );
                $result['success'] = $temp_result['success'];
                $result['msg'] = array_merge($result['msg'],$temp_result['msg']);

                $id = isset($intake_final['id'])?Tools::_str($intake_final['id']):'';
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
            case 'intake_final_add':
                $intake_final = $data['intake_final'];
                $reference = $data['reference'];

                $intake = $data['intake'];

                $result['intake_final'] = array(
                    'code'=>''
                    ,'store_id'=>$intake_final['store_id']
                    ,'intake_final_date'=>Tools::_date($intake_final['intake_final_date'],'Y-m-d H:i:s')
                    ,'intake_final_type'=>$intake_final['intake_final_type']
                    ,'intake_final_status'=>SI::status_default_status_get('Intake_Final_Engine')['val']
                );
                $result['sales_invoice_intake_final'] = array(
                    'sales_invoice_id'=>$reference['id']
                );

                $temp_do = array();
                foreach($intake as $do_idx=>$do){
                    $warehouse_from_id = $do['warehouse_from_id'];
                    if($warehouse_from_id !=='reserved_qty'){
                        $temp_do[] = array(
                            'data'=>array('intake_status'=>SI::status_default_status_get('Intake_Engine')['val']),
                            'product'=>$do['product'],
                            'warehouse_from'=>array('id'=>$do['warehouse_from_id']),
                        );
                    }
                }
                $result['intake'] = $temp_do;


                break;

            case 'intake_final_done':
            case 'intake_final_process':
                //<editor-fold defaultstate="collapsed">
                $intake_final = $data['intake_final']; 
                $intake_final_status = '';
                $status_list = SI::status_list_get('Intake_Engine');

                foreach($status_list as $status_idx=>$status){
                    if($status['method'] === $action) $intake_final_status = $status['val'];
                }

                $result['intake_final'] = array(
                    'notes'=>isset($intake_final['notes'])?$intake_final['notes']:''
                    ,'intake_final_status'=>$intake_final_status
                );
                //</editor-fold>
                break;
            case 'intake_final_canceled':
                //<editor-fold defaultstate="collapsed">
                $intake_final = $data['intake_final'];

                $result['intake_final'] = array(
                    'cancellation_reason'=>isset($intake_final['cancellation_reason'])?$intake_final['cancellation_reason']:''
                    ,'intake_final_status'=>'X'
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
        $intake_final_data = $data['intake_final'];
        $id = $intake_final_data['id'];

        $method_list = array('intake_final_add');
        foreach(SI::status_list_get('Intake_Final_Engine') as $status){
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
                case 'intake_final_add':
                    try{ 
                        $db->trans_begin();
                        $temp_result = self::intake_final_add($db, $final_data);
                        $success = $temp_result['success'];
                        $msg = array_merge($msg, $temp_result['msg']);
                        if($success === 1){
                            $result['trans_id']=$temp_result['trans_id']; // useful for view forwarder
                        }

                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = Lang::get(array('Add','Product Intake Final','Success'),true,true,false,false,true);
                        }
                    }
                    catch(Exception $e){

                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    }
                    break;
                case 'intake_final_process':
                    try{
                        $db->trans_begin();
                        $temp_result = self::intake_final_process($db,$final_data,$id);
                        $success = $temp_result['success'];
                        $msg = array_merge($msg,$temp_result['msg']);

                        if($success == 1){
                            $result['trans_id'] = $temp_result['trans_id'];
                        }

                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = Lang::get(array('Update','Product Intake Final','Success'),true,true,false,false,true);
                        }
                    }
                    catch(Exception $e){
                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    }  
                    break;
                case 'intake_final_done':                        
                    try{
                        $db->trans_begin();
                        $temp_result = self::intake_final_done($db,$final_data,$id);
                        $success = $temp_result['success'];
                        $msg = array_merge($msg,$temp_result['msg']);

                        if($success == 1){
                            $result['trans_id'] = $temp_result['trans_id'];
                        }

                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = Lang::get(array('Update','Product Intake Final','Success'),true,true,false,false,true);
                        }
                    }
                    catch(Exception $e){
                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    }                        

                    break;
                case 'intake_final_canceled':
                    try{
                        $db->trans_begin();
                        $temp_result = self::intake_final_canceled($db,$final_data,$id);
                        $success = $temp_result['success'];
                        $msg = array_merge($temp_result['msg'],$msg);
                        if($success === 1){
                            $result['trans_id'] = $temp_result['trans_id'];
                        }
                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = Lang::get(array('Cancel','Product Intake Final','Success'),true,true,false,false,true);
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

    function intake_final_add($db, $final_data){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $fintake_final = $final_data['intake_final'];

        $store_id = $fintake_final['store_id'];
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        $intake_final_type = $fintake_final['intake_final_type'];
        $intake_final_date = $fintake_final['intake_final_date'];

        $fintake_final['code'] = SI::code_counter_store_get($db,$store_id, 'intake_final');
        if(!$db->insert('intake_final',$fintake_final)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $intake_final_code = $fintake_final['code'];

        if($success == 1){                                
            $intake_final_id = $db->fast_get('intake_final'
                    ,array('code'=>$intake_final_code))[0]['id'];
            $result['trans_id']=$intake_final_id; 
        }

        if($success == 1){
            $temp_res = SI::status_log_add($db,
                'intake_final',
                $intake_final_id,
                $fintake_final['intake_final_status']
            );

            $success = $temp_res['success'];

            if($success !== 1){
                $msg = array_merge($msg, $temp_res['msg']);
            }                
        }

        if($success === 1){
            switch($intake_final_type){
                case 'sales_invoice':
                    $fsales_invoice_intake_final = $final_data['sales_invoice_intake_final'];
                    $fsales_invoice_intake_final['intake_final_id']=$intake_final_id;

                    if(!$db->insert('sales_invoice_intake_final'
                        ,$fsales_invoice_intake_final)){
                        $msg[] = $db->_error_message();
                        $db->trans_rollback();                                
                        $success = 0;
                    }
                    break;
            }
        }

        if($success === 1){
            $intake_arr = isset($final_data['intake'])?
                Tools::_arr($final_data['intake']):array();
            foreach($intake_arr as $do_idx=>$do){
                $do_data = isset($do['data'])?
                    Tools::_arr($do['data']):array();
                $do_product = $do['product']?
                    Tools::_arr($do['product']):array();
                $temp_intake = array();                    
                $temp_intake['intake'] = array(
                    'intake_date'=>$intake_final_date,
                    'intake_type'=>$intake_final_type,
                    'store_id'=>$store_id,
                    'intake_status'=>$do_data['intake_status'],
                    'status'=>'1',
                    'modid'=>$modid,
                    'moddate'=>$moddate,
                );

                $temp_intake['intake_product']=array();
                foreach($do_product as $product_idx=>$product){
                    $temp_product = array(
                        'reference_type'=>$product['reference_type'],
                        'reference_id'=>$product['reference_id'],
                        'product_type'=>'registered_product',
                        'product_id'=>$product['product_id'],
                        'unit_id'=>$product['unit_id'],
                        'qty'=>$product['qty'],
                    );
                    $temp_intake['intake_product'][] = $temp_product;
                }

                $temp_intake['intake_warehouse_from'] = array(
                    'warehouse_id'=>$do['warehouse_from']['id']
                );

                $temp_intake['intake_final_intake'] = array(
                    'intake_final_id'=>$intake_final_id
                );

                $temp_result = Intake_Engine::intake_add($db,$temp_intake);

                $success = $temp_result['success'];
                $msg = $temp_result['msg'];

                if($success){

                }
            }

        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
    }

    function intake_final_done($db, $final_data,$id){
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $fintake_final = $final_data['intake_final'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('intake_final',$fintake_final,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'intake_final',
                $id,$fintake_final['intake_final_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
    }

    function intake_final_canceled($db, $final_data,$intake_final_id){
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$intake_final_id);
        $success = 1;
        $msg = array();

        $fintake_final = $final_data['intake_final'];

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        if(!$db->update('intake_final',$fintake_final,array("id"=>$intake_final_id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'intake_final',
                $intake_final_id,$fintake_final['intake_final_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        if($success == 1){
            $q = '
                select t1.id intake_id
                from intake t1
                    inner join intake_final_intake t2
                        on t1.id = t2.intake_id
                where t2.intake_final_id = '.$db->escape($intake_final_id).'
            ';
            $rs = $db->query_array($q);
            for($i = 0;$i<count($rs);$i++){
                $temp_param = array(
                    'intake_status'=>'X',
                    'cancellation_reason'=>$fintake_final['cancellation_reason'],
                );
                $temp_result = Intake_Engine::intake_canceled($db, array('intake'=>$temp_param),$rs[$i]['intake_id']);
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];

                if($success !== 1) break;
            }

        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
    }
}
?>
