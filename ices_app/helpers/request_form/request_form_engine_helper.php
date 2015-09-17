<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Request_Form_Engine {
        
        private static $request_form_mutation_status_list = array(
            array(//label name is used for method name
                'val'=>'O'
                ,'label'=>'OPENED'
                ,'method'=>'mutation_opened'
                ,'default'=>true
                ,'next_allowed_status'=>array('C','X')
            )
            ,array(
                'val'=>'C'
                ,'label'=>'CLOSED'
                ,'method'=>'mutation_closed'
                ,'next_allowed_status'=>array('X')
                
            )
            ,array(
                'val'=>'X'
                ,'label'=>'CANCELED'
                ,'method'=>'mutation_canceled'
                ,'next_allowed_status'=>array()
            )
        );
        
        public static function request_form_mutation_status_list_get(){
            $result = array();
            $result = self::$request_form_mutation_status_list;
            return $result;
        }
        
        public static function request_form_mutation_status_get($product_status_val){
            $status_list = self::$request_form_mutation_status_list;
            $result = null;
            for($i = 0;$i<count($status_list);$i++){
                if($status_list[$i]['val'] === $product_status_val){
                    $result = $status_list[$i];
                }
            }
            return $result;
        }
        
        public static function request_form_mutation_status_next_allowed_status_get($curr_status_val){
            $result = array();
            $curr_status = null;
            for($i = 0;$i<count(self::$request_form_mutation_status_list);$i++){
                if(self::$request_form_mutation_status_list[$i]['val'] === $curr_status_val){
                    $curr_status = self::$request_form_mutation_status_list[$i];
                    break;
                }
            }
            
            for ($i = 0;$i<count($curr_status['next_allowed_status']);$i++){
                foreach(self::$request_form_mutation_status_list as $status){
                    if($status['val'] === $curr_status['next_allowed_status'][$i]){
                        $result[] = array('val'=>$status['val']
                                ,'label'=>$status['label']
                                ,'method'=>$status['method']);
                    }
                }
            }
            return $result;
        }
        
        public static function request_form_mutation_status_default_status_get(){
            $result = array();
            foreach(self::$request_form_mutation_status_list as $status){
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
        
        public static function request_form_exists($id){
            $result = false;
            $db = new DB();
            $q = '
                    select 1 
                    from request_form 
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
                'index'=>get_instance()->config->base_url().'request_form/'
                ,'request_form_engine'=>'request_form/request_form_engine'
                ,'request_form_renderer' => 'request_form/request_form_renderer'
                ,'ajax_search'=>get_instance()->config->base_url().'request_form/ajax_search/'
                
            );
            
            return json_decode(json_encode($path));
        }
        
        public static function mutation_submit($id,$method,$post){
            $post = json_decode($post,TRUE);
            $data = $post;
            $ajax_post = false;                  
            $result = null;
            $cont = true;

            if(isset($post['ajax_post'])) $ajax_post = $post['ajax_post'];
            if($method == 'add') $data['request_form']['id'] = '';
            else $data['request_form']['id'] = $id;
            
            if($cont){
                $result = self::mutation_save($method,$data);
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
        
        private static function mutation_validate($method,$data=array()){
            $result = array(
                "success"=>1
                ,"msg"=>array()
                
            );
            $request_form = isset($data['request_form'])?
                    $data['request_form']:null;
            $request_form_mutation_warehouse_to = isset($data['request_form_mutation_warehouse_to'])?
                    $data['request_form_mutation_warehouse_to']:null;
            $request_form_mutation_warehouse_from = isset($data['request_form_mutation_warehouse_from'])?
                    $data['request_form_mutation_warehouse_from']:null;
            $request_form_mutation_product = isset($data['request_form_mutation_product'])? 
                    $data['request_form_mutation_product']: null;
            switch($method){
                case 'mutation_add':                   
                    $db = new DB();

                    //check warehouse from is available
                    $warehouse_id = isset($request_form_mutation_warehouse_from['warehouse_id'])?
                            $request_form_mutation_warehouse_from['warehouse_id']:'';
                    $q = 'select 1 from warehouse where status>0 and id = '.$db->escape($warehouse_id).'';
                    if(count($db->query_array_obj($q)) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Invalid Warehouse From";
                    }

                    //check warehouse to is available
                    $warehouse_id = isset($request_form_mutation_warehouse_to['warehouse_id'])?
                            $request_form_mutation_warehouse_to['warehouse_id']:'';
                    $q = 'select 1 from warehouse where status>0 and id = '.$db->escape($warehouse_id).'';
                    if(count($db->query_array_obj($q)) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Invalid Warehouse To";
                    }
                    
                    if($result['success'] == 1){
                        if($request_form_mutation_warehouse_from['warehouse_id'] === 
                            $request_form_mutation_warehouse_to['warehouse_id']
                        ){
                            $result['success'] = 0;
                            $result['msg'][] = "Similar Warehouse from and to";
                        }
                    }
                    
                    //check product exists
                    if(count($request_form_mutation_product) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = 'Product does not exist ';
                    }
                    
                    //check product is available
                    for($i = 0;$i<count($request_form_mutation_product);$i++){
                        $product_id = isset($request_form_mutation_product[$i]['product_id'])?
                                $request_form_mutation_product[$i]['product_id']:'';
                        $unit_id = isset($request_form_mutation_product[$i]['unit_id'])?
                                $request_form_mutation_product[$i]['unit_id']:'';
                        $qty = isset($request_form_mutation_product[$i]['qty'])?
                                $request_form_mutation_product[$i]['qty']:'0';
                        
                        $q = '
                            select 1 
                            from product_unit
                                inner join product on product_unit.product_id = product.id 
                            where product_id = '.$db->escape($product_id).'
                                and unit_id='.$db->escape($unit_id).'
                                and product.status>0
                        ';
                        $rs = $db->query_array_obj($q);
                        if(count($rs) === 0){
                            $result['success'] = 0;
                            $result['msg'][] = "Invalid Product or Unit";
                            break;
                        }
                        
                        if(floatval($qty) === floatval('0')){
                            $result['success'] = 0;
                            $result['msg'][] = "Product Qty cannot be 0";
                            break;
                        }
                    }
                    
                    //check request_form date
                    $request_form_date = isset($request_form['request_form_date'])?$request_form['request_form_date']:'';
                    if(strlen($request_form_date) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Request Form Date cannot be empty";

                    }
                    break;
                case 'mutation_opened':
                case 'mutation_closed':
                    
                    $db = new DB();
                    //check receive product exists
                    $request_form_id = isset($request_form['id'])?$request_form['id']:'';
                    $q = '
                        select * 
                        from request_form 
                        where id = '.$db->escape($request_form['id']).'
                    ';
                    $rs_request_form = $db->query_array_obj($q);
                    
                    if(count($rs_request_form) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Receive Product data is not available";
                        break;
                    }
                    else{
                        $rs_request_form = $db->query_array_obj($q)[0];
                    }                    
                    
                    //check receive product is cancelled
                    if($rs_request_form->request_form_status === 'X'){
                        $result['success'] = 0;
                        $result['msg'][] = "Cannot update Canceled request_form";
                        break;
                    }
                    
                    //check if receive product status available
                    if(!isset($request_form['request_form_status'])){
                        $result['success'] = 0;
                        $result['msg'][] = "Receive Form Status is not available";
                        break;
                    }
                    
                    //check receive product status is in list
                    $status_exists_in_list = false;
                    foreach (self::$request_form_mutation_status_list as $status){
                        if($status['val'] === $request_form['request_form_status'])
                            $status_exists_in_list = true;
                    }
                    if(!$status_exists_in_list){
                        $result['success'] = 0;
                        $result['msg'][] = "Invalid Receive Product Status";
                        break;
                    }
                    
                    //check receive product status business logic
                    $status_business_logic_valid = true;
                    if($request_form['request_form_status'] !== $rs_request_form->request_form_status){
                        foreach(self::$request_form_mutation_status_list as $status){
                            if($status['val'] === $rs_request_form->request_form_status){
                                if(isset($status['next_allowed_status'])){
                                    if(!in_array($request_form['request_form_status'],$status['next_allowed_status'])){
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
                case 'mutation_canceled':
                    $db = new DB();
                    //check receive product exists
                    $request_form_id = isset($request_form['id'])?$request_form['id']:'';
                    $q = '
                        select * 
                        from request_form 
                        where id = '.$db->escape($request_form['id']).'
                    ';
                    $rs_request_form = $db->query_array_obj($q);
                    
                    if(count($rs_request_form) === 0){
                        $result['success'] = 0;
                        $result['msg'][] = "Receive Product data is not available";
                        break;
                    }
                    else{
                        $rs_request_form = $db->query_array_obj($q)[0];
                    } 
                    
                    //check receive product is cancelled
                    if($rs_request_form->request_form_status === 'X'){
                        $result['success'] = 0;
                        $result['msg'][] = "Cannot update Canceled request_form";
                        break;
                    }
                    
                    
                    $request_form['cancellation_reason'] = isset($request_form['cancellation_reason'])?$request_form['cancellation_reason']:'';
                    if(strlen(str_replace(' ','',$request_form['cancellation_reason'])) == 0){
                        $result['success'] = 0;
                        $result['msg'][] = 'Cancellation Reason is required';
                        break;
                    }
                    
                    break;
                    
               
            }
            
            return $result;
        }
        
        private static function mutation_adjust($action,$data=array()){
            $db = new DB();
            $result = array();
            
            switch($action){
                case 'mutation_add':
                    $request_form = $data['request_form'];                    
                    $request_form_mutation_product = $data['request_form_mutation_product'];
                    $request_form_mutation_warehouse_from = $data['request_form_mutation_warehouse_from'];
                    $request_form_mutation_warehouse_to = $data['request_form_mutation_warehouse_to'];

                    $rs = $db->query_array_obj('select func_code_counter("request_form_mutation") "code"');
                    $result['request_form'] = array(
                        'code'=>$rs[0]->code
                        ,'request_form_type_id'=>1
                        ,'request_form_date'=>$request_form['request_form_date']
                        ,'request_form_status'=>self::request_form_mutation_status_default_status_get()['val']
                        ,'notes'=>isset($request_form['notes'])?$request_form['notes']:''
                        ,'requester_id'=>User_Info::get()['user_id']
                    );
                    $result['request_form_mutation_product'] = array();
                    for($i = 0;$i<count($request_form_mutation_product);$i++){
                        if(floatval($request_form_mutation_product[$i]['qty'])>0){
                            $result['request_form_mutation_product'][] = array(
                                'product_id'=>$request_form_mutation_product[$i]['product_id']
                                ,'unit_id'=>$request_form_mutation_product[$i]['unit_id']
                                ,'qty'=>$request_form_mutation_product[$i]['qty']
                            );
                        }
                    }
                    
                    $result['request_form_mutation_warehouse_to'] = array();
                    $result['request_form_mutation_warehouse_to']['warehouse_id'] = $request_form_mutation_warehouse_from['warehouse_id'];
                        
                    $result['request_form_mutation_warehouse_from'] = array();
                    $result['request_form_mutation_warehouse_from']['warehouse_id'] = $request_form_mutation_warehouse_to['warehouse_id'];
                            
                    break;
                    
                case 'mutation_opened':
                case 'mutation_closed':
                    $request_form = $data['request_form'];                    
                    $result['request_form'] = array(
                        'notes'=>isset($request_form['notes'])?$request_form['notes']:''
                        ,'request_form_status'=>$request_form['request_form_status']
                    );
                    break;
                case 'mutation_canceled':
                    $request_form = $data['request_form'];

                    $result['request_form'] = array(
                        'notes'=>isset($request_form['notes'])?$request_form['notes']:''
                        ,'cancellation_reason'=>isset($request_form['cancellation_reason'])?$request_form['cancellation_reason']:''
                        ,'request_form_status'=>'X'
                    );
                            
                    break;
            }
            
            return $result;
        }
        
        public static function mutation_send_message($action, $id){
            $db = new DB();
            $request_form = $db->query_array_obj('select * from request_form where id = '.$db->escape($id).'')[0];
            $q = '
                select distinct t4.id from_manager_id
                    , t7.id to_manager_id
                    , t1.requester_id
                    , t1.modid
                from request_form t1
                    inner join request_form_mutation_warehouse_from t2 on t1.id = t2.request_form_id
                    inner join warehouse t3 on t3.id = t2.warehouse_id
                    inner join user_login t4 on t4.id = t3.warehouse_manager_id
                    inner join request_form_mutation_warehouse_to t5 on t1.id = t5.request_form_id
                    inner join warehouse t6 on t6.id = t5.warehouse_id
                    inner join user_login t7 on t7.id = t6.warehouse_manager_id

                where t1.id = '.$id.'
            ';
            $rs = $db->query_array_obj($q)[0];
            $user_login_list = array();
            
            if(!in_array($rs->from_manager_id,$user_login_list))$user_login_list[]=$rs->from_manager_id;
            if(!in_array($rs->to_manager_id,$user_login_list))$user_login_list[]=$rs->to_manager_id;
            if(!in_array($rs->modid,$user_login_list))$user_login_list[]=$rs->modid;
            if(!in_array($rs->requester_id,$user_login_list))$user_login_list[]=$rs->requester_id;
            
            switch($action){
                case 'mutation_add': 
                    $action ='created';
                    break;
                case 'mutation_opened': 
                case 'mutation_closed':
                    $action ='updated';
                    break;
                case 'mutation_canceled':
                    $action = 'canceled';
                default:
                    $action = str_replace('mutation_','',$action);
                    break;
            }
            
            $mail_data = array(
                'msg_header'=>'Request Form Notification '.$request_form->code
                ,'msg_body'=>'<a href="'.self::path_get()->index.'view/'.$id.'">
                    '.'Request Form Mutation '.$request_form->code.'</a> has been '.$action.' 
                    '
            );
            
            get_instance()->load->helper('app_message/app_message_engine');
            $result = array();
            foreach($user_login_list as $user_login_id){
                $inbox_data = array(
                    'user_login_id'=>$user_login_id
                    ,'sender_id'=>8 //this is system message
                    ,'msg_header'=>$mail_data['msg_header']
                    ,'msg_body'=>$mail_data['msg_body']
                );
                $result[] = App_Message_Engine::send_message($inbox_data);                
            }
            return $result;
        }
        
        public static function mutation_save($method,$data){
            $db = new DB();
            $success = 1;
            $msg = array();
            $action = $method;
            $result = array("success"=>0,"msg"=>array(),'trans_id'=>'');
            $request_form_data = $data['request_form'];
            $id = $request_form_data['id'];
            
            $method_list = array('mutation_add');
            foreach(self::$request_form_mutation_status_list as $status){
                $method_list[] = strtolower($status['method']);
            }
            
            if(in_array($action,$method_list)){
                $validation_res = self::mutation_validate($action,$data);
                $success = $validation_res['success']; 
                $msg = $validation_res['msg'];
            }
            else{
                $success = 0;
                $msg[] = 'Unknown method';
            }

            if($success == 1){
                $final_data = self::mutation_adjust($action,$data);
                $modid = User_Info::get()['user_id'];
                $moddate = date("Y-m-d H:i:s");
                
                switch($action){                    
                    case 'mutation_add':
                        try{ 
                            $db->trans_begin();
                            $frequest_form = array_merge($final_data['request_form'],array("modid"=>$modid,"moddate"=>$moddate));
                            $request_form_id = '';
                            if(!$db->insert('request_form',$frequest_form)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            if($success == 1){
                                $q = '
                                    select id 
                                    from request_form
                                    where status>0 
                                        and request_form_status = '.$db->escape(self::request_form_mutation_status_default_status_get()['val']).' 
                                        and code = '.$db->escape($frequest_form['code']).'
                                ';
                                $rs_request_form = $db->query_array_obj($q);
                                $request_form_id = $rs_request_form[0]->id;
                                $result['trans_id']=$request_form_id; // useful for view forwarder
                            }
                            
                            if($success == 1){
                                $request_form_status_log = array(
                                    'request_form_id'=>$request_form_id
                                    ,'request_form_status'=>self::request_form_mutation_status_default_status_get()['val']
                                    ,'modid'=>$modid
                                    ,'moddate'=>$moddate    
                                );
                                
                                if(!$db->insert('request_form_status_log',$request_form_status_log)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                            }
                            
                            if($success == 1){
                                $frequest_form_mutation_product = $final_data['request_form_mutation_product'];
                                for($i = 0;$i<count($frequest_form_mutation_product);$i++){
                                    $frequest_form_mutation_product[$i]['request_form_id'] = $request_form_id;
                                    if(!$db->insert('request_form_mutation_product',$frequest_form_mutation_product[$i])){
                                        $msg[] = $db->_error_message();
                                        $db->trans_rollback();                                
                                        $success = 0;
                                        break;
                                    }
                                }
                                
                            }
                            
                            if($success == 1){
                                $fwarehouse_from = $final_data['request_form_mutation_warehouse_from'];
                                $fwarehouse_from['request_form_id'] = $request_form_id;
                                if(!$db->insert('request_form_mutation_warehouse_from',$fwarehouse_from)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                            }
                            
                            if($success == 1){
                                $fwarehouse_to = $final_data['request_form_mutation_warehouse_to'];
                                $fwarehouse_to['request_form_id'] = $request_form_id;
                                if(!$db->insert('request_form_mutation_warehouse_to',$fwarehouse_to)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                            }
                            
                            if($success === 1){
                                $send_mail_result = self::mutation_send_message($action,$request_form_id);
                                
                                for($i = 0;$i<count($send_mail_result);$i++){
                                    foreach($send_mail_result[$i]['msg'] as $send_mail_msg){
                                        $msg[] = $send_mail_msg;
                                    }
                                }
                            }
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Add Request Form Success';
                            }
                        }
                        catch(Exception $e){
                            
                            $db->trans_rollback();
                            $msg[] = $e->getMessage();
                            $success = 0;
                        }
                        break;
                    case 'mutation_opened':
                    case 'mutation_closed':
                        try{
                            $db->trans_begin();
                            $frequest_form = array_merge($final_data['request_form'],array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->update('request_form',$frequest_form,array("id"=>$id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            if($success == 1){
                                $request_form_status_log = array(
                                    'request_form_id'=>$id
                                    ,'request_form_status'=>$frequest_form['request_form_status']
                                    ,'modid'=>$modid
                                    ,'moddate'=>$moddate    
                                );
                                
                                if(!$db->insert('request_form_status_log',$request_form_status_log)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                            }
                            
                            if($success === 1){
                                $send_mail_result = self::mutation_send_message($action,$id);
                                
                                for($i = 0;$i<count($send_mail_result);$i++){
                                    foreach($send_mail_result[$i]['msg'] as $send_mail_msg){
                                        $msg[] = $send_mail_msg;
                                    }
                                }
                            }
                            
                            $result['trans_id']=$id;
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Update Request Form Mutation Success';
                            }
                        }
                        catch(Exception $e){
                            $db->trans_rollback();
                            $msg[] = $e->getMessage();
                            $success = 0;
                        }                        
                        
                        break;
                    case 'mutation_canceled':
                        try{
                            $db->trans_begin();
                            $request_form = array();
                            $q = '
                                    select t1.*,t3.code purchase_invoice_code 
                                    from request_form t1
                                        inner join purchase_invoice_request_form t2 on t2.request_form_id = t1.id
                                        inner join purchase_invoice t3 on t3.id = t2.purchase_invoice_id
                                    where t1.id = '.$db->escape($id).'
                                ';
                            $request_form = $db->query_array($q)[0];
                            
                            $frequest_form = array_merge($final_data['request_form'],array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->update('request_form',$frequest_form,array("id"=>$id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            $result['trans_id']=$id;
                            if($success == 1){
                                $request_form_status_log = array(
                                    'request_form_id'=>$id
                                    ,'request_form_status'=>$frequest_form['request_form_status']
                                    ,'modid'=>$modid
                                    ,'moddate'=>$moddate    
                                );
                                
                                if(!$db->insert('request_form_status_log',$request_form_status_log)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }                                
                            }
                            
                            if($success === 1){
                                $send_mail_result = self::mutation_send_message($action,$id);
                                
                                for($i = 0;$i<count($send_mail_result);$i++){
                                    foreach($send_mail_result[$i]['msg'] as $send_mail_msg){
                                        $msg[] = $send_mail_msg;
                                    }
                                }
                            }
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Cancel Request Form Mutation Success';
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
