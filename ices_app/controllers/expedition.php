<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Expedition extends MY_Controller {
        
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = 'Expedition';
        get_instance()->load->helper('expedition/expedition_engine');
        $this->path = Expedition_Engine::path_get();
        $this->title_icon = App_Icon::expedition();
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
        $form = $row->form_add()->form_set('title','Expedition List')->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value','New Expedition')
                ->button_set('icon','fa fa-plus')->button_set('href',get_instance()->config->base_url().'expedition/add');
        
        $cols = array(
            array("name"=>"code","label"=>"Code","data_type"=>"text","is_key"=>true)
            ,array("name"=>"name","label"=>"Name","data_type"=>"text")
            ,array("name"=>"notes","label"=>"Notes","data_type"=>"text")
            ,array("name"=>"expedition_status_name","label"=>"Status","data_type"=>"text")

        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/expedition')
                //->table_ajax_set('controls',$controls)
                ->table_ajax_set('columns',$cols);
        
        
        $app->render();
    }
    
    public function add(){
        $this->load->helper($this->path->expedition_engine);
        $post = $this->input->post();        
        
        if($post!= null){
            Expedition_Engine::expedition_submit('','add',$post);
        }
        else{
            $this->view('','add');
        }
    }
    
    public function view($id = "",$method="view"){
        
        $this->load->helper($this->path->expedition_engine);
        $this->load->helper($this->path->expedition_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Expedition_Engine::expedition_exists($id)){
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
            Expedition_Renderer::expedition_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Expedition_Renderer::expedition_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
            }
            
            
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
    }
    
    
    public function ajax_search($method){
        $data = json_decode($this->input->post(), true);
        $result =array();
        switch($method){
            case 'expedition':
                $db = new DB();
                $records_page = $data['records_page'];
                $page = $data['page'];
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $q = '
                    select *
                        ,case expedition_status when "A" then "ACTIVE" when "I" then "INACTIVE" end expedition_status_name
                    from expedition t1
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
    }
    
    public function data_support($method="",$submethod=""){
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        switch($method){
            case 'default_status_get':                       
                $result = Expedition_Engine::expedition_status_default_status_get();
                if(isset($result['label'])){
                    $result['label'] = SI::get_status_attr($result['label']);
                }
                break;
            case 'next_allowed_status':
                $curr_status_val = $data['data'];
                $allowed_status = Expedition_Engine::expedition_status_next_allowed_status_get($curr_status_val);
                $num_of_res = count($allowed_status);
                for($i = 0;$i<$num_of_res;$i++){
                    if(Security_Engine::get_controller_permission(
                        User_Info::get()['user_id']
                            ,'expedition'
                            ,strtolower($allowed_status[$i]['method']))){
                            $allowed_status[$i]['label'] = SI::get_status_attr($allowed_status[$i]['label']);
                    }
                    else{
                        unset($allowed_status[$i]);
                    }
                }
                $result['response'] = $allowed_status;
                break;
            case 'expedition_get':
                $db = new DB();
                $result = null;
                $q = '
                    select t1.*
                        , t2.code unit_code, t2.name unit_name
                        , case t1.expedition_status when "A" then "ACTIVE"
                        when "I" then "INACTIVE" end expedition_status_name
                    from expedition t1
                        left outer join unit t2 on t1.measurement_unit_id = t2.id
                    where t1.id = '.$db->escape($data['data']).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $rs[0]['measurement_unit_name'] = SI::html_tag('strong',$rs[0]['unit_code']).' '.$rs[0]['unit_name'];
                    $result['response'] = $rs[0];
                }
                
                break;

        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        echo json_encode($result);
    }
    
    public function active($id){
        $this->load->helper($this->path->expedition_engine);
        $post = $this->input->post();
        if($post!= null){
            Expedition_Engine::expedition_submit($id,'active',$post);
        }        
    }
    
    public function inactive($id){
        $this->load->helper($this->path->expedition_engine);
        $post = $this->input->post();
        if($post!= null){
            Expedition_Engine::expedition_submit($id,'inactive',$post);
        }
        
    }
}

