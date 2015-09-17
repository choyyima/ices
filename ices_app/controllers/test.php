<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



class test extends MY_Extended_Controller {
        
    private $index_url= "";
    
    function __construct(){
        parent::__construct();
        $this->index_url=  get_instance()->config->base_url().'test';
    }
    
    function insert(){
        
    }
    
    function index($data=''){
        if(file_exists('pdf_file/zzzzz.pdf')){
            unlink('pdf_file/zzzzz.pdf');
        }
        
    }
        
    function product_init($fill_product = 1){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('product_stock_engine');
        $db = new DB();
        
        $db->query('delete from product_stock');
        $db->query('delete from product_stock_history');
        $db->query('delete from product_stock_sales_available');
        $db->query('delete from product_stock_sales_available_history');
        $db->query('delete from product_stock_bad');
        $db->query('delete from product_stock_bad_history');
        
        if($fill_product === 1){
            $q = '
                select t1.product_id, t1.unit_id
                from product_unit t1
                    inner join product t2 on t1.product_id = t2.id
                    inner join unit t3 on t1.unit_id = t3.id
            ';
            $product_arr = $db->query_array($q);

            $warehouse_arr = Warehouse_Engine::BOS_get();

            foreach($product_arr as $p_idx=>$p){
                $product_id = $p['product_id'];
                $unit_id = $p['unit_id'];            
                foreach($warehouse_arr as $w_idx=>$w){
                    $qty = (int)rand(100,150);
                    $warehouse_id = $w['id'];
                    Product_Stock_Engine::stock_good_add(
                        $db,
                        $warehouse_id,
                        $product_id,
                        $qty,
                        $unit_id,
                        'TEST INIT',
                        Date('Y-m-d H:i:s')
                    );
                }
            }
        }
        echo 'done';
        //</editor-fold>
    }

}

