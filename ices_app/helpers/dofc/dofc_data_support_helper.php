<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class DOFC_Data_Support{
    public static function dofc_get($dofc_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select *
            from delivery_order_final_confirmation
            where delivery_order_final_confirmation.id = '.$db->escape($dofc_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
        //</editor-fold>
    }
        
    public static function dofc_info_get($dofc_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select *
            from delivery_order_final_confirmation_info dofc_info
            where dofc_info.delivery_order_final_confirmation_id = '.$db->escape($dofc_id).'
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
                get_instance()->load->helper('sales_pos/sales_pos_engine');
                    
                $path = Sales_Pos_Engine::path_get();

                $q = '
                    select t1.id,t1.code si_code,
                        t1.delivery_cost_estimation si_delivery_cost_estimation,
                        t1.grand_total si_grand_total
                        
                    from sales_invoice t1
                        inner join sales_invoice_delivery_order_final t2
                            on t1.id = t2.sales_invoice_id
                    where t2.delivery_order_final_id = '.$db->escape($ref_id).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $result = array(
                        array('id'=>'type','label'=>'Type: ','val'=>SI::type_get('dofc_engine', $ref_type)['label']),
                        array('id'=>'si_code','label'=>'Sales Invoice: ','val'=>'<a target="_blank" href="'.$path->index.'view/'.$rs[0]['id'].'"><strong>'.$rs[0]['si_code'].'</strong></a>'),
                        array('id'=>'si_delivery_cost_estimation','label'=>Lang::get('Delivery Cost Estimation').': ','val'=>Tools::thousand_separator($rs[0]['si_delivery_cost_estimation'],5)),
                        array('id'=>'si_grand_total','label'=>'Grand Total Amount: ','val'=>Tools::thousand_separator($rs[0]['si_grand_total'],5)),
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
        $q = 'select * from delivery_order_final where id='.$db->escape($ref_id);
        $rs = $db->query_array($q);
        
        if(count($rs)>0){
            $ref = array();
            $ref_detail = self::reference_detail_get($ref_type,$ref_id);
            $expedition = array();
            $mail_to = '';
            $ref = $rs[0];
            
            switch($ref_type){// get ref_detail
                case 'sales_invoice':
                    $customer = array();
                    get_instance()->load->helper('expedition/expedition_data_support');
                    get_instance()->load->helper('customer/customer_data_support');
                    $q = '
                        select distinct expedition_id,
                            si.customer_id customer_id
                        from sales_invoice_delivery_order_final sidof
                            inner join sales_invoice_info sii 
                                on sidof.sales_invoice_id = sii.sales_invoice_id
                            inner join sales_invoice si on sii.sales_invoice_id = si.id
                        where sidof.delivery_order_final_id = '.$db->escape($ref_id).'
                    ';
                    $rs2 = $db->query_array($q);

                    if(count($rs2)>0){
                        $expedition = Expedition_Data_Support::expedition_get($rs2[0]['expedition_id']);
                        $customer = Customer_Data_Support::customer_get($rs2[0]['customer_id']);
                        $mail_to = $customer['email'];
                    }
                    
                    break;
            }
            
            $result['reference_detail'] = $ref_detail;
            $result['expedition'] = $expedition;
            $result['mail_to'] = $mail_to;
            
        }
        
        return $result;
        //</editor-fold>
    }
    
    public static function sales_invoice_get($dofc_id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('sales_pos/sales_pos_data_support');
        $result = array();
        $db = new DB();
        $q = '
            select distinct sidof.sales_invoice_id 
            from sales_invoice_delivery_order_final sidof
            inner join dof_dofc 
                on sidof.delivery_order_final_id = dof_dofc.delivery_order_final_id
            where dof_dofc.delivery_order_final_confirmation_id = '.$db->escape($dofc_id).'
        ';
        $t_rs = $db->query_array($q);
        if(count($t_rs)>0){
            $si_id = $t_rs[0]['sales_invoice_id'];
            $result = Sales_Pos_Data_Support::sales_invoice_get($si_id);
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
    
    public static function delivery_order_get($delivery_order_final_id){
        $result = array();
        $db = new DB();
        $q = '
            select t1.*
            from delivery_order t1
                inner join delivery_order_final_delivery_order t2 on t1.id = t2.delivery_order_id
            where t2.delivery_order_final_id = '.$db->escape($delivery_order_final_id).'
        ';
        $rs_do = $db->query_array($q);
        if(count($rs_do)>0){
            $do_arr = $rs_do;
            foreach($do_arr as $do_idx=>$do){
                $temp_do = array();
                $temp_do = $do;
                
                $q = '
                    select t1.*
                    from delivery_order_product t1 
                    where t1.delivery_order_id = '.$db->escape($do['id']).'
                ';
                $rs_dop = $db->query_array($q);
                
                $warehouse_from = array();
                $q = '
                    select t2.warehouse_id id
                    from delivery_order t1
                        inner join delivery_order_warehouse_from t2 on t1.id = t2.delivery_order_id
                        
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
    }
}
?>