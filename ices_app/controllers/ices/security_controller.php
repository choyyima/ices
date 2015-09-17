<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Security_Controller extends MY_ICES_Controller {
        
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get(array('Security Controller'),true,true,false,false,true);
        get_instance()->load->helper(ICES_Engine::$app['app_base_dir'].'security_controller/security_controller_engine');
        $this->path = Security_Controller_Engine::path_get();
        $this->title_icon = '';
    }
    
    public function index()
    {           
        //<editor-fold defaultstate="collapsed">
        $action = "";

        $app = new App();            
        $db = $this->db;

        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower('security_controller'));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title',Lang::get('Security Controller','List'))->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array('New','Security Controller')))
                ->button_set('icon','fa fa-plus')->button_set('href',ICES_Engine::$app['app_base_url'].'security_controller/add');
        
        
         
        $cols = array(
            array("name"=>"app_name","label"=>"App Name","data_type"=>"text"),
            array("name"=>"name","label"=>"Name","data_type"=>"text"),
            array("name"=>"method","label"=>"Method","data_type"=>"text"),
            array("name"=>"security_controller_status","label"=>"Status","data_type"=>"text")
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
            ->table_ajax_set('base_href',$this->path->index.'view')
            ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/security_controller')
            ->table_ajax_set('columns',$cols)
        ;
        $app->render();
        //</editor-fold>
    }
    
    public function add(){
        //<editor-fold defaultstate="collapsed">
        $post = $this->input->post();        
        $this->view('','add');        
        //</editor-fold>
    }
    
    public function view($id = "",$method="view"){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->security_controller_data_support);
        $this->load->helper($this->path->security_controller_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!count(Security_Controller_Data_Support::security_controller_get($id))>0){
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
            $app->set_menu('collapsed',true);
            $app->set_breadcrumb($this->title,strtolower('security_controller'));
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Security_Controller_Renderer::security_controller_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Security_Controller_Renderer::security_controller_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
            }
            
            
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
        //</editor-fold>
    }

    public function security_controller_add(){
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>'','method'=>'security_controller_add','primary_data_key'=>'security_controller','data_post'=>$post);
            SI::data_submit()->submit('security_controller_engine',$param);
            
        }        
    }
    
    public function security_controller_active($id = ''){
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'security_controller_active','primary_data_key'=>'security_controller','data_post'=>$post);
            SI::data_submit()->submit('security_controller_engine',$param);
            
        }
    }
    
    public function security_controller_inactive($id = ''){
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'security_controller_inactive','primary_data_key'=>'security_controller','data_post'=>$post);
            $result = SI::data_submit()->submit('security_controller_engine',$param);
            
        }
    }

    public function ajax_search($method=''){
        //<editor-fold defaultstate="collapsed">
        $data = json_decode($this->input->post(), true);
        $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
        $result =array();
        switch($method){
            case 'security_controller':
                $db = new DB();
                $lookup_str = $db->escape('%'.$lookup_data.'%');                
                $config = array(
                    'additional_filter'=>array(
                        
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select distinct *
                                from security_controller t1
                                where t1.status>0
                                
                        ',
                        'where'=>'
                            and (
                                t1.app_name like '.$lookup_str.' 
                                or t1.name like '.$lookup_str.' 
                                or t1.method like '.$lookup_str.'
                                or t1.security_controller_status like '.$lookup_str.'
                            )
                        ',
                        'group'=>'
                            )tfinal
                        ',
                        'order'=>'order by id desc'
                    ),
                );                
                $temp_result = SI::form_data()->ajax_table_search($config, $data,array('output_type'=>'object'));
                $t_data = $temp_result->data;
                foreach($t_data as $i=>$row){
                    $row->app_name = SI::type_get('ICES_Engine',$row->app_name,'$app_list')['text'];
                    $row->security_controller_status = SI::get_status_attr(
                        SI::type_get('Security_Controller_Engine',$row->security_controller_status,'$status_list')['text']
                    );
                }
                $temp_result = json_decode(json_encode($temp_result),true);
                $result = $temp_result;

                break;

        }
        
        echo json_encode($result);
        //</editor-fold>
    }

    public function data_support($method=''){
        //<editor-fold defaultstate="collapsed">
        $path = Security_Controller_Engine::path_get();
        get_instance()->load->helper($path->security_controller_data_support);
        $data = json_decode($this->input->post(), true);
        $result = SI::result_format_get();
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'security_controller_get':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $response = array();
                $q = '
                    select *
                    from security_controller t1
                    where t1.id = '.$db->escape($data['data']).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $security_controller = $rs[0];
                    $security_controller['app_name'] = array(
                        'id'=>$security_controller['app_name'],
                        'text'=>SI::type_get('ICES_Engine',
                            $security_controller['app_name'],'$app_list'
                        )['text'],
                    );
                    
                    $security_controller['security_controller_status'] = array(
                        'id'=>$security_controller['security_controller_status'],
                        'text'=>SI::get_status_attr(
                            SI::type_get('security_controller_engine',
                                $security_controller['security_controller_status'],
                                '$status_list'
                            )['text']
                        ),
                    );
                    
                            
                    $next_allowed_status_list = SI::form_data()
                    ->status_next_allowed_status_list_get('security_controller_engine',
                        $security_controller['security_controller_status']['id']
                    );        
                    
                    $response['security_controller'] = $security_controller;
                    $response['security_controller_status_list'] = $next_allowed_status_list;
                    
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
    
}