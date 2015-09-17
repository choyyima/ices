<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BOM_Data_Support{
    
    
    public static function bom_get($id){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = null;
        $q = '
            select *
            , case bom_status when "A" then "ACTIVE"
                when "I" then "INACTIVE" end bom_status_name
            from bom
            where id = '.$db->escape($id).'
        ';
        $rs = $db->query_array_obj($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
        //</editor-fold>
    }
    
    public static function bom_exists($id=""){
        //<editor-fold defaultstate="collapsed">
        $result = false;
        $db = new DB();
        $q = '
                select 1 
                from bom 
                where status > 0 && id = '.$db->escape($id).'
            ';
        $rs = $db->query_array_obj($q);
        if(count($rs)>0){
            $result = true;
        }
        return $result;
        //</editor-fold>
    }
    
    public static function product_search($lookup_str=''){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        get_instance()->load->helper('product/product_data_support');
        get_instance()->load->helper('product/product_engine');
        $trs = Product_Data_Support::registered_product_search($lookup_str,array('product_status'=>'active'));
        if(count($trs)>0){
            foreach($trs as $i=>$row){
                $trs[$i]['id'] = $row['product_id'];
                $trs[$i]['text'] = SI::html_tag('strong',$row['product_code'])
                    .' '.$row['product_name'];
                foreach($row['unit'] as $i2=>$row2){
                    $trs[$i]['unit'][$i2]['id'] = $row2['unit_id'];
                    $trs[$i]['unit'][$i2]['text'] = SI::html_tag('strong',$row2['unit_code'])
                    .' '.$row2['unit_name'];
                }
                $trs[$i]['product_img'] = Product_Engine::img_get($row['product_id']);
                $trs[$i]['product_type'] = 'registered_product';
            }
            $result = $trs;
        }
        return $result;
        //</editor-fold>
    }
    
    public static function result_product_get($bom_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select brp.*,
                p.code product_code,
                p.name product_name,
                u.code unit_code,
                u.name unit_name
                
            from bom_result_product brp 
                inner join product p on brp.product_id = p.id
                inner join unit u on brp.unit_id = u.id
            where brp.bom_id = '.$db->escape($bom_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        
        return $result;
        //</editor-fold>
    }
    
    public static function component_product_get($bom_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select bcp.*,
                p.code product_code,
                p.name product_name,
                u.code unit_code,
                u.name unit_name
                
            from bom_component_product bcp 
                inner join product p on bcp.product_id = p.id and bcp.product_type = "registered_product"
                inner join unit u on bcp.unit_id = u.id
            where bcp.bom_id = '.$db->escape($bom_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs;
        }
        
        return $result;
        //</editor-fold>
    }
    
    public function component_stock_sum_get($bom_id,$warehouse_id_arr){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        
        $db = new DB();
        $q = '';
        $q_warehouse = '';
        for($i = 0;$i<count($warehouse_id_arr);$i++){
            if($q_warehouse === ''){
                $q_warehouse = $warehouse_id_arr[$i];
            }
            else{
                $q_warehouse .=','.$warehouse_id_arr[$i];
            }
        }
        
        //<editor fold defaultstate="collapsed" desc="Registered Product">
        $comp_product_arr = self::component_product_get($bom_id);
        if(count($comp_product_arr)>0){
            $q_p = 'select -1 product_id, -1 unit_id, 1 qty';
            foreach($comp_product_arr as $i=>$row){
                $product_type = isset($row['product_type'])?Tools::_str($row['product_type']):'';
                $product_id = isset($row['product_id'])?Tools::_str($row['product_id']):'';
                $unit_id = isset($row['unit_id'])?Tools::_str($row['unit_id']):'';
                $qty = isset($row['qty'])?Tools::_str($row['qty']):'';
                if($product_type === 'registered_product' && Tools::_float($qty)>Tools::_float('0')){
                    $q_p.=' union select '.$db->escape($product_id).','.$db->escape($unit_id).','.$db->escape($qty);
                }
            }

            $q = '
                select "registered_product" product_type,tp.product_id, tp.unit_id, sum(coalesce(pssa.qty,0)) qty
                from ('.$q_p.') tp
                left outer join product_stock_sales_available pssa
                    on pssa.product_id = tp.product_id 
                    and pssa.unit_id = tp.unit_id
                    and pssa.warehouse_id in ('.$q_warehouse.')
                where tp.product_id != "-1"
                group by product_type,tp.product_id, tp.unit_id
            ';

            $t_rs = $db->query_array($q);
            if(count($t_rs)>0){
                $result = array_merge($result,$t_rs);
            }
        }
        //</editor-fold>
        
        return $result;
        //</editor-fold>
    }
    
    public function product_unit_all_exists($data_arr,$bom_status="active",$product_status = 'active'){
        //<editor-fold defaultstate="collapsed">
        $result = false;
        $db = new DB();
        $q_product = 'select -1 product_id, -1 unit_id, -1 bom_id';
        foreach($data_arr as $idx=>$data){
            $product_id = isset($data['product_id'])?Tools::_str($data['product_id']):'';
            $unit_id = isset($data['unit_id'])?Tools::_str($data['unit_id']):'';
            $bom_id = isset($data['bom_id'])?Tools::_str($data['bom_id']):'';
            $q_product.=' union all select '.$db->escape($product_id)
                .', '.$db->escape($unit_id)
                .', '.$db->escape($bom_id)    
                ;
        }

        $q = '
            select count(1) total
            from bom_result_product brp
                inner join ('.$q_product.') tp 
                    on brp.product_id = tp.product_id 
                    and brp.unit_id = tp.unit_id
                    and brp.bom_id = tp.bom_id
                inner join bom on brp.bom_id = bom.id
                inner join product p on brp.product_id = p.id
            where 1 = 1 '
            .($bom_status===''?'':
                'and bom.bom_status = '.$db->escape($bom_status))
            .($product_status===''?'':
                'and p.product_status = '.$db->escape($product_status))
            .'
        ';
        $total = $db->query_array($q)[0]['total'];
        if(Tools::_int(count($data_arr))===Tools::_int($total)) $result = true;
        return $result;
        //</editor-fold>
    }
    
    public function bom_from_component_stock_sum_get($bom_id, $warehouse_id_arr){
        //<editor-fold defaultstate="collapsed">
        $result = null;
        $component_stock_arr = self::component_stock_sum_get($bom_id,$warehouse_id_arr);
        $component_product_arr = self::component_product_get($bom_id);
        if(count($component_stock_arr)>0){
            $component_stock_arr = json_decode(json_encode($component_stock_arr));
            foreach($component_stock_arr as $i=>$row){
                $row->calculated_stock = 0;
                foreach($component_product_arr as $i2=>$row2){
                    if(
                        $row->product_type === $row2['product_type']
                        && $row->product_id === $row2['product_id']
                        && $row->unit_id === $row2['unit_id']                            
                            
                    ){
                        $row->calculated_stock = floor(Tools::_float($row->qty) / Tools::_float($row2['qty']));
                    }
                }
                if($result == null) $result = $row->calculated_stock;
                else if(Tools::_float($result) > Tools::_float($row->calculated_stock)){
                    $result = $row->calculated_stock;
                }
            }
        }
        if($result === null) $result = '0';
        else $result = Tools::_str($result);
        return $result;
        //</editor-fold>
    }
    
}
?>