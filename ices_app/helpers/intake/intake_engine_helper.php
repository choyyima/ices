<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Intake_Engine {

    public static $module_type_list = array(
        array('val'=>'sales_invoice','label'=>'Sales Invoice'),
    );

    public static $status_list = array(
        //<editor-fold defaultstate="collapsed">
        array(//label name is used for method name
            'val'=>'process'
            ,'label'=>'PROCESS'
            ,'method'=>'intake_process'
            ,'default'=>true
            ,'next_allowed_status'=>array('done','X')
        )
        ,array(
            'val'=>'done'
            ,'label'=>'DONE'
            ,'method'=>'intake_done'
            ,'next_allowed_status'=>array('X')

        )
        ,array(
            'val'=>'X'
            ,'label'=>'CANCELED'
            ,'method'=>'intake_canceled'
            ,'next_allowed_status'=>array()
        )
        //</editor-fold>
    );

    public static function intake_exists($id){
        $result = false;
        $db = new DB();
        $q = '
                select 1 
                from intake 
                where status > 0 && id = '.$db->escape($id).'
            ';
        $rs = $db->query_array_obj($q);
        if(count($rs)>0){
            $result = true;
        }
        return $result;
    }

    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'intake/'
            ,'intake_engine'=>'intake/intake_engine'
            ,'intake_sales_pos_engine'=>'intake/intake_sales_pos_engine'
            ,'intake_print'=>'intake/intake_print'
            ,'intake_renderer' => 'intake/intake_renderer'
            ,'ajax_search'=>get_instance()->config->base_url().'intake/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'intake/data_support/'

        );

        return json_decode(json_encode($path));
    }

    public static function submit($id,$method,$post){

        $post = json_decode($post,TRUE);
        $data = $post;
        $ajax_post = false;                  
        $result = null;
        $cont = true;
        /*
        if($method === 'add'){
            $cont = true;
        }else{

        }
        */
        if(isset($post['ajax_post'])) $ajax_post = $post['ajax_post'];
        if($method == 'add') $data['intake']['id'] = '';
        else $data['intake']['id'] = $id;

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
        //<editor-fold defaultstate="collapsed">
        $result = array(
            "success"=>1
            ,"msg"=>array()

        );
        
        $reference = isset($data['reference'])?$data['reference']:null;        
        $intake = isset($data['intake'])?$data['intake']:null;
        $intake_product = isset($data['intake_product'])? $data['intake_product']: null;
        $warehouse_to = isset($data['warehouse_to'])?$data['warehouse_to']:null;
        $warehouse_from = isset($data['warehouse_from'])?$data['warehouse_from']:null;
        $rma_intake = isset($data['rma_intake'])?$data['rma_intake']:null;
        $db = new DB();
        $intake_id = isset($intake['id'])?
            $intake['id']:'';
        $intake_type = isset($intake['intake_type'])?Tools::_str($intake['intake_type']):'';
        switch($method){
            case 'intake_add':
                //<editor-fold defaultstate="collapsed">
                $reference_id = Tools::empty_to_null(isset($reference['reference_id'])?
                    Tools::_str($reference['reference_id']):'');
                $store_id = isset($intake['store_id'])?$intake['store_id']:'';
                //<editor-fold defaultstate="collapsed" desc="Major Validation">
                
                if(is_null($reference_id)){
                    $result['success'] = 0;
                    $result['msg'][] = Lang::get('Reference').' '.Lang::get('empty',true,false);
                    break;
                }
                
                $q = 'select 1 from store where status>0 and id ='.$db->escape($store_id);
                if(count($db->query_array_obj($q)) == 0){
                    $result['success'] = 0;
                    $result['msg'][] = Lang::get("Store").' '.lang::get('empty',true,false);
                }                   

                //check warehouse from is available
                $warehouse_id = isset($warehouse_from['warehouse_id'])?
                        $warehouse_from['warehouse_id']:'';
                $q = 'select 1 from warehouse where status>0 and id = '.$db->escape($warehouse_id).'';
                if(count($db->query_array_obj($q)) === 0){
                    $result['success'] = 0;
                    $result['msg'][] = Lang::get("Warehouse From").' '.Lang::get('empty',true,false);
                    
                }

                $intake_date = Tools::_date(isset($intake['intake_date'])?$intake['intake_date']:'','Y-m-d H:i:s');

                if(strtotime($intake_date)< strtotime(Tools::_date('','Y-m-d H:i:s'))){
                    $result['success'] = 0;
                    $result['msg'][] = Lang::get(array("Product Intake","Date")).' '
                        .Lang::get('must be greater than').' '.Lang::get(Tools::_date('','F d, Y H:i:s'));
                }
                
                
                if($result['success']!== 1) break;
                //</editor-fold>
                
                
                if(!in_array($intake_type,array())){
                    $success = 0;
                    $msg[] = 'Reference Type invalid';
                }
                
                //check product qty > 1
                $has_product = false;
                for($i = 0;$i<count($intake_product);$i++){
                    $qty = isset($intake_product[$i]['qty'])?floatval($intake_product[$i]['qty']):0;
                    if($qty>0) $has_product = true;
                }
                if(!$has_product){
                    $result['success'] = 0;
                    $result['msg'][] = "One Product must have qty";
                }


                for($i = 0; $i<count($intake_product); $i++){
                    $product_id = isset($intake_product[$i]['product_id'])?
                            $intake_product[$i]['product_id']:'';
                    $unit_id = isset($intake_product[$i]['unit_id'])?
                            $intake_product[$i]['unit_id']:'';
                    $intake_id = $intake_intake['intake_id'];
                    $qty = isset($intake_product[$i]['qty'])?
                            str_replace(',','',$intake_product[$i]['qty']):0;
                    $max_qty = self::intake_product_max_qty($product_id, $unit_id, $intake_id, $warehouse_id);

                    if(floatval($max_qty)<floatval($qty)){
                        $result['success'] = 0;
                        $result['msg'][] = 'Product Qty is invalid';
                        break;
                    }
                }

                
                //check product is valid
                $all_product_valid = true;
                for($i = 0;$i<count($intake_product);$i++){
                    $product_id = isset($intake_product[$i]['product_id'])?$intake_product[$i]['product_id']:'';
                    $q = 'select 1 from product where status>0 and id = '.$db->escape($product_id);
                    if(count($db->query_array_obj($q)) === 0) $all_product_valid = false;
                }
                if(!$all_product_valid){
                    $result['success'] = 0;
                    $result['msg'][] = "Invalid Product";
                }

                //</editor-fold>
                break;
            case 'intake_process':
            case 'intake_done':
                $temp_result = Validator::validate_on_update(
                    array(
                        'module'=>'intake',
                        'module_name'=>'Intake',
                        'module_engine'=>'Intake_Engine',
                    ),
                    $intake
                );
                $result['success'] = $temp_result['success'];
                $result['msg'] = array_merge($result['msg'],$temp_result['msg']);
                if($result['success']!==1) break;

                $intake_db = $db->fast_get('intake',array('id'=>$intake_id))[0];
                $intake_type = $intake_db['intake_type'];

                break;
            case 'intake_canceled':
                $temp_result = Validator::validate_on_cancel(
                    array(
                        'module'=>'intake',
                        'module_name'=>'Intake',
                        'module_engine'=>'Intake_Engine',
                    ),
                    $intake
                );
                $result['success'] = $temp_result['success'];
                $result['msg'] = array_merge($result['msg'],$temp_result['msg']);

                if($result['success']!==1) break;

                $id = isset($intake['id'])?Tools::_str($intake['id']):'';

                if($result['success'] === 1){

                    $q = '
                        select *
                        from intake t1
                        where t1.id = '.$db->escape($id).'
                    ';
                    $rs_do = $db->query_array($q)[0];
                    $do_type = $rs_do['intake_type'];
                    if($do_type ==='sales_invoice'){
                        $result['success'] = 0;
                        $result['msg'][] = 'Cancel POS Intake '.Lang::get('failed',true,false).'. '.Lang::get('Use').' Intake Final';
                    }

                }
                break;
            default:
                $success = 0;
                $msg[] = 'Invalid Method';
                break;
                


        }

        return $result;
        //</editor-fold>
    }

    public static function adjust($action,$data=array()){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = array();

        switch($action){
            case 'intake_add':
                $intake = $data['intake'];

                $intake_product = $data['intake_product'];
                $rma_intake = $data['rma_intake'];
                $warehouse_to  = $data['warehouse_to'];
                $warehouse_from  = $data['warehouse_from'];

                $result['intake_warehouse_to'] = array(
                        'warehouse_id'=>$warehouse_to['warehouse_id']
                        ,'contact_name'=>isset($warehouse_to['contact_name'])?
                                $warehouse_to['contact_name']:''
                        ,'phone'=>isset($warehouse_to['phone'])?
                            str_replace('-', '',str_replace('_','',$warehouse_to['phone'])):''
                        ,'address'=>isset($warehouse_to['address'])?
                            $warehouse_to['address']:''
                    );

                $result['intake_warehouse_from'] = array(
                    'warehouse_id'=>$warehouse_from['warehouse_id']
                    );

                $result['intake'] = array(
                    'code'=>''
                    ,'store_id'=>$intake['store_id']
                    ,'intake_date'=>$intake['intake_date']
                    ,'intake_type'=>'rma'
                    ,'intake_status'=>self::intake_rma_status_default_status_get()['val']
                    ,'notes'=>$intake['notes']
                );
                $result['intake_product'] = array();
                for($i = 0;$i<count($intake_product);$i++){
                    if(floatval($intake_product[$i]['qty'])>0){
                        $result['intake_product'][] = array(
                            'product_id'=>$intake_product[$i]['product_id']
                            ,'unit_id'=>$intake_product[$i]['unit_id']
                            ,'qty'=>$intake_product[$i]['qty']
                        );
                    }
                }
                $result['rma_intake'] = array(
                    'rma_id'=>$rma_intake['rma_id']
                );

                break;

            case 'intake_done':
            case 'intake_process':
                $intake = $data['intake']; 
                $intake_status = '';
                $status_list = SI::status_list_get('Intake_Engine');

                foreach($status_list as $status_idx=>$status){
                    if($status['method'] === $action) $intake_status = $status['val'];
                }

                $result['intake'] = array(
                    'notes'=>isset($intake['notes'])?$intake['notes']:''
                    ,'intake_status'=>$intake_status
                );
                break;
            case 'intake_canceled':
                $intake = $data['intake'];

                $result['intake'] = array(
                    'notes'=>isset($intake['notes'])?$intake['notes']:''
                    ,'cancellation_reason'=>isset($intake['cancellation_reason'])?$intake['cancellation_reason']:''
                    ,'intake_status'=>'X'
                );

                break;
        }

        return $result;
        //</editor-fold>
    }

    public static function save($method,$data){
        $db = new DB();
        $success = 1;
        $msg = array();
        $action = $method;
        $result = array("success"=>0,"msg"=>array(),'trans_id'=>'');
        $intake_data = $data['intake'];
        $id = $intake_data['id'];

        $method_list = array('intake_add');
        foreach(SI::status_list_get('Intake_Engine') as $status){
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
                case 'intake_add':
                    try{ 
                        $db->trans_begin();
                        $fintake = array_merge($final_data['intake'],array("modid"=>$modid,"moddate"=>$moddate));
                        $intake_id = '';
                        $rs = $db->query_array_obj('select func_code_counter_store("intake",'.$db->escape($fintake['store_id']).') "code"');
                        $fintake['code'] = $rs[0]->code;
                        if(!$db->insert('intake',$fintake)){
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();                                
                            $success = 0;
                        }

                        if($success == 1){
                            $q = '
                                select id 
                                from intake
                                where status>0 
                                    and intake_status = '.$db->escape(self::intake_rma_status_default_status_get()['val']).' 
                                    and code = '.$db->escape($fintake['code']).'
                            ';
                            $rs_intake = $db->query_array_obj($q);
                            $intake_id = $rs_intake[0]->id;
                            $result['trans_id']=$intake_id; // useful for view forwarder
                        }

                        if($success == 1){
                            $fwarehouse_to = $final_data['intake_warehouse_to'];
                            $fwarehouse_to['intake_id'] = $intake_id;
                            if(!$db->insert('intake_warehouse_to',$fwarehouse_to)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                        }

                        if($success == 1){
                            $fwarehouse_from = $final_data['intake_warehouse_from'];
                            $fwarehouse_from['intake_id'] = $intake_id;
                            if(!$db->insert('intake_warehouse_from',$fwarehouse_from)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                        }

                        if($success == 1){
                            $intake_status_log = array(
                                'intake_id'=>$intake_id
                                ,'intake_status'=>self::intake_rma_status_default_status_get()['val']
                                ,'modid'=>$modid
                                ,'moddate'=>$moddate    
                            );

                            if(!$db->insert('intake_status_log',$intake_status_log)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                        }

                        if($success == 1){
                            $fintake_product = $final_data['intake_product'];
                            for($i = 0;$i<count($fintake_product);$i++){
                                $fintake_product[$i]['intake_id'] = $intake_id;
                                if(!$db->insert('intake_product',$fintake_product[$i])){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                    break;
                                }
                            }                                
                        }

                        if($success == 1){
                            $frma_intake = $final_data['rma_intake'];
                            $frma_intake['intake_id'] = $intake_id;
                            if(!$db->insert('rma_intake',$frma_intake)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                        }

                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = Lang::get(array('Add','Product Intake','Success'),true,true,false,false,true);
                        }
                    }
                    catch(Exception $e){

                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    }
                    break;
                case 'intake_process':
                    try{
                        $db->trans_begin();
                        $temp_result = self::intake_process($db,$final_data,$id);
                        $success = $temp_result['success'];
                        $msg = array_merge($msg,$temp_result['msg']);

                        if($success == 1){
                            $result['trans_id'] = $temp_result['trans_id'];
                        }

                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = Lang::get(array('Update','Product Intake','Success'),true,true,false,false,true);
                        }
                    }
                    catch(Exception $e){
                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    }  
                    break;
                case 'intake_done':                        
                    try{
                        $db->trans_begin();
                        $temp_result = self::intake_done($db,$final_data,$id);
                        $success = $temp_result['success'];
                        $msg = array_merge($msg,$temp_result['msg']);

                        if($success == 1){
                            $result['trans_id'] = $temp_result['trans_id'];
                        }

                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = Lang::get(array('Update','Product Intake','Success'),true,true,false,false,true);
                        }
                    }
                    catch(Exception $e){
                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    }                        

                    break;
                case 'intake_canceled':
                    try{
                        $db->trans_begin();
                        $intake = array();
                        $q = '
                                select t1.*,t3.code rma_code 
                                from intake t1
                                    inner join rma_intake t2 on t2.intake_id = t1.id
                                    inner join rma t3 on t3.id = t2.rma_id
                                where t1.id = '.$db->escape($id).'
                            ';
                        $intake = $db->query_array($q)[0];

                        $warehouse_from = array();
                        $q = '
                            select t3.id warehouse_id, t3.name warehouse_name 
                            from intake_warehouse_from t2 
                                inner join warehouse t3 on t3.id = t2.warehouse_id
                            where t2.intake_id = '.$db->escape($intake['id']).'
                        ';
                        $warehouse_from = $db->query_array($q)[0];


                        $fintake = array_merge($final_data['intake'],array("modid"=>$modid,"moddate"=>$moddate));
                        if(!$db->update('intake',$fintake,array("id"=>$id))){
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();                                
                            $success = 0;
                        }
                        $result['trans_id']=$id;
                        if($success == 1){
                            $intake_status_log = array(
                                'intake_id'=>$id
                                ,'intake_status'=>$fintake['intake_status']
                                ,'modid'=>$modid
                                ,'moddate'=>$moddate    
                            );

                            if(!$db->insert('intake_status_log',$intake_status_log)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }                                
                        }

                        if ($success == 1 && $action === 'rma_canceled' 
                                && $intake['intake_status'] !== 'O'){
                            $intake_product = array();
                            $q = '
                                select *
                                from intake_product
                                where intake_id = '.$db->escape($id).'

                            ';
                            $intake_product = $db->query_array($q);

                            foreach($intake_product as $product){
                                $product_id = $product['product_id'];
                                $unit_id = $product['unit_id'];
                                $qty = $product['qty'];
                                $warehouse_id = $warehouse_from['warehouse_id'];
                                $description = 'RMA:'.$intake['rma_code'].' DELIVERY PRODUCT:'.$intake['code'].' CANCELED';
                                get_instance()->load->helper('product_stock_engine');
                                $stock_result = Product_Stock_Engine::stock_good_add(
                                        $db,
                                        $warehouse_id
                                        ,$product_id
                                        ,$qty
                                        ,$unit_id
                                        ,$description
                                        ,$intake['intake_date']
                                    );
                                if($stock_result['success'] == 0){
                                    $success = 0;
                                    $msg[]=$stock_result['msg'];   
                                    $db->trans_rollback();
                                    break;
                                } 
                            }
                        }

                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = Lang::get(array('Cancel','Product Intake','Success'),true,true,false,false,true);
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
    }

    function intake_add($db, $final_data){
        //<editor-fold defaultstate="collapsed">
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $fintake = $final_data['intake'];
        $fwarehouse_from = $final_data['intake_warehouse_from'];
        $fintake_final_intake = isset($final_data['intake_final_intake'])?
            Tools::_arr($final_data['intake_final_intake']):array();
        $fintake_product = $final_data['intake_product'];


        $store_id = $fintake['store_id'];
        $intake_type = $fintake['intake_type'];
        $intake_code =  SI::code_counter_store_get($db,$store_id, 'intake');
        $fintake['code'] = $intake_code;
        if(!$db->insert('intake',$fintake)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $intake_id = '';

        if($success === 1){                                
            $intake_id = $db->fast_get('intake'
                    ,array('code'=>$intake_code))[0]['id'];
            $result['trans_id']=$intake_id; 
        }

        if($success === 1){
            $fwarehouse_from['intake_id']=$intake_id;
            if(!$db->insert('intake_warehouse_from',$fwarehouse_from)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }

        if($success === 1){
            $fintake_final_intake['intake_id']=$intake_id;
            if(!$db->insert('intake_final_intake',$fintake_final_intake)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }

        if($success === 1){
            foreach($fintake_product as $fp_idx=>$fp){
                $fp['intake_id']=$intake_id;
                if(!$db->insert('intake_product',$fp)){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                    break;
                }
                
                $intake_product_type = $fp['product_type'];

                if($success === 1){

                    switch($intake_type){
                        case 'sales_invoice':
                            if($intake_product_type ==='registered_product'){
                                get_instance()->load->helper('sales_pos/sales_pos_engine');
                                $q = '
                                    select t1.sales_invoice_id
                                    from sales_invoice_intake_final t1
                                    where t1.intake_final_id = '.$db->escape($fintake_final_intake['intake_final_id']).'
                                ';
                                $sales_invoice_id = $db->query_array($q)[0]['sales_invoice_id'];
                                $temp_si_product = array(
                                    'reference_id'=>$fp['reference_id'],
                                    'qty'=>-1*Tools::_float($fp['qty'])
                                );
                                $temp_result = Sales_Pos_Engine::movement_outstanding_qty_add($db, $sales_invoice_id,$temp_si_product);
                                $success = $temp_result['success'];
                                $msg = array_merge($msg,$temp_result['msg']);
                            }
                            break;
                    }

                    if($success !== 1) break; 
                }

                if($success === 1){
                    $temp_result = Product_Stock_Engine::stock_sales_available_only_add(
                        $db,
                        $fwarehouse_from['warehouse_id'],
                        $fp['product_id'],
                        -1*$fp['qty'],
                        $fp['unit_id'],
                        'Intake: '.$fintake['code'].' '.SI::status_get('Intake_Engine',
                            $fintake['intake_status'])['label'],
                        $moddate
                    );
                    $success = $temp_result['success'];
                    $msg = array_merge($msg,$temp_result['msg']);
                    if($success !== 1) break;

                }

                if($success !== 1) break;

            }
        }

        if($success === 1){
            $temp_res = SI::status_log_add($db,
                'intake',
                $intake_id,
                $fintake['intake_status']
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

    function intake_process($db, $final_data ,$id){
        //<editor-fold defaultstate="collapsed" >
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $fintake = array_merge($final_data['intake'],array("modid"=>$modid,"moddate"=>$moddate));


        $intake = array();
        $q = '
            select t1.*
            from intake t1
            where t1.id = '.$db->escape($id).'
        ';
        $intake = $db->query_array($q)[0];

        if(!$db->update('intake',$fintake,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'intake',
                $id,$fintake['intake_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }


        $result['trans_id']=$id;

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    function intake_done($db, $final_data ,$id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('intake_final/intake_final_engine');
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $fintake = array_merge($final_data['intake'],array("modid"=>$modid,"moddate"=>$moddate));


        $intake = array();
        $q = '
            select t1.*
            from intake t1
            where t1.id = '.$db->escape($id).'
        ';
        $intake = $db->query_array($q)[0];

        $intake_status_old = $intake['intake_status'];
        $intake_type = $intake['intake_type'];

        $warehouse_from = array();
        $q = '
            select t3.id warehouse_id, t3.name warehouse_name 
            from intake_warehouse_from t2 
                inner join warehouse t3 on t3.id = t2.warehouse_id
            where t2.intake_id = '.$db->escape($intake['id']).'
        ';
        $warehouse_from = $db->query_array($q)[0];


        $intake_product = array();
        $q = '
            select *
            from intake_product
            where intake_id = '.$db->escape($id).'
        ';
        $intake_product = $db->query_array($q);

        if(!$db->update('intake',$fintake,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'intake',
                $id,$fintake['intake_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        if($success === 1 && $intake_status_old !== 'done'){

            switch($intake_type){
                case 'sales_invoice':
                    $intake_final_id='';
                    $rs_temp = $db->fast_get('intake_final_intake',array('intake_id'=>$id));
                    if (count($rs_temp)>0){
                        $intake_final_id = $rs_temp[0]['intake_final_id'];

                        $q = '
                            select t1.intake_status
                            from intake t1
                                inner join intake_final_intake t2
                                    on t1.id = t2.intake_id
                            where t2.intake_final_id = '.$db->escape($intake_final_id).'
                        ';
                        $rs_do = $db->query_array($q);

                        if(count($rs_do)>0){
                            $all_done = true;
                            foreach($rs_do as $do_idx=>$do){
                                if($do['intake_status'] !== 'done') $all_done = false;
                            }
                            if($all_done){
                                $temp_intake_final = array(
                                    'intake_final'=>array(
                                        'intake_final_status'=>'done',
                                    ),
                                );
                                $temp_result = eval('return Intake_Final_Engine::'
                                    .'intake_final_done($db,'
                                    .'$temp_intake_final,'
                                    .'$intake_final_id'
                                    .');'
                                .'');

                                $success = $temp_result['success'];
                                $msg = array_merge($msg, $temp_result['msg']);
                            }
                        }
                    }
                    break;
            }
        }

        if($success == 1 && $intake_status_old !== 'done'){

            foreach($intake_product as $product){
                $product_id = $product['product_id'];
                $unit_id = $product['unit_id'];
                $qty = -1* $product['qty'];
                $intake_product_type = $product['product_type'];
                $warehouse_id = $warehouse_from['warehouse_id'];
                $description = 'Intake: '.$intake['code'].' '.SI::status_get('Intake_Engine',
                        $fintake['intake_status'])['label'];
                get_instance()->load->helper('product_stock_engine');
                $stock_result = array('success'=>1,'msg'=>array());
                switch($intake_product_type){
                    case'registered_product':
                    $stock_result = Product_Stock_Engine::stock_good_only_add(
                            $db,
                            $warehouse_id,
                            $product_id,
                            $qty,
                            $unit_id,
                            $description,
                            $moddate
                        );
                        break;
                }
                if($stock_result['success'] == 0){
                    $success = 0;
                    $msg[]=$stock_result['msg'];   
                    $db->trans_rollback();
                    break;
                } 
            }
        }


        $result['trans_id']=$id;

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    function intake_canceled($db, $final_data ,$id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('product_stock_engine');
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>$id);
        $success = 1;
        $msg = array();

        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $intake_id = $id;
        $fintake = array_merge($final_data['intake'],array("modid"=>$modid,"moddate"=>$moddate));

        $intake = array();
        $q = '
            select t1.*
            from intake t1
            where t1.id = '.$db->escape($id).'
        ';
        $intake = $db->query_array($q)[0];

        $intake_status_old = $intake['intake_status'];
        $intake_type = $intake['intake_type'];

        $warehouse_from = array();
        $q = '
            select t3.id warehouse_id, t3.name warehouse_name 
            from intake_warehouse_from t2 
                inner join warehouse t3 on t3.id = t2.warehouse_id
            where t2.intake_id = '.$db->escape($intake['id']).'
        ';
        $warehouse_from = $db->query_array($q)[0];


        $intake_product = array();
        $q = '
            select *
            from intake_product
            where intake_id = '.$db->escape($id).'
        ';
        $intake_product = $db->query_array($q);

        if(!$db->update('intake',$fintake,array("id"=>$id))){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        if($success == 1){
            $temp_result = SI::status_log_add($db,'intake',
                $id,$fintake['intake_status']);
            $success = $temp_result['success'];
            $msg = array_merge($msg, $temp_result['msg']);
        }

        if($success === 1){
            foreach($intake_product as $product){
                $product_reference_id = $product['reference_id'];
                $product_id = $product['product_id'];
                $unit_id = $product['unit_id'];
                $qty = $product['qty'];
                $warehouse_from_id = $warehouse_from['warehouse_id'];
                $intake_product_type = $product['product_type'];
                switch($intake_type){
                    case 'sales_invoice':
                        get_instance()->load->helper('sales_pos/sales_pos_engine');
                        $q = '
                            select t1.sales_invoice_id
                            from sales_invoice_intake_final t1
                                inner join intake_final_intake t2 
                                    on t1.intake_final_id = t2.intake_final_id
                            where t2.intake_id = '.$db->escape($intake_id).'
                        ';
                        $sales_invoice_id = $db->query_array($q)[0]['sales_invoice_id'];
                        $temp_si_product = array(
                            'reference_id'=>$product_reference_id,
                            'qty'=>Tools::_float($qty)
                        );
                        $temp_result = Sales_Pos_Engine::movement_outstanding_qty_add($db, $sales_invoice_id,$temp_si_product);
                        $success = $temp_result['success'];
                        $msg = array_merge($msg,$temp_result['msg']);

                        if($success !== 1) break;

                        break;
                }

                if($success){
                    //<editor-fold desc="warehouse stock adjustment" defaultstate="collapsed">
                    switch($intake_product_type){
                        case 'registered_product':
                            //<editor-fold defaultstate="collapsed">
                            if($intake_status_old === 'done'){
                                $description = 'Intake: '.$intake['code'].' '.
                                    SI::status_get('Intake_Engine',
                                        $fintake['intake_status']
                                    )['label'];

                                $temp_result = Product_Stock_Engine::stock_good_add(
                                    $db,
                                    $warehouse_from_id,
                                    $product_id,
                                    $qty,
                                    $unit_id,
                                    $description,
                                    $moddate
                                );
                                $success = $temp_result['success'];
                                $msg = array_merge($msg, $temp_result['msg']);
                                if($success !== 1) break;
                            }
                            else if($intake_status_old !== 'done'){
                                $description = 'Product Intake: '.$intake['code'].' '.
                                    SI::status_get('Intake_Engine',
                                        $fintake['intake_status']
                                    )['label'];

                                $temp_result = Product_Stock_Engine::stock_sales_available_only_add(
                                    $db,
                                    $warehouse_from_id,
                                    $product_id,
                                    $qty,
                                    $unit_id,
                                    $description,
                                    $moddate
                                );

                                $success = $temp_result['success'];
                                $msg = array_merge($msg, $temp_result['msg']);
                                if($success !== 1) break;
                            }
                            //</editor-fold>
                            break;
                    }
                    
                    //</editor-fold>
                }

                if($success !== 1) break;
            }            
        }

        $result['trans_id']=$id;

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }
}
?>
