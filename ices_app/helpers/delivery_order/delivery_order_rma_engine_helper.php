<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Delivery_Order_RMA_Engine {
        
        private static $delivery_order_rma_status_list = array(
            array(//label name is used for method name
                'val'=>'O'
                ,'label'=>'OPENED'
                ,'method'=>'rma_opened'
                ,'default'=>true
                ,'next_allowed_status'=>array('D','P','X')
            )
            ,array(
                'val'=>'D'
                ,'label'=>'DELIVERED'
                ,'method'=>'rma_delivered'
                ,'next_allowed_status'=>array('R','X')
                
            )
            ,array(
                'val'=>'P'
                ,'label'=>'POSTPONED'
                ,'method'=>'rma_posponed'
                ,'next_allowed_status'=>array('D','R','X')
            )
            ,array(
                'val'=>'R'
                ,'label'=>'RECEIVED'
                ,'method'=>'rma_received'
                ,'next_allowed_status'=>array('X')
            )
            ,array(
                'val'=>'X'
                ,'label'=>'CANCELED'
                ,'method'=>'rma_canceled'
                ,'next_allowed_status'=>array()
            )
        );
        
        public static function delivery_order_rma_status_list_get(){
            $result = array();
            $result = self::$delivery_order_rma_status_list;
            return $result;
        }
        
        public static function delivery_order_rma_status_get($product_status_val){
            $status_list = self::$delivery_order_rma_status_list;
            $result = null;
            for($i = 0;$i<count($status_list);$i++){
                if($status_list[$i]['val'] === $product_status_val){
                    $result = $status_list[$i];
                }
            }
            return $result;
        }
        
        public static function delivery_order_rma_status_next_allowed_status_get($curr_status_val){
            $result = array();
            $curr_status = null;
            for($i = 0;$i<count(self::$delivery_order_rma_status_list);$i++){
                if(self::$delivery_order_rma_status_list[$i]['val'] === $curr_status_val){
                    $curr_status = self::$delivery_order_rma_status_list[$i];
                    break;
                }
            }
            
            for ($i = 0;$i<count($curr_status['next_allowed_status']);$i++){
                foreach(self::$delivery_order_rma_status_list as $status){
                    if($status['val'] === $curr_status['next_allowed_status'][$i]){
                        $result[] = array('val'=>$status['val']
                                ,'label'=>$status['label']
                                ,'method'=>$status['method']);
                    }
                }
            }
            return $result;
        }
        
        public static function delivery_order_rma_status_default_status_get(){
            $result = array();
            foreach(self::$delivery_order_rma_status_list as $status){
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
        
        public static function rma_submit($id,$method,$post){
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
            if($method == 'add') $data['delivery_order']['id'] = '';
            else $data['delivery_order']['id'] = $id;
            
            if($cont){
                $result = self::rma_save($method,$data);
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
        
        private static function rma_validate($method,$data=array()){
            $result = array(
                "success"=>1
                ,"msg"=>array()
                
            );
            $delivery_order = isset($data['delivery_order'])?$data['delivery_order']:null;
            $delivery_order_product = isset($data['delivery_order_product'])? $data['delivery_order_product']: null;
            $warehouse_to = isset($data['warehouse_to'])?$data['warehouse_to']:null;
            $warehouse_from = isset($data['warehouse_from'])?$data['warehouse_from']:null;
            $rma_delivery_order = isset($data['rma_delivery_order'])?$data['rma_delivery_order']:null;
            switch($method){
                case 'rma_add':
                    
                    $db = new DB();

                    //check store is available
                    $store_id = isset($delivery_order['store_id'])?$delivery_order['store_id']:'';
                    $q = 'select 1 from store where status>0 and id ='.$db->escape($store_id);
                    if(count($db->query_array_obj($q)) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Store Empty";                    
                    }                   
                    
                    
                    //check warehouse from is available
                    $warehouse_id = isset($warehouse_from['warehouse_id'])?
                            $warehouse_from['warehouse_id']:'';
                    $q = 'select 1 from warehouse where status>0 and id = '.$db->escape($warehouse_id).'';
                    if(count($db->query_array_obj($q)) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Warehouse From Empty";
                        break;
                    }
                    
                    
                    //check rma is available
                    $rma_id = isset($rma_delivery_order['rma_id'])?
                            $rma_delivery_order['rma_id']:'';
                    $q = 'select * from rma where status>0 and rma_status = "O" and id = '.$db->escape($rma_id).'';
                    $rs_rma = $db->query_array_obj($q);
                    
                    if(count($rs_rma) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Invalid RMA";
                        break;
                    }
                    else{
                        
                        $delivery_order_date = isset($delivery_order['delivery_order_date'])?$delivery_order['delivery_order_date']:'';
                        
                        if(strtotime($rs_rma[0]->rma_date) > strtotime($delivery_order_date)){
                            $result['success'] = 0;
                            $result['msg'][] = "Invalid Delivery Order Date ";
                        }
                        
                        //check product qty > 1
                        $has_product = false;
                        for($i = 0;$i<count($delivery_order_product);$i++){
                            $qty = isset($delivery_order_product[$i]['qty'])?floatval($delivery_order_product[$i]['qty']):0;
                            if($qty>0) $has_product = true;
                        }
                        if(!$has_product){
                            $result['success'] = 0;
                            $result['msg'][] = "One Product must have qty";
                        }
                        

                        for($i = 0; $i<count($delivery_order_product); $i++){
                            $product_id = isset($delivery_order_product[$i]['product_id'])?
                                    $delivery_order_product[$i]['product_id']:'';
                            $unit_id = isset($delivery_order_product[$i]['unit_id'])?
                                    $delivery_order_product[$i]['unit_id']:'';
                            $rma_id = $rma_delivery_order['rma_id'];
                            $qty = isset($delivery_order_product[$i]['qty'])?
                                    str_replace(',','',$delivery_order_product[$i]['qty']):0;
                            $max_qty = self::rma_product_max_qty($product_id, $unit_id, $rma_id, $warehouse_id);
                            
                            if(floatval($max_qty)<floatval($qty)){
                                $result['success'] = 0;
                                $result['msg'][] = 'Product Qty is invalid';
                                break;
                            }
                        }

                    }
                    //check product is valid
                    $all_product_valid = true;
                    for($i = 0;$i<count($delivery_order_product);$i++){
                        $product_id = isset($delivery_order_product[$i]['product_id'])?$delivery_order_product[$i]['product_id']:'';
                        $q = 'select 1 from product where status>0 and id = '.$db->escape($product_id);
                        if(count($db->query_array_obj($q)) === 0) $all_product_valid = false;
                    }
                    if(!$all_product_valid){
                        $result['success'] = 0;
                        $result['msg'][] = "Invalid Product";
                    }

                    //check delivery_order date
                    $delivery_order_date = isset($delivery_order['delivery_order_date'])?$delivery_order['delivery_order_date']:'';
                    if(strlen($delivery_order_date) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Delivery Order Date cannot be empty";

                    }
                    break;
                case 'rma_opened':
                case 'rma_delivered':
                case 'rma_posponed':
                case 'rma_received':
                    $db = new DB();
                    //check receive product exists
                    $delivery_order_id = isset($delivery_order['id'])?$delivery_order['id']:'';
                    $q = '
                        select * 
                        from delivery_order 
                        where id = '.$db->escape($delivery_order['id']).'
                    ';
                    $rs_delivery_order = $db->query_array_obj($q);
                    
                    if(count($rs_delivery_order) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Delivery Order data is not available";
                        break;
                    }
                    else{
                        $rs_delivery_order = $db->query_array_obj($q)[0];
                    }                    
                    
                    //check receive product is cancelled
                    if($rs_delivery_order->delivery_order_status === 'X'){
                        $result['success'] = 0;
                        $result['msg'][] = "Cannot update Canceled delivery_order";
                        break;
                    }
                    
                    //check if receive product status available
                    if(isset($delivery_order['delivery_order_status'])){
                        $delivery_order['delivery_order_status'];
                    }
                    else{
                        $result['success'] = 0;
                        $result['msg'][] = "Delivery Order Status is not available";
                        break;
                    }
                    
                    //check receive product status is in list
                    $status_exists_in_list = false;
                    foreach (self::$delivery_order_rma_status_list as $status){
                        if($status['val'] === $delivery_order['delivery_order_status'])
                            $status_exists_in_list = true;
                    }
                    if(!$status_exists_in_list){
                        $result['success'] = 0;
                        $result['msg'][] = "Invalid Delivery Order Status";
                        break;
                    }
                    
                    //check receive product status business logic
                    $status_business_logic_valid = true;
                    if($delivery_order['delivery_order_status'] !== $rs_delivery_order->delivery_order_status){
                        foreach(self::$delivery_order_rma_status_list as $status){
                            if($status['val'] === $rs_delivery_order->delivery_order_status){
                                if(isset($status['next_allowed_status'])){
                                    if(!in_array($delivery_order['delivery_order_status'],$status['next_allowed_status'])){
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
                        $result['msg'][] = "Invalid Delivery Order Status business logic";
                        break;
                    }
                    
                    break;
                case 'rma_canceled':
                    $db = new DB();
                    //check receive product exists
                    $delivery_order_id = isset($delivery_order['id'])?$delivery_order['id']:'';
                    $q = '
                        select * 
                        from delivery_order 
                        where id = '.$db->escape($delivery_order['id']).'
                    ';
                    $rs_delivery_order = $db->query_array_obj($q);
                    
                    if(count($rs_delivery_order) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Delivery Order data is not available";
                        break;
                    }
                    else{
                        $rs_delivery_order = $db->query_array_obj($q)[0];
                    } 
                    
                    //check receive product is cancelled
                    if($rs_delivery_order->delivery_order_status === 'X'){
                        $result['success'] = 0;
                        $result['msg'][] = "Cannot update Canceled delivery_order";
                        break;
                    }
                    
                    
                    $delivery_order['cancellation_reason'] = isset($delivery_order['cancellation_reason'])?$delivery_order['cancellation_reason']:'';
                    if(strlen(str_replace(' ','',$delivery_order['cancellation_reason'])) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = 'Cancellation Reason is required';
                        break;
                    }
                    
                    break;
                    
               
            }
            
            return $result;
        }
        
        private static function rma_adjust($action,$data=array()){
            $db = new DB();
            $result = array();
            
            switch($action){
                case 'rma_add':
                    $delivery_order = $data['delivery_order'];
                    
                    $delivery_order_product = $data['delivery_order_product'];
                    $rma_delivery_order = $data['rma_delivery_order'];
                    $warehouse_to  = $data['warehouse_to'];
                    $warehouse_from  = $data['warehouse_from'];
                    
                    $result['delivery_order_warehouse_to'] = array(
                            'warehouse_id'=>$warehouse_to['warehouse_id']
                            ,'contact_name'=>isset($warehouse_to['contact_name'])?
                                    $warehouse_to['contact_name']:''
                            ,'phone'=>isset($warehouse_to['phone'])?
                                str_replace('-', '',str_replace('_','',$warehouse_to['phone'])):''
                            ,'address'=>isset($warehouse_to['address'])?
                                $warehouse_to['address']:''
                        );
                    
                    $result['delivery_order_warehouse_from'] = array(
                        'warehouse_id'=>$warehouse_from['warehouse_id']
                        );
                    
                    $result['delivery_order'] = array(
                        'code'=>''
                        ,'store_id'=>$delivery_order['store_id']
                        ,'delivery_order_date'=>$delivery_order['delivery_order_date']
                        ,'delivery_order_type'=>'rma'
                        ,'delivery_order_status'=>self::delivery_order_rma_status_default_status_get()['val']
                        ,'notes'=>$delivery_order['notes']
                    );
                    $result['delivery_order_product'] = array();
                    for($i = 0;$i<count($delivery_order_product);$i++){
                        if(floatval($delivery_order_product[$i]['qty'])>0){
                            $result['delivery_order_product'][] = array(
                                'product_id'=>$delivery_order_product[$i]['product_id']
                                ,'unit_id'=>$delivery_order_product[$i]['unit_id']
                                ,'qty'=>$delivery_order_product[$i]['qty']
                            );
                        }
                    }
                    $result['rma_delivery_order'] = array(
                        'rma_id'=>$rma_delivery_order['rma_id']
                    );
                            
                    break;
                    
                case 'rma_opened':
                case 'rma_delivered':
                case 'rma_posponed':
                case 'rma_received':
                    $delivery_order = $data['delivery_order'];                    
                    $result['delivery_order'] = array(
                        'notes'=>isset($delivery_order['notes'])?$delivery_order['notes']:''
                        ,'delivery_order_status'=>$delivery_order['delivery_order_status']
                    );
                    break;
                case 'rma_canceled':
                    $delivery_order = $data['delivery_order'];

                    $result['delivery_order'] = array(
                        'notes'=>isset($delivery_order['notes'])?$delivery_order['notes']:''
                        ,'cancellation_reason'=>isset($delivery_order['cancellation_reason'])?$delivery_order['cancellation_reason']:''
                        ,'delivery_order_status'=>'X'
                    );
                            
                    break;
            }
            
            return $result;
        }
        
        public static function rma_save($method,$data){
            $db = new DB();
            $success = 1;
            $msg = array();
            $action = $method;
            $result = array("success"=>0,"msg"=>array(),'trans_id'=>'');
            $delivery_order_data = $data['delivery_order'];
            $id = $delivery_order_data['id'];
            
            $method_list = array('rma_add');
            foreach(self::$delivery_order_rma_status_list as $status){
                $method_list[] = strtolower($status['method']);
            }
            
            if(in_array($action,$method_list)){
                $validation_res = self::rma_validate($action,$data);
                $success = $validation_res['success']; 
                $msg = $validation_res['msg'];
            }
            else{
                $success = 0;
                $msg[] = 'Unknown method';
            }

            if($success == 1){
                $final_data = self::rma_adjust($action,$data);
                $modid = User_Info::get()['user_id'];
                $moddate = date("Y-m-d H:i:s");
                
                switch($action){                    
                    case 'rma_add':
                        try{ 
                            $db->trans_begin();
                            $fdelivery_order = array_merge($final_data['delivery_order'],array("modid"=>$modid,"moddate"=>$moddate));
                            $delivery_order_id = '';
                            $rs = $db->query_array_obj('select func_code_counter_store("delivery_order",'.$db->escape($fdelivery_order['store_id']).') "code"');
                            $fdelivery_order['code'] = $rs[0]->code;
                            if(!$db->insert('delivery_order',$fdelivery_order)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            if($success == 1){
                                $q = '
                                    select id 
                                    from delivery_order
                                    where status>0 
                                        and delivery_order_status = '.$db->escape(self::delivery_order_rma_status_default_status_get()['val']).' 
                                        and code = '.$db->escape($fdelivery_order['code']).'
                                ';
                                $rs_delivery_order = $db->query_array_obj($q);
                                $delivery_order_id = $rs_delivery_order[0]->id;
                                $result['trans_id']=$delivery_order_id; // useful for view forwarder
                            }
                            
                            if($success == 1){
                                $fwarehouse_to = $final_data['delivery_order_warehouse_to'];
                                $fwarehouse_to['delivery_order_id'] = $delivery_order_id;
                                if(!$db->insert('delivery_order_warehouse_to',$fwarehouse_to)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                            }
                            
                            if($success == 1){
                                $fwarehouse_from = $final_data['delivery_order_warehouse_from'];
                                $fwarehouse_from['delivery_order_id'] = $delivery_order_id;
                                if(!$db->insert('delivery_order_warehouse_from',$fwarehouse_from)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                            }
                            
                            if($success == 1){
                                $delivery_order_status_log = array(
                                    'delivery_order_id'=>$delivery_order_id
                                    ,'delivery_order_status'=>self::delivery_order_rma_status_default_status_get()['val']
                                    ,'modid'=>$modid
                                    ,'moddate'=>$moddate    
                                );
                                
                                if(!$db->insert('delivery_order_status_log',$delivery_order_status_log)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                            }
                            
                            if($success == 1){
                                $fdelivery_order_product = $final_data['delivery_order_product'];
                                for($i = 0;$i<count($fdelivery_order_product);$i++){
                                    $fdelivery_order_product[$i]['delivery_order_id'] = $delivery_order_id;
                                    if(!$db->insert('delivery_order_product',$fdelivery_order_product[$i])){
                                        $msg[] = $db->_error_message();
                                        $db->trans_rollback();                                
                                        $success = 0;
                                        break;
                                    }
                                }
                                
                            }
                            
                            if($success == 1){
                                $frma_delivery_order = $final_data['rma_delivery_order'];
                                $frma_delivery_order['delivery_order_id'] = $delivery_order_id;
                                if(!$db->insert('rma_delivery_order',$frma_delivery_order)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                            }
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Add Delivery Order Success';
                            }
                        }
                        catch(Exception $e){
                            
                            $db->trans_rollback();
                            $msg[] = $e->getMessage();
                            $success = 0;
                        }
                        break;
                    case 'rma_opened':
                    case 'rma_delivered':
                    case 'rma_posponed':
                    case 'rma_received':
                        try{
                            $db->trans_begin();
                            $fdelivery_order = array_merge($final_data['delivery_order'],array("modid"=>$modid,"moddate"=>$moddate));

                            $delivery_order = array();
                            $q = '
                                select t1.*,t3.code rma_code 
                                from delivery_order t1
                                    inner join rma_delivery_order t2 on t2.delivery_order_id = t1.id
                                    inner join rma t3 on t3.id = t2.rma_id
                                where t1.id = '.$db->escape($id).'
                            ';
                            $delivery_order = $db->query_array($q)[0];
                            
                            $warehouse_from = array();
                            $q = '
                                select t3.id warehouse_id, t3.name warehouse_name 
                                from delivery_order_warehouse_from t2 
                                    inner join warehouse t3 on t3.id = t2.warehouse_id
                                where t2.delivery_order_id = '.$db->escape($delivery_order['id']).'
                            ';
                            $warehouse_from = $db->query_array($q)[0];
                            
                            
                            $delivery_order_product = array();
                            $q = '
                                select *
                                from delivery_order_product
                                where delivery_order_id = '.$db->escape($id).'

                            ';
                            $delivery_order_product = $db->query_array($q);
                            
                            if(!$db->update('delivery_order',$fdelivery_order,array("id"=>$id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            if($success == 1){
                                $delivery_order_status_log = array(
                                    'delivery_order_id'=>$id
                                    ,'delivery_order_status'=>$fdelivery_order['delivery_order_status']
                                    ,'modid'=>$modid
                                    ,'moddate'=>$moddate    
                                );
                                
                                if(!$db->insert('delivery_order_status_log',$delivery_order_status_log)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                            }
                            
                            
                            
                            if($success == 1 && $action === 'rma_delivered' 
                                    && $delivery_order['delivery_order_status'] !== 'D'){
                                
                                foreach($delivery_order_product as $product){
                                    $product_id = $product['product_id'];
                                    $unit_id = $product['unit_id'];
                                    $qty = -1* $product['qty'];
                                    $warehouse_id = $warehouse_from['warehouse_id'];
                                    $description = 'RMA :'.$delivery_order['rma_code'].' DELIVERY PRODUCT:'.$delivery_order['code'].' RECEIVED';
                                    get_instance()->load->helper('product_stock_engine');
                                    $stock_result = Product_Stock_Engine::stock_good_add(
                                            $db,
                                            $warehouse_id
                                            ,$product_id
                                            ,$qty
                                            ,$unit_id
                                            ,$description
                                            ,$delivery_order['delivery_order_date']
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
                                $msg[] = 'Update Delivery Order Success';
                            }
                        }
                        catch(Exception $e){
                            $db->trans_rollback();
                            $msg[] = $e->getMessage();
                            $success = 0;
                        }                        
                        
                        break;
                    case 'rma_canceled':
                        try{
                            $db->trans_begin();
                            $delivery_order = array();
                            $q = '
                                    select t1.*,t3.code rma_code 
                                    from delivery_order t1
                                        inner join rma_delivery_order t2 on t2.delivery_order_id = t1.id
                                        inner join rma t3 on t3.id = t2.rma_id
                                    where t1.id = '.$db->escape($id).'
                                ';
                            $delivery_order = $db->query_array($q)[0];
                            
                            $warehouse_from = array();
                            $q = '
                                select t3.id warehouse_id, t3.name warehouse_name 
                                from delivery_order_warehouse_from t2 
                                    inner join warehouse t3 on t3.id = t2.warehouse_id
                                where t2.delivery_order_id = '.$db->escape($delivery_order['id']).'
                            ';
                            $warehouse_from = $db->query_array($q)[0];
                            
                            
                            $fdelivery_order = array_merge($final_data['delivery_order'],array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->update('delivery_order',$fdelivery_order,array("id"=>$id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            $result['trans_id']=$id;
                            if($success == 1){
                                $delivery_order_status_log = array(
                                    'delivery_order_id'=>$id
                                    ,'delivery_order_status'=>$fdelivery_order['delivery_order_status']
                                    ,'modid'=>$modid
                                    ,'moddate'=>$moddate    
                                );
                                
                                if(!$db->insert('delivery_order_status_log',$delivery_order_status_log)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }                                
                            }
                            
                            if ($success == 1 && $action === 'rma_canceled' 
                                    && $delivery_order['delivery_order_status'] !== 'O'){
                                $delivery_order_product = array();
                                $q = '
                                    select *
                                    from delivery_order_product
                                    where delivery_order_id = '.$db->escape($id).'

                                ';
                                $delivery_order_product = $db->query_array($q);
                                
                                foreach($delivery_order_product as $product){
                                    $product_id = $product['product_id'];
                                    $unit_id = $product['unit_id'];
                                    $qty = $product['qty'];
                                    $warehouse_id = $warehouse_from['warehouse_id'];
                                    $description = 'RMA:'.$delivery_order['rma_code'].' DELIVERY PRODUCT:'.$delivery_order['code'].' CANCELED';
                                    get_instance()->load->helper('product_stock_engine');
                                    $stock_result = Product_Stock_Engine::stock_good_add(
                                            $db,
                                            $warehouse_id
                                            ,$product_id
                                            ,$qty
                                            ,$unit_id
                                            ,$description
                                            ,$delivery_order['delivery_order_date']
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
                                $msg[] = 'Cancel Delivery Order Success';
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
        
        public static function rma_product_max_qty($product_id, $unit_id, $rma_id, $warehouse_id){
            get_instance()->load->helper('master/product_engine');
            $db = new DB();
            $result = 0;
            $q = '
                select coalesce(rma_qty,0) - coalesce(delivered_qty,0) max_qty 
                from
                (
                    select sum(t1.qty) rma_qty
                    from rma_product t1
                        where t1.rma_id = '.$db->escape($rma_id).'
                            and t1.product_id = '.$db->escape($product_id).'
                            and t1.unit_id = '.$db->escape($unit_id).'                                
                ) t1
                ,(
                    select sum(t24.qty) delivered_qty
                    from rma_delivery_order t22 
                        inner join delivery_order t23 on t22.delivery_order_id = t23.id
                        inner join delivery_order_product t24 on t23.id = t24.delivery_order_id
                    where t23.delivery_order_status != "X"    
                        and t24.product_id = '.$db->escape($product_id).'
                        and t24.unit_id = '.$db->escape($unit_id).'
                        and t22.rma_id = '.$db->escape($rma_id).'
                ) t2
            ';
            $rs = $db->query_array_obj($q);
            if(count($rs)>0){
                $max_qty = $rs[0]->max_qty;
                $cont = true;
                
                if($max_qty < 1) $cont = false;
                
                if($cont){
                    
                    $stock_qty = Product_Engine::product_stock_get($product_id, $unit_id, $warehouse_id);
                    if($max_qty>$stock_qty) $max_qty = $stock_qty;

                    $result = $max_qty;
                }
            }
            return $result;
        }
        
    }
?>
