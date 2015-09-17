<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Product_Price_List_Engine {
        
        public static $status_list = array(
            array(//label name is used for method name
                'val'=>'active'
                ,'label'=>'ACTIVE'
                ,'method'=>'active'
                ,'default'=>true
                ,'next_allowed_status'=>array('inactive')
            )
            ,array(
                'val'=>'inactive'
                ,'label'=>'INACTIVE'
                ,'method'=>'inactive'
                ,'next_allowed_status'=>array('active')
                
            )            
            
        );
        
        public static function get($id){
            $result = false;
            $db = new DB();
            $q = '
                    select *
                    from product_price_list 
                    where status > 0 && id = '.$db->escape($id).'
                ';
            $rs = $db->query_array_obj($q);
            if(count($rs)>0){
                $result = $rs[0];
            }
            return $result;
        }
        
        public static function product_price_list_exists($id){
            $result = false;
            $db = new DB();
            $q = '
                    select 1 
                    from product_price_list 
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
                'index'=>get_instance()->config->base_url().'product_price_list/'
                ,'product_price_list_engine'=>'product_price_list/product_price_list_engine'
                ,'delivery_moq_engine'=>'product_price_list/delivery_moq/delivery_moq_engine'
                ,'delivery_mop_engine'=>'product_price_list/delivery_mop/delivery_mop_engine'
                ,'delivery_extra_charge_engine'=>'product_price_list/delivery_extra_charge/delivery_extra_charge_engine'
                ,'ppl_extra_charge_engine'=>'product_price_list/ppl_extra_charge/ppl_extra_charge_engine'
                ,'product_price_list_print'=>'product_price_list/product_price_list_print'
                ,'product_price_list_renderer' => 'product_price_list/product_price_list_renderer'
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
            /*
            if($method === 'add'){
                $cont = true;
            }else{
                
            }
            */
            if(isset($post['ajax_post'])) $ajax_post = $post['ajax_post'];
            if($method == 'add') $data['product_price_list']['id'] = '';
            else $data['product_price_list']['id'] = $id;
            
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
            $result = array(
                "success"=>1
                ,"msg"=>array()
                
            );
            $product_price_list = isset($data['product_price_list'])?$data['product_price_list']:null;
            $product_price_list_product = isset($data['product_price_list_product'])? $data['product_price_list_product']: null;

            $db = new DB();
            
            $code = isset($product_price_list['code'])?$product_price_list['code']:'';
            $is_refill_sparepart_price_list = isset($product_price_list['is_refill_sparepart_price_list'])?
                $product_price_list['is_refill_sparepart_price_list']:'0';
            if(strlen($code) === 0){
                $result['success'] = 0;
                $result['msg'][] = "Code cannot be empty";
            }
            $id = isset($product_price_list['id'])?$product_price_list['id']:'';
            $q = 'select 1 from product_price_list where id != '.$db->escape($id).' and code = '.$db->escape($code).' and status = 1';
            $rs = $db->query_array_obj($q);
            if(count($rs)>0){
                $result['success'] = 0;
                $result['msg'][] = "Code exists";
            
            }
            
            $name = isset($product_price_list['name'])?$product_price_list['name']:'';
            if(strlen($name) === 0){
                $result['success'] = 0;
                $result['msg'][] = "Name cannot be empty";
            }
            
            if(count($product_price_list_product) === 0){
                $result['success'] = 0;
                $result['msg'][] = "Product is empty";
            }
            else{
                $found_error = false;
                for($i = 0;$i<count($product_price_list_product);$i++){
                    $prod_i = $product_price_list_product[$i];
                    for($j = 0; $j<count($product_price_list_product); $j++){
                        $prod_j = $product_price_list_product[$j];
                        if($i !== $j){
                            if( $prod_i['product_id'] === $prod_j['product_id']
                                && $prod_i['unit_id'] === $prod_j['unit_id']
                                && floatval($prod_i['min_qty']) === floatval($prod_j['min_qty'])
                            ){
                                $result['success'] = 0;
                                $result['msg'][] = "Duplicate min qty";
                                $found_error = true;
                                break;
                            }
                        }
                    }
                    if($found_error) break;
                }
            }
            
            if($is_refill_sparepart_price_list === '1'){
                $q = '
                    select distinct ppl.code
                    from product_price_list ppl
                    where ppl.status > 0
                        and ppl.is_refill_sparepart_price_list = 1
                        and ppl.id <> '.$db->escape($id).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $result['success'] = 0;
                    $result['msg'][] = 'Refill Sparepart Price List '.Lang::get('exists',true,false).' '.$rs[0]['code'];
                }
            }
            return $result;
        }
        
        public static function adjust($action,$data=array()){
            $db = new DB();
            $result = array();
            $product_price_list = $data['product_price_list'];
            $product_price_list_product = $data['product_price_list_product'];
            
            $status = SI::status_default_status_get('product_price_list_engine')['val'];
            if($action !== 'add'){
                $status = isset($product_price_list['product_price_list_status'])?
                    $product_price_list['product_price_list_status']:'';
            }
            $result['product_price_list'] = array(
                'code'=>$product_price_list['code']
                ,'name'=>$product_price_list['name']
                ,'notes'=>$product_price_list['notes']
                ,'product_price_list_status'=>$status
                ,'notes'=>$product_price_list['notes']
                ,'is_delivery'=>isset($product_price_list['is_delivery'])?
                    ($product_price_list['is_delivery'] === '1'?'1':0):1
                ,'delivery_extra_charge'=>isset($product_price_list['delivery_extra_charge'])?
                    Tools::_float($product_price_list['delivery_extra_charge']):'0'
                ,'is_discount'=>isset($product_price_list['is_discount'])?
                    ($product_price_list['is_discount'] === '1'?'1':0):0
                ,'is_refill_sparepart_price_list'=>isset($product_price_list['is_refill_sparepart_price_list'])?
                    ($product_price_list['is_refill_sparepart_price_list'] === '1'?'1':0):0
                
            );
            
            $result['product_price_list_product'] = array();
            for($i = 0;$i<count($product_price_list_product);$i++){
                $result['product_price_list_product'][] = array(
                    'product_id'=>$product_price_list_product[$i]['product_id']
                    ,'unit_id'=>$product_price_list_product[$i]['unit_id']
                    ,'min_qty'=>str_replace(',','',$product_price_list_product[$i]['min_qty'])
                    ,'amount'=>str_replace(',','',$product_price_list_product[$i]['amount'])
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
            $product_price_list = $data['product_price_list'];
            $id = $product_price_list['id'];
            
            $method_list = array('add');
            foreach(self::$status_list as $status){
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
                
                try{ 
                    $db->trans_begin();
                    $fproduct_price_list = array_merge($final_data['product_price_list'],array("modid"=>$modid,"moddate"=>$moddate));
                    $product_price_list_id = '';
                    
                    switch($action){
                        case 'add':
                            if(!$db->insert('product_price_list',$fproduct_price_list)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            
                            if($success == 1){
                                $q = '
                                    select id 
                                    from product_price_list
                                    where status>0 
                                        and code = '.$db->escape($fproduct_price_list['code']).'
                                ';
                                $rs_product_price_list = $db->query_array_obj($q);
                                $product_price_list_id = $rs_product_price_list[0]->id;
                                
                            }
                            
                            break;
                        case 'active':
                        case 'inactive':    
                            if(!$db->update('product_price_list',$fproduct_price_list,array('id'=>$id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                            $product_price_list_id = $id;
                            break;
                    }

                    if($success == 1){
                        $result['trans_id']=$product_price_list_id; // useful for view forwarder
                    }
                    
                    if($success == 1){
                        $product_price_list_status_log = array(
                            'product_price_list_id'=>$product_price_list_id
                            ,'product_price_list_status'=>$fproduct_price_list['product_price_list_status']
                            ,'modid'=>$modid
                            ,'moddate'=>$moddate    
                        );

                        if(!$db->insert('product_price_list_status_log',$product_price_list_status_log)){
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();                                
                            $success = 0;
                        }
                    }

                    if($success == 1){
                        if(!$db->query('delete from product_price_list_product where product_price_list_id = '.$db->escape($product_price_list_id))){
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();                                
                            $success = 0;
                        }
                    }
                    
                    if($success == 1){
                        $fproduct_price_list_product = $final_data['product_price_list_product'];
                        for($i = 0;$i<count($fproduct_price_list_product);$i++){
                            $fproduct_price_list_product[$i]['product_price_list_id'] = $product_price_list_id;
                            if(!$db->insert('product_price_list_product',$fproduct_price_list_product[$i])){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                                break;
                            }
                        }

                    }
                    
                    if($success == 1){
                        $id = $product_price_list_id;
                        $q = '
                            delete t3
                            from product_price_list_delivery_moq t1
                                inner join product_price_list_delivery_moq_mixed t2 
                                        on t1.id = t2.product_price_list_delivery_moq_id
                                inner join product_price_list_delivery_moq_mixed_product t3 
                                        on t2.id = t3.product_price_list_delivery_moq_mixed_id
                            where t1.product_price_list_id = '.$db->escape($id).'
                                    and t3.product_id not in 
                            (
                                    select distinct ts1.product_id
                                    from product_price_list_product ts1
                                    where ts1.product_price_list_id = '.$db->escape($id).'
                            )
                        ';
                        if(!$db->query($q)){
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();                                
                            $success = 0;
                            
                        }
                    }
                    
                    if($success == 1){
                        $id = $product_price_list_id;
                        $q = '
                            delete tf1
                            from product_price_list_delivery_moq_separated tf1
                            inner join (
                                select t2.id
                                from product_price_list_delivery_moq t1
                                    inner join product_price_list_delivery_moq_separated t2 
                                            on t1.id = t2.product_price_list_delivery_moq_id
                                    left outer join(
                                        select distinct ts1.id 
                                        from product_price_list_delivery_moq_separated ts1
                                            inner join product_price_list_delivery_moq ts2 
                                                on ts1.product_price_list_delivery_moq_id = ts2.id
                                            inner join product_price_list_product ts3 
                                                on ts2.product_price_list_id = ts3.product_price_list_id
                                                and ts1.product_id = ts3.product_id 
                                                and ts1.unit_id = ts3.unit_id
                                        where ts3.product_price_list_id = '.$db->escape($id).'
                                    ) t3 on t3.id = t2.id			
                                where t1.product_price_list_id = '.$db->escape($id).'
                                and t3.id is null
                            ) tf2 on tf1.id = tf2.id
                        ';
                        if(!$db->query($q)){
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();                                
                            $success = 0;
                            
                        }
                    }
                    
                    if($success == 1){
                        $id = $product_price_list_id;
                        $q = '
                            delete t3
                            from product_price_list_delivery_mop t1
                                inner join product_price_list_delivery_mop_mixed t2 
                                        on t1.id = t2.product_price_list_delivery_mop_id
                                inner join product_price_list_delivery_mop_mixed_product t3 
                                        on t2.id = t3.product_price_list_delivery_mop_mixed_id
                            where t1.product_price_list_id = '.$db->escape($id).'
                                    and t3.product_id not in 
                            (
                                    select distinct ts1.product_id
                                    from product_price_list_product ts1
                                    where ts1.product_price_list_id = '.$db->escape($id).'
                            )
                        ';
                        if(!$db->query($q)){
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();                                
                            $success = 0;
                            
                        }
                    }
                    
                    if($success == 1){
                        $id = $product_price_list_id;
                        $q = '
                            delete tf1
                            from product_price_list_delivery_mop_separated tf1
                            inner join (
                                select t2.id
                                from product_price_list_delivery_mop t1
                                    inner join product_price_list_delivery_mop_separated t2 
                                        on t1.id = t2.product_price_list_delivery_mop_id
                                    left outer join(
                                        select distinct ts1.id 
                                        from product_price_list_delivery_mop_separated ts1
                                            inner join product_price_list_delivery_mop ts2 
                                                on ts1.product_price_list_delivery_mop_id = ts2.id
                                            inner join product_price_list_product ts3 
                                                on ts2.product_price_list_id = ts3.product_price_list_id
                                                and ts1.product_id = ts3.product_id 
                                                and ts1.unit_id = ts3.unit_id
                                        where ts3.product_price_list_id = '.$db->escape($id).'
                                    ) t3 on t3.id = t2.id			
                                where t1.product_price_list_id = '.$db->escape($id).'
                                and t3.id is null
                            ) tf2 on tf1.id = tf2.id
                        ';
                        if(!$db->query($q)){
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();                                
                            $success = 0;
                            
                        }
                    }
                    
                    if($success == 1){
                        $db->trans_commit();
                        switch($action){
                            case 'add':
                                $msg[] = Lang::get(array('Add','Product Price List',array('val'=>'success','lower_all'=>true)),true,true,false,false,true);
                                break;
                            case 'active':
                            case 'inactive':
                                $msg[] = Lang::get(array('Update','Product Price List',array('val'=>'success','lower_all'=>true)),true,true,false,false,true);
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
        
        
        public static function download_excel($id){
            $excel = new Excel();
            $excel::file_info_set('title','Product Price List');
            $db = new DB();
            $q = '
                select t3.name product_name, t4.name unit_name
                    ,t2.min_qty, t2.amount
                from product_price_list t1
                    inner join product_price_list_product t2 
                        on t1.id = t2.product_price_list_id
                    inner join product t3 on t2.product_id = t3.id
                    inner join unit t4 on t2.unit_id = t4.id
                where t1.id = '.$db->escape($id).'
                order by t3.name,t4.name, t2.min_qty
            ';
            $rs_product = $db->query_array($q);
            $data_arr= array();
            $data_arr[] = array('Product','Unit','Min. Qty','Amount');
            $data_arr = array_merge($data_arr,$rs_product);
            
            $excel::array_to_text($data_arr);
            $excel::save('Price List '.(string)Date('Ymd His'));            
        }
    }
?>
