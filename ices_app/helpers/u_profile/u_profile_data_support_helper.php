<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class U_Profile_Data_Support{
    
    public static function module_list_get(){
        get_instance()->load->helper('u_profile/u_profile_engine');
        return U_Profile_Engine::$module_list;
    }
    
    public function module_name_exists($module_name_val){
        //<editor-fold defaultstate="collapsed">
        $result = false;
        $module_list = self::module_list_get();
        foreach($module_list as $i=>$row){
            if($row['val'] === Tools::_str($module_name_val)){
                $result = true;
            }
        }
        return $result;
        //</editor-fold>
    }
    
    public function report_table_sales_invoice_pos_outstanding_amount($cfg=array()){
        //<editor-fold defaultstate="collapsed">        
        $result = array('column'=>array(),'data'=>array(),'info'=>array());
        $db = new DB();
        //read config
        $thousand_separator = isset($cfg['thousand_separator'])?$cfg['thousand_separator']:true;
        //end of read config
        $column = array(
            array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')),
            array("name"=>"code","label"=>"Code",'col_attrib'=>array('style'=>'text-align:left'),'is_key'=>true),
            array("name"=>"sales_invoice_date","label"=>"Sales Invoice Date",'col_attrib'=>array('style'=>'text-align:left')),
            array("name"=>"grand_total","label"=>"Grand Total",'col_attrib'=>array('style'=>'text-align:right'),'attribute'=>'style="text-align:right"'),
            array("name"=>"outstanding_amount","label"=>"Outstanding Amount",'col_attrib'=>array('style'=>'text-align:right'),'attribute'=>'style="text-align:right"'),
        );
        
        $data = array();
        
        $q = '
            select null row_num, t1.*
            from sales_invoice t1
                inner join sales_invoice_info t2 on t1.id = t2.sales_invoice_id
            where t1.outstanding_amount>0
                and t1.status>0
                and t1.sales_invoice_status != "X"
                and t2.sales_invoice_type="sales_invoice_pos"
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                if($thousand_separator){
                    $rs[$i]['grand_total'] = Tools::thousand_separator($rs[$i]['grand_total']);
                    $rs[$i]['outstanding_amount'] = Tools::thousand_separator($rs[$i]['outstanding_amount']);
                }
            }
            $data = $rs;
        }
        $info = array(
            'data_count'=>count($data),
            'base_href'=>(get_instance()->config->base_url().'sales_pos/view/')
            // end of info
        );
        
        
        $result['column'] = $column;
        $result['data'] = $data;
        $result['info'] = $info;
        return $result;
        //</editor-fold>
    }
    
    public function report_table_sales_invoice_pos_movement_outstanding_product_qty($cfg=array()){
        //<editor-fold defaultstate="collapsed">        
        $result = array('column'=>array(),'data'=>array(),'info'=>array());
        $db = new DB();
        //read config
        $thousand_separator = isset($cfg['thousand_separator'])?$cfg['thousand_separator']:true;
        //end of read config
        $column = array(
            array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')),
            array("name"=>"code","label"=>"Code",'col_attrib'=>array('style'=>'text-align:left'),'is_key'=>true),
            array("name"=>"sales_invoice_date","label"=>"Sales Invoice Date",'col_attrib'=>array('style'=>'text-align:left')),
            array("name"=>"grand_total","label"=>"Grand Total",'col_attrib'=>array('style'=>'text-align:right'),'attribute'=>'style="text-align:right"'),
        );
        
        $data = array();
        
        $q = '
            select distinct null row_num, t1.*
            from sales_invoice t1
                inner join sales_invoice_product t2 on t1.id = t2.sales_invoice_id
                inner join sales_invoice_info t3 on t1.id = t3.sales_invoice_id
            where t1.status>0
                and t1.sales_invoice_status != "X"
                and t3.sales_invoice_type="sales_invoice_pos"
                and t2.movement_outstanding_qty > 0
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                if($thousand_separator){
                    $rs[$i]['grand_total'] = Tools::thousand_separator($rs[$i]['grand_total']);
                }
                
            }
            $data = $rs;
        }
        $info = array(
            'data_count'=>count($data),
            'base_href'=>(get_instance()->config->base_url().'sales_pos/view/')
        );
        
        
        $result['column'] = $column;
        $result['data'] = $data;
        $result['info'] = $info;
        return $result;
        //</editor-fold>
    }
    
    public function report_table_sales_invoice_pos_movement_outstanding_product_qty_detail($cfg=array()){
        //<editor-fold defaultstate="collapsed">        
        $result = array('column'=>array(),'data'=>array(),'info'=>array());
        $db = new DB();
        //read config
        $thousand_separator = isset($cfg['thousand_separator'])?$cfg['thousand_separator']:true;
        //end of read config
        $column = array(
            array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')),
            array("name"=>"code","label"=>"Code",'col_attrib'=>array('style'=>'text-align:left'),'is_key'=>true),
            array("name"=>"sales_invoice_date","label"=>"Sales Invoice Date",'col_attrib'=>array('style'=>'text-align:left')),
            array("name"=>"product_code","label"=>"Product",'col_attrib'=>array('style'=>'text-align:left')),
            array("name"=>"unit_code","label"=>"Unit",'col_attrib'=>array('style'=>'text-align:left'),'attribute'=>'style="text-align:left"'),
            array("name"=>"qty","label"=>Lang::get("Ordered Qty"),'col_attrib'=>array('style'=>'text-align:right'),'attribute'=>'style="text-align:right"'),
            array("name"=>"undelivered_qty","label"=>Lang::get(array(array('val'=>'undelivered','grammar'=>'adj'),array('val'=>'Qty')),true,true,true),'col_attrib'=>array('style'=>'text-align:right'),'attribute'=>'style="text-align:right"'),
        );
        
        $data = array();
        
        $q = '
            select distinct null row_num, t1.*,
                t4.id product_id,
                t4.code product_code,
                t4.name product_name,
                t5.id unit_id,
                t5.code unit_code,
                t5.name unit_name,
                t2.qty,
                t2.movement_outstanding_qty undelivered_qty
            from sales_invoice t1
                inner join sales_invoice_product t2 on t1.id = t2.sales_invoice_id
                inner join sales_invoice_info t3 on t1.id = t3.sales_invoice_id
                inner join product t4 on t2.product_id = t4.id
                inner join unit t5 on t2.unit_id = t5.id
            where t1.status>0
                and t1.sales_invoice_status != "X"
                and t3.sales_invoice_type="sales_invoice_pos"
                and t2.movement_outstanding_qty > 0
            order by t1.code desc, t4.code asc, t5.code asc
        ';
        
        
        $rs = $db->query_array($q,100000);
        if(count($rs)>0){
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                if($thousand_separator){
                    $rs[$i]['qty'] = Tools::thousand_separator($rs[$i]['qty']);
                    if($thousand_separator){
                        $rs[$i]['undelivered_qty'] = Tools::thousand_separator($rs[$i]['undelivered_qty']);
                    }
                }

            }
            $data = $rs;
        }
        
        
        $info = array(
            'data_count'=>count($data),
            'base_href'=>(get_instance()->config->base_url().'sales_pos/view/'),
            'query'=>$q,

            // end of info
        );
        
        
        $result['column'] = $column;
        $result['data'] = $data;
        $result['info'] = $info;
        return $result;
        //</editor-fold>
    }
    
    public function report_table_sales_receipt_outstanding_amount($cfg=array()){
        //<editor-fold defaultstate="collapsed">        
        $result = array('column'=>array(),'data'=>array(),'info'=>array());
        $db = new DB();
        //read config
        $thousand_separator = isset($cfg['thousand_separator'])?$cfg['thousand_separator']:true;
        //end of read config
        $column = array(
            array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')),
            array("name"=>"code","label"=>"Code",'col_attrib'=>array('style'=>'text-align:left'),'is_key'=>true),
            array("name"=>"sales_receipt_date","label"=>"Sales Receipt Date",'col_attrib'=>array('style'=>'text-align:left')),
            array("name"=>"amount","label"=>"Amount",'col_attrib'=>array('style'=>'text-align:right'),'attribute'=>'style="text-align:right"'),
            array("name"=>"outstanding_amount","label"=>"Outstanding Amount",'col_attrib'=>array('style'=>'text-align:right'),'attribute'=>'style="text-align:right"'),
        );
        
        $data = array();
        
        $q = '
            select null row_num, t1.*,t1.amount - t1.allocated_amount - t1.change_amount outstanding_amount
            from sales_receipt t1
            where t1.amount - t1.allocated_amount - t1.change_amount>0
                and t1.status>0
                and t1.sales_receipt_status != "X"
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                if($thousand_separator){
                    $rs[$i]['amount'] = Tools::thousand_separator($rs[$i]['amount']);
                    $rs[$i]['outstanding_amount'] = Tools::thousand_separator($rs[$i]['outstanding_amount']);
                }
            }
            $data = $rs;
        }
        $info = array(
            'data_count'=>count($data),
            'base_href'=>(get_instance()->config->base_url().'sales_receipt/view/')            
            // end of info
        );
        
        
        $result['column'] = $column;
        $result['data'] = $data;
        $result['info'] = $info;
        return $result;
        //</editor-fold>
    }
    
    public function report_table_delivery_order_not_done($cfg=array()){
        //<editor-fold defaultstate="collapsed">        
        get_instance()->load->helper('delivery_order/delivery_order_engine');
        $result = array('column'=>array(),'data'=>array(),'info'=>array());
        $db = new DB();
        //read config
        $thousand_separator = isset($cfg['thousand_separator'])?$cfg['thousand_separator']:true;
        //end of read config
        $column = array(
            array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')),
            array("name"=>"code","label"=>"Code",'col_attrib'=>array('style'=>'text-align:left'),'is_key'=>true),
            array("name"=>"warehouse_from_name","label"=>"Warehouse From",'col_attrib'=>array('style'=>'text-align:left')),
            array("name"=>"delivery_order_date","label"=>"Delivery Order Date",'col_attrib'=>array('style'=>'text-align:left')),
            array("name"=>"delivery_order_status","label"=>"Delivery Order Status",'col_attrib'=>array('style'=>'text-align:left'),'attribute'=>'style="text-align:left"'),
        );
        
        $data = array();
        
        $q = '
            select t1.*, t3.name warehouse_from_name
            from delivery_order t1
                inner join delivery_order_warehouse_from t2 on t1.id = t2.delivery_order_id
                inner join warehouse t3 on t3.id = t2.warehouse_id
            where t1.status>0 and t1.delivery_order_status  != "done" and t1.delivery_order_status !="X"
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;                
                $rs[$i]['delivery_order_status'] = SI::get_status_attr(SI::status_get('Delivery_Order_Engine', $rs[$i]['delivery_order_status'])['label']);
            }
            $data = $rs;
        }
        $info = array(
            'data_count'=>count($data),
            'base_href'=>(get_instance()->config->base_url().'delivery_order/view/')
            
            // end of info
        );
        
        
        $result['column'] = $column;
        $result['data'] = $data;
        $result['info'] = $info;
        return $result;
        //</editor-fold>
    }
    
    public function report_table_receive_product_not_done($cfg=array()){
        //<editor-fold defaultstate="collapsed">        
        get_instance()->load->helper('receive_product/receive_product_engine');
        $result = array('column'=>array(),'data'=>array(),'info'=>array());
        $db = new DB();
        //read config
        $thousand_separator = isset($cfg['thousand_separator'])?$cfg['thousand_separator']:true;
        //end of read config
        $column = array(
            array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')),
            array("name"=>"code","label"=>"Code",'col_attrib'=>array('style'=>'text-align:left'),'is_key'=>true),
            array("name"=>"warehouse_from_name","label"=>"Warehouse From",'col_attrib'=>array('style'=>'text-align:left')),
            array("name"=>"warehouse_to_name","label"=>"Warehouse To",'col_attrib'=>array('style'=>'text-align:left')),
            array("name"=>"receive_product_date","label"=>"Receive Product Date",'col_attrib'=>array('style'=>'text-align:left')),
            array("name"=>"receive_product_status","label"=>"Receive Product Status",'col_attrib'=>array('style'=>'text-align:left'),'attribute'=>'style="text-align:left"'),
        );
        
        $data = array();
        
        $q = '
            select t1.*, t3.name warehouse_from_name, t5.name warehouse_to_name
            from receive_product t1
                inner join receive_product_warehouse_from t2 on t1.id = t2.receive_product_id
                inner join warehouse t3 on t3.id = t2.warehouse_id
                inner join receive_product_warehouse_to t4 on t1.id = t4.receive_product_id
                inner join warehouse t5 on t5.id = t4.warehouse_id
            where t1.status>0 and t1.receive_product_status  != "done" and t1.receive_product_status !="X"
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;                
                $rs[$i]['receive_product_status'] = SI::get_status_attr(SI::status_get('Receive_Product_Engine', $rs[$i]['receive_product_status'])['label']);
            }
            $data = $rs;
        }
        $info = array(
            'data_count'=>count($data),
            'base_href'=>(get_instance()->config->base_url().'receive_product/view/')
            
            // end of info
        );
        
        
        $result['column'] = $column;
        $result['data'] = $data;
        $result['info'] = $info;
        return $result;
        //</editor-fold>
    }
    
    public function report_table_purchase_invoice_outstanding_amount($cfg=array()){
        //<editor-fold defaultstate="collapsed">        
        $result = array('column'=>array(),'data'=>array(),'info'=>array());
        $db = new DB();
        //read config
        $thousand_separator = isset($cfg['thousand_separator'])?$cfg['thousand_separator']:true;
        //end of read config
        $column = array(
            array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')),
            array("name"=>"code","label"=>"Code",'col_attrib'=>array('style'=>'text-align:left'),'is_key'=>true),
            array("name"=>"purchase_invoice_date","label"=>"Sales Invoice Date",'col_attrib'=>array('style'=>'text-align:left')),
            array("name"=>"grand_total","label"=>"Grand Total",'col_attrib'=>array('style'=>'text-align:right'),'attribute'=>'style="text-align:right"'),
            array("name"=>"outstanding_amount","label"=>"Outstanding Amount",'col_attrib'=>array('style'=>'text-align:right'),'attribute'=>'style="text-align:right"'),
        );
        
        $data = array();
        
        $q = '
            select null row_num, t1.*
            from purchase_invoice t1
            where t1.outstanding_amount>0
                and t1.status>0
                and t1.purchase_invoice_status != "X"
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                if($thousand_separator){
                    $rs[$i]['grand_total'] = Tools::thousand_separator($rs[$i]['grand_total']);
                    $rs[$i]['outstanding_amount'] = Tools::thousand_separator($rs[$i]['outstanding_amount']);
                }
            }
            $data = $rs;
        }
        $info = array(
            'data_count'=>count($data),
            'base_href'=>(get_instance()->config->base_url().'purchase_invoice/view/')
            
            // end of info
        );
        
        
        $result['column'] = $column;
        $result['data'] = $data;
        $result['info'] = $info;
        return $result;
        //</editor-fold>
    }
    
    public function report_table_purchase_invoice_movement_outstanding_product_qty($cfg=array()){
        //<editor-fold defaultstate="collapsed">        
        $result = array('column'=>array(),'data'=>array(),'info'=>array());
        $db = new DB();
        //read config
        $thousand_separator = isset($cfg['thousand_separator'])?$cfg['thousand_separator']:true;
        //end of read config
        $column = array(
            array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')),
            array("name"=>"code","label"=>"Code",'col_attrib'=>array('style'=>'text-align:left'),'is_key'=>true),
            array("name"=>"purchase_invoice_date","label"=>"Sales Invoice Date",'col_attrib'=>array('style'=>'text-align:left')),
            array("name"=>"grand_total","label"=>"Grand Total",'col_attrib'=>array('style'=>'text-align:right'),'attribute'=>'style="text-align:right"'),
        );
        
        $data = array();
        
        $q = '
            select distinct null row_num, t1.*
            from purchase_invoice t1
                inner join purchase_invoice_product t2 on t1.id = t2.purchase_invoice_id
            where t1.status>0
                and t1.purchase_invoice_status = "invoiced"
                and t2.movement_outstanding_qty > 0
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                if($thousand_separator){
                    $rs[$i]['grand_total'] = Tools::thousand_separator($rs[$i]['grand_total']);
                }
            }
            $data = $rs;
        }
        $info = array(
            'data_count'=>count($data),
            'base_href'=>(get_instance()->config->base_url().'purchase_invoice/view/')
            
            // end of info
        );
        
        
        $result['column'] = $column;
        $result['data'] = $data;
        $result['info'] = $info;
        return $result;
        //</editor-fold>
    }
        
    public function report_table_product_buffer_stock_qty_mismatch($cfg=array()){
        //<editor-fold defaultstate="collapsed">        
        $result = array('column'=>array(),'data'=>array(),'info'=>array());
        $db = new DB();
        //read config
        $thousand_separator = isset($cfg['thousand_separator'])?$cfg['thousand_separator']:true;
        //end of read config
        $column = array(
            array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')),
            array("name"=>"product_code","label"=>"Product Code",'col_attrib'=>array('style'=>'text-align:left'),'is_key'=>true),
            array("name"=>"product_name","label"=>"Product Name",'col_attrib'=>array('style'=>'text-align:left')),
            array("name"=>"unit_code","label"=>"Unit",'col_attrib'=>array('style'=>'text-align:right'),'attribute'=>'style="text-align:right"'),
            array("name"=>"product_stock_qty","label"=>"Stock",'col_attrib'=>array('style'=>'text-align:right'),'attribute'=>'style="text-align:right"'),
            array("name"=>"buffer_stock_qty","label"=>"Buffer Stock",'col_attrib'=>array('style'=>'text-align:right'),'attribute'=>'style="text-align:right"'),
        );
        
        $data = array();
        
        $q = '
            select t4.code product_code
                , t1.product_id id
                , t4.name product_name
                , t5.code unit_code
                , t3.qty product_stock_qty
                ,t1.qty buffer_stock_qty

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
            where t1.qty - t3.qty > 0 
                and t4.status > 0
                and t4.product_status = "active"
                and t5.status > 0
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                if($thousand_separator){
                    $rs[$i]['product_stock_qty'] = Tools::thousand_separator($rs[$i]['product_stock_qty']);
                    $rs[$i]['buffer_stock_qty'] = Tools::thousand_separator($rs[$i]['buffer_stock_qty']);
                }
                
            }
            $data = $rs;
        }
        $info = array(
            'data_count'=>count($data),
            'base_href'=>(get_instance()->config->base_url().'product/view/')
            
            // end of info
        );
        
        
        $result['column'] = $column;
        $result['data'] = $data;
        $result['info'] = $info;
        return $result;
        //</editor-fold>
    }
}
?>