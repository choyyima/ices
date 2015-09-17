<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rpt_Mismatch_Product_Buffer_Stock_old extends MY_Controller {
        
    private $title='Report Mismatch Product Buffer Stock';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        get_instance()->load->helper('report/product/rpt_mismatch_product_buffer_stock_engine');
        $this->path = Rpt_Mismatch_Product_buffer_stock_Engine::path_get();
        $this->title_icon = App_Icon::report();
        
    }
    
    
    public function index()
    {           
        get_instance()->load->helper($this->path->rpt_mismatch_product_buffer_stock_engine);
        $action = "";

        $app = new App();            
        $db = $this->db;

        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower(get_class($this)));
        $app->set_content_header($this->title,$this->title_icon,$action);
        
        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title','Product List')->form_set('span','12');


        $cols = array(
            array("name"=>"product_code","label"=>"Code","data_type"=>"text")
            ,array("name"=>"product_name","label"=>"Name","data_type"=>"text")            
            ,array("name"=>"product_stock_qty","label"=>"Qty","data_type"=>"text")
            ,array("name"=>"buffer_stock_qty","label"=>"Buffer Stock","data_type"=>"text")
            ,array("name"=>"unit_name","label"=>"Unit","data_type"=>"text")
            

        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',get_instance()->config->base_url().'product/view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/rpt_product_buffer_stock')
                //->table_ajax_set('controls',$controls)
                ->table_ajax_set('columns',$cols);
        
        
        $app->render();
    }
    
    public function ajax_search($method){
        $data = json_decode($this->input->post(), true);
        $result =array();
        switch($method){
            case 'rpt_product_buffer_stock':
                $db = new DB();
                $records_page = $data['records_page'];
                $page = $data['page'];
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $q = '
                    select t4.code product_code
                        , t1.product_id id
                        , t4.name product_name
                        , t5.name unit_name
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
                    
                ';
                $q_group = ' ';
                $q_where=' and (t4.name like '.$lookup_str.' 
                        or t4.code like '.$lookup_str.' 
                        )';
                
                $extra='';
                if(strlen($data['sort_by'])>0) {$extra.=' order by '.$data['sort_by'];}
                else {$extra.=' order by t4.code asc';}
                $extra .= '  limit '.(($page-1)*$records_page).', '.($records_page);
                $q_total_row = $q.$q_where.$q_group;
                $q_data = $q.$q_where.$q_group.$extra;
                $total_rows = $db->select_count($q_total_row,null,null);
                $result = array("header"=>array("total_rows"=>$total_rows),"data"=>$db->query_array($q_data));
                break;
        }
        echo json_encode($result);
    }
    
    
}

