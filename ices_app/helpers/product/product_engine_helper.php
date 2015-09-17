<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product_Engine {

    public static $product_type_list = array(
        array('val'=>'registered_product','label'=>'Registered Product'),
        array('val'=>'refill_work_order_product','label'=>'SPK Product')
    );

    public static $status_list;
    public static $rswo_product_reference_req_list;
    
    public static function helper_init(){
        self::$status_list =  array(
            //<editor-fold defaultstate="collapsed">
            array(//label name is used for method name
                'val'=>'active'
                ,'label'=>'ACTIVE'
                ,'method'=>'product_active'
                ,'default'=>true
                ,'next_allowed_status'=>array('inactive')
            ),
            array(
                'val'=>'inactive'
                ,'label'=>'INACTIVE'
                ,'method'=>'product_inactive'
                ,'next_allowed_status'=>array('active')
            )
            //</editor-fold>
        );
        
        self::$rswo_product_reference_req_list = array(
            array('val'=>'yes','label'=>'Yes'),
            array('val'=>'no','label'=>'No'),
            array('val'=>'both','label'=>'Both'),
        );
    }
    
    public static function get($id=""){
        $db = new DB();
        $q = "select * from product where status>0 and id = ".$db->escape($id);
        $rs = $db->query_array_obj($q);
        if(count($rs)>0) $rs = $rs[0];
        else $rs = null;
        return $rs;
    }

    public static function img_get($id){
        $filename = 'img/product/'.$id.'.jpg';
        $result = '<img class="product-img" src = "'.Tools::img_load($filename,false).'"></img>';
        return $result;
    }
    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'product/'
            ,'product_engine'=>'product/product_engine'
            ,'product_data_support'=>'product/product_data_support'
            ,'product_renderer'=>'product/product_renderer'
            ,'product_subcategory_engine'=>'master/product_subcategory_engine'
            ,'product_unit_conversion_engine'=>'product_unit_conversion/product_unit_conversion_engine'
            ,'ajax_search'=>get_instance()->config->base_url().'product/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'product/data_support/'

        );
        return json_decode(json_encode($path));
    }

    public static function subcategories_get($id=""){
        $db = new DB();
        $q = "select t2.* 
            from product t1
            inner join product_subcategory t2 on t1.product_subcategory_id = t2.id
            where t1.status>0 and t2.id and t1.id = ".$db->escape($id);
        $rs = $db->query_array_obj($q);
        return $rs;
    }

    public static function units_get($id=""){
        $db = new DB();
        $q = "select t3.*, t4.qty buffer_stock_qty 
            from product t1
            inner join product_unit t2 on t1.id = t2.product_id
            inner join unit t3 on t3.id = t2.unit_id
            left outer join product_buffer_stock t4 on t4.product_id = t1.id and t4.unit_id = t3.id
            where t1.status>0 and t3.status>0 and t1.id = ".$db->escape($id);
        $rs = $db->query_array_obj($q);            
        return $rs;
    }        

    public static function sales_multiplication_qty_get($product_id){
        $result = '';
        $db = new DB();
        $q = ' 
            select concat(t1.qty," ",t3.code) result
            from product_sales_multiplication_qty t1
                inner join unit t3 on t1.unit_id = t3.id
            where t1.product_id = '.$db->escape($product_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = $rs[0]['result'];
        return $result;
    }

    public static function validate($method,$data=array()){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('refill_product_category/refill_product_category_data_support');
        $result = array(
            "success"=>1
            ,"msg"=>array()

        );
        $success = 1;
        $msg = array();
        $product = isset($data['product'])?
            Tools::_arr($data['product']):array();
        $unit = isset($data['unit'])?
            Tools::_arr($data['unit']):array();
        $product_cfg = isset($data['product_cfg'])?
            Tools::_arr($data['product_cfg']):array();
        switch($method){
            case 'product_add':
            case 'product_active':
            case 'product_inactive':
                $product_code = isset($product['code'])?Tools::_str($product['code']):'';
                $product_name = isset($product['name'])?Tools::_str($product['name']):'';
                $product_img = isset($product['product_img'])?Tools::_str($product['product_img']):'';
                $rswo_product_reference_req = isset($product_cfg['rswo_product_reference_req'])?
                    Tools::_str($product_cfg['rswo_product_reference_req']):'';
                
                if(strlen($product_code)==0){
                    $success = 0;
                    $msg[] = "Code cannot be empty";
                }
                if(strlen($product_name)==0){
                    $success = 0;
                    $msg[] = "Name cannot be empty";
                }

                $db = new DB();
                $id = isset($product['id'])?$product['id']:'';
                $q = '
                    select 1 
                    from product
                    where status>0 and id != '.$db->escape($id).' 
                        and (
                            code = '.$db->escape($product_code).'
                        )
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $success = 0;
                    $msg[] = 'Code already exists';
                }

                $q = 'select 1 from product where status>0 and id = '.$db->escape($id);

                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $q_units = 'select -1 unit_id';
                    for($i = 0 ;$i<count($unit);$i++){
                        $q_units.=' union select '.$unit[$i]['id'];
                    }
                    $q = '
                        select 1
                        from product_unit_conversion
                        where product_id = '.$db->escape($id).'
                            and unit_id_1 not in ('.$q_units.')
                    ';
                    if(count($db->query_array($q))>0){
                        $success = 0;
                        $msg[] = "Unit/s is still active in Unit Conversion";
                    }

                    $q = '
                        select 1
                        from product_price_list_product t1
                            inner join product_price_list t2 on t1.product_price_list_id = t2.id
                        where t1.product_id = '.$db->escape($id).'
                            and t1.unit_id not in ('.$q_units.')
                            and t2.status>0
                    ';
                    if(count($db->query_array($q))>0){
                        $success = 0;
                        $msg[] = "Unit/s is still active in Price List";
                    }

                }

                if(is_null(SI::type_get('Product_Engine', $rswo_product_reference_req,'$rswo_product_reference_req_list'))){
                    $success = 0;
                    $msg[] = 'Refill '.Lang::get('Subcon Work Order').' - '.Lang::get('Product Reference').' invalid';
                }
                
                if(strlen($product_img)>15000){
                    $success = 0;
                    $msg[] = "Image size is too big";
                }
                break;
            default:
                $success = 0;
                $msg[] = 'Invalid Method';
                break;
        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    public static function adjust($action,$data=array()){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = array();
        $product_data = isset($data['product'])?
            Tools::_arr($data['product']):array();

        $modid = User_Info::get()['user_id'];
        $datetime_curr = Date('Y-m-d H:i:s');
        switch($action){
            case 'product_add':                
            case 'product_active':   
            case 'product_inactive':
                $unit_data = isset($data['unit'])?Tools::_arr($data['unit']):array();
                $product_unit_parent_data = isset($data['product_unit_parent'])?Tools::_arr($data['product_unit_parent']):array();
                $product_cfg_data = isset($data['product_cfg'])?Tools::_arr($data['product_cfg']):array();
                $product_id = $product_data['id'];

                $product = array(
                    'product_subcategory_id'=>Tools::_str($product_data['product_subcategory_id']),
                    'code'=>Tools::_str($product_data['code']),
                    'name'=>Tools::_str($product_data['name']),
                    'product_status'=>'',
                    'notes'=>isset($product_data['notes'])?
                        Tools::empty_to_null(Tools::_str($product_data['notes'])):'',
                    'additional_info'=>isset($product_data['additional_info'])?
                        Tools::empty_to_null(Tools::_str($product_data['additional_info'])):'',
                    'modid'=>$modid,
                    'status'=>'1',
                    'moddate'=>$datetime_curr,
                );

                if($action ==='product_active') $product['product_status']='active';
                else if($action ==='product_inactive') $product['product_status']='inactive';
                else if ($action ==='product_add') $product['product_status']='active';

                $product_unit = array();
                $product_sales_multiplication_qty = array();
                $product_buffer_stock = array();
                foreach($unit_data as $idx=>$unit){
                    $product_unit[] = array(
                        'unit_id'=>$unit['id'],
                        'product_id'=>$product_id,
                        'modid'=>$modid,
                        'moddate'=>$datetime_curr
                    );

                    $product_sales_multiplication_qty[] = array(
                        'unit_id'=>$unit['id'],
                        'product_id'=>$product_id,
                        'qty'=>preg_replace('/[^0-9.]/','',$unit['product_sales_multiplication_qty'])
                    );

                    $product_buffer_stock[] = array(
                        'unit_id'=>$unit['id'],
                        'product_id'=>$product_id,
                        'qty'=>preg_replace('/[^0-9.]/','',$unit['buffer_stock_qty'])
                    );
                }

                $product_img = isset($product_data['product_img'])?
                    Tools::_str($product_data['product_img']):'';
                
                $product_unit_parent = array();
                foreach($product_unit_parent_data as $idx=>$row){
                    $product_unit_parent[] = array(       
                        'product_id'=>Tools::_str($row['product_id']),
                        'unit_id'=>Tools::_str($row['unit_id']),
                        'qty'=>Tools::_str($row['qty']),
                        'product_unit_child'=>array(),
                    );
                    $row['product_unit_child'] = isset($row['product_unit_child'])?
                        Tools::_arr($row['product_unit_child']):array();
                    $t_puc = array();
                    foreach($row['product_unit_child'] as $idx2=>$row2){
                        $t_puc[] = array(
                            'product_id'=>Tools::_str($row2['product_id']),
                            'unit_id'=>Tools::_str($row2['unit_id']),
                            'qty'=>Tools::_str($row2['qty']),
                        );
                    }
                    $product_unit_parent[count($product_unit_parent)-1]['product_unit_child'] = $t_puc;
                }
                
                $product_cfg = array(
                    'rswo_product_reference_req'=>$product_cfg_data['rswo_product_reference_req']
                );
                
                $result['product_cfg'] = $product_cfg;
                $result['product'] = $product;    
                $result['product_unit'] = $product_unit;    
                $result['product_sales_multiplication_qty'] = $product_sales_multiplication_qty;
                $result['product_buffer_stock'] = $product_buffer_stock;
                $result['product_img'] = $product_img;
                $result['product_unit_parent'] = $product_unit_parent;
                
                break;


                break;
        }

        return $result;
        //</editor-fold>
    }

    public static function submit($id,$method,$post){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper(self::path_get()->product_data_support);

        $post = json_decode($post,TRUE);
        $data = $post;
        $ajax_post = false;                  
        $result = null;
        $cont = true;

        if(isset($post['ajax_post'])) $ajax_post = $post['ajax_post'];
        if($method == 'product_add') $data['product']['id'] = '';
        else $data['product']['id'] = $id;

        if($cont){
            $result = self::save($method,$data);
        }

        if(!$ajax_post){

        }            
        else{
            echo json_encode($result);
            die();
        }
        //</editor-fold>
    }

    public static function save($method,$data){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $success = 1;
        $msg = array();
        $action = $method;
        $result = array("status"=>0,"msg"=>array(),'trans_id'=>'');

        $product_data = $data['product'];

        $id = $product_data['id'];

        $method_list = array('product_add');
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
            switch($action){                    
                case 'product_add':
                    //<editor-fold defaultstate="collapsed">
                    try{

                        $db->trans_begin();
                        $product_id = '';
                        $fproduct = $final_data['product'];
                        $fproduct_unit = $final_data['product_unit'];
                        $fproduct_sales_multiplication_qty = $final_data['product_sales_multiplication_qty'];
                        $fproduct_buffer_stock =  $final_data['product_buffer_stock'];
                        $fproduct_img = $final_data['product_img'];
                        $fproduct_unit_parent = $final_data['product_unit_parent'];
                        $fproduct_cfg = $final_data['product_cfg'];
                        
                        if(!$db->insert('product',$fproduct)){
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();                                
                            $success = 0;
                        }

                        if($success == 1){
                            $product = $db->query_array_obj('select id from product where status>0 and code = '.$db->escape($fproduct['code']));
                            $product_id = $product[0]->id;
                            $result['trans_id'] = $product_id;
                        }

                        if($success === 1){
                            for($i = 0;$i<count($fproduct_unit);$i++){
                                $fproduct_unit[$i]['product_id'] = $product_id;
                                if(!$db->insert('product_unit',$fproduct_unit[$i])){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                    break;
                                }

                            }
                        }

                        if($success == 1){
                            for($i = 0;$i<count($fproduct_sales_multiplication_qty);$i++){
                                $fproduct_sales_multiplication_qty[$i]['product_id'] = $product_id;
                                if(!$db->insert('product_sales_multiplication_qty',
                                        $fproduct_sales_multiplication_qty[$i])){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                    break;
                                }

                            }
                        }

                        if($success == 1){
                            for($i = 0;$i<count($fproduct_buffer_stock);$i++){
                                $fproduct_buffer_stock[$i]['product_id'] = $product_id;
                                if(!$db->insert('product_buffer_stock',$fproduct_buffer_stock[$i])){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                    break;
                                }

                            }
                        }

                        if($success == 1){
                            $filename = 'img/product/'.$product_id.'.jpg';

                            if($fproduct_img!= ''){

                                file_put_contents($filename,base64_decode(
                                        str_replace('data:image/jpeg;base64,', '',$fproduct_img)));
                            }
                            else{
                                if(file_exists($filename))
                                unlink($filename);
                            }
                        }
                        
                        if($success === 1){
                            $fproduct_cfg['product_id'] = $product_id;
                            if(!$db->insert('product_cfg',$fproduct_cfg)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                                break;
                            }
                        }
                        
                        //<editor-fold defaultstate="collapsed" desc="Product Unit Parent & Child">
                        if($success == 1){
                            
                            $q = '
                                delete from product_unit_child 
                                where product_unit_parent_id in (
                                    select id from product_unit_parent
                                    where product_id = '.$db->escape($product_id).'                                    
                                )
                            ';
                            if(!$db->query($q)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                        }
                        
                        if($success == 1){
                            
                            $q = '
                                delete from product_unit_parent
                                where product_id = '.$db->escape($product_id)
                                    
                            .'';
                            if(!$db->query($q)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                                break;
                            }
                        }
                        
                        if($success == 1){
                            
                            foreach($fproduct_unit_parent as $idx=>$row){ 
                                $row['product_id'] = $product_id;
                                $temp = array(
                                    'product_id'=>$row['product_id'],
                                    'unit_id'=>$row['unit_id'],
                                    'qty'=>$row['qty'],
                                );
                                $product_unit_parent_id = '';
                                if(!$db->insert('product_unit_parent',$temp)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                    break;
                                }
                                
                                if($success === 1){
                                    $q = '
                                        select *
                                        from product_unit_parent pup
                                        where pup.product_id = '.$db->escape($row['product_id']).'
                                            and pup.unit_id = '.$db->escape($row['unit_id']).'
                                    ';
                                    $rs = $db->query_array($q);
                                    if(count($rs)>0){
                                        $product_unit_parent_id = $rs[0]['id'];
                                    }
                                    else{
                                        $success = 0;
                                        $msg[] = 'Cannot find Product Unit Parent ID';
                                        break;
                                    }
                                }
                                
                                if($success === 1){
                                    foreach($row['product_unit_child'] as $idx2=>$row2){
                                        $t_product_unit_child = array(
                                            'product_unit_parent_id'=>$product_unit_parent_id
                                            ,'product_id'=>$row2['product_id']
                                            ,'unit_id'=>$row2['unit_id']
                                            ,'qty'=>$row2['qty']
                                        );
                                        if(!$db->insert('product_unit_child',$t_product_unit_child)){
                                            $msg[] = $db->_error_message();
                                            $db->trans_rollback();                                
                                            $success = 0;
                                            break;
                                        }
                                    }
                                }

                            }
                        }
                        //</editor-fold>

                        
                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = Lang::get('Add').' '.Lang::get('Product').' '.Lang::get('success',true,false,false,true);
                        }                            

                    }
                    catch(Exception $e){
                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    }
                    //</editor-fold>
                    break;
                case 'product_active':
                case 'product_inactive':
                    try{
                        $db->trans_begin();
                        $fproduct = $final_data['product'];
                        $fproduct_unit = $final_data['product_unit'];
                        $fproduct_sales_multiplication_qty = $final_data['product_sales_multiplication_qty'];
                        $fproduct_buffer_stock =  $final_data['product_buffer_stock'];
                        $fproduct_img = $final_data['product_img'];
                        $fproduct_unit_parent = $final_data['product_unit_parent'];
                        $fproduct_cfg = $final_data['product_cfg'];
                        
                        $product_id = $id;
                        if(!$db->update('product',$fproduct,array("id"=>$product_id))){
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();                                
                            $success = 0;
                        }  
                        $result['trans_id'] = $product_id;
                        if($success == 1){
                            $q = 'delete from product_unit where product_id = '.$db->escape($product_id);
                            if(!$db->query($q)){
                                $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                    break;
                            }
                        }
                        if($success == 1){

                            for($i = 0;$i<count($fproduct_unit);$i++){

                                if(!$db->insert('product_unit',$fproduct_unit[$i])){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                    break;
                                }

                            }
                        }                            

                        if($success == 1){
                            $q = 'delete from product_sales_multiplication_qty where product_id = '.$db->escape($product_id);
                            if(!$db->query($q)){
                                $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                    break;
                            }
                        }

                        if($success == 1){
                            for($i = 0;$i<count($fproduct_sales_multiplication_qty);$i++){

                                if(!$db->insert('product_sales_multiplication_qty',
                                        $fproduct_sales_multiplication_qty[$i])){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                    break;
                                }

                            }
                        }

                        if($success == 1){
                            $q = 'delete from product_buffer_stock where product_id = '.$db->escape($product_id);
                            if(!$db->query($q)){
                                $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                    break;
                            }
                        }
                        if($success == 1){
                            for($i = 0;$i<count($fproduct_buffer_stock);$i++){

                            if(!$db->insert('product_buffer_stock',$fproduct_buffer_stock[$i])){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                    break;
                                }

                            }
                        }

                        if($success == 1){
                            $q = '
                                delete t1 from  product_unit_conversion t1
                                where t1.product_id = '.$db->escape($product_id).'
                                    and t1.unit_id_1 not in
                                (
                                        select distinct ts3.id
                                        from product ts1
                                            inner join product_unit ts2 
                                                on ts1.id = ts2.product_id
                                            inner join unit ts3
                                                on ts3.id = ts2.unit_id
                                        where ts1.id = '.$db->escape($product_id).'
                                )
                            ';
                            if(!$db->query($q)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                        }

                        if($success == 1){
                            $filename = 'img/product/'.$product_id.'.jpg';

                            if($fproduct_img!= ''){

                                file_put_contents($filename,base64_decode(
                                        str_replace('data:image/jpeg;base64,', '',$fproduct_img)));
                            }
                            else{
                                if(file_exists($filename))
                                unlink($filename);
                            }
                        }
                        
                        if($success === 1){
                            if(!$db->query('delete from product_cfg where product_id = '.$db->escape($product_id))){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                                break;
                            }
                        }
                        
                        if($success === 1){
                            $fproduct_cfg['product_id'] = $product_id;
                            
                            if(!$db->insert('product_cfg',$fproduct_cfg)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                                break;
                            }
                        }
                        
                        
                        //<editor-fold defaultstate="collapsed" desc="Product Unit Parent & Child">
                        if($success == 1){
                            
                            $q = '
                                delete from product_unit_child 
                                where product_unit_parent_id in (
                                    select id from product_unit_parent
                                    where product_id = '.$db->escape($product_id).'                                    
                                )
                            ';
                            if(!$db->query($q)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                            }
                        }
                        
                        if($success == 1){
                            
                            $q = '
                                delete from product_unit_parent
                                where product_id = '.$db->escape($product_id)
                                    
                            .'';
                            if(!$db->query($q)){
                                $msg[] = $db->_error_message();
                                $db->trans_rollback();                                
                                $success = 0;
                                break;
                            }
                        }
                        
                        if($success == 1){
                            
                            foreach($fproduct_unit_parent as $idx=>$row){                                
                                $temp = array(
                                    'product_id'=>$row['product_id'],
                                    'unit_id'=>$row['unit_id'],
                                    'qty'=>$row['qty'],
                                );
                                $product_unit_parent_id = '';
                                if(!$db->insert('product_unit_parent',$temp)){
                                    $msg[] = $db->_error_message();
                                    $db->trans_rollback();                                
                                    $success = 0;
                                    break;
                                }
                                
                                if($success === 1){
                                    $q = '
                                        select *
                                        from product_unit_parent pup
                                        where pup.product_id = '.$db->escape($row['product_id']).'
                                            and pup.unit_id = '.$db->escape($row['unit_id']).'
                                    ';
                                    $rs = $db->query_array($q);
                                    if(count($rs)>0){
                                        $product_unit_parent_id = $rs[0]['id'];
                                    }
                                    else{
                                        $success = 0;
                                        $msg[] = 'Cannot find Product Unit Parent ID';
                                        break;
                                    }
                                }
                                
                                if($success === 1){
                                    foreach($row['product_unit_child'] as $idx2=>$row2){
                                        $t_product_unit_child = array(
                                            'product_unit_parent_id'=>$product_unit_parent_id
                                            ,'product_id'=>$row2['product_id']
                                            ,'unit_id'=>$row2['unit_id']
                                            ,'qty'=>$row2['qty']
                                        );
                                        if(!$db->insert('product_unit_child',$t_product_unit_child)){
                                            $msg[] = $db->_error_message();
                                            $db->trans_rollback();                                
                                            $success = 0;
                                            break;
                                        }
                                    }
                                }

                            }
                        }
                        //</editor-fold>
                        
                        if($success == 1){
                            $db->trans_commit();
                            $msg[] = Lang::get('Update').' '.Lang::get('Product').' '.Lang::get('success',true,false,false,true);
                        }


                    }
                    catch(Exception $e){
                        $db->trans_rollback();
                        $msg[] = $e->getMessage();
                        $success = 0;
                    }


                    break;
                case 'delete':
                    $data_delete = array("status"=>0,"modid"=>$modid,"moddate"=>$moddate);
                    $db->update('product',$data_delete,array("id"=>$product_data['id']));
                    $db->update('unit_product',$data_delete,array("product_id"=>$product_data['id'],'status'=>1));
                    $msg[] = "Delete Product Success";
                    break;
            }
        }
        if($success == 1){
            Message::set('success',$msg);
        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
        //</editor-fold>
    }

    public static function detail_render($pane, $data){

    }

    public static function detail_render_old($pane,$data){
        //<editor-fold defaultstate="collapsed">
        $product = self::get($data['id']);
        $subcategories = self::subcategories_get($data['id']);
        $units = self::units_get($data['id']);

        $pane->div_add()->div_set("class","form-group");
        $first_row = $pane->div_add()->div_set("class","form-group");


        $product_img='';
        $filename = 'img/product/'.$data['id'].'.jpg';

        $product_img = Tools::img_load($filename);
        $first_row->div_add()->div_set('class','form-group')
                ->img_add()->img_set('id','product_img_view')
                ->img_set('src',$product_img);

        $first_row->label_add()->label_set("value",'Code: ');
        $first_row->span_add()->span_set("value",$product->code);

        if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'product','edit')){
            $first_row->button_add()->button_set('value','Edit')
                    ->button_set('style','margin-left:0px')
                    ->button_set('icon',App_Icon::detail_btn_edit())
                    ->button_set('href',get_instance()->config->base_url().'product/edit/'.$data['id']);
        }
        if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'product','delete')){
            $first_row->button_add()->button_set('value','Delete')
                    ->button_set('icon',App_Icon::detail_btn_delete())
                    ->button_set('class','btn btn-danger')
                    ->button_set('confirmation',true)
                    ->button_set('confirmation msg','Are you sure want to delete '.$product->name.'?')
                    ->button_set('href',get_instance()->config->base_url().'product/delete/'.$data['id']);
        }

        $pane->label_span_add()->label_span_set("value",array('label'=>"Name: ","span"=>$product->name));
        $unit_name = '';
        $subcategory_name = '';

        foreach($units as $unit){
            if($unit_name == '') $unit_name.=Tools::thousand_separator($unit->buffer_stock_qty,2,true).' '.$unit->name;
            else $unit_name.=', '.Tools::thousand_separator($unit->buffer_stock_qty,2,true).' '.$unit->name;
        }

        foreach($subcategories as $subcategory){
            if($subcategory_name == '') $subcategory_name.=$subcategory->name;
            else $subcategory_name.=', '.$subcategory->name;
        }

        $sales_multiplication_qty = self::sales_multiplication_qty_get($product->id);

        $pane->label_span_add()->label_span_set("value",array('label'=>"Sub Category: ","span"=>$subcategory_name));
        $pane->label_span_add()->label_span_set("value",array('label'=>"Buffer Stock Unit: ","span"=>$unit_name));
        $pane->label_span_add()->label_span_set("value",array('label'=>"Sales Multiplication Qty: ","span"=>$sales_multiplication_qty));
        $pane->textarea_add()->textarea_set('label','Notes')->textarea_set('name','notes')
            ->textarea_set('value',$product->notes)
            ->textarea_set('attrib',array('disabled'=>''))
            ;

        $pane->hr_add();
        $pane->button_add()->button_set('value','BACK')
            ->button_set('class','btn btn-default')
            ->button_set('icon','fa fa-arrow-left')
            ->button_set('href',get_instance()->config->base_url().'product/index');
        //</editor-fold>
    }
    
    public static function unit_conversion_render($app, $pane, $data){
        //<editor-fold defaultstate="collapsed">
        $path = self::path_get();
        $id = $data['id'];
        get_instance()->load->helper($path->product_unit_conversion_engine);
        $path_puc = Product_Unit_Conversion_Engine::path_get();
        get_instance()->load->helper($path_puc->product_unit_conversion_renderer);

        $product = self::get($id);
        $pane->form_group_add();
        if(!is_null($product )){
            if(Security_Engine::get_controller_permission(User_Info::get()['user_id']
                    ,'product','product_unit_conversion_add')){
            $pane->button_add()->button_set('class','primary')
                    ->button_set('value',Lang::get('New Unit Conversion'))
                    ->button_set('icon','fa fa-plus')
                    ->button_set('attrib',array(
                        'data-toggle'=>"modal" 
                        ,'data-target'=>"#modal_product_unit_conversion"
                    ))
                    ->button_set('disable_after_click',false)
                    ->button_set('id','product_unit_conversion_btn_new')
                ;
            }
        }

        $pane->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
        $tbl = $pane->table_add();
        $tbl->table_set('class','table');
        $tbl->table_set('id','product_unit_conversion_table');
        $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center'),"is_key"=>true));            
        $tbl->table_set('columns',array("name"=>"type_name","label"=>Lang::get("Type"),'attribute'=>'','col_attrib'=>array()));
        $tbl->table_set('columns',array("name"=>"qty_1","label"=>Lang::get("Qty 1"),'attribute'=>'style="text-align:right"','col_attrib'=>array('style'=>'text-align:right')));
        $tbl->table_set('columns',array("name"=>"unit_name_1","label"=>Lang::get("Unit 1"),'attribute'=>'','col_attrib'=>array()));
        $tbl->table_set('columns',array("name"=>"qty_2","label"=>Lang::get("Qty 2"),'attribute'=>'style="text-align:right"','col_attrib'=>array('style'=>'text-align:right')));
        $tbl->table_set('columns',array("name"=>"unit_name_2","label"=>Lang::get("Unit 2"),'attribute'=>'','col_attrib'=>array()));
        $tbl->table_set('columns',array("name"=>"puc_status","label"=>Lang::get("Status"),'attribute'=>'','col_attrib'=>array()));
        $tbl->table_set('columns',array("name"=>"action","label"=>'','attribute'=>'style="width:50px"','col_attrib'=>array()));

        $tbl->table_set('data key','id');

        $db = new DB();
        $q = '
            select distinct NULL row_num
                ,t1.id
                ,t1.qty_1
                ,t3.name unit_name_1
                ,t1.qty_2
                ,t4.name unit_name_2
                ,t1.type 
                ,t5.code expedition_code
                ,t5.name expedition_name
                ,t1.product_unit_conversion_status
            from product_unit_conversion t1
                inner join product t2 on t1.product_id = t2.id
                inner join unit t3 on t1.unit_id_1 = t3.id                    
                inner join unit t4 on t1.unit_id_2 = t4.id  
                left outer join expedition t5 on t1.expedition_id = t5.id
            where t1.product_id = '.$id.' 
                and t1.status>0
            order by t1.type, t3.name, t4.name

        ';

        $rs = $db->query_array($q);
        $delete_url = get_instance()->config->base_url().'product/product_unit_conversion_delete/';
        for($i = 0;$i<count($rs);$i++){
            $rs[$i]['row_num'] = $i+1;
            $rs[$i]['action'] = '<button class="fa fa-trash-o text-red no-border background-transparent"'
                . ' delete_url="'.$delete_url.$rs[$i]['id'].'/'.$id.'"></button>';
            $type = $rs[$i]['type'];
            $rs[$i]['puc_status'] = SI::get_status_attr(
                SI::status_get('Product_Unit_Conversion_Engine',$rs[$i]['product_unit_conversion_status'])['label']
            );
            switch($type){
                case 'sales_moq':
                    $rs[$i]['type_name'] = 'Sales MOQ';
                    break;
                case 'sales_real_weight':
                    $rs[$i]['type_name'] = 'Sales Real Weight';
                    break;
                case 'sales_expedition_weight':
                    $rs[$i]['type_name'] = 'Sales Expedition Weight - '.SI::html_tag('strong',$rs[$i]['expedition_code']).' '.$rs[$i]['expedition_name'];

                    break;


            }
        }

        $tbl->table_set('data',$rs);

        $modal_product_unit_conversion = $app->engine->modal_add()
                ->id_set('modal_product_unit_conversion')
                ->width_set('50%')
                ->header_set(array('title'=>''))
                ->footer_attr_set(array('style'=>'display:none'))
                ;

        Product_Unit_Conversion_Renderer::product_unit_conversion_components_render(
                $app
                ,$modal_product_unit_conversion
                ,true
            );            

        $param = array('product_id'=>$id,'product_name'=>$product->name);

        $js = get_instance()->load->view('product/product_unit_conversion_view_js',$param,TRUE);
        $app->js_set($js);
        //</editor-fold>
    }


    public static function stock_history_render($app,$pane,$data,$path){
        //<editor-fold defaultstate="collapsed">
        $id = $data['id'];
        $product = self::get($data['id']);

        $pane->div_add()->div_set("class","form-group");
        $pane->label_span_add()->label_span_set("value",array('label'=>$product->name,"span"=>''));
        $db  = new DB();
        $q ='
            select t1.id value,t1.name label
            from warehouse t1
                inner join warehouse_type t2 on t1.warehouse_type_id = t2.id and t2.code = "BOS"
            where t1.status>0
        ';

        $warehouse = $db->query_array($q);

        $pane->select_add()->select_set('id','s_warehouse_id')
                ->select_set('options_add',$warehouse)                    
                ;

        $cols = array(
            array("name"=>"warehouse_name","label"=>"Warehouse","data_type"=>"text")
            ,array("name"=>"moddate","label"=>"Modified Date","data_type"=>"text")
            ,array("name"=>"stock_qty_old","label"=>"Old Stock","data_type"=>"text")
            ,array("name"=>"qty","label"=>"Qty","data_type"=>"text")            
            ,array("name"=>"stock_qty_new","label"=>"New Stock","data_type"=>"text")
            ,array("name"=>"unit_name","label"=>"Unit","data_type"=>"text")  
            ,array("name"=>"description","label"=>"Description","data_type"=>"text")  
        );

        $pane->input_add()->input_set('id','s_product_id')
                ->input_set('hide',true)
                ->input_set('value',$id)
                ;

        $tbl = $pane->table_ajax_add();
        $tbl->table_ajax_set('id','s_ajax_table')
                ->table_ajax_set('lookup_url',$path->ajax_search.'product_stock_history')
                //->table_ajax_set('controls',$controls)
                ->table_ajax_set('columns',$cols)
                ->filter_set(
                    array(
                        array('id'=>'s_product_id','field'=>'product_id')
                        ,array('id'=>'s_warehouse_id','field'=>'warehouse_id')
                        )
                    )

                ;
        $js = ' $("#s_warehouse_id").on("change",function(){               
                s_ajax_table.methods.data_show(1);
            }); 

        ';
        $app->js_set($js);
        //</editor-fold>
    }

    public static function stock_sales_available_history_render($app,$pane,$data,$path){
        //<editor-fold defaultstate="collapsed">
        $id = $data['id'];
        $product = self::get($data['id']);

        $pane->div_add()->div_set("class","form-group");
        $pane->label_span_add()->label_span_set("value",array('label'=>$product->name,"span"=>''));
        $db  = new DB();
        $q ='
            select t1.id value,t1.name label
            from warehouse t1
                inner join warehouse_type t2 on t1.warehouse_type_id = t2.id and t2.code = "BOS"
            where t1.status>0
        ';

        $warehouse = $db->query_array($q);

        $pane->select_add()->select_set('id','ssa_warehouse_id')
                ->select_set('options_add',$warehouse)                    
                ;

        $cols = array(
            array("name"=>"warehouse_name","label"=>"Warehouse","data_type"=>"text")
            ,array("name"=>"moddate","label"=>"Modified Date","data_type"=>"text")
            ,array("name"=>"stock_qty_old","label"=>"Old Stock","data_type"=>"text")
            ,array("name"=>"qty","label"=>"Qty","data_type"=>"text")            
            ,array("name"=>"stock_qty_new","label"=>"New Stock","data_type"=>"text")
            ,array("name"=>"unit_name","label"=>"Unit","data_type"=>"text")  
            ,array("name"=>"description","label"=>"Description","data_type"=>"text")  
        );

        $pane->input_add()->input_set('id','ssa_product_id')
                ->input_set('hide',true)
                ->input_set('value',$id)
                ;

        $tbl = $pane->table_ajax_add();
        $tbl->table_ajax_set('id','ssa_ajax_table')
                ->table_ajax_set('lookup_url',$path->ajax_search.'product_stock_sales_available_history')
                ->table_ajax_set('columns',$cols)
                ->filter_set(
                    array(
                        array('id'=>'ssa_product_id','field'=>'product_id')
                        ,array('id'=>'ssa_warehouse_id','field'=>'warehouse_id')
                        )
                    )

                ;
        $js = ' $("#ssa_warehouse_id").on("change",function(){
                ssa_ajax_table.methods.data_show(1);
            }) 

        ';
        $app->js_set($js);
        //</editor-fold>
    }

    public static function stock_bad_history_render($app,$pane,$data,$path){
        //<editor-fold defaultstate="collapsed">
        $id = $data['id'];
        $product = self::get($data['id']);

        $pane->div_add()->div_set("class","form-group");
        $pane->label_span_add()->label_span_set("value",array('label'=>$product->name,"span"=>''));
        $db  = new DB();
        $q ='
            select t1.id value,t1.name label
            from warehouse t1 
                inner join warehouse_type t2 on t1.warehouse_type_id = t2.id and t2.code = "BOS"
            where t1.status>0
        ';

        $warehouse = $db->query_array($q);

        $pane->select_add()->select_set('id','sb_warehouse_id')
                ->select_set('options_add',$warehouse)                    
                ;

        $cols = array(
            array("name"=>"warehouse_name","label"=>"Warehouse","data_type"=>"text")
            ,array("name"=>"moddate","label"=>"Modified Date","data_type"=>"text")
            ,array("name"=>"stock_qty_old","label"=>"Old Stock","data_type"=>"text")
            ,array("name"=>"qty","label"=>"Qty","data_type"=>"text")            
            ,array("name"=>"stock_qty_new","label"=>"New Stock","data_type"=>"text")
            ,array("name"=>"unit_name","label"=>"Unit","data_type"=>"text")  
            ,array("name"=>"description","label"=>"Description","data_type"=>"text")  
        );

        $pane->input_add()->input_set('id','sb_product_id')
                ->input_set('hide',true)
                ->input_set('value',$id)
                ;

        $tbl = $pane->table_ajax_add();
        $tbl->table_ajax_set('id','sb_ajax_table')
                ->table_ajax_set('lookup_url',$path->ajax_search.'product_stock_bad_history')
                ->table_ajax_set('columns',$cols)
                ->filter_set(
                    array(
                        array('id'=>'sb_product_id','field'=>'product_id')
                        ,array('id'=>'sb_warehouse_id','field'=>'warehouse_id')
                        )
                    )

                ;
        $js = ' $("#sb_warehouse_id").on("change",function(){
                sb_ajax_table.methods.data_show(1);
            }) 

        ';
        $app->js_set($js);
        //</editor-fold>
    }




}
?>
