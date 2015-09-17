<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rpt_Product_Engine {
    public static $prefix_id = 'rpt_product';
    public static $module_type_list;
    
    static function helper_init(){
        //<editor-fold defaultstate="collapsed">
        self::$module_type_list = array(
            array(
                'val'=>'product_stock'
                ,'label'=>'Product Stock'
                ,'type'=>'table'
                ,'tbl_col'=>array(
                    array("name"=>"warehouse_text","label"=>Lang::get("Warehouse"),"data_type"=>"text",'attribute'=>array('style'=>'width:150px')),
                    array("name"=>"product_text","label"=>Lang::get("Product"),"data_type"=>"text","is_key"=>true),
                    array("name"=>"unit_text","label"=>Lang::get("Unit"),"data_type"=>'text','attribute'=>array('style'=>'width:100px')),
                    array("name"=>"product_stock_qty","label"=>Lang::get(array("Stock")),"data_type"=>"text",'attribute'=>array('style'=>'width:150px;text-align:right'),'row_attrib'=>array('style'=>'text-align:right'),'data_format'=>array('thousand_separator')),
                    array("name"=>"product_stock_sales_available_qty","label"=>Lang::get(array("Stock Sales Available")),"data_type"=>"text",'attribute'=>array('style'=>'width:150px;text-align:right'),'row_attrib'=>array('style'=>'text-align:right'),'data_format'=>array('thousand_separator')),
                    array("name"=>"product_stock_bad_qty","label"=>Lang::get(array("Stock Bad")),"data_type"=>"text",'attribute'=>array('style'=>'width:150px;text-align:right'),'row_attrib'=>array('style'=>'text-align:right'),'data_format'=>array('thousand_separator')),
                    
                )
            ),
        );
        //</editor-fold>
    }
    
    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'rpt_product/'
            ,'rpt_product_engine'=>'rpt_product/rpt_product_engine'
            ,'rpt_product_renderer' => 'rpt_product/rpt_product_renderer'
            ,'rpt_product_data_support' => 'rpt_product/rpt_product_data_support'
            ,'ajax_search'=>get_instance()->config->base_url().'rpt_product/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'rpt_product/data_support/'
            ,'form_render'=>get_instance()->config->base_url().'rpt_product/form_render/'
        );

        return json_decode(json_encode($path));
    }

     
    



}
?>
