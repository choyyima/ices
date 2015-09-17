<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product_Stock_Engine {
    
    public static $module_list;
    
    public static function helper_init(){
        self::$module_list = array(
            array('val'=>'stock','label'=>'Good Stock'),
            array('val'=>'stock_sales_available','label'=>'Sales Available Stock'),
            array('val'=>'stock_bad','label'=>'Bad Stock'),
        );
    }
    
    function __construct(){
        
    }
    
    public function make_stock_available($db,$module,$warehouse_id, $product_id, $unit_id, $date){
        //<editor-fold defaultstate="collapsed">
        $success = 1;
        $msg = array();
        $exists = false;
        //$db = new DB();
        $result = array(
            'success' => 1
            ,'msg'=>array()
        );
        $tbl_name = '';
        if(!in_array($module,array('stock','stock_sales_available','stock_bad'))){
            $success = 0;
            $msg[] = 'Invalid Module';
        }
        else{
            switch($module){
                case 'stock':
                    $tbl_name = 'product_stock';
                    break;
                case 'stock_sales_available':
                    $tbl_name = 'product_stock_sales_available';
                    break;
                case 'stock_bad':
                    $tbl_name = 'product_stock_bad';
                    break;
            }
        }
        
        if($success === 1){
            
            $q = '
                select 1 
                from '.$tbl_name.'
                where 
                    status = 1
                    and product_id = '.$db->escape($product_id).'
                    and warehouse_id = '.$db->escape($warehouse_id).'
                    and unit_id = '.$db->escape($unit_id).'    
            ';

            $rs = $db->query_array_obj($q);

            if(count($rs)>0){
                $exists = true;
            }

            if(!$exists){            

                try{
                    $qty = 0;
                    $modid = User_Info::get()['user_id'];
                    $moddate = date('Y-m-d H:i:s');
                    //$db->trans_begin();

                    $stock_param = array(
                        'product_id'=>$product_id
                        ,'warehouse_id'=>$warehouse_id
                        ,'unit_id'=>$unit_id
                        ,'modid'=>  $modid
                        ,'moddate'=> $moddate
                        ,'status'=>'1'
                        ,'qty'=>$qty
                    );

                    if(!$db->insert($tbl_name,$stock_param)){
                        $success = 0;
                        $msg[] = $db->_error_message();
                        $db->trans_rollback();
                    }

                    if($success == 1){
                        $q = '
                            select id from '.$tbl_name.'
                            where product_id = '.$db->escape($product_id).'
                                and warehouse_id = '.$db->escape($warehouse_id).'
                                and unit_id = '.$db->escape($unit_id).'    
                        ';

                        $item_stock = $db->query_array_obj($q)[0];
                        $history_param = array(
                            'product_stock_id'=>$item_stock->id
                            ,'stock_qty_new'=>$qty
                            ,'qty'=>$qty
                            ,'stock_qty_new'=>$qty
                            ,'modid'=>$modid
                            ,'moddate'=>$moddate
                            ,'desc'=>'Product Stock Initialization'
                            ,'date'=>$date
                        );
                        if(!$db->insert($tbl_name.'_history',$history_param)){
                            $success = 0;
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();
                        }
                    }

                }catch(Exception $e){
                    $db->trans_rollback();
                    $msg[] = $e->getMessage();
                    $success = 0;
                }
                
            }
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        
        return $result;
        //</editor-fold>
    }
    
    public function stock_add($db,$module,$warehouse_id="", $product_id="", $qty="", $unit_id="",$description="",$date = ""){
        //<editor-fold defaultstate="collapsed">
        $result = array('success'=>1,'msg'=>array());
        $success = 1;
        $msg = array();
        if(strlen($warehouse_id) == 0 || strlen($product_id) == 0 
                || strlen($qty) == 0 || strlen($unit_id) == 0 
                || strlen($description) == 0
                || strlen($date) == 0
        ){
            $success  = 0;
            $msg[] = 'incomplete parameter';
            return $result;
        }
        
        $tbl_name = '';
        if(!in_array($module,array('stock','stock_sales_available','stock_bad'))){
            $success = 0;
            $msg[] = 'Invalid Module';
        }
        else{
            switch($module){
                case 'stock':
                    $tbl_name = 'product_stock';
                    break;
                case 'stock_sales_available':
                    $tbl_name = 'product_stock_sales_available';
                    break;
                case 'stock_bad':
                    $tbl_name = 'product_stock_bad';
                    break;
            }
        }
        
        
        if($success === 1){
            $temp_result = self::make_stock_available($db,$module,$warehouse_id,$product_id,$unit_id,$date);
            if($temp_result['success'] !== 1) $success = 0;
            $msg = $temp_result['msg'];

            if($success === 1){
                try{
                    $product_stock_old = null;
                    $product_stock = null;
                    
                    $moddate = date('Y-m-d H:i:s');
                    $modid = User_Info::get()['user_id'];
                    
                    $q = '
                        select * from '.$tbl_name.'
                        where product_id = '.$db->escape($product_id).'
                            and warehouse_id = '.$db->escape($warehouse_id).'
                            and unit_id = '.$db->escape($unit_id).'    
                    ';
                    $rs = $db->query_array_obj($q);
                    
                    if(count($rs)>0){
                        $product_stock_old = $rs[0];
                    }
                    else{
                        $success = 0;
                        $msg[] = 'Unable to get old stock';
                        $db->trans_rollback();
                    }
                    
                    $q = '
                        update '.$tbl_name.' 
                        set qty = qty+'.$db->escape($qty).'
                            ,modid = '.$db->escape($modid).'
                            ,moddate = '.$db->escape($moddate).'
                        where product_id = '.$db->escape($product_id).'
                            and unit_id = '.$db->escape($unit_id).'
                            and warehouse_id = '.$db->escape($warehouse_id).'    
                    ';

                    if(!$db->query($q)){
                        $success = 0;
                        $msg[] = $db->_error_message();
                        $db->trans_rollback();
                    }
                                        
                    
                    if($success === 1){
                        $q = '
                            select * from '.$tbl_name.'
                            where product_id = '.$db->escape($product_id).'
                                and warehouse_id = '.$db->escape($warehouse_id).'
                                and unit_id = '.$db->escape($unit_id).'    
                        ';

                        $product_stock = $db->query_array_obj($q)[0];
                        
                        if(Tools::_float($product_stock->qty)<Tools::_float('0')){
                            $success = 0;
                            $msg[] = 'Stock Qty less than 0';
                            $db->trans_rollback();
                        }
                    }
                    
                    if($success === 1){
                        
                        $history_param = array(
                            'product_stock_id'=>$product_stock->id
                            ,'qty'=>$qty
                            ,'stock_qty_old'=>$product_stock_old->qty
                            ,'stock_qty_new'=>$product_stock->qty
                            ,'modid'=>$modid
                            ,'moddate'=>$moddate
                            ,'desc'=>$description
                            ,'date'=>$date
                        );

                        if(!$db->insert($tbl_name.'_history',$history_param)){
                            $success = 0;
                            $msg[] = $db->_error_message();
                            $db->trans_rollback();
                        }

                    }

                }
                catch(Exception $e){
                    $success = 0;
                    $msg[] = $e->getMessage();
                    $db->trans_rollback();
                }

            }
            
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }
    
    public function stock_good_add($db,$warehouse_id="", $product_id="", $qty="", $unit_id="",$description="",$date = ""){
        //<editor-fold defaultstate="collapsed">
        $success = 1;
        $msg = array();
        $result = array('success'=>1,'msg'=>array());
        //$db = new DB();
        //$db->trans_begin();
        
        try{
            if($success === 1){ 
                $temp_result = self::stock_add($db,'stock',$warehouse_id, $product_id, $qty, $unit_id,$description,$date);
                if($temp_result['success'] !== 1){
                    $success = 0;
                    $msg = array_merge($temp_result['msg']);
                }
            }
            
            if($success === 1){
                $temp_result = self::stock_add($db,'stock_sales_available',$warehouse_id, $product_id, $qty, $unit_id,$description,$date);
                if($temp_result['success'] !== 1){
                    $success = 0;
                    $msg = array_merge($temp_result['msg']);
                }            
            }
            
            if($success === 1){
                $temp_result = self::make_stock_available($db,'stock_bad',$warehouse_id,$product_id,$unit_id,$date);
                if($temp_result['success'] !== 1){
                    $success = 0;
                    $msg = array_merge($temp_result['msg']);
                }            
            }
            
            if($success === 1){
                //$db->trans_commit();
            }
        }
        catch(Exception $e){
            $success = 0;
            $msg[] = $e->getMessage();
            $db->trans_rollback();
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        
        return $result;
        //</editor-fold>
    }
    
    public function stock_bad_only_add($db,$warehouse_id="", $product_id="", $qty="", $unit_id="",$description="",$date = ""){
        //<editor-fold defaultstate="collapsed">
        $success = 1;
        $msg = array();
        $result = array('success'=>1,'msg'=>array());
        //$db = new DB();
        //$db->trans_begin();
        
        try{
            if($success === 1){ 
                $temp_result = self::stock_add($db,'stock_bad',$warehouse_id, $product_id, $qty, $unit_id,$description,$date);
                if($temp_result['success'] !== 1){
                    $success = 0;
                    $msg = array_merge($temp_result['msg']);
                }
            }
            
            if($success === 1){
                //$db->trans_commit();
            }
        }
        catch(Exception $e){
            $success = 0;
            $msg[] = $e->getMessage();
            $db->trans_rollback();
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        
        return $result;
        //</editor-fold>
    }
    
    public function stock_good_only_add($db,$warehouse_id="", $product_id="", $qty="", $unit_id="",$description="",$date = ""){
        //<editor-fold defaultstate="collapsed">
        $success = 1;
        $msg = array();
        $result = array('success'=>1,'msg'=>array());
        //$db = new DB();
        //$db->trans_begin();
        
        try{
            if($success === 1){ 
                $temp_result = self::stock_add($db,'stock',$warehouse_id, $product_id, $qty, $unit_id,$description,$date);
                if($temp_result['success'] !== 1){
                    $success = 0;
                    $msg = array_merge($temp_result['msg']);
                }
            }
            
            if($success === 1){
                //$db->trans_commit();
            }
        }
        catch(Exception $e){
            $success = 0;
            $msg[] = $e->getMessage();
            $db->trans_rollback();
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        
        return $result;
        //</editor-fold>
    }
    
    public function stock_sales_available_only_add($db,$warehouse_id="", $product_id="", $qty="", $unit_id="",$description="",$date = ""){
        //<editor-fold defaultstate="collapsed">
        $success = 1;
        $msg = array();
        $result = array('success'=>1,'msg'=>array());
        //$db = new DB();
        //$db->trans_begin();
        try{
            
            if($success === 1){
                $temp_result = self::stock_add($db,'stock_sales_available',$warehouse_id, $product_id, $qty, $unit_id,$description,$date);
                if($temp_result['success'] !== 1){
                    $success = 0;
                    $msg = array_merge($temp_result['msg']);
                }            
            }

        }
        catch(Exception $e){
            $success = 0;
            $msg[] = $e->getMessage();
            $db->trans_rollback();
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        
        return $result;
        //</editor-fold>
    }
    
    public static function stock_sum_get($module_name,$product_id,$unit_id,$warehouse=array()){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = '0';
        $tbl_name = 'product_'.$module_name;
        
        $q_warehouse = '';
        if($warehouse !== array()){
            for($i = 0;$i<count($warehouse);$i++){
                if($q_warehouse === ''){
                    $q_warehouse = $warehouse[$i];
                }
                else{
                    $q_warehouse .=','.$warehouse[$i];
                }
            }
        }
        else{
            $tmp_warehouse_arr = Warehouse_Engine::BOS_get('id');
            for($i = 0;$i<count($tmp_warehouse_arr);$i++) $tmp_warehouse_arr[$i] = $db->escape($tmp_warehouse_arr[$i]);
            $q_warehouse = implode($tmp_warehouse_arr,',');
        }

        $q = '
            select coalesce(sum(qty),0) stock
            from '.$tbl_name.'
            where product_id = '.$db->escape($product_id).'
                and unit_id = '.$db->escape($unit_id).'
                and warehouse_id in ('.$q_warehouse.')'
        .'';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0]['stock'];
        }
        return $result;
        //</editor-fold>
    }
    
    public function stock_mass_get($module_name,$product_unit_data, $warehouse){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $q_table_product = 'select -1 product_id, -1 unit_id, -1 warehouse_id';
        $db = new DB();
        $tbl_name = 'product_'.$module_name;
        
        for($i=0;$i<count($product_unit_data);$i++){
            $product_id = isset($product_unit_data[$i]['product_id'])?
                Tools::_str($product_unit_data[$i]['product_id']):'';
            $unit_id = isset($product_unit_data[$i]['unit_id'])?
                Tools::_str($product_unit_data[$i]['unit_id']):'';
            $q_temp_product_unit='';
            $q_temp_product_unit.=' union select '.$product_id.', '.$unit_id;
            if(count($warehouse) === 0) $warehouse = Warehouse_Engine::BOS_get ('id');
            for($j = 0;$j<count($warehouse);$j++){
                $q_table_product.= $q_temp_product_unit.','.$warehouse[$j];
            }
        }
        $q = '
            select distinct t1.product_id, t1.unit_id, t1.warehouse_id, coalesce(t2.qty,0) qty
            from ('.$q_table_product.') t1
            left outer join '.$tbl_name.' t2 on
                t1.product_id = t2.product_id and t1.unit_id = t2.unit_id
                and t1.warehouse_id = t2.warehouse_id
                and t2.status>0
            where t1.product_id != -1
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = $rs;
        
        return $result;
        //</editor-fold>
    }
    
    
    
}
?>
