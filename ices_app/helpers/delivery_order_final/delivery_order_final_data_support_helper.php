<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Delivery_Order_Final_Data_Support{
    
    
    static function delivery_order_final_get($id){
        //<editor-fold defaultstate="collapsed">
            $result = array();
            $db = new DB();
            $q = '
                select t1.*
                from delivery_order_final t1
                where t1.id = '.$db->escape($id).'
            ';
            $rs = $db->query_array($q);

            if(count($rs)>0){
                $result = $rs[0];
            }

            return $result;
            //</editor-fold>
    }

    static function delivery_order_final_product_get($id){
        //<editor-fold defaultstate="collapsed">
            $success = 0;
            $result = array();
            $db = new DB();
            $q = '
                select t2.delivery_order_status
                from delivery_order_final_delivery_order t1
                    inner join delivery_order t2 on t1.delivery_order_id = t2.id
                where t1.delivery_order_final_id = '.$db->escape($id).'
            ';
            $rs = $db->query_array($q);
            
            if(count($rs)>0){
                $success = 1;
            }
            
            if($success === 1){
                $q = '
                    select 
                        sum(t1.qty) qty
                        ,t2.code product_code
                        ,t2.name product_name
                        ,t2.additional_info product_additional_info
                        ,t3.code unit_code
                        ,t3.name unit_name
                    from delivery_order_product t1
                        inner join product t2 on t1.product_id = t2.id
                        inner join unit t3 on t1.unit_id = t3.id
                        inner join delivery_order_final_delivery_order t4 
                            on t4.delivery_order_id = t1.delivery_order_id
                    where t4.delivery_order_final_id = '.$db->escape($id).'

                    group by t2.code, t3.code
                    order by t2.code, t3.code
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0) $result = $rs;
            }
            return $result;
            //</editor-fold>
    }
    
    static function delivery_order_final_warehouse_to($id){
        //<editor-fold defaultstate="collapsed">
        
        $result = array();
        $db = new DB();
        $q = '
            select dow.*
            from delivery_order_warehouse_to dow
            inner join delivery_order_final_delivery_order dofdo 
                on dofdo.delivery_order_id = dow.delivery_order_id
            where dofdo.delivery_order_final_id = '.$db->escape($id).'
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
                $module = SI::type_get('Delivery_Order_Final_Engine', $ref_type);
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
        get_instance()->load->helper('expedition/expedition_data_support');
        get_instance()->load->helper('customer/customer_data_support');
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
            $reference_detail = Delivery_Order_Final_Data_Support::reference_detail_get($ref_type, $ref_id);
            $ref_product = array();
            $product_stock = array();
            $ref = $rs[0];
            $warehouse_to = array();
            
            
            switch($ref_type){
                //<editor-fold defaultstate="collapsed" desc="Get warehouse to">
                case 'sales_invoice':                    
                    if(strlen($ref['expedition_id'])>0){
                        $expedition = Expedition_Data_Support::expedition_get($ref['expedition_id']);
                        $warehouse_expedition_arr = Warehouse_Engine::expedition_get();
                        if(count($warehouse_expedition_arr)>0){
                            foreach($warehouse_expedition_arr as $idx=>$we){
                                $warehouse_to[] = array(
                                    'id'=>$we['id'],
                                    'text'=>SI::html_tag('strong',$we['code']).' '.$we['name'],
                                    'code'=>$we['code'],
                                    'name'=>$we['name'],
                                    'contact_name'=>$expedition['name'],
                                    'phone'=>$expedition['phone'],
                                    'address'=>$expedition['address'].' - '.$expedition['city'],
                                    'type_name'=>'Warehouse Expedition'
                                );
                            }
                        }
                    }
                    else{
                        $customer = Customer_Data_Support::customer_get($ref['customer_id']);
                        $warehouse_customer_arr = Warehouse_Engine::customer_get();
                        if(count($warehouse_customer_arr)>0){
                            foreach($warehouse_customer_arr as $idx=>$wc){
                                $warehouse_to[] = array(
                                    'id'=>$wc['id'],
                                    'text'=>SI::html_tag('strong',$wc['code']).' '.$wc['name'],
                                    'code'=>$wc['code'],
                                    'name'=>$wc['name'],
                                    'contact_name'=>$customer['name'],
                                    'phone'=>$customer['phone'],
                                    'address'=>$customer['address'],
                                    'type_name'=>'Warehouse Customer'
                                );
                            }
                        }
                    }
                
                    break;
                //</editor-fold>

            }
            
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
            $result['warehouse_to'] = $warehouse_to;
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
        //<editor-fold defaultstate="collapsed">
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
                
                $warehouse_to = array();
                $q = '
                    select t2.warehouse_id id
                    from delivery_order t1
                        inner join delivery_order_warehouse_to t2 on t1.id = t2.delivery_order_id
                        
                    where t1.id = '.$db->escape($do['id']).'
                ';
                $rs_warehouse_to = $db->query_array($q);
                if(count($rs_warehouse_to)>0) $warehouse_to = $rs_warehouse_to[0];
                
                $temp_do['warehouse_from'] = $warehouse_from;
                $temp_do['warehouse_to'] = $warehouse_to;
                $temp_do['product']  = $rs_dop;
                $result[] = $temp_do;
                
                
                
            }
        }
        return $result;
        //</editor-fold>
    }
    
    public static function sales_invoice_get($delivery_order_final_id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select sidof.sales_invoice_id
            from sales_invoice_delivery_order_final sidof
            where sidof.delivery_order_final_id = '.$db->escape($delivery_order_final_id).'
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
    
    static function notification_delivery_order_final_not_done_get(){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('rpt_simple/rpt_simple_data_support');
        $result = array('response'=>null);
        $response = null;
        $temp_result = Rpt_Simple_Data_Support::report_table_delivery_order_final_not_done();        
        if($temp_result['info']['data_count']>0){
            $response = array(
                'icon'=>App_Icon::html_get('fa fa-truck')
                ,'href'=>get_instance()->config->base_url().'rpt_simple/index/delivery_order_final/not_done'
                ,'msg'=>' '.($temp_result['info']['data_count']).' '.Lang::get('delivery order final',true,false,false,true).' - '.Lang::get(array('not done yet'),true,false,false,true).''
            );
        }
        $result['response'] = $response;
        return $result;
        //</editor-fold>
    }
    
    static function notification_delivery_order_final_not_confirmed_get(){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('rpt_simple/rpt_simple_data_support');
        $result = array('response'=>null);
        $response = null;
        $temp_result = Rpt_Simple_Data_Support::report_table_delivery_order_final_not_confirmed();        
        if($temp_result['info']['data_count']>0){
            $response = array(
                'icon'=>App_Icon::html_get('fa fa-truck')
                ,'href'=>get_instance()->config->base_url().'rpt_simple/index/delivery_order_final/not_confirmed'
                ,'msg'=>' '.($temp_result['info']['data_count']).' '.Lang::get('delivery order final',true,false,false,true).' - '.Lang::get(array('not confirmed yet'),true,false,false,true).''
            );
        }
        $result['response'] = $response;
        return $result;
        //</editor-fold>
    }
    
}
?>