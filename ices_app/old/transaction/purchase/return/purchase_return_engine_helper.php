<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Purchase_Return_Engine {
        
        private static $purchase_return_status_list = array(
            array(//label name is used for method name
                'val'=>'I'
                ,'label'=>'INVOICED'
                ,'method'=>'purchase_return_invoiced'
                ,'default'=>true
                ,'next_allowed_status'=>array('X')
            )
            ,array(
                'val'=>'X'
                ,'label'=>'CANCELED'
                ,'method'=>'purchase_return_canceled'
                ,'next_allowed_status'=>array()
            )
        );
        
        public static function purchase_return_status_list_get(){
            $result = array();
            $result = self::$purchase_return_status_list;
            return $result;
        }
        
        public static function purchase_return_status_get($product_status_val){
            $status_list = self::$purchase_return_status_list;
            $result = null;
            for($i = 0;$i<count($status_list);$i++){
                if($status_list[$i]['val'] === $product_status_val){
                    $result = $status_list[$i];
                }
            }
            return $result;
        }
        
        public static function purchase_return_status_next_allowed_status_get($curr_status_val){
            $result = array();
            $curr_status = null;
            for($i = 0;$i<count(self::$purchase_return_status_list);$i++){
                if(self::$purchase_return_status_list[$i]['val'] === $curr_status_val){
                    $curr_status = self::$purchase_return_status_list[$i];
                    break;
                }
            }
            
            for ($i = 0;$i<count($curr_status['next_allowed_status']);$i++){
                foreach(self::$purchase_return_status_list as $status){
                    if($status['val'] === $curr_status['next_allowed_status'][$i]){
                        $result[] = array('val'=>$status['val']
                                ,'label'=>$status['label']
                                ,'method'=>$status['method']);
                    }
                }
            }
            return $result;
        }
        
        public static function purchase_return_status_default_status_get(){
            $result = array();
            foreach(self::$purchase_return_status_list as $status){
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
        
        public static function purchase_return_exists($id){
            $result = false;
            $db = new DB();
            $q = '
                    select 1 
                    from purchase_return 
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
                'index'=>get_instance()->config->base_url().'purchase_return/'
                ,'purchase_return_engine'=>'transaction/purchase/return/purchase_return_engine'
                ,'purchase_return_renderer' => 'transaction/purchase/return/purchase_return_renderer'
                ,'ajax_search'=>get_instance()->config->base_url().'purchase_return/ajax_search/'
                
            );
            
            return json_decode(json_encode($path));
        }
        
        public static function purchase_return_submit($id,$method,$post){
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
            if($method == 'add') $data['purchase_return']['id'] = '';
            else $data['purchase_return']['id'] = $id;
            
            if($cont){
                $result = self::purchase_return_save($method,$data);
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
        
        private static function purchase_return_validate($method,$data=array()){
            $result = array(
                "success"=>1
                ,"msg"=>array()
                
            );
            $purchase_invoice = isset($data['purchase_invoice'])?$data['purchase_invoice']:null;
            $purchase_return = isset($data['purchase_return'])?$data['purchase_return']:null;
            $purchase_return_product = isset($data['purchase_return_product'])? $data['purchase_return_product']: null;
            switch($method){
                case 'purchase_return_add':                   
                    $db = new DB();

                    //check purchase invoice is available
                    $purchase_invoice_id = isset($purchase_invoice['id'])?
                            $purchase_invoice['id']:'';
                    $q = '
                        select 1 
                        from purchase_invoice 
                        where status>0  
                            and purchase_invoice_status = "I" 
                            and id = '.$db->escape($purchase_invoice_id).'
                    ';
                    
                    if(count($db->query_array_obj($q)) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Invalid Purchase Invoice";
                    }
                    else{
                        //check product qty > 1
                        $has_product = true;
                        if(count($purchase_return_product) === 0){
                            $has_product = false;
                            $result['success'] = 0;
                            $result['msg'][] = "Purchase Return must have product";
                        }
                        
                        // check product is valid
                        
                        if($result['success'] === 1 && $has_product){
                               
                            $q = '
                                select distinct t3.product_id, t4.name product_name
                                    , t5.id unit_id
                                    , t5.name unit_name
                                    , sum(t3.qty) received_qty
                                    , t7.price
                                from purchase_invoice_receive_product t1
                                    inner join receive_product t2 on t1.receive_product_id = t2.id
                                    inner join receive_product_product t3 on t2.id = t3.receive_product_id
                                    inner join product t4 on t3.product_id = t4.id
                                    inner join unit t5 on t3.unit_id = t5.id
                                    inner join purchase_invoice t6 on t6.id = t1.purchase_invoice_id
                                    inner join purchase_invoice_product t7 
                                        on t7.purchase_invoice_id = t6.id
                                        and t7.product_id = t3.product_id and t7.unit_id = t3.unit_id
                                where t1.purchase_invoice_id = '.$db->escape($purchase_invoice_id).'
                                    and t2.receive_product_status = "R"
                                group by t3.product_id, t4.name , t5.id , t5.name 
                                
                            ';
                            
                            $rs_available_product = $db->query_array($q);
                            
                            
                            // check product id exists
                            for($i = 0;$i<count($purchase_return_product);$i++){
                                $selected_product = $purchase_return_product[$i];
                                $product_id_valid = false;
                                
                                foreach($rs_available_product as $available_product){
                                    $product_id = isset($selected_product['product_id'])?$selected_product['product_id']:null;
                                    if($available_product['product_id'] === $selected_product['product_id']){
                                        $product_id_valid = true;
                                    }
                                }
                                if(!$product_id_valid ){
                                    $result['success'] = 0;
                                    $result['msg'][] = "Invalid Product";
                                    break;
                                }                                
                            }
                            if($result['success'] === 1){
                                //check at least one product has qty
                                $at_least_one_product_has_qty = false;
                                for($i = 0;$i<count($purchase_return_product);$i++){
                                    $selected_product = $purchase_return_product[$i];

                                    $product_qty = isset($selected_product['qty'])?
                                                floatval(str_replace(',','',$selected_product['qty'])):null;
                                    if($product_qty>0) $at_least_one_product_has_qty = true;

                                };

                                if(!$at_least_one_product_has_qty){
                                    $result['success'] = 0;
                                    $result['msg'][] = "At least one product must have qty";
                                }
                            }
                            
                            if($result['success'] === 1){
                                //check price is in range
                                for($i = 0;$i<count($purchase_return_product);$i++){
                                    $selected_product = $purchase_return_product[$i];
                                    $product_price_valid = false;

                                    $product_qty = isset($selected_product['qty'])?
                                                floatval(str_replace(',','',$selected_product['qty'])):null;
                                    $product_price = isset($selected_product['price'])?
                                                    floatval(str_replace(',','',$selected_product['price'])):null;
                                    if($product_qty>0){
                                        foreach($rs_available_product as $available_product){

                                            $product_id = isset($selected_product['product_id'])?
                                                    $selected_product['product_id']:null;
                                            
                                            if($available_product['product_id'] === $selected_product['product_id']){
                                                if($product_price>0 
                                                    && $product_price<=$available_product['price']){
                                                    $product_price_valid = true;                                        
                                                }                                            
                                            }
                                            
                                        }
                                        
                                        if(!$product_price_valid ){
                                            $result['success'] = 0;
                                            $result['msg'][] = "Price must be higher than 0";
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                        
                    }

                    //check purchase_return date
                    $purchase_return_date = isset($purchase_return['purchase_return_date'])?$purchase_return['purchase_return_date']:'';
                    if(strlen($purchase_return_date) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Purchase Return Date cannot be empty";

                    }
                    break;
                case 'purchase_return_invoiced':
                   
                    $db = new DB();
                    //check receive product exists
                    $purchase_return_id = isset($purchase_return['id'])?$purchase_return['id']:'';
                    $q = '
                        select * 
                        from purchase_return 
                        where id = '.$db->escape($purchase_return['id']).'
                    ';
                    $rs_purchase_return = $db->query_array_obj($q);
                    
                    if(count($rs_purchase_return) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Receive Product data is not available";
                        break;
                    }
                    else{
                        $rs_purchase_return = $db->query_array_obj($q)[0];
                    }                    
                    
                    //check receive product is cancelled
                    if($rs_purchase_return->purchase_return_status === 'X'){
                        $result['success'] = 0;
                        $result['msg'][] = "Cannot update Canceled purchase_return";
                        break;
                    }
                    
                    //check if receive product status available
                    if(isset($purchase_return['purchase_return_status'])){
                        $purchase_return['purchase_return_status'];
                    }
                    else{
                        $result['success'] = 0;
                        $result['msg'][] = "Receive Product Status is not available";
                        break;
                    }
                    
                    //check receive product status is in list
                    $status_exists_in_list = false;
                    foreach (self::$purchase_return_status_list as $status){
                        if($status['val'] === $purchase_return['purchase_return_status'])
                            $status_exists_in_list = true;
                    }
                    if(!$status_exists_in_list){
                        $result['success'] = 0;
                        $result['msg'][] = "Invalid Receive Product Status";
                        break;
                    }
                    
                    //check receive product status business logic
                    $status_business_logic_valid = true;
                    if($purchase_return['purchase_return_status'] !== $rs_purchase_return->purchase_return_status){
                        foreach(self::$purchase_return_status_list as $status){
                            if($status['val'] === $rs_purchase_return->purchase_return_status){
                                if(isset($status['next_allowed_status'])){
                                    if(!in_array($purchase_return['purchase_return_status'],$status['next_allowed_status'])){
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
                case 'purchase_return_canceled':
                    $db = new DB();
                    //check receive product exists
                    $purchase_return_id = isset($purchase_return['id'])?$purchase_return['id']:'';
                    $q = '
                        select * 
                        from purchase_return 
                        where id = '.$db->escape($purchase_return['id']).'
                    ';
                    
                    $rs_purchase_return = $db->query_array_obj($q);
                    
                    if(count($rs_purchase_return) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Receive Product data is not available";
                        break;
                    }
                    else{
                        $rs_purchase_return = $db->query_array_obj($q)[0];
                    } 
                    
                    //check receive product is cancelled
                    if($rs_purchase_return->purchase_return_status === 'X'){
                        $result['success'] = 0;
                        $result['msg'][] = "Cannot update Canceled purchase_return";
                        break;
                    }
                    
                    
                    $purchase_return['cancellation_reason'] = isset($purchase_return['cancellation_reason'])?$purchase_return['cancellation_reason']:'';
                    if(strlen(str_replace(' ','',$purchase_return['cancellation_reason'])) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = 'Cancellation Reason is required';
                        break;
                    }
                    
                    break;
                default:
                    $result['success'] = 0;
                    $result['msg'][] = 'Invalid Method';
                        
                    break;
                    
               
            }
            
            return $result;
        }
        
        private static function purchase_return_adjust($action,$data=array()){
            $db = new DB();
            $result = array();
            
            switch($action){
                case 'purchase_return_add':
                    $purchase_return = $data['purchase_return'];                    
                    $purchase_return_product = $data['purchase_return_product'];
                    $purchase_invoice = $data['purchase_invoice'];
                    
                    $purchase_invoice_data = $db->query_array_obj('select * from purchase_invoice where id = '.$db->escape($purchase_invoice['id']))[0];
                    
                    
                    
                    
                    $result['purchase_return_product'] = array();
                    $grand_total = 0;
                    for($i = 0;$i<count($purchase_return_product);$i++){
                        if(floatval(str_replace(',','',$purchase_return_product[$i]['qty']))>0){
                            $qty = floatval(str_replace(',','',$purchase_return_product[$i]['qty']));
                            $price = floatval(str_replace(',','',$purchase_return_product[$i]['price']));
                            $subtotal = $qty * $price;
                            $result['purchase_return_product'][] = array(
                                'product_id'=>$purchase_return_product[$i]['product_id']
                                ,'unit_id'=>$purchase_return_product[$i]['unit_id']
                                ,'qty'=>$qty
                                ,'price'=>$price
                                ,'sub_total'=>$subtotal
                            );
                            $grand_total+=$subtotal;
                        }
                    }
                    
                    $result['purchase_return'] = array(
                        'code'=>''
                        ,'purchase_return_date'=>$purchase_return['purchase_return_date']
                        ,'supplier_id'=>$purchase_invoice_data->supplier_id
                        ,'purchase_return_status'=>self::purchase_return_status_default_status_get()['val']
                        ,'notes'=>isset($purchase_return['notes'])?$purchase_return['notes']:''
                        ,'grand_total'=>$grand_total
                    );
                    
                    $result['purchase_invoice_purchase_return'] = array(
                        'purchase_invoice_id'=>$purchase_invoice['id']
                    );
                            
                    break;
                    
                case 'purchase_return_invoiced':
                    $purchase_return = $data['purchase_return'];                    
                    $result['purchase_return'] = array(
                        'notes'=>isset($purchase_return['notes'])?$purchase_return['notes']:''
                        ,'purchase_return_status'=>$purchase_return['purchase_return_status']
                    );
                    break;
                case 'purchase_return_canceled':
                    $purchase_return = $data['purchase_return'];

                    $result['purchase_return'] = array(
                        'notes'=>isset($purchase_return['notes'])?$purchase_return['notes']:''
                        ,'cancellation_reason'=>isset($purchase_return['cancellation_reason'])?$purchase_return['cancellation_reason']:''
                        ,'purchase_return_status'=>'X'
                    );
                            
                    break;
            }
            
            return $result;
        }
        
        public static function purchase_return_save($method,$data){
            $db = new DB();
            $success = 1;
            $msg = array();
            $action = $method;
            $result = array("success"=>0,"msg"=>array(),'trans_id'=>'');
            $purchase_return_data = $data['purchase_return'];
            $id = $purchase_return_data['id'];
            
            $method_list = array('purchase_return_add');
            foreach(self::$purchase_return_status_list as $status){
                $method_list[] = strtolower($status['method']);
            }
            
            if(in_array($action,$method_list)){
                $validation_res = self::purchase_return_validate($action,$data);
                $success = $validation_res['success']; 
                $msg = $validation_res['msg'];
            }
            else{
                $success = 0;
                $msg[] = 'Unknown method';
            }

            if($success == 1){
                $final_data = self::purchase_return_adjust($action,$data);
                $modid = User_Info::get()['user_id'];
                $moddate = date("Y-m-d H:i:s");
                
                switch($action){                    
                    case 'purchase_return_add':
                        try{ 
                            $db->trans_begin();
                            $fpurchase_return = array_merge($final_data['purchase_return'],array("modid"=>$modid,"moddate"=>$moddate));
                            $purchase_return_id = '';
                            $rs = $db->query_array_obj('select func_code_counter("purchase_return") "code"');
                            $fpurchase_return['code'] = $rs[0]->code;
                            if(!$db->insert('purchase_return',$fpurchase_return)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            if($success == 1){
                                $q = '
                                    select id 
                                    from purchase_return
                                    where status>0 
                                        and purchase_return_status = '.$db->escape(self::purchase_return_status_default_status_get()['val']).' 
                                        and code = '.$db->escape($fpurchase_return['code']).'
                                ';
                                $rs_purchase_return = $db->query_array_obj($q);
                                $purchase_return_id = $rs_purchase_return[0]->id;
                                $result['trans_id']=$purchase_return_id; // useful for view forwarder
                            }
                            
                            if($success == 1){
                                $purchase_return_status_log = array(
                                    'purchase_return_id'=>$purchase_return_id
                                    ,'purchase_return_status'=>self::purchase_return_status_default_status_get()['val']
                                    ,'modid'=>$modid
                                    ,'moddate'=>$moddate    
                                );
                                
                                if(!$db->insert('purchase_return_status_log',$purchase_return_status_log)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                            }
                            
                            if($success == 1){
                                $fpurchase_return_product = $final_data['purchase_return_product'];
                                for($i = 0;$i<count($fpurchase_return_product);$i++){
                                    $fpurchase_return_product[$i]['purchase_return_id'] = $purchase_return_id;
                                    if(!$db->insert('purchase_return_product',$fpurchase_return_product[$i])){
                                        $msg[] = $db->_error_message();
                                        $db->trans_rollback();                                
                                        $success = 0;
                                        break;
                                    }
                                }                                
                            }
                            
                            if($success == 1){
                                $fpurchase_invoice_purchase_return = $final_data['purchase_invoice_purchase_return'];
                                $fpurchase_invoice_purchase_return['purchase_return_id'] = $purchase_return_id;
                                if(!$db->insert('purchase_invoice_purchase_return',$fpurchase_invoice_purchase_return)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                            }
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Add Purchase Return Success';
                            }
                        }
                        catch(Exception $e){
                            
                            $db->trans_rollback();
                            $msg[] = $e->getMessage();
                            $success = 0;
                        }
                        break;
                    case 'purchase_return_invoiced':
                        try{
                            $db->trans_begin();
                            $fpurchase_return = array_merge($final_data['purchase_return'],array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->update('purchase_return',$fpurchase_return,array("id"=>$id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            if($success == 1){
                                $purchase_return_status_log = array(
                                    'purchase_return_id'=>$id
                                    ,'purchase_return_status'=>$fpurchase_return['purchase_return_status']
                                    ,'modid'=>$modid
                                    ,'moddate'=>$moddate    
                                );
                                
                                if(!$db->insert('purchase_return_status_log',$purchase_return_status_log)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                            }
                            
                            
                            if($success == 1 && $action === 'received'){
                                $purchase_return = array();
                                $purchase_return_product = array();
                                $q = '
                                    select t1.*,t3.code purchase_invoice_code 
                                    from purchase_return t1
                                        inner join purchase_invoice_purchase_return t2 on t2.purchase_return_id = t1.id
                                        inner join purchase_invoice t3 on t3.id = t2.purchase_invoice_id
                                    where t1.id = '.$db->escape($id).'
                                ';
                                $purchase_return = $db->query_array($q)[0];
                                $q = '
                                    select *
                                    from purchase_return_product
                                    where purchase_return_id = '.$db->escape($id).'
                                    
                                ';
                                $purchase_return_product = $db->query_array($q);
                                
                                
                                foreach($purchase_return_product as $product){
                                    $product_id = $product['product_id'];
                                    $unit_id = $product['unit_id'];
                                    $qty = $product['qty'];
                                    $warehouse_id = $purchase_return['to_warehouse_id'];
                                    $description = 'PURCHASE INVOCE:'.$purchase_return['purchase_invoice_code'].' RECEIVE PRODUCT:'.$purchase_return['code'].' RECEIVED';
                                    get_instance()->load->helper('product_stock_engine');
                                    $stock_result = Product_Stock_Engine::stock_good_add(
                                            $db,
                                            $warehouse_id
                                            ,$product_id
                                            ,$qty
                                            ,$unit_id
                                            ,$description
                                            ,$purchase_return['purchase_return_date']
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
                    case 'purchase_return_canceled':
                        try{
                            $db->trans_begin();
                            $purchase_return = array();
                            $q = '
                                    select t1.*,t3.code purchase_invoice_code 
                                    from purchase_return t1
                                        inner join purchase_invoice_purchase_return t2 on t2.purchase_return_id = t1.id
                                        inner join purchase_invoice t3 on t3.id = t2.purchase_invoice_id
                                    where t1.id = '.$db->escape($id).'
                                ';
                            $purchase_return = $db->query_array($q)[0];
                            
                            $fpurchase_return = array_merge($final_data['purchase_return'],array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->update('purchase_return',$fpurchase_return,array("id"=>$id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            $result['trans_id']=$id;
                            if($success == 1){
                                $purchase_return_status_log = array(
                                    'purchase_return_id'=>$id
                                    ,'purchase_return_status'=>$fpurchase_return['purchase_return_status']
                                    ,'modid'=>$modid
                                    ,'moddate'=>$moddate    
                                );
                                
                                if(!$db->insert('purchase_return_status_log',$purchase_return_status_log)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }                                
                            }
                            
                            if($purchase_return['purchase_return_status'] === 'R'){
                                
                                $purchase_return_product = array();
                                
                                $q = '
                                    select *
                                    from purchase_return_product
                                    where purchase_return_id = '.$db->escape($id).'
                                    
                                ';
                                $purchase_return_product = $db->query_array($q);                                
                                
                                foreach($purchase_return_product as $product){
                                    $product_id = $product['product_id'];
                                    $unit_id = $product['unit_id'];
                                    $qty = -1*$product['qty'];
                                    $warehouse_id = $purchase_return['to_warehouse_id'];
                                    $description = 'PURCHASE INVOCE:'.$purchase_return['purchase_invoice_code'].' RECEIVE PRODUCT:'.$purchase_return['code'].' CANCELED';
                                    get_instance()->load->helper('product_stock_engine');
                                    $stock_result = Product_Stock_Engine::stock_good_add(
                                            $db,
                                            $warehouse_id
                                            ,$product_id
                                            ,$qty
                                            ,$unit_id
                                            ,$description
                                            ,$purchase_return['purchase_return_date']
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
        
    }
?>
