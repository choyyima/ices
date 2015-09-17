<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Intake_Final_Data_Support{
    
    static function intake_final_get($id){
        //<editor-fold defaultstate="collapsed">
            $result = array();
            $db = new DB();
            $q = '
                select t1.*
                from intake_final t1
                where t1.id = '.$db->escape($id).'
            ';
            $rs = $db->query_array($q);

            if(count($rs)>0){
                $result = $rs[0];
            }

            return $result;
            //</editor-fold>
    }
    
    public static function reference_detail_get($ref_type, $ref_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        switch($ref_type){
            case 'sales_invoice':
                get_instance()->load->helper('sales_pos/sales_pos_data_support');
                $module = SI::type_get('Intake_Final_Engine', $ref_type);
                $sales_invoice = Sales_Pos_Data_Support::sales_invoice_get($ref_id);
                if(count($sales_invoice)>0){
                    $result = array(                        
                        array('id'=>'type','val'=>$module['label'],'label'=>Lang::get('Type: ')),
                        array('id'=>'code','val'=>$sales_invoice['code'],'label'=>Lang::get('Code: ')),
                        array('id'=>'transactional_date','val'=>Tools::_date($sales_invoice['sales_invoice_date'],'F d, Y H:i:s'),'label'=>Lang::get('Transactional Date: ')),
                    );
                }
                break;
        }
        return $result;
        //</editor-fold>
    }
    
    public static function reference_dependency_get($ref_type, $ref_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        
        $db = new DB();
        $q = 'select 1 = 0';
        switch($ref_type){
            case 'sales_invoice':
                $q = '
                    select t1.*,
                        t2.is_delivery,
                        t2.expedition_id
                    from sales_invoice t1
                        inner join sales_invoice_info t2 on t1.id = t2.sales_invoice_id
                    where t1.id = '.$db->escape($ref_id).'
                ';
                break;
        }
        $rs = $db->query_array($q);
        
        if(count($rs)>0){
            $ref = array();
            $reference_detail = Intake_Final_Data_Support::reference_detail_get($ref_type, $ref_id);
            $ref_product = array();
            $product_stock = array();
            $warehouse_to = array();
            $ref = $rs[0];
            
            switch($ref_type){//get product
                case 'sales_invoice':
                    //<editor-fold defaultstate="collapsed">
                    $q = '
                        select t1.product_id, 
                        t1.unit_id, 
                        t1.qty,
                        t2.code product_code,
                        t2.name product_name,
                        t3.code unit_code,
                        t1.qty - t1.movement_outstanding_qty qty_delivered,
                        t1.movement_outstanding_qty qty_outstanding,
                        "sales_invoice_product" reference_type,
                        t1.id reference_id
                    from sales_invoice_product t1
                        inner join product t2 on t1.product_id = t2.id
                        inner join unit t3 on t1.unit_id = t3.id
                        
                    where t1.sales_invoice_id = '.$db->escape($ref_id).'
                    ';
                    $rs_p = $db->query_array($q);
                    if(count($rs_p)>0) $ref_product = $rs_p;
                    //</editor-fold>
                    break;
            }
            
            
            $t_param = array();
            for($i = 0;$i<count($ref_product);$i++){
                $t_param[] = array('product_id'=>$ref_product[$i]['product_id']
                    ,'unit_id'=>$ref_product[$i]['unit_id']);
            }
            $product_stock = Product_Stock_Engine::stock_mass_get('stock_sales_available',$t_param,array());
            
            $result['ref'] = $ref;
            $result['reference_detail'] = $reference_detail;
            $result['ref_product'] = $ref_product;
            $result['product_stock'] = $product_stock;
        }
        
        return $result;
        //</editor-fold>
    }
    
    public static function sales_invoice_get($intake_final_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select siif.sales_invoice_id
            from sales_invoice_intake_final siif
            where siif.intake_final_id = '.$db->escape($intake_final_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            get_instance()->load->helper('sales_pos/sales_pos_data_support');
            $sales_invoice_id = $rs[0]['sales_invoice_id'];
            $result = Sales_Pos_Data_Support::sales_invoice_get($sales_invoice_id);
        }
        return $result;
        //</editor-fold>
    }
    
    public static function sales_invoice_product_get($sales_invoice_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select t1.product_id, 
                t1.unit_id, 
                t1.qty,
                t2.code product_code,
                t2.name product_name,
                t3.code unit_code

            from sales_invoice_product t1
                inner join product t2 on t1.product_id = t2.id
                inner join unit t3 on t1.unit_id = t3.id

            where t1.sales_invoice_id = '.$sales_invoice_id.'
        ';
        $rs_si = $db->query_array($q);
        if(count($rs_si)>0){
            $result = $rs_si;
        }
        


        return $result;
        //</editor-fold>
    }
    
    public static function intake_get($intake_final_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select t1.*
            from intake t1
                inner join intake_final_intake t2 on t1.id = t2.intake_id
            where t2.intake_final_id = '.$db->escape($intake_final_id).'
        ';
        $rs_do = $db->query_array($q);
        if(count($rs_do)>0){
            $do_arr = $rs_do;
            foreach($do_arr as $do_idx=>$do){
                $temp_do = array();
                $temp_do = $do;
                
                $q = '
                    select t1.*
                    from intake_product t1 
                    where t1.intake_id = '.$db->escape($do['id']).'
                ';
                $rs_dop = $db->query_array($q);
                
                $warehouse_from = array();
                $q = '
                    select t2.warehouse_id id
                    from intake t1
                        inner join intake_warehouse_from t2 on t1.id = t2.intake_id
                        
                    where t1.id = '.$db->escape($do['id']).'
                ';
                $rs_warehouse_from = $db->query_array($q);
                if(count($rs_warehouse_from)>0) $warehouse_from = $rs_warehouse_from[0];
                
                $temp_do['warehouse_from'] = $warehouse_from;
                $temp_do['product']  = $rs_dop;
                $result[] = $temp_do;
                
                
                
            }
        }
        return $result;
        //</editor-fold>
    }
    
    
    
}
?>