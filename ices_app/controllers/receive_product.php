<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Receive_Product extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get('Receive Product');
        get_instance()->load->helper('receive_product/receive_product_engine');
        $this->path = Receive_Product_Engine::path_get();
        $this->title_icon = App_Icon::receive_product();
        
    }
    
    public function index(){           
        //<editor-fold defaultstate="collapsed">
        $action = "";

        $app = new App();            
        $db = $this->db;

        
        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower($this->title));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title',Lang::get('Receive Product List'))->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get('New Receive Product'))
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        
        $reference_type_list = array(
            array('value'=>'','label'=>'ALL')
        );
        $module_list = SI::type_list_get('Receive_Product_Engine');
        foreach($module_list as $module_idx=>$module){
            $reference_type_list[] = array('value'=>$module['val'],'label'=>$module['label']);
        }
        
        $form->select_add()
                ->select_set('id','reference_type_filter')
                ->select_set('options_add',$reference_type_list)
                ;
        
        $cols = array(
            array("name"=>"code","label"=>Lang::get("Receive Product Code"),"data_type"=>"text","is_key"=>true),
            array("name"=>"receive_product_type","label"=>Lang::get("Type"),"data_type"=>"text"),
            array("name"=>"receive_product_date","label"=>Lang::get("Receive Product Date"),"data_type"=>"text"),
            array("name"=>"receive_product_status","label"=>Lang::get("Status"),"data_type"=>"text"),
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/receive_product')
                ->table_ajax_set('columns',$cols)
                ->table_ajax_set('key_column','id')
                ->filter_set(array(
                        array('id'=>'reference_type_filter','field'=>'reference_type')
                    ))
                ;        
        $js = ' $("#reference_type_filter").on("change",function(){
                    ajax_table.methods.data_show(1);
                }); 
            ';
        $app->js_set($js);
        $app->render();
        //</editor-fold>
    }
    
    public function add(){
        $this->load->helper($this->path->receive_product_engine);
        $post = $this->input->post();
        
        $this->view('','add');
        
    }
    
    public function view($id="",$method="view"){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->receive_product_engine);
        $this->load->helper($this->path->receive_product_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Receive_Product_Engine::receive_product_exists($id)){
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
            //$app->set_menu('collapsed',false);
            $app->set_title($this->title);
            $app->set_breadcrumb($this->title,'receive_product');
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row')->div_set('id','receive_product');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Receive_Product_Renderer::receive_product_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Receive_Product_Renderer::receive_product_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
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
        get_instance()->load->helper('receive_product/receive_product_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[],'response'=>array());
        $success = 1;
        $response = array();
        $msg = [];
        $limit = 10;
        switch($method){
            
            case 'receive_product':
                // <editor-fold defaultstate="collapsed">

                $db = new DB();
                $lookup_str = $db->escape('%' . $data['data'] . '%');

                $config = array(
                    'reference_type' => array(
                        array(
                            'val' => 'purchase_invoice',
                            'query' => 'and t1.delivery_order_type = "purchase_invoice"'
                        ),
                    ),
                    'query' => array(
                        'basic' => '
                            select * from (
                                select distinct t1.*
                                from receive_product t1                    
                                where t1.status>0
                        ',
                        'where' => '
                            and (t1.code like ' . $lookup_str . '
                            )
                        ',
                        'group' => '
                            )tfinal
                        ',
                        'order' => 'order by code desc'
                    ),
                );

                $temp_result = SI::form_data()->ajax_table_search($config, $data);
                for ($i = 0; $i < count($temp_result['data']); $i++) {
                    $temp_result['data'][$i]['receive_product_status'] = SI::get_status_attr(
                            SI::status_get('Receive_Product_Engine', 
                        $temp_result['data'][$i]['receive_product_status']
                            )['label']
                    );
                    $temp_result['data'][$i]['receive_product_type'] =  SI::type_get('Receive_Product_Engine',                             $temp_result['data'][$i]['receive_product_type']
                            )['label'];
                }
                $result = $temp_result;
                // </editor-fold>

                break;
                
            case 'input_select_reference_search':
                //<editor-fold defaultstate="collapsed">
                $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
                $response = Receive_Product_Data_Support::reference_search($lookup_data);
                //</editor-fold>
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

        get_instance()->load->helper('delivery_order/delivery_order_engine');
        get_instance()->load->helper('receive_product/receive_product_data_support');
        get_instance()->load->helper('product/product_engine');
        get_instance()->load->helper('product_stock_engine');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'reference_dependency_data_get':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('product/product_engine');
                $response = array();
                $reference_id = isset($data['reference_id'])?Tools::_str($data['reference_id']):'';
                $reference_type = isset($data['reference_type'])?Tools::_str($data['reference_type']):'';
                
                $reference_detail = Receive_Product_Data_Support::reference_detail_get($reference_type,$reference_id);
                $warehouse_from = Receive_Product_Data_Support::warehouse_from_list_get($reference_type);
                $warehouse_to = Receive_Product_Data_Support::warehouse_to_list_get($reference_type);
                $product = Receive_Product_Data_Support::reference_product_list_get($reference_type,$reference_id);
                for($i = 0;$i<count($product);$i++){
                    $product[$i]['ordered_qty'] = $product[$i]['ordered_qty'];
                    $product[$i]['outstanding_qty'] = $product[$i]['outstanding_qty'];
                }
                
                $response['reference_detail'] = $reference_detail;
                $response['warehouse_from'] = $warehouse_from;
                $response['warehouse_to'] = $warehouse_to;
                $response['product'] = $product;
                //</editor-fold>
                break;
            case 'warehouse_to_detail_get':
                //<editor-fold>
                $warehouse_id = isset($data['warehouse_id'])?Tools::_str($data['warehouse_id']):'';
                $db = new DB();
                $warehouse = Warehouse_Engine::warehouse_get($warehouse_id);
                $warehouse_detail =array();
                if(count($warehouse)>0){
                    $warehouse_detail = array(
                        array('id'=>'type','label'=>'Type: ','val'=>$warehouse['warehouse_type_name']),
                        array('id'=>'address','label'=>'Address: ','val'=>$warehouse['address']),
                    );
                }
                $response['warehouse_detail'] = $warehouse_detail;
                //</editor-fold>
                break;
            
            case 'receive_product_get':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('product/product_engine');
                
                $response =array();
                $db = new DB();
                $receive_product_id = $data['data'];
                $q = '
                    select t1.*,
                        t2.code store_code,
                        t2.name store_name                        
                    from receive_product t1
                        inner join store t2 on t1.store_id = t2.id                        
                    where t1.id = '.$db->escape($receive_product_id).'
                ';
                $rs = $db->query_array($q);

                if(count($rs)>0){
                    $receive_product = $rs[0];
                    $receive_product_type = $receive_product['receive_product_type'];

                    $receive_product_product = array();
                    $warehouse_from = array();
                    $warehouse_to = array();
                    $reference_raw = Receive_Product_Data_Support::reference_get($receive_product_id);
                    $reference = array(
                        'id'=>$reference_raw['id'],
                        'text'=>SI::html_tag('strong',$reference_raw['code']),
                        'reference_type'=>$receive_product['receive_product_type'],
                    );
                    $reference_detail = Receive_Product_Data_Support::reference_detail_get(
                            $receive_product_type, $reference['id']);
                    
                    $receive_product['receive_product_date'] = Tools::_date($receive_product['receive_product_date'],'F d, Y H:i');
                    $receive_product['store_text'] = SI::html_tag('strong',$receive_product['store_code'])
                        .' '.$receive_product['store_name'];
                    $receive_product['receive_product_status_text'] = SI::get_status_attr(
                            SI::status_get('Receive_Product_Engine',$receive_product['receive_product_status'])['label']
                        );
                    
                    $warehouse_from_raw = Receive_Product_Data_Support::warehouse_from_get($receive_product_id);
                    $warehouse_from = array(
                        'id'=>$warehouse_from_raw['id'],
                        'text' => SI::html_tag('strong',$warehouse_from_raw['code']).' '.$warehouse_from_raw['name']
                    );
                    $warehouse_from_detail = Receive_Product_Data_Support::warehouse_detail_get($warehouse_from['id']);
                    
                    $warehouse_to_raw = Receive_Product_Data_Support::warehouse_to_get($receive_product_id);
                    $warehouse_to = array(
                        'id'=>$warehouse_to_raw['id'],
                        'text' => SI::html_tag('strong',$warehouse_to_raw['code']).' '.$warehouse_to_raw['name']
                    );
                    $warehouse_to_detail = Receive_Product_Data_Support::warehouse_detail_get($warehouse_to['id']);
                    
                    
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('Receive_Product_Engine',
                            $receive_product['receive_product_status']
                        );
                    $receive_product_product = Receive_Product_Data_Support::receive_product_product_get($receive_product_id);
                    
                    $response['receive_product'] = $receive_product;
                    $response['warehouse_from'] = $warehouse_from;
                    $response['warehouse_from_detail'] = $warehouse_from_detail;
                    $response['warehouse_to'] = $warehouse_to;                 
                    $response['warehouse_to_detail'] = $warehouse_to_detail;
                    $response['receive_product_product'] = $receive_product_product;
                    $response['receive_product_status_list'] = $next_allowed_status_list;
                    $response['reference'] = $reference;
                    $response['reference_detail'] = $reference_detail;
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
    
    public function receive_product_add(){
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>'','method'=>'receive_product_add','primary_data_key'=>'receive_product','data_post'=>$post);            
            SI::data_submit()->submit('receive_product_engine',$param);            
        }
        
    }
    
    public function receive_product_process($id){
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'receive_product_process','primary_data_key'=>'receive_product','data_post'=>$post);            
            SI::data_submit()->submit('receive_product_engine',$param);            
        }
    }
    
    public function receive_product_done($id){
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'receive_product_done','primary_data_key'=>'receive_product','data_post'=>$post);            
            SI::data_submit()->submit('receive_product_engine',$param);            
        }
    }
    
    public function receive_product_canceled($id){
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'receive_product_canceled','primary_data_key'=>'receive_product','data_post'=>$post);            
            SI::data_submit()->submit('receive_product_engine',$param);            
        }
    }
}

?>