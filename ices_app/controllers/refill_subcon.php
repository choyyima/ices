<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Subcon extends MY_Controller {
        
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = 'Refill - Subcontractor';
        get_instance()->load->helper('refill_subcon/refill_subcon_engine');
        $this->path = Refill_Subcon_Engine::path_get();
        $this->title_icon = App_Icon::refill_subcon();
    }
    
    public function index()
    {           

        $action = "";

        $app = new App();            
        $db = $this->db;

        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower('refill_subcon'));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title','Subcontractor List')->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array(array('val'=>'New','grammar'=>'adj'),array('val'=>'Subcontractor'))))
                ->button_set('icon','fa fa-plus')->button_set('href',get_instance()->config->base_url().'refill_subcon/add');
        
        $cols = array(
            array("name"=>"code","label"=>"Code","data_type"=>"text","is_key"=>true)
            ,array("name"=>"name","label"=>"Name","data_type"=>"text")
            ,array("name"=>"notes","label"=>"Notes","data_type"=>"text")
            ,array("name"=>"refill_subcon_status_text","label"=>"Status","data_type"=>"text")

        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/refill_subcon')
                //->table_ajax_set('controls',$controls)
                ->table_ajax_set('columns',$cols);
        
        
        $app->render();
    }
    
    public function add(){
        $this->load->helper($this->path->refill_subcon_engine);
        $post = $this->input->post();        
        $this->view('','add');
        
    }
    
    public function view($id = "",$method="view"){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->refill_subcon_engine);
        $this->load->helper($this->path->refill_subcon_data_support);
        $this->load->helper($this->path->refill_subcon_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Refill_Subcon_Data_Support::refill_subcon_exists($id)){
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
            $app->set_breadcrumb($this->title,strtolower('refill_subcon'));
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Refill_Subcon_Renderer::refill_subcon_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Refill_Subcon_Renderer::refill_subcon_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
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
            case 'refill_subcon':
                $db = new DB();
                $records_page = $data['records_page'];
                $page = $data['page'];
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $q = '
                    select *
                    from refill_subcon t1
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
                for($i = 0;$i<count($result['data']);$i++){
                    $result['data'][$i]['refill_subcon_status_text'] = SI::get_status_attr(
                        SI::status_get('refill_subcon_engine', $result['data'][$i]['refill_subcon_status'])['label']);
                }
                break;
        }
        
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function data_support($method="",$submethod=""){
        //<editor-fold defaultstate="collapsed">
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[],'response'=>array());
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'refill_subcon_get':
                $db = new DB();
                $response = array();
                $q = '
                    select t1.*
                    from refill_subcon t1
                    where t1.id = '.$db->escape($data['data']).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $refill_subcon = $rs[0];
                    
                    $refill_subcon['refill_subcon_status_text'] = SI::get_status_attr(
                            SI::status_get('Refill_Subcon_Engine',$refill_subcon['refill_subcon_status'])['label']
                        );
                    
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('refill_subcon_engine',
                            $refill_subcon['refill_subcon_status']
                        );
                    
                    $response['refill_subcon'] = $refill_subcon;
                    $response['refill_subcon_status_list'] = $next_allowed_status_list;
                }
                
                
                
                break;

        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function refill_subcon_add(){
        $this->load->helper($this->path->refill_subcon_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>'','method'=>'refill_subcon_add','primary_data_key'=>'refill_subcon','data_post'=>$post);            
            SI::data_submit()->submit('refill_subcon_engine',$param);
            
        }        
    }
    
    public function refill_subcon_active($id){
        $this->load->helper($this->path->refill_subcon_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'refill_subcon_active','primary_data_key'=>'refill_subcon','data_post'=>$post);
            SI::data_submit()->submit('refill_subcon_engine',$param);
        }        
    }
    
    public function refill_subcon_inactive($id){
        $this->load->helper($this->path->refill_subcon_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'refill_subcon_inactive','primary_data_key'=>'refill_subcon','data_post'=>$post);
            SI::data_submit()->submit('refill_subcon_engine',$param);
        }
        
    }
}

