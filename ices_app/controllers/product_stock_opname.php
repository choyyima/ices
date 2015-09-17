<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product_Stock_Opname extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get(array('Product Stock Opname'),true,true,false,false,true);
        get_instance()->load->helper('product_stock_opname/product_stock_opname_engine');
        $this->path = Product_Stock_Opname_Engine::path_get();
        $this->title_icon = App_Icon::product_stock_opname();
        
    }
    
    public function index()
    {           
        $action = "";

        $app = new App();            
        $db = $this->db;

        
        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower($this->title));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title',Lang::get(array('Product Stock Opname','List')))->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array('New','Product Stock Opname')))
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        
        $form->form_group_add();
        $cols = array(
            array("name"=>"code","label"=>Lang::get("Code"),"data_type"=>"text","is_key"=>true),
            array("name"=>"product_stock_opname_date","label"=>Lang::get(array("Product Stock Opname","Date")),"data_type"=>"text"),            
            array("name"=>"product_stock_opname_status_text","label"=>Lang::get(array("Status")),"data_type"=>"text"),            
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/product_stock_opname')
                ->table_ajax_set('columns',$cols)
                ->table_ajax_set('key_column','id')
                ->filter_set(array(
                        array('id'=>'reference_type_filter','field'=>'reference_type')
                    ))
                ;        
        
        
        $app->render();
    }
    
    
    public function add(){
        
        $this->load->helper($this->path->product_stock_opname_engine);
        $post = $this->input->post();
        
        $this->view('','add');
        
    }
    
    
    public function view($id="",$method="view"){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->product_stock_opname_engine);
        $this->load->helper($this->path->product_stock_opname_data_support);
        $this->load->helper($this->path->product_stock_opname_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(is_null(Product_Stock_Opname_Data_support::pso_get($id))){
                    Message::set('error',array("Data doesn't exist"));
                    $cont = false;
                }
            }
        }
        
        if($cont){
        
            if($method=='add') $id = '';
            $data = array(
                'id'=>$id
            );
            
            $app = new App();    
            $app->set_title($this->title);
            $app->set_menu('collapsed',false);
            $app->set_breadcrumb($this->title,'product_stock_opname');
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row')->div_set('id','product_stock_opname');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Product_Stock_Opname_Renderer::product_stock_opname_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Product_Stock_Opname_Renderer::product_stock_opname_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
                
            }            
            
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
        //</editor-fold>
    }
    
    public function ajax_search($method="",$submethod=""){
        //<editor-fold defaultstate="collapsed">
        $data = json_decode($this->input->post(), true);
        $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
        $result =array('success'=>1,'msg'=>[]);
        $success = 1;
        $msg = [];
        $response = array();
        $limit = 10;
        switch($method){            
            case 'product_stock_opname':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = $db->escape('%'.$lookup_data.'%');                
                $config = array(
                    'additional_filter'=>array(
                        
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select distinct rcrf.*
                                from product_stock_opname rcrf
                                where rcrf.status>0
                        ',
                        'where'=>'
                            and (rcrf.code like '.$lookup_str.'
                            )
                        ',
                        'group'=>'
                            )tfinal
                        ',
                        'order'=>'order by id desc'
                    ),
                );                
                $temp_result = SI::form_data()->ajax_table_search($config, $data);
                for($i = 0;$i<count($temp_result['data']);$i++){
                    $temp_result['data'][$i]['product_stock_opname_status_text'] =
                        SI::get_status_attr(
                            SI::status_get('Product_Stock_Opname_Engine', 
                                $temp_result['data'][$i]['product_stock_opname_status']
                            )['label']
                        );
                    
                }
                $result = $temp_result;
                //</editor-fold>
                break;
            
            case 'input_select_product_search':
                $response = array();        
                get_instance()->load->helper('product_stock_opname/product_stock_opname_data_support');
                $response = Product_Stock_Opname_Data_Support::product_search($lookup_data);
                
                
                break;
            
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        
        //</editor-fold>
    }
    
    public function data_support($method="",$submethod=""){
        //<editor-fold defaultstate="collapsed">
        //this function only used for urgently data retrieve
        get_instance()->load->helper('product_stock_opname/product_stock_opname_engine');
        get_instance()->load->helper('product_stock_opname/product_stock_opname_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'product_stock_opname_get':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('product/product_data_support');
                $response =array();
                $db = new DB();
                $pso_id = $data['data'];
                $q = '
                    select t1.*,
                        t2.code store_code,
                        t2.name store_name,
                        w.code warehouse_code,
                        w.name warehouse_name
                    from product_stock_opname t1
                        inner join store t2 on t1.store_id = t2.id
                        inner join warehouse w on t1.warehouse_id = w.id
                    where t1.id = '.$db->escape($pso_id).'
                ';
                $rs = $db->query_array($q);

                if(count($rs)>0){
                    $pso = $rs[0];                    
                    $pso['product_stock_opname_date'] = Tools::_date($pso['product_stock_opname_date'],'F d, Y H:i');
                    $pso['store_text'] = SI::html_tag('strong',$pso['store_code'])
                        .' '.$pso['store_name'];
                    $pso['product_stock_opname_status_text'] = SI::get_status_attr(
                            SI::status_get('Product_Stock_Opname_Engine',$pso['product_stock_opname_status'])['label']
                        );
                    $pso['warehouse_text'] = SI::html_tag('strong',$pso['warehouse_code'])
                        .' '.$pso['warehouse_name'];
                    $pso_product = Product_Stock_Opname_Data_Support::pso_product_get($pso['id']);
                    for($i = 0;$i<count($pso_product);$i++){
                        //<editor-fold defaultstate="collapsed" desc="Product">
                        
                        //</editor-fold>
                    }
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('Product_Stock_Opname_Engine',
                            $pso['product_stock_opname_status']
                        );
                    
                    
                    $response['pso'] = $pso;
                    $response['pso_product'] = $pso_product;
                    $response['product_stock_opname_status_list'] = $next_allowed_status_list;
                }
                //</editor-fold>
                break;
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function pso_add(){
        // <editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->product_stock_opname_engine);
        $post = $this->input->post();
        if ($post != null) {
            $param = array('id' => '', 'method' => 'pso_add',
                'primary_data_key' => 'pso',
                'data_post' => $post,
                'last_func'=>false
            );
            $temp_result = SI::data_submit()->submit('product_stock_opname_engine', $param);
            
            if($temp_result['success'] === 1){
                Product_Stock_Opname_Engine::pso_mail();
            }
        }
        // </editor-fold>
    }
    
    public function pso_process($id=''){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->product_stock_opname_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'pso_process',
                'primary_data_key'=>'pso',
                'data_post'=>$post
            );
            SI::data_submit()->submit('product_stock_opname_engine',$param);

        }
        //</editor-fold>
    }
    
    public function pso_finalized($id=''){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->product_stock_opname_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'pso_finalized',
                'primary_data_key'=>'pso',
                'data_post'=>$post,
                'last_func'=>false
            );
            $temp_result = SI::data_submit()->submit('product_stock_opname_engine',$param);
            
            if($temp_result['success'] === 1){
                Product_Stock_Opname_Engine::pso_mail();
            }
        }
        //</editor-fold>
    }
    
    public function pso_print($module='',$id=''){
        
        $this->load->helper($this->path->product_stock_opname_print);
        $post = $this->input->post();
        switch($module){
            case 'product_stock_opname':
                Product_Stock_Opname_Print::pso_print($id,array('p_output'=>true));
                
                break;
        }
    
    }
    
}

?>