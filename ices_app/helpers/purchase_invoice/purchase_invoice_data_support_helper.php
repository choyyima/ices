<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Purchase_Invoice_Data_Support {

    public function purchase_invoice_get($purchase_invoice_id){
        // <editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select t1.*,
                        t2.code store_code,
                        t2.name store_name,
                        t3.id supplier_id,
                        t3.code supplier_code,
                        t3.name supplier_name
                    from purchase_invoice t1
                        inner join store t2 on t1.store_id = t2.id
                        inner join supplier t3 
                            on t1.supplier_id = t3.id
                    where t1.id = ' . $db->escape($purchase_invoice_id) . '
        ';
        $rs = $db->query_array($q);
        if (count($rs) > 0) {
            $result = $rs[0];
        }
        return $result; 
        // </editor-fold>
    }
    
    public static function purchase_invoice_outstanding_amount_search($param){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $supplier_id = isset($param['supplier_id'])?Tools::_str($param['supplier_id']):'';
        $lookup_val = isset($param['lookup_val'])?'%'.Tools::_str($param['lookup_val']).'%':'';
        $db = new DB();
        $limit = 10;
        $q = '
            select *
            from purchase_invoice t1
            where t1.purchase_invoice_status = "invoiced" 
                and t1.outstanding_amount > 0
                and t1.code like '.$db->escape($lookup_val).'
                and t1.supplier_id = '.$db->escape($supplier_id).'
            order by t1.purchase_invoice_date desc
            limit '.$limit.'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){                    
            $result = $rs;
        }
        return $result;
        //</editor-fold>
    }
    
    public function purchase_invoice_info_get($purchase_invoice_id){
        // <editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select t1.*
            from purchase_invoice_info t1
            where t1.purchase_invoice_id = ' . $db->escape($purchase_invoice_id) . '
        ';
        $rs = $db->query_array($q);
        if (count($rs) > 0) {
            $result = $rs[0];
        }
        return $result; 
        // </editor-fold>
    }
    
    public static function purchase_invoice_product_get($purchase_invoice_id){
        // <editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select t1.*,
                t2.code product_code,
                t2.name product_name,
                t3.code unit_code,
                t3.name unit_name
            from purchase_invoice_product t1
                inner join product t2 on t1.product_id = t2.id
                inner join unit t3 on t3.id = t1.unit_id
            where t1.purchase_invoice_id = ' . $db->escape($purchase_invoice_id) . '
        ';
        $rs = $db->query_array($q);
        if (count($rs) > 0) {
            $result = $rs;
        }
        return $result; 
        // </editor-fold>
    }
    
    public function purchase_invoice_expense_get($purchase_invoice_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select t1.*
            from purchase_invoice_expense t1
            where t1.purchase_invoice_id = ' . $db->escape($purchase_invoice_id) . '
        ';
        $rs = $db->query_array($q);
        if (count($rs) > 0) {
            $result = $rs;
        }
        return $result;
        //</editor-fold>
    }
    
    public static function supplier_purchase_invoice_type_get($supplier_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();

        $q = 'select 1 from supplier where is_credit = 1 and id = '.$db->escape($supplier_id);
        $rs = $db->query_array($q);
        $is_credit = false;
        if(count($rs)>0){
            $is_credit=true;
        }

        $q = '
            select *
            from purchase_invoice_type t1         
            where 1 = 1 '.($is_credit=== false?' and is_credit != "1" ':'').'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = $rs;
        return $result;
        //</editor-fold>
    }

    public static function purchase_receipt_allocation_get($purchase_invoice_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select *
            from purchase_receipt_allocation prs
            where pra.purchase_invoice_id = '.$db->escape($purchase_invoice_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs;
        }
        return $result;
        //</editor-fold>
    }
    
    public static function receive_product_get($purchase_invoice_id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('receive_product/receive_product_data_support');
        $result = array();
        $db = new DB();
        $q = '
            select pirp.receive_product_id id
            from purchase_invoice_receive_product pirp
            where pirp.purchase_invoice_id = '.$db->escape($purchase_invoice_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $t_receive_product_id = $rs;
            foreach($t_receive_product_id as $i=>$row){
                $result[] = Receive_Product_Data_Support::receive_product_get($row['id']);
            }
        }
        return $result;
        //</editor-fold>
    }
    
    function notification_outstanding_amount_get(){
        get_instance()->load->helper('rpt_simple/rpt_simple_data_support');
        $result = array('response'=>null);
        $response = null;        
        $temp_result = Rpt_Simple_Data_Support::report_table_purchase_invoice_outstanding_amount();        
        if($temp_result['info']['data_count']>0){
            $response = array(
                'icon'=>App_Icon::html_get(APP_Icon::purchase_invoice())
                ,'href'=>get_instance()->config->base_url().'rpt_simple/index/purchase_invoice/outstanding_amount'
                ,'msg'=>' '.($temp_result['info']['data_count']).' purchase invoice'.' - '.Lang::get(array(array('val'=>'incomplete','grammar'=>'adj'),array('val'=>'payment')),true,false,false,true)
            );
        }
        $result['response'] = $response;
        return $result;
    }
    
    function notification_movement_outstanding_product_qty_get(){
        get_instance()->load->helper('rpt_simple/rpt_simple_data_support');
        $result = array('response'=>null);
        $response = null;        
        $temp_result = Rpt_Simple_Data_Support::report_table_purchase_invoice_movement_outstanding_product_qty();        
        if($temp_result['info']['data_count']>0){
            $response = array(
                'icon'=>App_Icon::html_get(APP_Icon::purchase_invoice())
                ,'href'=>get_instance()->config->base_url().'rpt_simple/index/purchase_invoice/movement_outstanding_product_qty'
                ,'msg'=>' '.($temp_result['info']['data_count']).' '.'purchase invoice'.' - '.Lang::get(array(array('val'=>'unreceived','grammar'=>'adj'),array('val'=>'product')),true,false,false,true)
            );
        }
        $result['response'] = $response;
        return  $result;
    }
}
?>