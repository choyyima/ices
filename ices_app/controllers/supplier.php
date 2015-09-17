<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Supplier extends MY_Controller {
        
    private $index_url= "";
    private $title='Supplier';
    private $title_icon = 'fa fa-user';
    private $path = array(
        'index'=>''
        ,'supplier_engine'=>''
        ,'ajax_search'=>''
        ,'supplier_data_support'=>''
        ,'supplier_renderer'=>''
    );
    
    function __construct(){
        parent::__construct();
        $this->path = json_decode(json_encode($this->path));
        $this->path->index=  get_instance()->config->base_url().'supplier/';
        $this->path->supplier_engine=  'supplier/supplier_engine';
        $this->path->supplier_data_support=  'supplier/supplier_data_support';
        $this->path->supplier_renderer=  'supplier/supplier_renderer';
        $this->path->ajax_search=  $this->path->index.'ajax_search/'; 
        $this->index_url=  get_instance()->config->base_url().'supplier';
    }
    
    public function index()
    {           
        //<editor-fold defaultstate="collpased">
        $action = "";

        $app = new App();            
        $db = $this->db;

        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower($this->title));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title',Lang::get(array('Supplier','List')))->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array('New','Supplier')))
                ->button_set('icon','fa fa-plus')->button_set('href',get_instance()->config->base_url().'supplier/add');
        
        $cols = array(
            array("name"=>"code","label"=>"Code","data_type"=>"text","is_key"=>true)
            ,array("name"=>"name","label"=>"Name","data_type"=>"text")
            ,array("name"=>"notes","label"=>"Notes","data_type"=>"text")
            ,array("name"=>"supplier_status_name","label"=>"Status","data_type"=>"text")

        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->index_url.'/view')
                ->table_ajax_set('lookup_url',$this->index_url.'/ajax_search/supplier')
                //->table_ajax_set('controls',$controls)
                ->table_ajax_set('columns',$cols);
        
        
        $app->render();
        //</editor-fold>
    }
    
    public function add(){
        $this->load->helper($this->path->supplier_engine);
        $post = $this->input->post();
        $this->view('','add');
    }
    
    
    public function view($id = "",$method="view"){
        
        $this->load->helper($this->path->supplier_engine);
        $this->load->helper($this->path->supplier_renderer);
        $this->load->helper($this->path->supplier_data_support);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(Supplier_Data_Support::supplier_get($id) == null){
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
            Supplier_Renderer::supplier_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
            }
            
            
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
        
    }
    
    
    public function ajax_search($method){
        //<editor-fold defaultstate="collapsed">
        $data = json_decode($this->input->post(), true);
        $result =array();
        switch($method){
            case 'supplier':
                $db = new DB();
                $records_page = $data['records_page'];
                $page = $data['page'];
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $q = '
                    select *
                        ,case supplier_status when "A" then "ACTIVE" when "I" then "INACTIVE" end supplier_status_name
                    from supplier t1
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
                
            case 'supplier_ajax_get':
                $db = new DB();
                $result = null;
                $q = '
                    select *
                    , case supplier_status when "A" then "ACTIVE"
                        when "I" then "INACTIVE" end supplier_status_name
                    from supplier
                    where id = '.$db->escape($data['data']).'
                ';
                $rs = $db->query_array_obj($q);
                if(count($rs)>0) $result = $rs[0];
                
                
                break;

        }
        
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function data_support($method="",$submethod=""){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('supplier/supplier_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[],'response'=>array());
        $msg=[];
        $response = array();
        $success = 1;
        switch($method){
            case 'supplier_get':
                get_instance()->load->helper('supplier/supplier_engine');
                $db = new DB();
                $result = null;
                $supplier = Supplier_Data_Support::supplier_get($data['data']);
                
                if(count($supplier)>0){
                    $supplier['supplier_status_text'] = SI::status_get('supplier_engine', $supplier['supplier_status'])['label'];
                    
                    $response['supplier'] = $supplier;
                                        
                    $next_allowed_status_list = SI::form_data()
                    ->status_next_allowed_status_list_get('supplier_engine',
                        $supplier['supplier_status']
                    );
                    $response['supplier_status_list'] = $next_allowed_status_list;                    
                    
                }
                
                break;
            
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function supplier_add(){
        $this->load->helper($this->path->supplier_engine);
        $post = $this->input->post();        
        
        if($post!= null){
            $param = array('id'=>'','method'=>'supplier_add','primary_data_key'=>'supplier','data_post'=>$post);            
            SI::data_submit()->submit('supplier_engine',$param);
        }
       
    }
    
    public function supplier_active($id=''){
        $this->load->helper($this->path->supplier_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'supplier_active','primary_data_key'=>'supplier','data_post'=>$post);
            SI::data_submit()->submit('supplier_engine',$param);
        }        
    }
    
    public function supplier_inactive($id=''){
        $this->load->helper($this->path->supplier_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'supplier_inactive','primary_data_key'=>'supplier','data_post'=>$post);
            SI::data_submit()->submit('supplier_engine',$param);
        }
        
    }
}

