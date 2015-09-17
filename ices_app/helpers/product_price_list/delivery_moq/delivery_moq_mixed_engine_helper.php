<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Delivery_MOQ_Mixed_Engine {
        
        public static function path_get(){
            $path = array(
                'index'=>get_instance()->config->base_url().'product_price_list/'
                ,'product_price_list_delivery_moq_engine' => 'product_price_list_delivery_mo/product_price_list_delivery_moq_engine'
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
            if($method == 'add') $data['delivery_moq']['id'] = '';
            else $data['delivery_moq']['id'] = $id;
            
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
            $delivery_moq = isset($data['delivery_moq'])?$data['delivery_moq']:null;
            $delivery_moq_mixed = isset($data['delivery_moq_mixed'])?$data['delivery_moq_mixed']:null;
            $delivery_moq_product = isset($data['delivery_moq_mixed_product'])? $data['delivery_moq_mixed_product']: null;

            $db = new DB();
            
            $id = isset($delivery_moq['id'])?$delivery_moq['id']:'';
            
            if($method !== 'add'){
                if(!SI::record_exists(
                        'product_price_list_delivery_moq'
                        ,array('id'=>$id,'calculation_type'=>'mixed','status'=>'1'))
            ){
                    $result['success'] = 0;
                    $result['msg'][] = "Data does not exists";
                }
            }
            
            $price_list_id = isset($delivery_moq['product_price_list_id'])?$delivery_moq['product_price_list_id']:'';
            if(strlen($price_list_id) === 0){
                $result['success'] = 0;
                $result['msg'][] = "Product Price List cannot be empty";
            }
            
            $code = isset($delivery_moq['code'])?$delivery_moq['code']:'';
            if(strlen($code) === 0){
                $result['success'] = 0;
                $result['msg'][] = "Code cannot be empty";
            }
            
            if(SI::duplicate_value('product_price_list_delivery_moq',$id,'code',$code)){
                $result['success'] = 0;
                $result['msg'][] = "Code already exists";
            
            }
            
            $unit_id = isset($delivery_moq_mixed['unit_id'])?$delivery_moq_mixed['unit_id']:'';
            if(strlen($unit_id) === 0){
                $result['success'] = 0;
                $result['msg'][] = "Unit cannot be empty";
            }
            
            $qty = isset($delivery_moq_mixed['qty'])?$delivery_moq_mixed['qty']:'';
            if(floatval($qty) <= 0){
                $result['success'] = 0;
                $result['msg'][] = "Qty must be higher than 0";
            }
            
            if(count($delivery_moq_product) === 0){
                $result['success'] = 0;
                $result['msg'][] = "Product is empty";
            }
            
            if($result['success'] === 1){
                foreach($delivery_moq_product as $idx=>$row){
                    $product_id = isset($row['product_id'])?Tools::_str($row['product_id']):'';
                    $q = '
                        select 1
                        from product_price_list_delivery_moq ppldm
                        inner join product_price_list_delivery_moq_separated ppldms 
                            on ppldm.id = ppldms.product_price_list_delivery_moq_id
                        where ppldm.status > 0
                            and ppldm.product_price_list_id = '.$db->escape($price_list_id).'
                            and ppldm.id != '.$db->escape($id).'
                            and ppldms.product_id = '.$db->escape($product_id).'
                            and ppldms.unit_id = '.$db->escape($unit_id).'
                        
                        union 
                        
                        select 1
                        from product_price_list_delivery_moq ppldm
                        inner join product_price_list_delivery_moq_mixed ppldmm 
                            on ppldm.id = ppldmm.product_price_list_delivery_moq_id
                        inner join product_price_list_delivery_moq_mixed_product ppldmmp 
                            on ppldmm.id = ppldmmp.product_price_list_delivery_moq_mixed_id
                        where ppldm.status > 0
                            and ppldm.product_price_list_id = '.$db->escape($price_list_id).'
                            and ppldm.id != '.$db->escape($id).'
                            and ppldmmp.product_id = '.$db->escape($product_id).'
                            and ppldmm.unit_id = '.$db->escape($unit_id).'
                        ;

                    ';
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        $product_data = Product_Data_Support::product_get($product_id);
                        $result['success'] = 0;
                        $result['msg'][] = SI::html_tag('strong',$product_data['code']).' '.$product_data['name'].' exists in Delivery Min. Order Qty';
                        break;
                    }
                }
                
            }
            
            return $result;
        }
        
        public static function adjust($action,$data=array()){
            $db = new DB();
            $result = array();
            $delivery_moq = $data['delivery_moq'];
            $delivery_moq_mixed = $data['delivery_moq_mixed'];
            $delivery_moq_mixed_product = $data['delivery_moq_mixed_product'];
            
            $result['delivery_moq'] = array(
                'calculation_type'=>'mixed'
                ,'product_price_list_id'=>$delivery_moq['product_price_list_id']
                ,'code'=>$delivery_moq['code']
            );
            
            $result['delivery_moq_mixed']=array(
                'qty'=>isset($delivery_moq_mixed['qty'])?
                    $delivery_moq_mixed['qty']:'0'
                ,'unit_id'=>$delivery_moq_mixed['unit_id']    
            );
            
            $result['delivery_moq_mixed_product'] = array();
            for($i = 0;$i<count($delivery_moq_mixed_product);$i++){
                $result['delivery_moq_mixed_product'][] = array(
                    'product_id'=>$delivery_moq_mixed_product[$i]['product_id']                    
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
            $delivery_moq = $data['delivery_moq'];
            $id = $delivery_moq['id'];
            
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
                    $fdelivery_moq = $final_data['delivery_moq'];
                    $fdelivery_moq_mixed = $final_data['delivery_moq_mixed'];
                    $fdelivery_moq_mixed_product = $final_data['delivery_moq_mixed_product'];
                    $delivery_moq_id = '';
                    
                    switch($action){
                        case 'add':
                            if(!$db->insert('product_price_list_delivery_moq',$fdelivery_moq)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            if($success == 1){
                                $delivery_moq_id = SI::get_trans_id(
                                    $db
                                    ,'product_price_list_delivery_moq'
                                    ,'code'
                                    ,$fdelivery_moq['code']
                                );
                            }

                            
                            break;
                        case 'update':    
                            if(!$db->update('product_price_list_delivery_moq',$fdelivery_moq,array('id'=>$id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            $delivery_moq_id = $id;
                            
                            
                            break;
                    }

                    if($success == 1){
                        $result['trans_id']=$delivery_moq_id; // useful for view forwarder
                    }
                    
                    if($success == 1){
                        $price_list_id = $fdelivery_moq['product_price_list_id'];
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
                    
                    $delivery_moq_mixed_id = '';
                    
                    if($success == 1){
                        $q = '
                            select id
                            from product_price_list_delivery_moq_mixed
                            where product_price_list_delivery_moq_id = '.$db->escape($delivery_moq_id).' 
                        ';
                        $rs  = $db->query_array($q);
                        if(count($rs)>0){
                            $delivery_moq_mixed_id = $rs[0]['id'];
                        }
                    }
                    // clean up mixed records
                    if ($success == 1){
                        $q = '
                            delete from product_price_list_delivery_moq_mixed
                            where product_price_list_delivery_moq_id = '.$db->escape($delivery_moq_id).'
                        ';
                        if(!$db->query($q)){
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();                                
                            $success = 0;
                        }
                    }
                    
                    if ($success == 1){
                        $q = '
                            delete from product_price_list_delivery_moq_mixed_product
                            where product_price_list_delivery_moq_mixed_id = '.$db->escape($delivery_moq_mixed_id).'
                        ';
                        
                        if(!$db->query($q)){
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();                                
                            $success = 0;
                        }
                    }
                    // end of clean up
                    if($success == 1){
                        $fdelivery_moq_mixed['product_price_list_delivery_moq_id'] = $delivery_moq_id;
                        if(!$db->insert('product_price_list_delivery_moq_mixed',$fdelivery_moq_mixed)){
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();                                
                            $success = 0;
                        }
                    }
                    if($success == 1){
                        $delivery_moq_mixed_id = SI::get_trans_id(
                            $db
                            ,'product_price_list_delivery_moq_mixed'
                            ,'product_price_list_delivery_moq_id'
                            ,$delivery_moq_id
                            ,false
                        );
                    }
                    if($success == 1){
                        for($i  = 0; $i<count($fdelivery_moq_mixed_product);$i++){
                            $fdelivery_moq_mixed_product[$i]['product_price_list_delivery_moq_mixed_id'] 
                                    = $delivery_moq_mixed_id;
                            if(!$db->insert('product_price_list_delivery_moq_mixed_product',$fdelivery_moq_mixed_product[$i])){
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
                                $msg[] = Lang::get(array('Add','Delivery Min Order Qty',array('val'=>'success','lower_all'=>true)),true,true,false,false,true);
                                break;
                            case 'update':
                                $msg[] = Lang::get(array('Update','Delivery Min Order Qty',array('val'=>'success','lower_all'=>true)),true,true,false,false,true);
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