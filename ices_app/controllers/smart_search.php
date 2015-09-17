<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Smart_Search extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get('Smart Search');
        get_instance()->load->helper('smart_search/smart_search_engine');
        $this->path = Smart_Search_Engine::path_get();
        $this->title_icon = App_Icon::smart_search();
        
    }
    
    public function index($lookup_val='')
    {           
        $action = "";

        $app = new App();            
        $db = $this->db;

        
        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower($this->title));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title',Lang::get(array('Smart Search Result')))->form_set('span','12');
        $form->form_group_add();
        $cols = array(
            array("name"=>"module_name","label"=>Lang::get("Module"),"data_type"=>"text"),
            array("name"=>"module_data","label"=>Lang::get("Data"),"data_type"=>"text"),
            array("name"=>"module_description","label"=>Lang::get("Description"),"data_type"=>"text"),
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/smart_search')
                ->table_ajax_set('columns',$cols)
                ->table_ajax_set('key_column','id')
                
                ->filter_set(array(
                    ))
                ;        
        $app->js_set('
            ajax_table.filter = "'.Tools::_str($lookup_val).'";
            ajax_table.methods.data_show(1);
        ');
        $app->render();
    }
    
    
    public function add(){        
        $this->load->helper($this->path->smart_search_engine);
        $active_id =Smart_Search_Engine::smart_search_active_get(); 
        if($active_id!== null){
            redirect($this->path->index.'view/'.$active_id);
        }
        
        $post = $this->input->post();      
        $default_status = SI::status_default_status_get('Smart_Search_Engine')['val'];
        $user_id = User_Info::get()['user_id'];
        $db = new DB();
        $q = '
            select t1.id
            from smart_search t1
                inner join working_order_info t2 on t1.id = t2.smart_search_id
            where t1.smart_search_status === '.$db->escape($default_status).'
                and t2.creator_id = '.$db->escape($user_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            redirect(get_instance()->config->base_url().'smart_search/view'.$rs[0]);
        }
        
        $this->view('','add');
        
    }
    
    
    public function view($id="",$method="view"){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->smart_search_engine);
        $this->load->helper($this->path->smart_search_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Smart_Search_Engine::smart_search_exists($id)){
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
            $app->set_breadcrumb($this->title,'smart_search');
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row')->div_set('id','smart_search');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Smart_Search_Renderer::Smart_Search_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Smart_Search_Renderer::Smart_Search_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
                
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
        $result =array('success'=>1,'msg'=>[]);
        $success = 1;
        $msg = [];
        $response = array();
        $limit = 10;
        
        switch($method){
            
            case 'smart_search':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                
                $lookup_str = $db->escape('%'.$data['data'].'%');        
                
                $config = array(
                    'additional_filter'=>array(

                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select 1
                                
                        ',
                        'where'=>'
                            
                        ',
                        'group'=>'
                            )tfinal
                        ',
                        'order'=>''
                    ),
                );                
                $temp_result = SI::form_data()->ajax_table_search($config, $data);
                for($i = 0;$i<count($temp_result['data']);$i++){
                    
                    
                }
                $result = $temp_result;
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
        //this function only used for urgently data retrieve
        get_instance()->load->helper('smart_search/smart_search_engine');
        get_instance()->load->helper('smart_search/smart_search_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'customer_detail_get':
                $response = array();
                $customer_id = isset($data['customer_id'])?Tools::_str($data['customer_id']):'';
                $response['customer_detail'] = Smart_Search_Data_Support::customer_detail_get($customer_id);
                break;
            case 'smart_search_get':
                $response =array();
                $db = new DB();
                $smart_search_id = Tools::_str($data['data']);
                $q = '
                    select t1.*,
                        t2.code store_code,
                        t2.name store_name,
                        t3.id customer_id,
                        t3.code customer_code,
                        t3.name customer_name
                    from smart_search t1
                        inner join store t2 on t1.store_id = t2.id
                        inner join customer t3 
                            on t1.customer_id = t3.id
                    where t1.id = '.$db->escape($smart_search_id).'
                ';
                $rs = $db->query_array($q);

                if(count($rs)>0){
                    $smart_search = $rs[0];                                        
                    $smart_search_info = Smart_Search_Data_Support::smart_search_info_get($smart_search['id']);
                    $smart_search['smart_search_date'] = Tools::_date($smart_search['smart_search_date'],'F d, Y H:i');
                    $smart_search['store_text'] = SI::html_tag('strong',$smart_search['store_code'])
                        .' '.$smart_search['store_name'];
                    $smart_search['smart_search_status_text'] = SI::get_status_attr(
                            SI::status_get('Smart_Search_Engine',$smart_search['smart_search_status'])['label']
                        );
                    $smart_search['customer_text'] = SI::html_tag('strong',$smart_search['customer_code'])
                        .' '.$smart_search['customer_name'];
                    
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('Smart_Search_Engine',
                            $smart_search['smart_search_status']
                        );
                    
                    $response['smart_search'] = $smart_search;
                    $response['smart_search_info'] = $smart_search_info;
                    $response['smart_search_status_list'] = $next_allowed_status_list;
                }
                
                break;
            
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    
}

?>