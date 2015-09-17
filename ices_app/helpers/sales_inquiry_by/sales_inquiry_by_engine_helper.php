<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Sales_Inquiry_By_Engine {
        
        public static function sales_inquiry_by_exists($id=""){
            $result = false;
            $db = new DB();
            $q = '
                    select 1 
                    from sales_inquiry_by 
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
                'index'=>get_instance()->config->base_url().'sales_inquiry_by/'
                ,'sales_inquiry_by_engine'=>'sales_inquiry_by/sales_inquiry_by_engine'
                ,'sales_inquiry_by_renderer' => 'sales_inquiry_by/sales_inquiry_by_renderer'
                ,'ajax_search'=>get_instance()->config->base_url().'sales_inquiry_by/ajax_search/'
                ,'data_support'=>get_instance()->config->base_url().'sales_inquiry_by/data_support/'
                
            );
            
            return json_decode(json_encode($path));
        }
        
        private static $sales_inquiry_by_status_list = array(
            array(//label name is used for method name
                'val'=>'A'
                ,'label'=>'ACTIVE'
                ,'method'=>'active'
                ,'default'=>true
                ,'next_allowed_status'=>array('I')
            )
            ,array(
                'val'=>'I'
                ,'label'=>'INACTIVE'
                ,'method'=>'inactive'
                ,'next_allowed_status'=>array('A')
                
            )            
            
        );
        
        public static function sales_inquiry_by_status_list_get(){
            $result = array();
            $result = self::$sales_inquiry_by_status_list;
            return $result;
        }
        
        public static function sales_inquiry_by_status_get($product_status_val){
            $status_list = self::$sales_inquiry_by_status_list;
            $result = null;
            for($i = 0;$i<count($status_list);$i++){
                if($status_list[$i]['val'] === $product_status_val){
                    $result = $status_list[$i];
                }
            }
            return $result;
        }
        
        public static function sales_inquiry_by_status_next_allowed_status_get($curr_status_val){
            $result = array();
            $curr_status = null;
            for($i = 0;$i<count(self::$sales_inquiry_by_status_list);$i++){
                if(self::$sales_inquiry_by_status_list[$i]['val'] === $curr_status_val){
                    $curr_status = self::$sales_inquiry_by_status_list[$i];
                    break;
                }
            }
            
            for ($i = 0;$i<count($curr_status['next_allowed_status']);$i++){
                foreach(self::$sales_inquiry_by_status_list as $status){
                    if($status['val'] === $curr_status['next_allowed_status'][$i]){
                        $result[] = array('val'=>$status['val']
                                ,'label'=>$status['label']
                                ,'method'=>$status['method']);
                    }
                }
            }
            return $result;
        }
        
        public static function sales_inquiry_by_status_default_status_get(){
            $result = array();
            foreach(self::$sales_inquiry_by_status_list as $status){
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
        
        public static function sales_inquiry_by_submit($id,$method,$post){
            $post = json_decode($post,TRUE);
            $data = $post;
            $ajax_post = false;                  
            $result = null;
            $cont = true;

            if(isset($post['ajax_post'])) $ajax_post = $post['ajax_post'];
            if($method == 'add') $data['sales_inquiry_by']['id'] = '';
            else $data['sales_inquiry_by']['id'] = $id;
            
            if($cont){
                $result = self::sales_inquiry_by_save($method,$data);
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
        
        public static function sales_inquiry_by_get($id){
            $db = new DB();
            $result = null;
            $q = '
                select *
                , case sales_inquiry_by_status when "A" then "ACTIVE"
                    when "I" then "INACTIVE" end sales_inquiry_by_status_name
                from sales_inquiry_by
                where id = '.$db->escape($id).'
            ';
            $rs = $db->query_array_obj($q);
            if(count($rs)>0){
                $result = $rs[0];
            }
            return $result;
        }
        
        private static function sales_inquiry_by_validate($action,$data=array()){
            $result = array(
                "success"=>1
                ,"msg"=>array()
            );
            
            switch($action){
                case 'add':
                case 'active':
                case 'inactive':
                    $sales_inquiry_by = isset($data['sales_inquiry_by'])?$data['sales_inquiry_by']:null;
                    $db = new DB();
                    $sales_inquiry_by_id = $data['sales_inquiry_by']['id'];

                    $sales_inquiry_by_code = isset($data['sales_inquiry_by']['code'])?$data['sales_inquiry_by']['code']:'';
                    $sales_inquiry_by_code = str_replace(' ','',$sales_inquiry_by_code);

                    if(strlen($sales_inquiry_by_code)==0){
                        $result['success'] = 0;
                        $result['msg'][] = "Code cannot be empty";
                    }
                    
                    $sales_inquiry_by_name = isset($data['sales_inquiry_by']['name'])?$data['sales_inquiry_by']['name']:'';
                    $sales_inquiry_by_name = str_replace(' ','',$sales_inquiry_by_name);

                    if(strlen($sales_inquiry_by_name)==0){
                        $result['success'] = 0;
                        $result['msg'][] = "Name cannot be empty";
                    }

                    if(in_array($action,array('active','inactive'))){
                        $sales_inquiry_by_id = isset($sales_inquiry_by['id'])?$sales_inquiry_by['id']:'';
                        
                        $q = '
                            select * 
                            from sales_inquiry_by 
                            where id = '.$db->escape($sales_inquiry_by['id']).'
                        ';
                        $rs_sales_inquiry_by = $db->query_array_obj($q);

                        if(count($rs_sales_inquiry_by) === 0){
                            $result['success'] = 0;
                            $result['msg'][] = "Sales Inquiry By data is not available";
                            break;
                        }
                        else{
                            $rs_sales_inquiry_by = $db->query_array_obj($q)[0];
                        }
                        
                        //check receive product status is in list
                        $status_exists_in_list = false;
                        foreach (self::$sales_inquiry_by_status_list as $status){
                            if($status['val'] === $sales_inquiry_by['sales_inquiry_by_status'])
                                $status_exists_in_list = true;
                        }
                        if(!$status_exists_in_list){
                            $result['success'] = 0;
                            $result['msg'][] = "Invalid Sales Inquiry By Status";
                            break;
                        }

                        //check receive product status business logic
                        $status_business_logic_valid = true;
                        if($sales_inquiry_by['sales_inquiry_by_status'] !== $rs_sales_inquiry_by->sales_inquiry_by_status){
                            foreach(self::$sales_inquiry_by_status_list as $status){
                                if($status['val'] === $rs_sales_inquiry_by->sales_inquiry_by_status){
                                    if(isset($status['next_allowed_status'])){
                                        if(!in_array($sales_inquiry_by['sales_inquiry_by_status'],$status['next_allowed_status'])){
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
                            $result['msg'][] = "Invalid Sales Inquiry By Status business logic";
                            break;
                        }
                    }
                    
                    break;
            }
            
            
            return $result;
        }
        
        private static function sales_inquiry_by_adjust($method, $data=array()){
            $db = new DB();
            $result = array();
            
            $sales_inquiry_by = isset($data['sales_inquiry_by'])?$data['sales_inquiry_by']:null;

            switch($method){
                case 'add':
                    
                    $result['sales_inquiry_by'] = array(
                        'code'=> $sales_inquiry_by['code'],
                        'name' => $sales_inquiry_by['name'],
                        'notes' => isset($sales_inquiry_by['notes'])?$sales_inquiry_by['notes']:'',
                        'sales_inquiry_by_status'=>self::sales_inquiry_by_status_default_status_get()['val'],
                        
                    );
                    break;
                case 'active':
                case 'inactive':
                    $result['sales_inquiry_by'] = array(
                        'code'=> $sales_inquiry_by['code'],
                        'name' => $sales_inquiry_by['name'],
                        'notes' => isset($sales_inquiry_by['notes'])?$sales_inquiry_by['notes']:'',
                        'sales_inquiry_by_status'=>isset($sales_inquiry_by['sales_inquiry_by_status'])?
                            $sales_inquiry_by['sales_inquiry_by_status']:'',
                    );  
                    break;
            }        
            
            return $result;
        }
        
        public static function sales_inquiry_by_save($method, $data){
            $db = new DB();
            $success = 1;
            $msg = array();
            $action = $method;
            $id = $data['sales_inquiry_by']['id'];
            
            $method_list = array('add');
            foreach(self::$sales_inquiry_by_status_list as $status){
                $method_list[] = strtolower($status['method']);
            }
            
            if(in_array($action,$method_list)){
                $validation_res = self::sales_inquiry_by_validate($action,$data);
                $success = $validation_res['success']; 
                $msg = $validation_res['msg'];
            }
            else{
                $success = 0;
                $msg[] = 'Unknown method';
            }
            
            if($success == 1){
                $final_data = self::sales_inquiry_by_adjust($action,$data);
                $modid = User_Info::get()['user_id'];
                $moddate = date("Y-m-d H:i:s");
                switch($action){                    
                    case 'add':
                        try{
                            $fsales_inquiry_by = $final_data['sales_inquiry_by'];
                            $sales_inquiry_by_id = '';
                            $db->trans_begin();
                            
                            $fsales_inquiry_by = array_merge($fsales_inquiry_by,array("modid"=>$modid,"moddate"=>$moddate));
                            if(!$db->insert('sales_inquiry_by',$fsales_inquiry_by)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            if($success){
                                $result['trans_id'] = SI::get_trans_id($db,'sales_inquiry_by','code',$fsales_inquiry_by['code']);
                                if($result['trans_id'] === null){
                                    $msg[] = 'Unable to get trans id';
                                    $db->trans_rollback();                                
                                    $success = 0;
                                }
                                $sales_inquiry_by_id = $result['trans_id'];
                            }
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Add Sales Inquiry By Success';
                            }
                            
                            
                        }
                        catch(Exception $e){
                            $db->trans_rollback();
                            $msg[] = $e->getMessage();
                            $success = 0;
                        }
                        
                        break;
                    case 'active':
                    case 'inactive':
                        try{
                            $db->trans_begin();

                            $fsales_inquiry_by = $final_data['sales_inquiry_by'];
                            $sales_inquiry_by_id = $id;
                            if(!$db->update('sales_inquiry_by',$fsales_inquiry_by,array("id"=>$sales_inquiry_by_id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }                            
                            $result['trans_id']=$id;
                            
                            
                            if($success == 1){
                                $db->trans_commit();
                                $msg[] = 'Update Sales Inquiry By Success';
                            }
                            
                            
                        }
                        catch(Exception $e){
                            $db->trans_rollback();
                            $msg[] = $e->getMessage();
                            $success = 0;
                        }
                        
                        
                        break;
                    case 'delete':
                        break;
                }
            }
            if($success == 1){
                Message::set('success',$msg);
            }
            else{
                Message::set('error',$msg);
            }
            
            $result['success'] = $success;
            $result['msg'] = $msg;
            
            return $result;
            
        }
        
        
        
        
        

        
    }
?>
