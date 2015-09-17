<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard_Data_Support {
    public static function product_buffer_stock_get(){
        //<editor-fold>
        $result = array(
            'prefix_id'=>'#product_buffer_stock',
            'target_data'=>'#product_buffer_stock_table tbody',
            'data'=>''
        );

        $db = new DB();
        $q = '
            select t1.product_id, t1.unit_id, t4.code product_code, t4.name product_name, t5.code unit_name
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
            where t1.qty - t3.qty >0
                and t4.status>0
                and t4.product_status = "active"
                and t5.status>0
            order by t4.code
            limit 100
        ';
        $rs = $db->query_array($q);
        if(count ($rs) > 0 ){
            $t_data = '';
            foreach($rs as $i=>$row){
                $t_data .= '<tr>';
                $t_data .= '<td>'.($i+1).'</td>';
                $t_data .= '<td><a target="_blank" href="'.get_instance()->config->base_url().'product/view/'
                    .$row['product_id'].'">'.($row['product_code']).'</a> '.$row['product_name'].'</td>';
                $t_data .= '<td style="text-align:right"><span>'.Tools::thousand_separator($rs[$i]['product_stock_qty'])
                    .'</span></td>';
                $t_data .= '<td style="text-align:right"><span>'.Tools::thousand_separator($rs[$i]['buffer_stock_qty'])
                    .'</span></td>';
                $t_data .= '<td style="text-align:right"><span style="color:red">'.Tools::thousand_separator($rs[$i]['product_qty_difference'])
                    .'</span></td>';
                $t_data.= '<td>'.($row['unit_name']).'</td>';
                $t_data .= '</tr>';
            }
            $result['data'] = $t_data;
        }
        
        
        return $result;
        //</editor-fold>
    }


}
?>
