<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Stock_Transfer_Data_Support {

    public function stock_transfer_get($id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select t1.*
            from stock_transfer t1
            where t1.id = '.$db->escape($id).'
                and t1.status > 0
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
        //</editor-fold>
    }
    
    public function stock_transfer_product_get($id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select t1.*
                ,p.id product_id
                ,p.code product_code
                ,p.name product_name
                ,u.id unit_id
                ,u.code unit_code
                ,u.name unit_name
            from stock_transfer_product t1      
                inner join product p on t1.product_id = p.id
                inner join unit u on t1.unit_id = u.id
            where t1.stock_transfer_id = '.$db->escape($id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs;
        }
        return $result;
        //</editor-fold>
    }
    
    public function registered_product_search($lookup_data,$warehouse_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        get_instance()->load->helper('product/product_data_support');
        get_instance()->load->helper('product_stock_engine');
        get_instance()->load->helper('product/product_engine');
        
        $temp_product = Product_Data_Support::registered_product_search($lookup_data,array('product_status'=>'active'));
        for($i = 0;$i<count($temp_product);$i++){           
            $product_id = $temp_product[$i]['product_id'];
            $temp_product[$i]['id'] = $product_id;
            $temp_product[$i]['text'] = SI::html_tag('strong',$temp_product[$i]['product_code'])
                .' '.$temp_product[$i]['product_name'];
            $temp_product[$i]['product_img'] = Product_Engine::img_get($product_id);
            for($j = 0;$j<count($temp_product[$i]['unit']);$j++){
                $unit_id = $temp_product[$i]['unit'][$j]['unit_id'];
                $temp_product[$i]['unit'][$j]['id'] = $unit_id;
                $temp_product[$i]['unit'][$j]['text'] = SI::html_tag('strong',$temp_product[$i]['unit'][$j]['unit_code'])
                    .' '.$temp_product[$i]['unit'][$j]['unit_name'];
                $temp_product[$i]['unit'][$j]['max_qty'] = Product_Stock_Engine::
                    stock_sum_get('stock_sales_available',$product_id,$unit_id,array($warehouse_id));
            }
            
        }
        $result = $temp_product;
        return $result;        
        //</editor-fold>
    }
        
}
?>