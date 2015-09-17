<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class RMA_Purchase_Invoice_Engine {
        
        private static $rma_purchase_invoice_status_list = array(
            array(//label name is used for method name
                'val'=>'O'
                ,'label'=>'OPENED'
                ,'method'=>'purchase_invoice_opened'
                ,'default'=>true
                ,'next_allowed_status'=>array('C','X')
            )
            ,array(
                'val'=>'C'
                ,'label'=>'CLOSED'
                ,'method'=>'purchase_invoice_closed'
                ,'next_allowed_status'=>array('X')
                
            )
            ,array(
                'val'=>'X'
                ,'label'=>'CANCELED'
                ,'method'=>'purchase_invoice_canceled'
                ,'next_allowed_status'=>array()
            )
        );
        
        public static function rma_purchase_invoice_status_list_get(){
            $result = array();
            $result = self::$rma_purchase_invoice_status_list;
            return $result;
        }
        
        public static function rma_purchase_invoice_status_get($product_status_val){
            $status_list = self::$rma_purchase_invoice_status_list;
            $result = null;
            for($i = 0;$i<count($status_list);$i++){
                if($status_list[$i]['val'] === $product_status_val){
                    $result = $status_list[$i];
                }
            }
            return $result;
        }
        
        public static function rma_purchase_invoice_status_next_allowed_status_get($curr_status_val){
            $result = array();
            $curr_status = null;
            for($i = 0;$i<count(self::$rma_purchase_invoice_status_list);$i++){
                if(self::$rma_purchase_invoice_status_list[$i]['val'] === $curr_status_val){
                    $curr_status = self::$rma_purchase_invoice_status_list[$i];
                    break;
                }
            }
            
            for ($i = 0;$i<count($curr_status['next_allowed_status']);$i++){
                foreach(self::$rma_purchase_invoice_status_list as $status){
                    if($status['val'] === $curr_status['next_allowed_status'][$i]){
                        $result[] = array('val'=>$status['val']
                                ,'label'=>$status['label']
                                ,'method'=>$status['method']);
                    }
                }
            }
            return $result;
        }
        
        public static function rma_purchase_invoice_status_default_status_get(){
            $result = array();
            foreach(self::$rma_purchase_invoice_status_list as $status){
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
            if($method == 'add') $data['rma']['id'] = '';
            else $data['rma']['id'] = $id;
            
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
            $rma = isset($data['rma'])?$data['rma']:null;
            $rma_product = isset($data['rma_product'])? $data['rma_product']: null;
            $supplier = isset($data['supplier'])?$data['supplier']:null;
            switch($method){
                case 'purchase_invoice_add':                   

                    $purchase_invoice = isset($data['purchase_invoice'])?$data['purchase_invoice']:null;

                    $db = new DB();

                    //check store is available
                    $store_id = isset($rma['store_id'])?$rma['store_id']:'';
                    $q = 'select 1 from store where status>0 and id ='.$db->escape($store_id);
                    if(count($db->query_array_obj($q)) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Store cannot be Empty";
                    
                    }
                    
                    //check supplier is available
                    $supplier_id = isset($supplier['id'])?$supplier['id']:'';
                    $q = 'select 1 from supplier where status>0 and id ='.$db->escape($supplier_id);
                    if(count($db->query_array_obj($q)) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Supplier cannot be Empty";
                    
                    }
                    

                    //check purchase invoice is available
                    $purchase_invoice_id = isset($purchase_invoice['id'])?
                            $purchase_invoice['id']:'';
                    $q = 'select 1 from purchase_invoice where status>0 and purchase_invoice_status = "I" and id = '.$db->escape($purchase_invoice_id).'';
                    if(count($db->query_array_obj($q)) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Invalid Purchase Invoice";
                    }
                    else{
                        //check product qty > 1
                        $has_product = false;
                        for($i = 0;$i<count($rma_product);$i++){
                            $qty = isset($rma_product[$i]['qty'])?floatval($rma_product[$i]['qty']):0;
                            if($qty>0) $has_product = true;
                        }
                        if(!$has_product){
                            $result['success'] = 0;
                            $result['msg'][] = "One Product must have qty";
                        }
                        

                        for($i = 0; $i<count($rma_product); $i++){
                            $product_id =isset($rma_product[$i]['product_id'])?$rma_product[$i]['product_id']:'';
                            $unit_id =isset($rma_product[$i]['unit_id'])?$rma_product[$i]['unit_id']:'';
                            $purchase_invoice_id = isset($purchase_invoice['id'])?$purchase_invoice['id']:'';
                            $qty = isset($rma_product[$i]['qty'])?
                                    str_replace(',','',$rma_product[$i]['qty']):0;
                            $max_qty = self::receive_product_max_qty($product_id, $unit_id, $purchase_invoice_id);
                            if($qty > $max_qty){
                                $result['success'] = 0;
                                $result['msg'][] = 'Invalid Product Qty';
                                break;
                            }

                        }
                    }
                    //check product is valid
                    $all_product_valid = true;
                    for($i = 0;$i<count($rma_product);$i++){
                        $product_id = isset($rma_product[$i]['product_id'])?$rma_product[$i]['product_id']:'';
                        $q = 'select 1 from product where status>0 and id = '.$db->escape($product_id);
                        if(count($db->query_array_obj($q)) === 0) $all_product_valid = false;
                    }
                    if(!$all_product_valid){
                        $result['success'] = 0;
                        $result['msg'][] = "Invalid Product";
                    }

                    //check rma date
                    $rma_date = isset($rma['rma_date'])?$rma['rma_date']:'';
                    if(strlen($rma_date) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Receive Product Date cannot be empty";

                    }
                    break;
                case 'purchase_invoice_opened':
                case 'purchase_invoice_closed':

                    $db = new DB();
                    //check receive product exists
                    $rma_id = isset($rma['id'])?$rma['id']:'';
                    $q = '
                        select * 
                        from rma 
                        where id = '.$db->escape($rma['id']).'
                    ';
                    $rs_rma = $db->query_array_obj($q);
                    
                    if(count($rs_rma) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Receive Product data is not available";
                        break;
                    }
                    else{
                        $rs_rma = $db->query_array_obj($q)[0];
                    }                    
                    
                    //check receive product is cancelled
                    if($rs_rma->rma_status === 'X'){
                        $result['success'] = 0;
                        $result['msg'][] = "Cannot update Canceled rma";
                        break;
                    }
                    
                    //check if receive product status available
                    if(isset($rma['rma_status'])){
                        $rma['rma_status'];
                    }
                    else{
                        $result['success'] = 0;
                        $result['msg'][] = "Receive Product Status is not available";
                        break;
                    }
                    
                    //check receive product status is in list
                    $status_exists_in_list = false;
                    foreach (self::$rma_purchase_invoice_status_list as $status){
                        if($status['val'] === $rma['rma_status'])
                            $status_exists_in_list = true;
                    }
                    if(!$status_exists_in_list){
                        $result['success'] = 0;
                        $result['msg'][] = "Invalid Receive Product Status";
                        break;
                    }
                    
                    if($method == 'purchase_invoice_closed'){
                        $q = '
                            select coalesce(delivered_qty,0) - coalesce(received_qty,0) mismatch_qty
                            from(
                                select sum(t11.qty) delivered_qty
                                from delivery_order_product t11
                                    inner join rma_delivery_order t12 
                                        on t11.delivery_order_id = t12.delivery_order_id
                                    inner join rma t13
                                        on t13.id = t12.rma_id                                
                                    inner join delivery_order t14
                                        on t14.id = t11.delivery_order_id
                                where t13.id = '.$rma_id.'
                                    and t14.delivery_order_status !="X"
                            ) t1
                            ,(
                                select sum(t21.qty) received_qty
                                from receive_product_product t21
                                    inner join rma_receive_product t22 
                                        on t21.receive_product_id = t22.receive_product_id
                                    inner join rma t23
                                        on t23.id = t22.rma_id       
                                    inner join receive_product t24
                                        on t24.id = t21.receive_product_id
                                where t23.id = '.$rma_id.'
                                    and t24.receive_product_status ="R"
                            )t2
                        ';
                        $rs = $db->query_array_obj($q);
                        $mismatch_qty = isset($rs[0]->mismatch_qty)?$rs[0]->mismatch_qty:0;
                        if(floatval($mismatch_qty)>0){
                            $result['success'] = 0;
                            $result['msg'][] = "Mismatch between delivered qty and received qty";
                            break;
                        }
                    }
                    
                    //check receive product status business logic
                    $status_business_logic_valid = true;
                    if($rma['rma_status'] !== $rs_rma->rma_status){
                        foreach(self::$rma_purchase_invoice_status_list as $status){
                            if($status['val'] === $rs_rma->rma_status){
                                if(isset($status['next_allowed_status'])){
                                    if(!in_array($rma['rma_status'],$status['next_allowed_status'])){
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
                    $db = new DB();
                    //check receive product exists
                    $rma_id = isset($rma['id'])?$rma['id']:'';
                    $q = '
                        select * 
                        from rma 
                        where id = '.$db->escape($rma['id']).'
                    ';
                    $rs_rma = $db->query_array_obj($q);
                    
                    if(count($rs_rma) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Receive Product data is not available";
                        break;
                    }
                    else{
                        $rs_rma = $db->query_array_obj($q)[0];
                    } 
                    
                    //check rma cancelled
                    if($rs_rma->rma_status === 'X'){
                        $result['success'] = 0;
                        $result['msg'][] = "Cannot update Canceled RMA";
                        break;
                    }
                    
                    // check active receive product or delivery product
                    $q = '
                        select count(1) total
                        from(
                            select 1 
                            from rma_receive_product t1 
                                inner join receive_product t2 on t1.receive_product_id = t2.id
                            where t2.receive_product_status != "X"
                                and t1.rma_id = '.$db->escape($rma_id).'
                            union all 
                            select 1 
                            from rma_delivery_order t3 
                                inner join delivery_order t4 on t3.delivery_order_id = t4.id
                            where t4.delivery_order_status != "X"
                                and t3.rma_id = '.$db->escape($rma_id).'
                        )tf
                    ';
                    $rs = $db->query_array_obj($q);
                    $total = isset($rs[0]->total)?$rs[0]->total:0;
                    if(floatval($total)>0){
                        $result['success'] = 0;
                        $result['msg'][] = 'Active Delivery Order or Receive Product';
                    }
                    
                    $rma['cancellation_reason'] = isset($rma['cancellation_reason'])?$rma['cancellation_reason']:'';
                    if(strlen(str_replace(' ','',$rma['cancellation_reason'])) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = 'Cancellation Reason is required';
                        
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
                    $rma = $data['rma'];
                    
                    $rma_product = $data['rma_product'];
                    $purchase_invoice = $data['purchase_invoice'];
                    $supplier = $data['supplier'];
                    
                    
                    $result['rma_supplier'] = array(
                        'supplier_id'=>$supplier['id']
                    );
                    $result['rma'] = array(
                        'code'=>''
                        ,'store_id'=>$rma['store_id']
                        
                        ,'rma_date'=>$rma['rma_date']
                        ,'rma_status'=>self::rma_purchase_invoice_status_default_status_get()['val']
                        ,'notes'=>$rma['notes']
                        ,'rma_type'=>'purchase_invoice'
                    );
                    $result['rma_product'] = array();
                    for($i = 0;$i<count($rma_product);$i++){
                        if(floatval($rma_product[$i]['qty'])>0){
                            $result['rma_product'][] = array(
                                'product_id'=>$rma_product[$i]['product_id']
                                ,'unit_id'=>$rma_product[$i]['unit_id']
                                ,'qty'=>$rma_product[$i]['qty']
                            );
                        }
                    }
                    $result['purchase_invoice_rma'] = array(
                        'purchase_invoice_id'=>$purchase_invoice['id']
                    );
                            
                    break;
                    
                case 'purchase_invoice_opened':
                case 'purchase_invoice_closed':
                    $rma = $data['rma'];                    
                    $result['rma'] = array(
                        'notes'=>isset($rma['notes'])?$rma['notes']:''
                        ,'rma_status'=>$rma['rma_status']
                    );
                    break;
                case 'purchase_invoice_canceled':
                    $rma = $data['rma'];

                    $result['rma'] = array(
                        'notes'=>isset($rma['notes'])?$rma['notes']:''
                        ,'cancellation_reason'=>isset($rma['cancellation_reason'])?$rma['cancellation_reason']:''
                        ,'rma_status'=>'X'
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
            $rma_data = $data['rma'];
            $id = $rma_data['id'];
            
            $method_list = array('purchase_invoice_add');
            foreach(self::$rma_purchase_invoice_status_list as $status){
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
                            $frma = array_merge($final_data['rma'],array("modid"=>$modid,"moddate"=>$moddate));
                            $rma_id = '';
                            $rs = $db->query_array_obj('select func_code_counter_store("rma",'.$db->escape($frma['store_id']).') "code"');
                            $frma['code'] = $rs[0]->code;
                            if(!$db->insert('rma',$frma)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            if($success == 1){
                                $q = '
                                    select id 
                                    from rma
                                    where status>0 
                                        and rma_status = '.$db->escape(self::rma_purchase_invoice_status_default_status_get()['val']).' 
                                        and code = '.$db->escape($frma['code']).'
                                ';
                                $rs_rma = $db->query_array_obj($q);
                                $rma_id = $rs_rma[0]->id;
                                $result['trans_id']=$rma_id; // useful for view forwarder
                            }
                            
                            if($success == 1){
                                $frma_supplier = $final_data['rma_supplier'];
                                $frma_supplier['rma_id'] = $rma_id;
                                if(!$db->insert('rma_supplier',$frma_supplier)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                            }
                            
                            if($success == 1){
                                $rma_status_log = array(
                                    'rma_id'=>$rma_id
                                    ,'rma_status'=>self::rma_purchase_invoice_status_default_status_get()['val']
                                    ,'modid'=>$modid
                                    ,'moddate'=>$moddate    
                                );
                                
                                if(!$db->insert('rma_status_log',$rma_status_log)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                            }
                            
                            if($success == 1){
                                $frma_product = $final_data['rma_product'];
                                for($i = 0;$i<count($frma_product);$i++){
                                    $frma_product[$i]['rma_id'] = $rma_id;
                                    if(!$db->insert('rma_product',$frma_product[$i])){
                                        $msg[] = $db->_error_message();
                                        $db->trans_rollback();                                
                                        $success = 0;
                                        break;
                                    }
                                }
                                
                            }
                            
                            if($success == 1){
                                $fpurchase_invoice_rma = $final_data['purchase_invoice_rma'];
                                $fpurchase_invoice_rma['rma_id'] = $rma_id;
                                if(!$db->insert('purchase_invoice_rma',$fpurchase_invoice_rma)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                            }
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Add Return Merchandise Authorization Success';
                            }
                        }
                        catch(Exception $e){
                            
                            $db->trans_rollback();
                            $msg[] = $e->getMessage();
                            $success = 0;
                        }
                        break;
                    case 'purchase_invoice_opened':
                    case 'purchase_invoice_closed':

                        try{
                            $db->trans_begin();
                            $frma = array_merge($final_data['rma'],array("modid"=>$modid,"moddate"=>$moddate));
                            $rma = array();
                            $rma_product = array();
                            $q = '
                                select t1.*,t3.code purchase_invoice_code 
                                from rma t1
                                    inner join purchase_invoice_rma t2 on t2.rma_id = t1.id
                                    inner join purchase_invoice t3 on t3.id = t2.purchase_invoice_id
                                where t1.id = '.$db->escape($id).'
                            ';
                            $rma = $db->query_array($q)[0];
                            
                            $q = '
                                select *
                                from rma_product
                                where rma_id = '.$db->escape($id).'

                            ';
                            $rma_product = $db->query_array($q);
                            
                            if(!$db->update('rma',$frma,array("id"=>$id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            if($success == 1){
                                $rma_status_log = array(
                                    'rma_id'=>$id
                                    ,'rma_status'=>$frma['rma_status']
                                    ,'modid'=>$modid
                                    ,'moddate'=>$moddate    
                                );
                                
                                if(!$db->insert('rma_status_log',$rma_status_log)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                            }
                            
                            $result['trans_id']=$id;
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Update RMA Success';
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
                            $rma = array();
                            $q = '
                                    select t1.*,t3.code purchase_invoice_code 
                                    from rma t1
                                        inner join purchase_invoice_rma t2 on t2.rma_id = t1.id
                                        inner join purchase_invoice t3 on t3.id = t2.purchase_invoice_id
                                    where t1.id = '.$db->escape($id).'
                                ';
                            $rma = $db->query_array($q)[0];
                            
                            $frma = array_merge($final_data['rma'],array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->update('rma',$frma,array("id"=>$id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            $result['trans_id']=$id;
                            if($success == 1){
                                $rma_status_log = array(
                                    'rma_id'=>$id
                                    ,'rma_status'=>$frma['rma_status']
                                    ,'modid'=>$modid
                                    ,'moddate'=>$moddate    
                                );
                                
                                if(!$db->insert('rma_status_log',$rma_status_log)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }                                
                            }
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Cancel RMA Success';
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
        
        public static function receive_product_max_qty($product_id, $unit_id, $purchase_invoice_id){
            $db = new DB();
            $result = 0;
            $q = '
                select tf1.received_qty - coalesce(tf2.rma_qty,0) max_qty
                from(
                    select sum(t4.qty) received_qty
                    from 
                        purchase_invoice_receive_product t2 
                        inner join receive_product t3 
                            on t3.id = t2.receive_product_id and t3.receive_product_status = "R"
                        inner join receive_product_product t4 
                            on t4.receive_product_id = t3.id

                    where t2.purchase_invoice_id = '.$db->escape($purchase_invoice_id).' 
                        and t4.product_id = '.$db->escape($product_id).'
                        and t4.unit_id = '.$db->escape($unit_id).'
                ) tf1
                ,(
                    select sum(t71.qty) rma_qty
                    from rma_product t71                        
                        inner join rma t73 on t71.rma_id = t73.id
                        inner join purchase_invoice_rma t72  on t73.id = t72.rma_id 
                    where t72.purchase_invoice_id = '.$db->escape($purchase_invoice_id).'
                        and t73.rma_status = "O"
                        and t71.product_id = '.$db->escape($product_id).'
                        and t71.unit_id = '.$db->escape($unit_id).'
                ) tf2 
            ';
            $rs = $db->query_array_obj($q);
            if(count($rs)>0) $result = $rs[0]->max_qty;
            return $result;
        }
        
    }
?>
