<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Delivery_MOP_Mixed_Engine {
        
        public static function path_get(){
            $path = array(
                'index'=>get_instance()->config->base_url().'product_price_list/'
                ,'product_price_list_delivery_mop_engine' => 'product_price_list_delivery_mo/product_price_list_delivery_mop_engine'
                ,'ajax_search'=>get_instance()->config->base_url().'product_price_list/ajax_search/'
                ,'data_support'=>get_instance()->config->base_url().'product_price_list/data_support/'
                
            );
            
            return json_decode(json_encode($path));
        }
        
        
        public static function submit($id,$method,$post){
            $post = json_decode($post,TRUE);
            $data = $post;
            $ajax_post = false;                  
            $result = null;
            $cont = true;
            
            if(isset($post['ajax_post'])) $ajax_post = $post['ajax_post'];
            if($method == 'add') $data['delivery_mop']['id'] = '';
            else $data['delivery_mop']['id'] = $id;
            
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
            get_instance()->load->helper('product/product_data_support');
            $result = array(
                "success"=>1
                ,"msg"=>array()
                
            );
            $delivery_mop = isset($data['delivery_mop'])?$data['delivery_mop']:null;
            $delivery_mop_mixed = isset($data['delivery_mop_mixed'])?$data['delivery_mop_mixed']:null;
            $delivery_mop_product = isset($data['delivery_mop_mixed_product'])? $data['delivery_mop_mixed_product']: null;

            $db = new DB();
            
            $id = isset($delivery_mop['id'])?$delivery_mop['id']:'';
            
            if($method !== 'add'){
                if(!SI::record_exists(
                        'product_price_list_delivery_mop'
                        ,array('id'=>$id,'calculation_type'=>'mixed','status'=>'1'))
                ){
                    $result['success'] = 0;
                    $result['msg'][] = "Data does not exists";
                }
            }
            
            $price_list_id = isset($delivery_mop['product_price_list_id'])?$delivery_mop['product_price_list_id']:'';
            if(strlen($price_list_id) === 0){
                $result['success'] = 0;
                $result['msg'][] = "Product Price List cannot be empty";
            }
            
            $code = isset($delivery_mop['code'])?$delivery_mop['code']:'';
            if(strlen($code) === 0){
                $result['success'] = 0;
                $result['msg'][] = "Code cannot be empty";
            }
            
            if(SI::duplicate_value('product_price_list_delivery_mop',$id,'code',$code)){
                $result['success'] = 0;
                $result['msg'][] = "Code already exists";
            
            }
            
            $amount = isset($delivery_mop_mixed['amount'])?$delivery_mop_mixed['amount']:'';
            if(floatval($amount) <= 0){
                $result['success'] = 0;
                $result['msg'][] = "Amount must be higher than 0";
            }
            
            if(count($delivery_mop_product) === 0){
                $result['success'] = 0;
                $result['msg'][] = "Product is empty";
            }
            
            if($result['success'] === 1){
                foreach($delivery_mop_product as $idx=>$row){
                    $product_id = isset($row['product_id'])?Tools::_str($row['product_id']):'';
                    $q = '
                        select 1
                        from product_price_list_delivery_mop ppldm
                        inner join product_price_list_delivery_mop_separated ppldms 
                            on ppldm.id = ppldms.product_price_list_delivery_mop_id
                        where ppldm.status > 0
                            and ppldm.product_price_list_id = '.$db->escape($price_list_id).'
                            and ppldm.id != '.$db->escape($id).'
                            and ppldms.product_id = '.$db->escape($product_id).'
                        
                        union 
                        
                        select 1
                        from product_price_list_delivery_mop ppldm
                        inner join product_price_list_delivery_mop_mixed ppldmm 
                            on ppldm.id = ppldmm.product_price_list_delivery_mop_id
                        inner join product_price_list_delivery_mop_mixed_product ppldmmp 
                            on ppldmm.id = ppldmmp.product_price_list_delivery_mop_mixed_id
                        where ppldm.status > 0
                            and ppldm.product_price_list_id = '.$db->escape($price_list_id).'
                            and ppldm.id != '.$db->escape($id).'
                            and ppldmmp.product_id = '.$db->escape($product_id).'
                        ;

                    ';
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        $product_data = Product_Data_Support::product_get($product_id);
                        $result['success'] = 0;
                        $result['msg'][] = SI::html_tag('strong',$product_data['code']).' '.$product_data['name'].' exists in Delivery Min. Order Price';
                        break;
                    }
                }
                
            }
            
            return $result;
        }
        
        public static function adjust($action,$data=array()){
            $db = new DB();
            $result = array();
            $delivery_mop = $data['delivery_mop'];
            $delivery_mop_mixed = $data['delivery_mop_mixed'];
            $delivery_mop_mixed_product = $data['delivery_mop_mixed_product'];
            
            $result['delivery_mop'] = array(
                'calculation_type'=>'mixed'
                ,'product_price_list_id'=>$delivery_mop['product_price_list_id']
                ,'code'=>$delivery_mop['code']
            );
            
            $result['delivery_mop_mixed']=array(
                'amount'=>isset($delivery_mop_mixed['amount'])?$delivery_mop_mixed['amount']:'0'
            );
            
            $result['delivery_mop_mixed_product'] = array();
            for($i = 0;$i<count($delivery_mop_mixed_product);$i++){
                $result['delivery_mop_mixed_product'][] = array(
                    'product_id'=>$delivery_mop_mixed_product[$i]['product_id']                    
                );
                
            }
            
            return $result;
        }
        
        public static function save($method,$data){
            $db = new DB();
            $success = 1;
            $msg = array();
            $action = $method;
            $result = array("success"=>0,"msg"=>array(),'trans_id'=>'');
            $delivery_mop = $data['delivery_mop'];
            $id = $delivery_mop['id'];
            
            $method_list = array('add','update');
            
            
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
                
                try{ 
                    $db->trans_begin();
                    $fdelivery_mop = $final_data['delivery_mop'];
                    $fdelivery_mop_mixed = $final_data['delivery_mop_mixed'];
                    $fdelivery_mop_mixed_product = $final_data['delivery_mop_mixed_product'];
                    $delivery_mop_id = '';
                    
                    switch($action){
                        case 'add':
                            if(!$db->insert('product_price_list_delivery_mop',$fdelivery_mop)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            if($success == 1){
                                $delivery_mop_id = SI::get_trans_id(
                                    $db
                                    ,'product_price_list_delivery_mop'
                                    ,'code'
                                    ,$fdelivery_mop['code']
                                );
                            }

                            
                            break;
                        case 'update':    
                            if(!$db->update('product_price_list_delivery_mop',$fdelivery_mop,array('id'=>$id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            $delivery_mop_id = $id;
                            
                            
                            break;
                    }

                    if($success == 1){
                        $result['trans_id']=$delivery_mop_id; // useful for view forwarder
                    }
                    
                    if($success == 1){
                        $price_list_id = $fdelivery_mop['product_price_list_id'];
                        $rs = $db->query_array('select product_price_list_status from product_price_list where id ='.$db->escape($price_list_id));
                        $price_list_status = $rs[0]['product_price_list_status'];
                        $product_price_list_status_log = array(
                            'product_price_list_id'=>$price_list_id
                            ,'product_price_list_status'=>$price_list_status
                            ,'modid'=>$modid
                            ,'moddate'=>$moddate    
                        );

                        if(!$db->insert('product_price_list_status_log',$product_price_list_status_log)){
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();                                
                            $success = 0;
                        }
                    }
                    
                    $delivery_mop_mixed_id = '';
                    
                    if($success == 1){
                        $q = '
                            select id
                            from product_price_list_delivery_mop_mixed
                            where product_price_list_delivery_mop_id = '.$db->escape($delivery_mop_id).' 
                        ';
                        $rs  = $db->query_array($q);
                        if(count($rs)>0){
                            $delivery_mop_mixed_id = $rs[0]['id'];
                        }
                    }
                    // clean up mixed records
                    if ($success == 1){
                        $q = '
                            delete from product_price_list_delivery_mop_mixed
                            where product_price_list_delivery_mop_id = '.$db->escape($delivery_mop_id).'
                        ';
                        if(!$db->query($q)){
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();                                
                            $success = 0;
                        }
                    }
                    
                    if ($success == 1){
                        $q = '
                            delete from product_price_list_delivery_mop_mixed_product
                            where product_price_list_delivery_mop_mixed_id = '.$db->escape($delivery_mop_mixed_id).'
                        ';
                        
                        if(!$db->query($q)){
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();                                
                            $success = 0;
                        }
                    }
                    // end of clean up
                    if($success == 1){
                        $fdelivery_mop_mixed['product_price_list_delivery_mop_id'] = $delivery_mop_id;
                        if(!$db->insert('product_price_list_delivery_mop_mixed',$fdelivery_mop_mixed)){
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();                                
                            $success = 0;
                        }
                    }
                    if($success == 1){
                        $delivery_mop_mixed_id = SI::get_trans_id(
                            $db
                            ,'product_price_list_delivery_mop_mixed'
                            ,'product_price_list_delivery_mop_id'
                            ,$delivery_mop_id
                            ,false
                        );
                    }
                    if($success == 1){
                        for($i  = 0; $i<count($fdelivery_mop_mixed_product);$i++){
                            $fdelivery_mop_mixed_product[$i]['product_price_list_delivery_mop_mixed_id'] 
                                    = $delivery_mop_mixed_id;
                            if(!$db->insert('product_price_list_delivery_mop_mixed_product',$fdelivery_mop_mixed_product[$i])){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }                                    
                        }

                    }

                    if($success == 1){
                        $db->trans_commit();
                        switch($action){
                            case 'add':
                                $msg[] = Lang::get(array('Add','Delivery Min Order Price',array('val'=>'success','lower_all'=>true)),true,true,false,false,true);
                                break;
                            case 'update':
                                $msg[] = Lang::get(array('Update','Delivery Min Order Price',array('val'=>'success','lower_all'=>true)),true,true,false,false,true);
                                break;
                            
                        }
                    }
                }
                catch(Exception $e){

                    $db->trans_rollback();
                    $msg[] = $e->getMessage();
                    $success = 0;
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