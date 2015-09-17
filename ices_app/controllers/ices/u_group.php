<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class U_Group extends MY_ICES_Controller {
        
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get(array('User Group'),true,true,false,false,true);
        get_instance()->load->helper(ICES_Engine::$app['app_base_dir'].'u_group/u_group_engine');
        $this->path = U_Group_Engine::path_get();
        $this->title_icon = APP_ICON::u_group();
    }
    
    public function index()
    {
        //<editor-fold defaultstate="collapsed">
        $action = "";

        $app = new App();            
        $db = $this->db;

        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower('u_group'));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title',Lang::get('User Group','List'))->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array('New','User Group')))
                ->button_set('icon','fa fa-plus')->button_set('href',ICES_Engine::$app['app_base_url'].'u_group/add');
        
        
         
        $cols = array(
            array("name"=>"app_name","label"=>"App Name","data_type"=>"text"),
            array("name"=>"name","label"=>"Name","data_type"=>"text"),
            array("name"=>"u_group_status","label"=>"Status","data_type"=>"text")
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
            ->table_ajax_set('base_href',$this->path->index.'view')
            ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/u_group')
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
        $this->load->helper($this->path->u_group_data_support);
        $this->load->helper($this->path->u_group_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!count(U_Group_Data_Support::u_group_get($id))>0){
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
            $app->set_breadcrumb($this->title,strtolower('u_group'));
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            U_Group_Renderer::u_group_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                U_Group_Renderer::u_group_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
                
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#security_menu_tab',"value"=>"Security Menu"));
                $history_pane = $history_tab->div_add()->div_set('id','security_menu_tab')->div_set('class','tab-pane');
                U_Group_Renderer::security_menu_render($app,$history_pane,array("id"=>$id),$this->path);
                
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#security_controller_tab',"value"=>"Security Controller"));
                $history_pane = $history_tab->div_add()->div_set('id','security_controller_tab')->div_set('class','tab-pane');
                U_Group_Renderer::security_controller_render($app,$history_pane,array("id"=>$id),$this->path);
                
            }
            
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
        //</editor-fold>
    }

    public function u_group_add(){
        //<editor-fold defaultstate="collapsed">
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>'','method'=>'u_group_add','primary_data_key'=>'u_group','data_post'=>$post);
            SI::data_submit()->submit('u_group_engine',$param);
            
        }
        //</editor-fold>
    }
    
    public function u_group_active($id = ''){
        //<editor-fold defaultstate="collapsed">
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'u_group_active','primary_data_key'=>'u_group','data_post'=>$post);
            SI::data_submit()->submit('u_group_engine',$param);
            
        }
        //</editor-fold>
    }
    
    public function u_group_inactive($id = ''){
        //<editor-fold defaultstate="collapsed">
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'u_group_inactive','primary_data_key'=>'u_group','data_post'=>$post);
            $result = SI::data_submit()->submit('u_group_engine',$param);
            
        }
        //</editor-fold>
    }
        
    public function security_menu_save(){
        //<editor-fold defaultstate="collapsed">
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>'','method'=>'security_menu_save','primary_data_key'=>'security_menu','data_post'=>$post);
            $result = SI::data_submit()->submit('u_group_engine',$param);
            
        }
        //</editor-fold>
    }
    
    public function security_controller_save(){
        //<editor-fold defaultstate="collapsed">
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>'','method'=>'security_controller_save','primary_data_key'=>'security_controller','data_post'=>$post);
            $result = SI::data_submit()->submit('u_group_engine',$param);
            
        }
        //</editor-fold>
    }

    public function ajax_search($method=''){
        //<editor-fold defaultstate="collapsed">
        $data = json_decode($this->input->post(), true);
        $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
        $result =array();
        switch($method){
            case 'u_group':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = $db->escape('%'.$lookup_data.'%');                
                $config = array(
                    'additional_filter'=>array(
                        
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select distinct *
                                from u_group t1
                                where t1.status>0
                                
                        ',
                        'where'=>'
                            and (
                                t1.app_name like '.$lookup_str.' 
                                or t1.name like '.$lookup_str.' 
                                or t1.u_group_status like '.$lookup_str.' 
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
                    $row->u_group_status = SI::get_status_attr(
                        SI::type_get('U_Group_Engine',$row->u_group_status,'$status_list')['text']
                    );
                }
                $temp_result = json_decode(json_encode($temp_result),true);
                $result = $temp_result;
                //</editor-fold>
                break;

        }
        
        echo json_encode($result);
        //</editor-fold>
    }

    public function data_support($method=''){
        //<editor-fold defaultstate="collapsed">
        $path = U_Group_Engine::path_get();
        get_instance()->load->helper($path->u_group_data_support);
        $data = json_decode($this->input->post(), true);
        $result = SI::result_format_get();
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'u_group_get':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $response = array();
                $u_group_id = Tools::_str(isset($data['data'])?$data['data']:'');
                $u_group_db = U_Group_Data_Support::u_group_get($u_group_id);
                if(count($u_group_db)>0){
                    $u_group = $u_group_db;
                    $u_group['app_name'] = array(
                        'id'=>$u_group['app_name'],
                        'text'=>SI::type_get('ICES_Engine',
                            $u_group['app_name'],'$app_list'
                        )['text'],
                    );
                    $u_group['u_group_status'] = array(
                        'id'=>$u_group['u_group_status'],
                        'text'=>SI::get_status_attr(
                            SI::type_get('u_group_engine',$u_group['u_group_status'],'$status_list'
                            )['text']
                        )
                    );
                            
                    $next_allowed_status_list = SI::form_data()
                    ->status_next_allowed_status_list_get('u_group_engine',
                        $u_group['u_group_status']['id']
                    );        
                    
                    $response['u_group'] = $u_group;
                    $response['u_group_status_list'] = $next_allowed_status_list;
                    
                }
                
                
                //</editor-fold>
                break;
            case 'security_menu_get':
                //<editor-fold defaultstate="collapsed">
                $app_name = Tools::_str(isset($data['app_name'])?$data['app_name']:'');
                $u_group_id = Tools::_str(isset($data['u_group_id'])?$data['u_group_id']:'');
                $app_base_dir = SI::type_get('ICES_Engine','ices','$app_list')['app_base_dir'];                
                get_instance()->load->helper($app_base_dir.'security_menu/security_menu_engine');
                $path = Security_Menu_Engine::path_get();
                get_instance()->load->helper($path->security_menu_data_support);
                $security_menu_list = Security_Menu_Data_Support::menu_list_get($app_name);
                $security_menu = U_Group_Data_Support::u_group_security_menu_get($u_group_id,$app_name);
                $response['security_menu'] = $security_menu;
                $response['security_menu_list'] = $security_menu_list;
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