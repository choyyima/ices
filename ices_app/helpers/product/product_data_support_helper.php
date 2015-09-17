<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product_Data_Support{
    public static function product_get($id=""){
        $db = new DB();
        $q = "select * from product where status>0 and id = ".$db->escape($id);
        $rs = $db->query_array($q);
        if(count($rs)>0) $rs = $rs[0];
        else $rs = null;
        return $rs;
    }
        
    function notification_product_buffer_stock_get(){
        //<editor-fold defaultstate="collapsed">
        $result = array('response'=>null);
        $response = null;
        $db = new DB();
        $q = '
            select count(1) result
            from(
            select t1.product_id, t1.unit_id, t4.code product_name, t5.name unit_name
            , t3.qty product_stock_qty
            ,t1.qty buffer_stock_qty
            ,t1.qty - t3.qty product_qty_difference
            from product_buffer_stock t1
                inner join (
                    select t31.id product_id, t33.id unit_id,coalesce(sum(t34.qty),0) qty
                    from product t31
                        inner join product_unit t32 on t31.id = t32.product_id
                        inner join unit t33 on t32.unit_id = t33.id
                        left outer join product_stock_sales_available t34 
                            on t31.id = t34.product_id 
                                and t33.id = t34.unit_id
                                and t34.status>0
                    where t31.status>0
                    group by t31.id, t33.id
                ) t3 on t3.product_id = t1.product_id and t1.unit_id = t3.unit_id
                inner join product t4 on t4.id = t1.product_id
                inner join unit t5 on t5.id = t1.unit_id
            where t1.qty - t3.qty >0 and t4.product_status = "active"
            ) tfinal

        ';
        $rs = $db->query_array_obj($q);
        if($rs[0]->result>0){
            $response = array(
                'icon'=>App_Icon::html_get(APP_Icon::product())
                ,'href'=>get_instance()->config->base_url().'rpt_simple/index/product/buffer_stock_qty_mismatch'
                ,'msg'=>' '.($rs[0]->result).' mismatch product buffer stock');
        }
        
        $result['response'] = $response;
        return $result;
        //</editor-fold>
    }
    
    function registered_product_search($lookup_data,$opt = array()){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $lookup_str = '%'.$lookup_data.'%';
        $db = new DB();
        
        $q_product_status = isset($opt['product_status'])?
            (' and p.product_status = '.$db->escape($opt['product_status'])):
            '';
        
        $q = '
            select distinct p.id product_id
                ,p.code product_code
                ,p.name product_name
                ,"registered_product" product_type
                ,pc.*
            from product p 
                inner join product_cfg pc on p.id = pc.product_id
            where p.status>0 
                '.$q_product_status.'
                and (
                    p.code like '.$db->escape($lookup_str).' 
                    or p.name like '.$db->escape($lookup_str).' 
                )
            limit 10
        ';
        $rs_p = $db->query_array($q);
        if(count($rs_p)>0){
            for ($i = 0;$i<count($rs_p);$i++){
                $temp_p = $rs_p[$i];
                $temp_p['unit'] = array();
                $q = '
                    select u.id unit_id
                        ,u.code unit_code
                        ,u.name unit_name
                    from product_unit pu
                        inner join unit u on pu.unit_id = u.id
                    where u.status > 0 
                        and pu.product_id = '.$db->escape($temp_p['product_id']).'
                ';
                $rs_u = $db->query_array($q);
                if(count($rs_u) > 0 ){
                    for($j = 0;$j<count($rs_u);$j++){
                        $temp_p['unit'][] = $rs_u[$j];
                    }
                }
                $result[] = $temp_p;
            }
        }
        return $result;
        //</editor-fold>
    }
    
    function product_type_get($val){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        get_instance()->load->helper('product/product_engine');
        foreach(Product_Engine::$product_type_list as $idx=>$row){
            if($row['val'] === $val){
                $result = $row;
            }
        }
        return $result;
        //</editor-fold>
    }
    
    public static function product_unit_all_exists($data_arr,$opt = array()){
        //<editor-fold defaultstate="collapsed">
        $result = false;
        $db = new DB();
        $q_product = 'select -1 product_id, -1 unit_id';
        foreach($data_arr as $idx=>$data){
            $product_id = isset($data['product_id'])?Tools::_str($data['product_id']):'';
            $unit_id = isset($data['unit_id'])?Tools::_str($data['unit_id']):'';
            $q_product.=' union all select '.$db->escape($product_id).', '.$db->escape($unit_id);
        }

        $q_product_status = isset($opt['product_status'])?'and t3.product_status = '.$db->escape($opt['product_status']):'';
        
        $q = '
            select count(1) total
            from product_unit t1
                inner join ('.$q_product.') tp 
                    on t1.product_id = tp.product_id and t1.unit_id = tp.unit_id
                inner join product t3 on t1.product_id = t3.id
            where 1 = 1 
                and t3.status > 0
            '.$q_product_status.'
        ';
        $total = $db->query_array($q)[0]['total'];
        if(Tools::_int(count($data_arr))===Tools::_int($total)) $result = true;
        return $result;
        //</editor-fold>
    }
    
    public static function product_cfg_get($product_arr,$opt = array()){ 
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        
        $q_prd = '';
        foreach($product_arr as $idx=>$row){
            $product_id = isset($row['product_id'])?Tools::_str($row['product_id']):'';
            $q_prd.=($q_prd===''?'':',').$db->escape($product_id);
        }
        
        $q = '
            select *
            from product_cfg
            where product_cfg.product_id in ('.$q_prd.')
        ';
        
        $rs = $db->query_array($q);
        if(count($rs)>0){
            foreach($rs as $idx=>$row){
            $rs[$idx]['rswo_product_reference_req_text'] = SI::type_get(
                   'Product_Engine',
                   $row['rswo_product_reference_req'],
                   '$rswo_product_reference_req_list'
               )['label'];
            }
            $result = $rs;
        }
        return $result;
        //</editor-fold>
    }
    
    public function product_unit_parent_get($product_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select pup.*
                ,p.code product_code
                ,p.name product_name
                ,u.code unit_code
                ,u.name unit_name
            from product_unit_parent pup
                inner join product p on pup.product_id = p.id
                inner join unit u on pup.unit_id = u.id
            where pup.product_id = '.$db->escape($product_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            
            for($i = 0;$i<count($rs);$i++){
                $t_product_unit_child = array();
                $q = '
                    select puc.*
                        ,p.code product_code
                        ,p.name product_name
                        ,u.code unit_code
                        ,u.name unit_name
                    from product_unit_child puc
                        inner join product p on puc.product_id = p.id
                        inner join unit u on puc.unit_id = u.id
                    where puc.product_unit_parent_id = '.$db->escape($rs[$i]['id']).'
                ';
                $rs2 = $db->query_array($q);
                if(count($rs2)>0){
                    $t_product_unit_child = $rs2;
                }
                $rs[$i]['product_unit_child'] = $t_product_unit_child;                
            }
            
            $result = $rs;
        }
        return $result;
        //</editor-fold>
    }
    
    
}
?>