<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SIR_Data_Support{
    public function sir_get($id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select t1.*
            from sir t1
            where t1.id = '.$db->escape($id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
        //</editor-fold>
    }
    
    public function sir_by_reference_get($module_name, $module_action, $reference_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select *
            from sir
            where sir.reference_id = '.$db->escape($reference_id).'
                and module_name = '.$db->escape($module_name).'
                and module_action = '.$db->escape($module_action).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
        //</editor-fold>
    }
    
    public static function module_list_get(){
        get_instance()->load->helper('sir/sir_engine');
        return SIR_Engine::$module_list;
    }
    
    public function module_get($module_name_val){
        $result = array();
        $module_list = self::module_list_get();
        foreach($module_list as $module_idx=>$module){
            if($module['name']['val'] === $module_name_val) $result = $module;
        }
        return $result;
    }
    
    public function module_action_get($module_name_val, $module_action_val){
        $result = array();
        $module_list = self::module_list_get();
        foreach($module_list as $module_idx=>$module){
            if($module['name']['val'] === $module_name_val){
                foreach($module['action'] as $action_idx=>$action){
                    if($action['val'] === $module_action_val) $result = $action;
                }
            }
        }
        return $result;
    }
    
    public function module_action_exists($module_name_val, $module_action_val){
        $result = false;
        $module_list = self::module_list_get();
        foreach($module_list as $module_idx=>$module){
            if($module['name']['val'] === $module_name_val){
                foreach($module['action'] as $action_idx=>$action){
                    if($action['val'] === $module_action_val) $result = true;
                }
            }
        }
        return $result;
    }
    
    public function module_name_action_method_get($module_name_val, $module_action_val){
        $result = '';
        get_instance()->load->helper('sir/sir_engine');
        foreach(SIR_Engine::$module_list as $idx=>$row){
            if($row['name']['val'] === $module_name_val){
                foreach($row['action'] as $idx2=>$row2){
                    if($row2['val'] === $module_action_val){
                        $result = $row2['method'];
                    }
                }
            }
        }
        return $result;
    }
    
    public static function reference_detail_get($module_name, $module_action, $reference_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        switch($module_name.'_'.$module_action){
            case 'sales_invoice_pos_cancel':
                get_instance()->load->helper('sales_pos/sales_pos_engine');
                $sales_pos_path = Sales_Pos_Engine::path_get();
                $q = '
                    select t1.*
                    from sales_invoice t1
                        inner join sales_invoice_info t2 on t1.id = t2.sales_invoice_id
                    where t1.id = '.$db->escape($reference_id).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){

                    $result = array(
                        array('id'=>'code','label'=>'Code: ','val'=>'<a href="'.$sales_pos_path->index.'view/'.$rs[0]['id'].'" target="_blank">'.$rs[0]['code'].'</a>'),
                        array('id'=>'type','label'=>'Type: ','val'=>'Sales Invoice'),
                        array('id'=>'sales_invoice_date','label'=>'Sales Invoice Date: ','val'=>Tools::_date($rs[0]['sales_invoice_date'],'F d, Y H:i:s')),
                        array('id'=>'grand_total','label'=>'Grand Total Amount: ','val'=>Tools::thousand_separator($rs[0]['grand_total'])),
                        array('id'=>'outstanding_amount','label'=>'Outstanding Amount: ','val'=>Tools::thousand_separator($rs[0]['outstanding_amount'])),

                    );
                }
                break;
            case 'refill_invoice_cancel':
                get_instance()->load->helper('refill_invoice/refill_invoice_engine');
                $refill_invoice_path = Refill_Invoice_Engine::path_get();
                $q = '
                    select t1.*
                    from refill_invoice t1
                    where t1.id = '.$db->escape($reference_id).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){

                    $result = array(
                        array('id'=>'code','label'=>'Code: ','val'=>'<a href="'.$refill_invoice_path->index.'view/'.$rs[0]['id'].'" target="_blank">'.$rs[0]['code'].'</a>'),
                        array('id'=>'type','label'=>'Type: ','val'=>'Sales Invoice'),
                        array('id'=>'refill_invoice_date','label'=>Lang::get(array('Refill Invoice','Date')).': ','val'=>Tools::_date($rs[0]['refill_invoice_date'],'F d, Y H:i:s')),
                        array('id'=>'grand_total','label'=>'Grand Total Amount: ','val'=>Tools::thousand_separator($rs[0]['grand_total_amount'])),
                        array('id'=>'outstanding_amount','label'=>'Outstanding Amount: ','val'=>Tools::thousand_separator($rs[0]['outstanding_amount'])),

                    );
                }
                break;
                
        }        
        return $result;
        //</editor-fold>
    }
}
?>