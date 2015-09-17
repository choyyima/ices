<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product_Stock_Opname_Data_Support {
    
    public static function pso_get($id){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = null;
        $q = '
            select *
            from product_stock_opname
            where id = '.$db->escape($id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
        //</editor-fold>
    }
        
    public static function pso_product_get($pso_id){
        //<editor-fold defaultstate="collapsed">
        $db = new DB();
        $result = array();
        $pso = self::pso_get($pso_id);
        if($pso!== null){
            
            $warehouse_id = $pso['warehouse_id'];
            $q = '
                select *
                    ,stock_sales_available_qty "total_qty"
                    ,stock_sales_available_qty_old "total_qty_old"
                    ,outstanding_qty - outstanding_qty_old "outstanding_qty_diff"
                    ,stock_sales_available_qty - stock_sales_available_qty_old "total_qty_diff"
                    ,stock_bad_qty - stock_bad_qty_old "stock_bad_qty_diff"
                from (
                    select distinct pso_product.id,
                        pso_product.product_type,
                        pso_product.product_id,
                        pso_product.unit_id,
                        pso_product.outstanding_qty,
                        case when pso.product_stock_opname_status = "process" then coalesce(ps.qty,0) - coalesce(pssa.qty,0)
                            when pso.product_stock_opname_status = "finalized" then pso_product.outstanding_qty_old
                            end outstanding_qty_old,
                        pso_product.stock_qty,
                        case when pso.product_stock_opname_status = "process" then coalesce(ps.qty,0)
                            when pso.product_stock_opname_status = "finalized" then pso_product.stock_qty_old
                            end stock_qty_old,
                        pso_product.ssa_floor_1_qty,
                        pso_product.ssa_floor_2_qty,
                        pso_product.ssa_floor_3_qty,
                        pso_product.ssa_floor_4_qty,
                        pso_product.stock_sales_available_qty,
                        case when pso.product_stock_opname_status = "process" then coalesce(pssa.qty,0)
                            when pso.product_stock_opname_status = "finalized" then pso_product.stock_sales_available_qty_old
                            end stock_sales_available_qty_old,
                        pso_product.stock_bad_qty,
                        case when pso.product_stock_opname_status = "process" then coalesce(psb.qty,0)
                            when pso.product_stock_opname_status = "finalized" then pso_product.stock_bad_qty_old
                            end stock_bad_qty_old,
                        p.name product_name,
                        p.code product_code,
                        u.name unit_name,
                        u.code unit_code,
                        ps.qty product_stock_qty,
                        pssa.qty product_stock_sales_available_qty,
                        psb.qty product_stock_bad_qty
                    from product_stock_opname pso
                        inner join pso_product on pso.id = pso_product.product_stock_opname_id
                        inner join product p on pso_product.product_id = p.id
                        inner join unit u on pso_product.unit_id = u.id
                        left outer join product_stock ps 
                            on pso_product.product_id = ps.product_id 
                            and pso_product.unit_id = ps.unit_id
                            and ps.warehouse_id = '.$warehouse_id.'
                        left outer join product_stock_sales_available pssa
                            on pso_product.product_id = pssa.product_id 
                            and pso_product.unit_id = pssa.unit_id
                            and pssa.warehouse_id = '.$warehouse_id.'
                        left outer join product_stock_bad psb
                            on pso_product.product_id = psb.product_id 
                            and pso_product.unit_id = psb.unit_id
                            and psb.warehouse_id = '.$warehouse_id.'                        
                    where pso_product.product_stock_opname_id = '.$db->escape($pso_id).'
                ) tf
                order by abs(total_qty_diff) desc, abs(outstanding_qty_diff) desc, abs(stock_bad_qty_diff) desc  
            ';
            
            $rs = $db->query_array_obj($q);
            if(count($rs)>0){
                foreach($rs as $idx=>$row){
                    $row->product_text = SI::html_tag('strong',$row->product_code).' '.$row->product_name;
                    $row->unit_text = SI::html_tag('strong',$row->unit_code);
                }
                $rs = json_decode(json_encode($rs),true);
                $result = $rs;
            }
            
        }
        return $result;
        //</editor-fold>
    }
    
    public static function product_search($lookup_str){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('product/product_data_support');
        $result = array();
        
        $t_rs = Product_Data_Support::registered_product_search($lookup_str,array());
        if(count($t_rs)>0){
            $t_rs = json_decode(json_encode($t_rs));
            foreach($t_rs as $i=>$row){
                $row->id = $row->product_id;
                $row->text = SI::html_tag('strong',$row->product_code)
                    .' '.$row->product_name;
                foreach($row->unit as $i2=>$row2){
                    $row2->id = $row2->unit_id;
                    $row2->text = SI::html_tag('strong',$row2->unit_code)
                    ;
                }
                $row->product_type = 'registered_product';
                
            }
            $t_rs = json_decode(json_encode($t_rs),true);
            $result = $t_rs;
        }
        return $result;
        //</editor-fold>
    }
    
}
?>