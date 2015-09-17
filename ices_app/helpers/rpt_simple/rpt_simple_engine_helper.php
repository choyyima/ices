<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rpt_Simple_Engine {

    public static $module_list;
    
    public static function path_get(){
        //<editor-fold defaultstate="collapsed">
        $path = array(
            'index'=>get_instance()->config->base_url().'rpt_simple/'
            ,'rpt_simple_engine'=>'rpt_simple/rpt_simple_engine'
            ,'rpt_simple_renderer' => 'rpt_simple/rpt_simple_renderer'
            ,'rpt_simple_data_support' => 'rpt_simple/rpt_simple_data_support'
            ,'ajax_search'=>get_instance()->config->base_url().'rpt_simple/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'rpt_simple/data_support/'

        );

        return json_decode(json_encode($path));
        //</editor-fold>
    }

    public static function helper_init(){
        //<editor-fold defaultstate="collapsed">
        self::$module_list = array(
            array(
                'name'=>array('val'=>'delivery_order_final','label'=>Lang::get('Delivery Order Final')),
                'condition'=>array(
                    array('val'=>'not_done','label'=>Lang::get('Not Done',true)),
                    array('val'=>'not_confirmed','label'=>Lang::get('Not Confirmed Yet',true)),
                ),
            ),  
            array(
                'name'=>array('val'=>'delivery_order','label'=>Lang::get('Delivery Order')),
                'condition'=>array(
                    array('val'=>'not_done','label'=>Lang::get('Not Done Yet',true)),
                ),
            ),
            array(
                'name'=>array('val'=>'intake','label'=>Lang::get(array('Product Intake'))),
                'condition'=>array(
                    array('val'=>'not_done','label'=>Lang::get('Not Done Yet',true)),
                ),
            ),
            array(
                'name'=>array('val'=>'receive_product','label'=>'Receive Product'),
                'condition'=>array(
                    array('val'=>'not_done','label'=>'Not Done'),
                ),
            ),
            array(
                'name'=>array('val'=>'product','label'=>'Product'),
                'condition'=>array(
                    array('val'=>'buffer_stock_qty_mismatch','label'=>'Mismatch Buffer Stock Qty'),
                ),
            ),
            array(
                'name'=>array('val'=>'purchase_invoice','label'=>'Purchase Invoice'),
                'condition'=>array(
                    array('val'=>'outstanding_amount','label'=>'Outstanding Amount'),
                    array('val'=>'movement_outstanding_product_qty','label'=>'Outstanding Product Qty'),
                ),
            ),            
            array(
                'name'=>array('val'=>'sales_pos','label'=>'Sales POS'),
                'condition'=>array(
                    array('val'=>'outstanding_amount','label'=>'Outstanding Amount'),
                    array('val'=>'movement_outstanding_product_qty','label'=>'Indent Product'),
                    array('val'=>'movement_outstanding_product_qty_detail','label'=>'Indent Product Detail'),
                ),
            ),
            array(
                'name'=>array('val'=>'sales_receipt','label'=>'Sales Receipt'),
                'condition'=>array(
                    array('val'=>'outstanding_amount','label'=>Lang::get('Unallocated Receipt')),
                ),
            ),
            array(
                'name'=>array('val'=>'customer_bill','label'=>'Customer Bill'),
                'condition'=>array(
                    array('val'=>'outstanding_amount','label'=>'Outstanding Amount'),
                ),
            ),
            array(
                'name'=>array('val'=>'refill_invoice','label'=>'Refill Invoice'),
                'condition'=>array(
                    array('val'=>'outstanding_amount','label'=>'Outstanding Amount'),
                ),
            ),
            array(
                'name'=>array('val'=>'refill_receipt','label'=>'Refill Receipt'),
                'condition'=>array(
                    array('val'=>'outstanding_amount','label'=>Lang::get('Unallocated Receipt')),
                ),
            ),
            array(
                'name'=>array('val'=>'customer_payment','label'=>Lang::get('Customer Payment')),
                'condition'=>array(
                    array('val'=>'deposit_date_null','label'=>Lang::get('Has not been deposited')),
                ),
            ),
        
        );
        //</editor-fold>
    }



}
?>
