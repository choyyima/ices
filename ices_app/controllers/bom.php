<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BOM extends MY_Controller {
        
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = 'Bill of Material';
        get_instance()->load->helper('bom/bom_engine');
        $this->path = BOM_Engine::path_get();
        $this->title_icon = App_Icon::bom();
    }
    
    public function index()
    {           

        $action = "";

        $app = new App();            
        $db = $this->db;

        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower('bom'));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title','Bill of Material List')->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array(array('val'=>'New','grammar'=>'adj'),array('val'=>'Bill of Material'))))
                ->button_set('icon','fa fa-plus')->button_set('href',get_instance()->config->base_url().'bom/add');
        
        $cols = array(
            array("name"=>"code","label"=>"Code","data_type"=>"text","is_key"=>true)
            ,array("name"=>"name","label"=>"Name","data_type"=>"text")
            ,array("name"=>"total_component","label"=>"Total Component","data_type"=>"text")
            ,array("name"=>"bom_status_text","label"=>"Status","data_type"=>"text")
            

        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/bom')
                //->table_ajax_set('controls',$controls)
                ->table_ajax_set('columns',$cols);
        
        
        $app->render();
    }
    
    public function add(){
        $this->load->helper($this->path->bom_engine);
        $post = $this->input->post();        
        $this->view('','add');
        
    }
    
    public function view($id = "",$method="view"){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->bom_engine);
        $this->load->helper($this->path->bom_data_support);
        $this->load->helper($this->path->bom_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!BOM_Data_Support::bom_exists($id)){
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
            $app->set_breadcrumb($this->title,strtolower('bom'));
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            BOM_Renderer::bom_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                BOM_Renderer::bom_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
            }
            
            
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
        //</editor-fold>
    }
    
    
    public function ajax_search($method){
        //<editor-fold defaultstate="collapsed">
        $data = json_decode($this->input->post(), true);
        $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
        $result =array('success'=>1,'msg'=>[]);
        $success = 1;
        $msg = [];
        $response = array();
        $limit = 10;
        switch($method){
            case 'bom':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = $db->escape('%'.$lookup_data.'%');                
                $config = array(
                    'additional_filter'=>array(
                        
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select bom.*, count(1) bcp_total
                                from bom
                                    left outer join bom_component_product bcp on bom.id = bcp.bom_id
                                where bom.status>0
                                
                        ',
                        'where'=>'
                            and (bom.code like '.$lookup_str.'
                            )
                        ',
                        'group'=>' group by bom.id
                            )tfinal
                        ',
                        'order'=>'order by id desc'
                    ),
                );                
                $temp_result = SI::form_data()->ajax_table_search($config, $data,array('output_type'=>'object'));
                $t_data = $temp_result->data;
                foreach($t_data as $i=>$row){
                    $row->bom_status_text =
                        SI::get_status_attr(
                            SI::status_get('BOM_Engine', 
                                $row->bom_status
                            )['label']
                        );
                    $row->total_component = 0;
                    $row->total_component+= $row->bcp_total;
                    
                }
                $temp_result = json_decode(json_encode($temp_result),true);
                $result = $temp_result;
                //</editor-fold>
                break;
            case 'input_select_bom_result_product_search':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('bom/bom_data_support');
                $response = array();
                $lookup_str = isset($data['data'])?Tools::_str($data['data']):'';
                $response = BOM_Data_Support::product_search($lookup_str);
                //</editor-fold>
                break;
            case 'input_select_bom_component_product_search':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('bom/bom_data_support');
                $response = array();
                $lookup_str = isset($data['data'])?Tools::_str($data['data']):'';
                $response = BOM_Data_Support::product_search($lookup_str);
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
        get_instance()->load->helper('bom/bom_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[],'response'=>array());
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'bom_get':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $response = array();
                $q = '
                    select t1.*
                    from bom t1
                    where t1.id = '.$db->escape($data['data']).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $bom = $rs[0];
                    $bom_id = $bom['id'];
                    $bom_type = $bom['bom_type'];
                    
                    $bom['bom_type_text'] = SI::type_get('bom_engine',$bom_type)['label'];
                    $bom['bom_status_text'] = SI::get_status_attr(
                            SI::status_get('BOM_Engine',$bom['bom_status'])['label']
                        );
                    
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('bom_engine',
                            $bom['bom_status']
                        );
                    
                    $response['bom'] = $bom;
                    $response['bom_status_list'] = $next_allowed_status_list;
                    
                    switch($bom_type){
                        case 'normal':
                            //<editor-fold defaultstate="collapsed">
                            $bom_result_product = BOM_Data_Support::result_product_get($bom_id);
                            if(count($bom_result_product)>0){
                                $bom_result_product['product_text'] = SI::html_tag('strong',$bom_result_product['product_code'])
                                    .' '.$bom_result_product['product_name'];
                                $bom_result_product['unit_text'] = SI::html_tag('strong',$bom_result_product['unit_code'])
                                    .' '.$bom_result_product['unit_name'];
                                $bom_result_product['product_img'] = Product_Engine::img_get($bom_result_product['product_id']);
                            }
                            
                            $bom_component_product = BOM_Data_Support::component_product_get($bom_id);
                            if(count($bom_component_product)>0){
                                $bom_component_product = json_decode(json_encode($bom_component_product));
                                foreach($bom_component_product as $i=>$row){
                                    $row->product_text = SI::html_tag('strong',$row->product_code)
                                        .' '.$row->product_name;
                                    $row->unit_text = SI::html_tag('strong',$row->unit_code)
                                    .   ' '.$row->unit_name;
                                    $row->product_img = Product_Engine::img_get($row->product_id);
                                }
                                $bom_component_product = json_decode(json_encode($bom_component_product),true);
                            }
                            
                            
                            $response['bom_result_product'] = $bom_result_product;
                            $response['bom_component_product'] = $bom_component_product;
                            //</editor-fold>
                            break;
                    }
                    
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
    
    public function bom_add(){
        $this->load->helper($this->path->bom_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>'','method'=>'bom_add','primary_data_key'=>'bom','data_post'=>$post);            
            SI::data_submit()->submit('bom_engine',$param);
            
        }        
    }
    
    public function bom_active($id){
        $this->load->helper($this->path->bom_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'bom_active','primary_data_key'=>'bom','data_post'=>$post);
            SI::data_submit()->submit('bom_engine',$param);
        }        
    }
    
    public function bom_inactive($id){
        $this->load->helper($this->path->bom_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'bom_inactive','primary_data_key'=>'bom','data_post'=>$post);
            SI::data_submit()->submit('bom_engine',$param);
        }
        
    }
}

