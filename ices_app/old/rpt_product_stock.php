<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rpt_Product_Stock extends MY_Controller {

    
    
    private $title='Report Product Stock';
    private $title_icon = 'fa fa-bar-chart-o';
    private $path = array(
        'index'=>''
        ,'rpt_product_stock_engine'=>''
        ,'ajax_search'=>''
        ,'rpt_product_stock_js'=>''
        ,'product_view'=>''
    );
    
    function __construct(){
        parent::__construct();
        $this->path = json_decode(json_encode($this->path));
        $this->path->index=  get_instance()->config->base_url().'rpt_product_stock/';
        $this->path->rpt_product_stock_engine=  'report/product_stock_engine';
        
        $this->path->ajax_search=  $this->path->index.'ajax_search/';
        $this->path->product_view = get_instance()->config->base_url().'product/view/';
        
    }

    public function index()
    {           

        $action = "";

        $app = new App();            
        $db = new DB();

        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower(get_class($this)));
        $app->set_content_header($this->title,$this->title_icon,$action);
        
        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title','Product List')->form_set('span','12');

        $q = '
            select "" id, "All" data 
            union 
            select t1.id , t1.name 
            from warehouse t1 
                inner join warehouse_type t2 on t1.warehouse_type_id = t2.id
            where t1.status>0 and t2.code = "BOS"
            
        ';
        $warehouse_list = $db->query_array($q);
        
        $form->input_select_add()
                ->input_select_set('label','Warehouse')
                ->input_select_set('icon',App_Icon::warehouse())
                ->input_select_set('min_length','0')
                ->input_select_set('id','warehouse')
                ->input_select_set('data_add',$warehouse_list)
                ->input_select_set('value',array('id'=>'','data'=>'All'))
                ;
        $js = '
                $("#warehouse").on("change",function(){
                    ajax_table.methods.data_show(1);
                });
                $("#warehouse").select2("data",{id:"",text:"All"}).change();
                
            ';
        $app->js_set($js);
        
        
        $cols = array(
            array("name"=>"warehouse_name","label"=>"Warehouse","data_type"=>"text")
            ,array("name"=>"product_code","label"=>"Code","data_type"=>"text","is_key"=>true)            
            ,array("name"=>"product_qty","label"=>"Qty","data_type"=>"text")            
            ,array("name"=>"unit_name","label"=>"Unit","data_type"=>"text")

        );
        
        $tbl = $form->form_group_add()->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',get_instance()->config->base_url().'product/view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/product_stock_warehouse')
                ->table_ajax_set('class','table dataTable fixed-table')
                ->table_ajax_set('columns',$cols)
                ->table_ajax_set('key_column','product_id')
                ->filter_set(array(array('id'=>'warehouse','field'=>'warehouse_id')))
        ;
        $app->render();
    }
    
    
    public function ajax_search($method){
        $data = json_decode($this->input->post(), true);
        $result =array();
        switch($method){
            case 'product_stock_warehouse':
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');                
                $config = array(
                    'additional_filter'=>array(
                        array('key'=>'warehouse_id','query'=>' and t3.id = '),
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select t2.code product_code
                                    , t3.name warehouse_name
                                    , coalesce(t1.qty,0) product_qty
                                    , pu.product_id product_id
                                    , t4.name unit_name
                                from product_unit pu
                                    cross join warehouse t3 
                                    inner join warehouse_type t5 
                                        on t3.warehouse_type_id = t5.id and t5.code = "BOS"
                                    inner join product t2 on pu.product_id = t2.id 
                                        and t2.product_status = "active"
                                    inner join unit t4 on t4.id = pu.unit_id
                                    left outer join product_stock t1 
                                        on pu.product_id = t1.product_id and pu.unit_id = t1.unit_id 
                                        and t3.id = t1.warehouse_id
                                    
                                where 1 = 1 and t3.status>0
                        ',
                        'where'=>'
                            and (
                                t2.code like '.$lookup_str.'

                            )
                        ',
                        'group'=>'
                            )tfinal
                        ',
                        'order'=>'order by product_code asc, warehouse_name asc'
                    ),
                );                
                $temp_result = SI::form_data()->ajax_table_search($config, $data);
                for($i = 0;$i<count($temp_result['data']);$i++){
                    $temp_result['data'][$i]['product_qty'] = Tools::thousand_separator($temp_result['data'][$i]['product_qty']);
                }
                $result = $temp_result;
                break;

        }
        
        echo json_encode($result);
    }
}

