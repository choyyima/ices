<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Contact_Category extends MY_ICES_Controller {
        
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get(array('Contact Category Controller'),true,true,false,false,true);
        get_instance()->load->helper(ICES_Engine::$app['app_base_dir'].'coba/coba_engine');
        $this->path = Contact_Category_Engine::path_get();
        $this->title_icon = '';
    }
    
    public function index()
    {           
        //<editor-fold defaultstate="collapsed">
        $action = "";

        $app = new App();            
        $db = $this->db;

        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower('coba'));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title',Lang::get('Contact_Category Controller','List'))->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array('New','Contact_Category Controller')))
                ->button_set('icon','fa fa-plus')->button_set('href',ICES_Engine::$app['app_base_url'].'coba/add');
        
        $cols = array(
            array("name"=>"app_name","label"=>"App Name","data_type"=>"text"),
            array("name"=>"name","label"=>"Name","data_type"=>"text"),
            array("name"=>"method","label"=>"Method","data_type"=>"text"),
            array("name"=>"coba_status","label"=>"Status","data_type"=>"text")
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
            ->table_ajax_set('base_href',$this->path->index.'view')
            ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/coba')
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
        $this->load->helper($this->path->coba_data_support);
        $this->load->helper($this->path->coba_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!count(Contact_Category_Data_Support::coba_get($id))>0){
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
            $app->set_breadcrumb($this->title,strtolower('coba'));
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Contact_Category_Renderer::coba_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Contact_Category_Renderer::coba_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
            }
            
            
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
        //</editor-fold>
    }

    public function coba_add(){
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>'','method'=>'coba_add','primary_data_key'=>'coba','data_post'=>$post);
            SI::data_submit()->submit('coba_engine',$param);
            
        }        
    }
    
    public function coba_active($id = ''){
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'coba_active','primary_data_key'=>'coba','data_post'=>$post);
            SI::data_submit()->submit('coba_engine',$param);
            
        }
    }
    
    public function coba_inactive($id = ''){
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'coba_inactive','primary_data_key'=>'coba','data_post'=>$post);
            $result = SI::data_submit()->submit('coba_engine',$param);
            
        }
    }

    public function ajax_search($method=''){
        //<editor-fold defaultstate="collapsed">
        $data = json_decode($this->input->post(), true);
        $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
        $result =array();
        switch($method){
            case 'coba':
                $db = new DB();
                $lookup_str = $db->escape('%'.$lookup_data.'%');                
                $config = array(
                    'additional_filter'=>array(
                        
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select distinct *
                                from coba t1
                                where t1.status>0
                                
                        ',
                        'where'=>'
                            and (
                                t1.coba_status like '.$lookup_str.'
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
                    $row->coba_status = SI::get_status_attr(
                        SI::type_get('Contact_Category_Engine',$row->coba_status,'$status_list')['text']
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
        $path = Contact_Category_Engine::path_get();
        get_instance()->load->helper($path->coba_data_support);
        $data = json_decode($this->input->post(), true);
        $result = SI::result_format_get();
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'coba_get':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $response = array();
                $q = '
                    select *
                    from coba t1
                    where t1.id = '.$db->escape($data['data']).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $coba = $rs[0];
                    $coba['app_name_text'] = SI::type_get('ICES_Engine',
                        $coba['app_name'],'$app_list'
                    )['text'];
                    $coba['coba_status_text'] = SI::get_status_attr(
                        SI::type_get('coba_engine',$coba['coba_status'],'$status_list'
                        )['text']
                    );
                            
                    $next_allowed_status_list = SI::form_data()
                    ->status_next_allowed_status_list_get('coba_engine',
                        $coba['coba_status']
                    );        
                    
                    $response['coba'] = $coba;
                    $response['coba_status_list'] = $next_allowed_status_list;
                    
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