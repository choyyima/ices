<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Unit extends MY_Controller {
        
    private $index_url= "";
    private $title='';
    private $title_icon = 'fa fa-tags';
    
    private $path = array(
        'index'=>''
        ,'unit_engine'=>''
        ,'ajax_search'=>''
        ,'unit_renderer'=>''
        ,'unit_data_support'=>''
    );
    
    function __construct(){
        parent::__construct();
        $this->path = json_decode(json_encode($this->path));
        $this->title = Lang::get('Unit');
        $this->index_url=  get_instance()->config->base_url().'unit';
        $this->path->unit_engine =  'unit/unit_engine';
        $this->path->unit_data_support =  'unit/unit_data_support';
        $this->path->unit_renderer =  'unit/unit_renderer';
        $this->path->ajax_search=  $this->path->index.'ajax_search/'; 
        
    }
    
    public function index()
    {           
        //<editor-fold defaultstate="collapsed">
        $action = "";

        $app = new App();            
        $db = $this->db;

        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower($this->title));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title',Lang::get(array('Unit','List')))->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array('New','Unit')))
                ->button_set('icon','fa fa-plus')->button_set('href',get_instance()->config->base_url().'unit/add');
        $cols = array(
            array("name"=>"code","label"=>"Code","data_type"=>"text","is_key"=>true)
            ,array("name"=>"name","label"=>"Name","data_type"=>"text")
            ,array("name"=>"notes","label"=>"Notes","data_type"=>"text")
            

        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->index_url.'/view')
                ->table_ajax_set('lookup_url',$this->index_url.'/ajax_search/unit')
                ->table_ajax_set('columns',$cols);
        
        
        $app->render();
        //</editor-fold>
    }
    
    public function add(){
        $this->load->helper($this->path->unit_engine);
        $post = $this->input->post();        
        $this->view('','add');        
    }

    public function view($id = "",$method="view"){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->unit_engine);
        $this->load->helper($this->path->unit_renderer);
        $this->load->helper($this->path->unit_data_support);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(Unit_Data_Support::unit_get($id) == null){
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
            $app->set_breadcrumb($this->title,strtolower($this->title));
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Unit_Renderer::unit_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
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
        $result =array();
        switch($method){
            case 'unit':
                $db = new DB();
                $records_page = $data['records_page'];
                $page = $data['page'];
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $q = '
                    select *

                    from unit t1
                    where t1.status>0
                ';
                
                $q_where=' and (t1.name like '.$lookup_str.' 
                        or t1.code like '.$lookup_str.' 
                        or t1.notes like '.$lookup_str.' 
                        )';
                
                $extra='';
                if(strlen($data['sort_by'])>0) {$extra.=' order by '.$data['sort_by'];}
                else {$extra.=' order by t1.code asc';}
                $extra .= ' limit '.(($page-1)*$records_page).', '.($records_page);
                $q_total_row = $q.$q_where;
                $q_data = $q.$q_where.$extra;
                $total_rows = $db->select_count($q_total_row,null,null);
                $result = array("header"=>array("total_rows"=>$total_rows),"data"=>$db->query_array($q_data));
                break;

        }
        
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function data_support($method="",$submethod=""){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('unit/unit_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[],'response'=>array());
        $msg=[];
        $response = array();
        $success = 1;
        switch($method){
            case 'unit_get':
                get_instance()->load->helper('unit/unit_engine');
                $db = new DB();
                $result = null;
                $unit = Unit_Data_Support::unit_get($data['data']);
                
                if(count($unit)>0){
                    $response['unit'] = $unit;
                }
                
                break;
            
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    public function unit_add(){
        $this->load->helper($this->path->unit_engine);
        $post = $this->input->post();        
        die(var_dump($post));
        if($post!= null){
            $param = array('id'=>'','method'=>'unit_add','primary_data_key'=>'unit','data_post'=>$post);            
            SI::data_submit()->submit('unit_engine',$param);
        }
       
    }
    
    public function unit_update($id=''){
        $this->load->helper($this->path->unit_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'unit_update','primary_data_key'=>'unit','data_post'=>$post);
            SI::data_submit()->submit('unit_engine',$param);
        }        
    }
    
    public function unit_delete($id=''){
        $this->load->helper('unit/unit_engine');

        $post = $this->input->post();        
        
        $param = array('id'=>$id,'method'=>'unit_delete','primary_data_key'=>'unit','data_post'=>$post);
        SI::data_submit()->submit('unit_engine',$param);

        
    }
}

