<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Receive_Product_Purchase_Invoice_Engine {
        
        // <editor-fold defaultstate="collapsed" desc="receive_product_purchase_invoice_status_list">
        private static $receive_product_purchase_invoice_status_list = array(
            array(//label name is used for method name
                'val'=>'O'
                ,'label'=>'OPENED'
                ,'method'=>'purchase_invoice_opened'
                ,'default'=>true
                ,'next_allowed_status'=>array('D','P','X')
            )
            ,array(
                'val'=>'D'
                ,'label'=>'DELIVERED'
                ,'method'=>'purchase_invoice_delivered'
                ,'next_allowed_status'=>array('R','X')
                
            )
            ,array(
                'val'=>'P'
                ,'label'=>'POSTPONED'
                ,'method'=>'purchase_invoice_posponed'
                ,'next_allowed_status'=>array('D','R','X')
            )
            ,array(
                'val'=>'R'
                ,'label'=>'RECEIVED'
                ,'method'=>'purchase_invoice_received'
                ,'next_allowed_status'=>array('X')
            )
            ,array(
                'val'=>'X'
                ,'label'=>'CANCELED'
                ,'method'=>'purchase_invoice_canceled'
                ,'next_allowed_status'=>array()
            )
        );
        // </editor-fold>
        
        public static function receive_product_purchase_invoice_status_list_get(){
            $result = array();
            $result = self::$receive_product_purchase_invoice_status_list;
            return $result;
        }
        
        public static function receive_product_purchase_invoice_status_get($product_status_val){
            $status_list = self::$receive_product_purchase_invoice_status_list;
            $result = null;
            for($i = 0;$i<count($status_list);$i++){
                if($status_list[$i]['val'] === $product_status_val){
                    $result = $status_list[$i];
                }
            }
            return $result;
        }
        
        public static function receive_product_purchase_invoice_status_next_allowed_status_get($curr_status_val){
            $result = array();
            $curr_status = null;
            for($i = 0;$i<count(self::$receive_product_purchase_invoice_status_list);$i++){
                if(self::$receive_product_purchase_invoice_status_list[$i]['val'] === $curr_status_val){
                    $curr_status = self::$receive_product_purchase_invoice_status_list[$i];
                    break;
                }
            }
            
            for ($i = 0;$i<count($curr_status['next_allowed_status']);$i++){
                foreach(self::$receive_product_purchase_invoice_status_list as $status){
                    if($status['val'] === $curr_status['next_allowed_status'][$i]){
                        $result[] = array('val'=>$status['val']
                                ,'label'=>$status['label']
                                ,'method'=>$status['method']);
                    }
                }
            }
            return $result;
        }
        
        public static function receive_product_purchase_invoice_status_default_status_get(){
            $result = array();
            foreach(self::$receive_product_purchase_invoice_status_list as $status){
                if(isset($status['default'])){
                    if($status['default']){
                        $result['val'] = $status['val'];
                        $result['label'] = $status['label'];
                        $result['method'] = $status['method'];
                    }
                }
            }
            return $result;
        }
        
        public static function purchase_invoice_submit($id,$method,$post){
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
            if($method == 'add') $data['receive_product']['id'] = '';
            else $data['receive_product']['id'] = $id;
            
            if($cont){
                $result = self::purchase_invoice_save($method,$data);
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
        
        private static function purchase_invoice_validate($method,$data=array()){
            $result = array(
                "success"=>1
                ,"msg"=>array()
                
            );
            $receive_product = isset($data['receive_product'])?$data['receive_product']:null;
            $receive_product_product = isset($data['receive_product_product'])? $data['receive_product_product']: null;
            $warehouse_to = isset($data['warehouse_to'])?$data['warehouse_to']:null;
            $warehouse_from = isset($data['warehouse_from'])?$data['warehouse_from']:null;
            switch($method){
                case 'purchase_invoice_add':                   

                    $purchase_invoice_receive_product = isset($data['purchase_invoice_receive_product'])?$data['purchase_invoice_receive_product']:null;

                    $db = new DB();

                    //check store is available
                    $store_id = isset($receive_product['store_id'])?$receive_product['store_id']:'';
                    $q = 'select 1 from store where status>0 and id ='.$db->escape($store_id);
                    if(count($db->query_array_obj($q)) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Store is empty";                    
                    }
                    
                    //check warehouse is available
                    if($warehouse_from === null 
                            || !isset($warehouse_from['reference_code'])
                            || !isset($warehouse_from['contact_name'])
                            || !isset($warehouse_from['address'])
                            || !isset($warehouse_from['phone'])
                        ){
                        $result['success'] = 0;
                        $result['msg'][] = "Warehouse From is invalid";
                    }
                    
                    
                    
                    //check warehouse is available
                    $warehouse_id = isset($warehouse_to['warehouse_id'])?
                            $warehouse_to['warehouse_id']:'';
                    $q = 'select 1 from warehouse where status>0 and id = '.$db->escape($warehouse_id).'';
                    if(count($db->query_array_obj($q)) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Warehouse To is empty";
                    }

                    //check purchase invoice is available
                    $purchase_invoice_id = isset($purchase_invoice_receive_product['purchase_invoice_id'])?
                            $purchase_invoice_receive_product['purchase_invoice_id']:'';
                    $q = 'select * from purchase_invoice where status>0 and purchase_invoice_status = "I" and id = '.$db->escape($purchase_invoice_id).'';
                    $rs_purchase_invoice = $db->query_array_obj($q);
                    
                    if(count($rs_purchase_invoice) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Invalid Purchase Invoice";
                    }
                    else{
                        
                        $receive_product_date = isset($receive_product['receive_product_date'])?$receive_product['receive_product_date']:'';
                        
                        if(strtotime($rs_purchase_invoice[0]->purchase_invoice_date) > strtotime($receive_product_date)){
                            $result['success'] = 0;
                            $result['msg'][] = "Receive Product Date less than ".$rs_purchase_invoice[0]->purchase_invoice_date;
                        }
                        
                        //check product qty > 1
                        $has_product = false;
                        for($i = 0;$i<count($receive_product_product);$i++){
                            $qty = isset($receive_product_product[$i]['qty'])?floatval($receive_product_product[$i]['qty']):0;
                            if($qty>0) $has_product = true;
                        }
                        if(!$has_product){
                            $result['success'] = 0;
                            $result['msg'][] = "One Product must have qty ";
                        }
                        
                        for($i = 0; $i<count($receive_product_product); $i++){
                            $product_id =isset($receive_product_product[$i]['product_id'])?$receive_product_product[$i]['product_id']:'';
                            $unit_id = isset($receive_product_product[$i]['unit_id'])?$receive_product_product[$i]['unit_id']:'';
                            $purchase_invoice_id = $purchase_invoice_receive_product['purchase_invoice_id'];
                            $qty = isset($receive_product_product[$i]['qty'])?
                                    str_replace(',','',$receive_product_product[$i]['qty']):0;
                            $max_qty = self::purchase_invoice_max_qty_get($product_id, $unit_id, $purchase_invoice_id);
                            if(floatval($qty)>floatval($max_qty)){
                                $result['success'] = 0;
                                $result['msg'][] = 'Product qty is invalid';
                                break;
                            }
                        }

                    }
                    //check product is valid
                    $all_product_valid = true;
                    for($i = 0;$i<count($receive_product_product);$i++){
                        $product_id = isset($receive_product_product[$i]['product_id'])?$receive_product_product[$i]['product_id']:'';
                        $q = 'select 1 from product where status>0 and id = '.$db->escape($product_id);
                        if(count($db->query_array_obj($q)) === 0) $all_product_valid = false;
                    }
                    if(!$all_product_valid){
                        $result['success'] = 0;
                        $result['msg'][] = "Invalid Product";
                    }

                    //check receive_product date
                    $receive_product_date = isset($receive_product['receive_product_date'])?$receive_product['receive_product_date']:'';
                    if(strlen($receive_product_date) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Receive Product Date cannot be empty";

                    }
                    break;
                case 'purchase_invoice_opened':
                case 'purchase_invoice_delivered':
                case 'purchase_invoice_posponed':
                case 'purchase_invoice_received':
                    $db = new DB();
                    //check receive product exists
                    $receive_product_id = isset($receive_product['id'])?$receive_product['id']:'';
                    $q = '
                        select * 
                        from receive_product 
                        where id = '.$db->escape($receive_product['id']).'
                    ';
                    $rs_receive_product = $db->query_array_obj($q);
                    
                    if(count($rs_receive_product) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Receive Product data is not available";
                        break;
                    }
                    else{
                        $rs_receive_product = $db->query_array_obj($q)[0];
                    }                    
                    
                    //check receive product is cancelled
                    if($rs_receive_product->receive_product_status === 'X'){
                        $result['success'] = 0;
                        $result['msg'][] = "Cannot update Canceled receive_product";
                        break;
                    }
                    
                    //check if receive product status available
                    if(isset($receive_product['receive_product_status'])){
                        $receive_product['receive_product_status'];
                    }
                    else{
                        $result['success'] = 0;
                        $result['msg'][] = "Receive Product Status is not available";
                        break;
                    }
                    
                    //check receive product status is in list
                    $status_exists_in_list = false;
                    foreach (self::$receive_product_purchase_invoice_status_list as $status){
                        if($status['val'] === $receive_product['receive_product_status'])
                            $status_exists_in_list = true;
                    }
                    if(!$status_exists_in_list){
                        $result['success'] = 0;
                        $result['msg'][] = "Invalid Receive Product Status";
                        break;
                    }
                    
                    //check receive product status business logic
                    $status_business_logic_valid = true;
                    if($receive_product['receive_product_status'] !== $rs_receive_product->receive_product_status){
                        foreach(self::$receive_product_purchase_invoice_status_list as $status){
                            if($status['val'] === $rs_receive_product->receive_product_status){
                                if(isset($status['next_allowed_status'])){
                                    if(!in_array($receive_product['receive_product_status'],$status['next_allowed_status'])){
                                        $status_business_logic_valid = false;
                                    }
                                }
                                else{
                                    $status_business_logic_valid = false;
                                }
                                break;
                            }
                        }
                    }
                    if(!$status_business_logic_valid){
                        $result['success'] = 0;
                        $result['msg'][] = "Invalid Receive Product Status business logic";
                        break;
                    }
                    
                    break;
                case 'purchase_invoice_canceled':
                    get_instance()->load->helper('product_stock_engine');
                    get_instance()->load->helper('master/product_engine');
                    $db = new DB();
                    //check receive product exists
                    $receive_product_id = isset($receive_product['id'])?$receive_product['id']:'';
                    $q = '
                        select * 
                        from receive_product 
                        where id = '.$db->escape($receive_product['id']).'
                    ';
                    $rs_receive_product = $db->query_array_obj($q);
                    
                    if(count($rs_receive_product) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Receive Product data is not available";
                        break;
                    }
                    else{
                        $rs_receive_product = $db->query_array_obj($q)[0];
                    } 
                    
                    //check receive product is cancelled
                    if($rs_receive_product->receive_product_status === 'X'){
                        $result['success'] = 0;
                        $result['msg'][] = "Cannot update Canceled receive_product";
                        break;
                    }
                    
                    
                    $receive_product['cancellation_reason'] = isset($receive_product['cancellation_reason'])?$receive_product['cancellation_reason']:'';
                    if(strlen(str_replace(' ','',$receive_product['cancellation_reason'])) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = 'Cancellation Reason is required';
                    }
                    
                    $q = '
                        select t2.product_id, t2.unit_id, t4.id warehouse_id, t2.qty
                            ,t5.name product_name, t6.name unit_name
                        from receive_product t1
                            inner join receive_product_product t2 on t1.id = t2.receive_product_id
                            inner join receive_product_warehouse_to t3 on t3.receive_product_id = t1.id
                            inner join warehouse t4 on t4.id = t3.warehouse_id
                            inner join product t5 on t5.id = t2.product_id
                            inner join unit t6 on t6.id = t2.unit_id
                        where t1.id = '.$db->escape($receive_product_id).'
                            and t1.receive_product_status = "R"
                        ';
                    $rs = $db->query_array_obj($q);
                    for($i = 0;$i<count($rs);$i++){
                        $product_id = $rs[$i]->product_id;
                        $unit_id = $rs[$i]->unit_id;
                        $warehouse_id = $rs[$i]->warehouse_id;
                        $qty = $rs[$i]->qty;
                        $product_name = $rs[$i]->product_name;
                        $unit_name = $rs[$i]->unit_name;
                        $stock_qty = Product_Stock_Engine::stock_sum_get('stock_sales_available',$product_id, $unit_id, array($warehouse_id));
                        if($stock_qty<$qty){
                            $result['success'] = 0;
                            $result['msg'][] = $product_name.' '.$unit_name.' stock is not enough';
                            break;
                        }
                    }
                    break;
                    
               
            }
            
            return $result;
        }
        
        private static function purchase_invoice_adjust($action,$data=array()){
            $db = new DB();
            $result = array();
            
            switch($action){
                case 'purchase_invoice_add':
                    $receive_product = $data['receive_product'];
                    
                    $receive_product_product = $data['receive_product_product'];
                    $purchase_invoice_receive_product = $data['purchase_invoice_receive_product'];
                    $warehouse_to  = $data['warehouse_to'];
                    $warehouse_from  = $data['warehouse_from'];
                    
                    $q = 'select id from warehouse where code = "WS"';
                    $warehouse_supplier_id = $db->query_array_obj($q)[0]->id;
                    
                    $result['receive_product_warehouse_from'] = array(
                        'warehouse_id'=>$warehouse_supplier_id
                        ,'reference_code'=>$warehouse_from['reference_code']
                        ,'contact_name'=>$warehouse_from['contact_name']
                        ,'address'=>$warehouse_from['address']
                        ,'phone'=>str_replace('-','',str_replace('_','',$warehouse_from['phone']))
                        );
                    
                    $result['receive_product_warehouse_to'] = array(
                        'warehouse_id'=>$warehouse_to['warehouse_id']
                        );
                    $result['receive_product'] = array(
                        'code'=>''
                        ,'store_id'=>$receive_product['store_id']
                        ,'receive_product_date'=>$receive_product['receive_product_date']
                        ,'receive_product_type'=>'purchase_invoice'
                        ,'receive_product_status'=>self::receive_product_purchase_invoice_status_default_status_get()['val']
                        ,'notes'=>$receive_product['notes']
                    );
                    $result['receive_product_product'] = array();
                    for($i = 0;$i<count($receive_product_product);$i++){
                        if(floatval($receive_product_product[$i]['qty'])>0){
                            $result['receive_product_product'][] = array(
                                'product_id'=>$receive_product_product[$i]['product_id']
                                ,'unit_id'=>$receive_product_product[$i]['unit_id']
                                ,'qty'=>$receive_product_product[$i]['qty']
                            );
                        }
                    }
                    $result['purchase_invoice_receive_product'] = array(
                        'purchase_invoice_id'=>$purchase_invoice_receive_product['purchase_invoice_id']
                    );
                            
                    break;
                    
                case 'purchase_invoice_opened':
                case 'purchase_invoice_delivered':
                case 'purchase_invoice_posponed':
                case 'purchase_invoice_received':
                    $receive_product = $data['receive_product'];                    
                    $result['receive_product'] = array(
                        'notes'=>isset($receive_product['notes'])?$receive_product['notes']:''
                        ,'receive_product_status'=>$receive_product['receive_product_status']
                    );
                    break;
                case 'purchase_invoice_canceled':
                    $receive_product = $data['receive_product'];

                    $result['receive_product'] = array(
                        'notes'=>isset($receive_product['notes'])?$receive_product['notes']:''
                        ,'cancellation_reason'=>isset($receive_product['cancellation_reason'])?$receive_product['cancellation_reason']:''
                        ,'receive_product_status'=>'X'
                    );
                            
                    break;
            }
            
            return $result;
        }
        
        public static function purchase_invoice_save($method,$data){
            $db = new DB();
            $success = 1;
            $msg = array();
            $action = $method;
            $result = array("success"=>0,"msg"=>array(),'trans_id'=>'');
            $receive_product_data = $data['receive_product'];
            $id = $receive_product_data['id'];
            
            $method_list = array('purchase_invoice_add');
            foreach(self::$receive_product_purchase_invoice_status_list as $status){
                $method_list[] = strtolower($status['method']);
            }
            
            if(in_array($action,$method_list)){
                $validation_res = self::purchase_invoice_validate($action,$data);
                $success = $validation_res['success']; 
                $msg = $validation_res['msg'];
            }
            else{
                $success = 0;
                $msg[] = 'Unknown method';
            }

            if($success == 1){
                $final_data = self::purchase_invoice_adjust($action,$data);
                $modid = User_Info::get()['user_id'];
                $moddate = date("Y-m-d H:i:s");
                
                switch($action){                    
                    case 'purchase_invoice_add':
                        try{ 
                            $db->trans_begin();
                            $freceive_product = array_merge($final_data['receive_product'],array("modid"=>$modid,"moddate"=>$moddate));
                            $receive_product_id = '';
                            $rs = $db->query_array_obj('select func_code_counter_store("receive_product",'.$db->escape($freceive_product['store_id']).') "code"');
                            $freceive_product['code'] = $rs[0]->code;
                            if(!$db->insert('receive_product',$freceive_product)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            if($success == 1){
                                $q = '
                                    select id 
                                    from receive_product
                                    where status>0 
                                        and receive_product_status = '.$db->escape(self::receive_product_purchase_invoice_status_default_status_get()['val']).' 
                                        and code = '.$db->escape($freceive_product['code']).'
                                ';
                                $rs_receive_product = $db->query_array_obj($q);
                                $receive_product_id = $rs_receive_product[0]->id;
                                $result['trans_id']=$receive_product_id; // useful for view forwarder
                            }
                            
                            if($success == 1){
                                $fwarehouse_from = $final_data['receive_product_warehouse_from'];
                                $fwarehouse_from['receive_product_id'] = $receive_product_id;
                                if(!$db->insert('receive_product_warehouse_from',$fwarehouse_from)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                            }
                            
                            if($success == 1){
                                $fwarehouse_to = $final_data['receive_product_warehouse_to'];
                                $fwarehouse_to['receive_product_id'] = $receive_product_id;
                                if(!$db->insert('receive_product_warehouse_to',$fwarehouse_to)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                            }
                            
                            if($success == 1){
                                $receive_product_status_log = array(
                                    'receive_product_id'=>$receive_product_id
                                    ,'receive_product_status'=>self::receive_product_purchase_invoice_status_default_status_get()['val']
                                    ,'modid'=>$modid
                                    ,'moddate'=>$moddate    
                                );
                                
                                if(!$db->insert('receive_product_status_log',$receive_product_status_log)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                            }
                            
                            if($success == 1){
                                $freceive_product_product = $final_data['receive_product_product'];
                                for($i = 0;$i<count($freceive_product_product);$i++){
                                    $freceive_product_product[$i]['receive_product_id'] = $receive_product_id;
                                    if(!$db->insert('receive_product_product',$freceive_product_product[$i])){
                                        $msg[] = $db->_error_message();
                                        $db->trans_rollback();                                
                                        $success = 0;
                                        break;
                                    }
                                }
                                
                            }
                            
                            if($success == 1){
                                $fpurchase_invoice_receive_product = $final_data['purchase_invoice_receive_product'];
                                $fpurchase_invoice_receive_product['receive_product_id'] = $receive_product_id;
                                if(!$db->insert('purchase_invoice_receive_product',$fpurchase_invoice_receive_product)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                            }
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Add Receive Product Success';
                            }
                        }
                        catch(Exception $e){
                            
                            $db->trans_rollback();
                            $msg[] = $e->getMessage();
                            $success = 0;
                        }
                        break;
                    case 'purchase_invoice_opened':
                    case 'purchase_invoice_delivered':
                    case 'purchase_invoice_posponed':
                    case 'purchase_invoice_received':
                        try{
                            $db->trans_begin();
                            $freceive_product = array_merge($final_data['receive_product'],array("modid"=>$modid,"moddate"=>$moddate));

                            $receive_product = array();
                            $q = '
                                select t1.*,t3.code purchase_invoice_code 
                                from receive_product t1
                                    inner join purchase_invoice_receive_product t2 on t2.receive_product_id = t1.id
                                    inner join purchase_invoice t3 on t3.id = t2.purchase_invoice_id
                                where t1.id = '.$db->escape($id).'
                            ';
                            $receive_product = $db->query_array($q)[0];
                            
                            $warehouse_to = array();
                            $q = '
                                select t3.id warehouse_id, t3.name warehouse_name 
                                from receive_product_warehouse_to t2 
                                    inner join warehouse t3 on t3.id = t2.warehouse_id
                                where t2.receive_product_id = '.$db->escape($receive_product['id']).'
                            ';
                            $warehouse_to = $db->query_array($q)[0];
                            
                            
                            $receive_product_product = array();
                            $q = '
                                select *
                                from receive_product_product
                                where receive_product_id = '.$db->escape($id).'

                            ';
                            $receive_product_product = $db->query_array($q);
                            
                            if(!$db->update('receive_product',$freceive_product,array("id"=>$id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            if($success == 1){
                                $receive_product_status_log = array(
                                    'receive_product_id'=>$id
                                    ,'receive_product_status'=>$freceive_product['receive_product_status']
                                    ,'modid'=>$modid
                                    ,'moddate'=>$moddate    
                                );
                                
                                if(!$db->insert('receive_product_status_log',$receive_product_status_log)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                            }
                            
                            
                            
                            if($success == 1 && $action === 'purchase_invoice_received' 
                                    && $receive_product['receive_product_status'] !== 'R'){
                                
                                foreach($receive_product_product as $product){
                                    $product_id = $product['product_id'];
                                    $unit_id = $product['unit_id'];
                                    $qty = $product['qty'];
                                    $warehouse_id = $warehouse_to['warehouse_id'];
                                    $description = 'PURCHASE INVOICE:'.$receive_product['purchase_invoice_code'].' RECEIVE PRODUCT:'.$receive_product['code'].' RECEIVED';
                                    get_instance()->load->helper('product_stock_engine');
                                    $stock_result = Product_Stock_Engine::stock_good_add(
                                            $db,
                                            $warehouse_id
                                            ,$product_id
                                            ,$qty
                                            ,$unit_id
                                            ,$description
                                            ,$receive_product['receive_product_date']
                                        );
                                    if($stock_result['success'] == 0){
                                        $success = 0;
                                        $msg[]=$stock_result['msg'];   
                                        $db->trans_rollback();
                                        break;
                                    } 
                                }
                            }
                            
                            
                            $result['trans_id']=$id;
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Update Receive Product Success';
                            }
                        }
                        catch(Exception $e){
                            $db->trans_rollback();
                            $msg[] = $e->getMessage();
                            $success = 0;
                        }
                        break;
                    case 'purchase_invoice_canceled':
                        try{
                            $db->trans_begin();
                            $receive_product = array();
                            $q = '
                                    select t1.*,t3.code purchase_invoice_code 
                                    from receive_product t1
                                        inner join purchase_invoice_receive_product t2 on t2.receive_product_id = t1.id
                                        inner join purchase_invoice t3 on t3.id = t2.purchase_invoice_id
                                    where t1.id = '.$db->escape($id).'
                                ';
                            $receive_product = $db->query_array($q)[0];
                            
                            $warehouse_to = array();
                            $q = '
                                select t3.id warehouse_id, t3.name warehouse_name 
                                from receive_product_warehouse_to t2 
                                    inner join warehouse t3 on t3.id = t2.warehouse_id
                                where t2.receive_product_id = '.$db->escape($receive_product['id']).'
                            ';
                            $warehouse_to = $db->query_array($q)[0];
                            
                            
                            $freceive_product = array_merge($final_data['receive_product'],array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->update('receive_product',$freceive_product,array("id"=>$id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            $result['trans_id']=$id;
                            if($success == 1){
                                $receive_product_status_log = array(
                                    'receive_product_id'=>$id
                                    ,'receive_product_status'=>$freceive_product['receive_product_status']
                                    ,'modid'=>$modid
                                    ,'moddate'=>$moddate    
                                );
                                
                                if(!$db->insert('receive_product_status_log',$receive_product_status_log)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }                                
                            }
                            
                            if ($success == 1 && $action === 'purchase_invoice_canceled' 
                                    && $receive_product['receive_product_status'] === 'R'){
                                $receive_product_product = array();
                                $q = '
                                    select *
                                    from receive_product_product
                                    where receive_product_id = '.$db->escape($id).'

                                ';
                                $receive_product_product = $db->query_array($q);
                                
                                foreach($receive_product_product as $product){
                                    $product_id = $product['product_id'];
                                    $unit_id = $product['unit_id'];
                                    $qty = -1*$product['qty'];
                                    $warehouse_id = $warehouse_to['warehouse_id'];
                                    $description = 'PURCHASE INVOICE:'.$receive_product['purchase_invoice_code'].' RECEIVE PRODUCT:'.$receive_product['code'].' CANCELED';
                                    get_instance()->load->helper('product_stock_engine');
                                    $stock_result = Product_Stock_Engine::stock_good_add(
                                            $db,
                                            $warehouse_id
                                            ,$product_id
                                            ,$qty
                                            ,$unit_id
                                            ,$description
                                            ,$receive_product['receive_product_date']
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
                                $msg[] = 'Cancel Receive Product Success';
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
        
        public static function purchase_invoice_max_qty_get($product_id, $unit_id, $purchase_invoice_id){
            $db = new DB();
            $result = 0;
            $q = '
                select (t1.qty - coalesce(t2.qty,0)) max_qty
                from purchase_invoice_product t1
                    left outer join(
                        select tt2.purchase_invoice_id, tt4.product_id, tt4.unit_id, sum(tt4.qty) qty
                        from purchase_invoice_receive_product tt2 
                                inner join receive_product tt3 on tt2.receive_product_id = tt3.id
                                inner join receive_product_product tt4 on tt3.id = tt4.receive_product_id
                        where tt3.receive_product_status != "X"
                            and tt4.product_id = '.$db->escape($product_id).'
                            and tt4.unit_id = '.$db->escape($unit_id).'
                            and tt2.purchase_invoice_id = '.$db->escape($purchase_invoice_id).'
                        group by tt2.purchase_invoice_id, tt4.product_id, tt4.unit_id
                    ) t2 
                        on t2.purchase_invoice_id = t1.purchase_invoice_id
                            and t2.unit_id = t1.unit_id
                            and t2.product_id = t1.product_id
                where t1.purchase_invoice_id = '.$db->escape($purchase_invoice_id).'
                    and t1.product_id = '.$db->escape($product_id).'
                    and t1.unit_id = '.$db->escape($unit_id).'
            ';
            $rs = $db->query_array_obj($q);
            if(count($rs)>0){
                $result = $rs[0]->max_qty;
            }
            return $result;
        }
        
    }
?>
