<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BOS_Bank_Account extends MY_Controller {
        
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = 'BOS Bank Account';
        get_instance()->load->helper('bos_bank_account/bos_bank_account_engine');
        $this->path = Bos_Bank_Account_Engine::path_get();
        $this->title_icon = App_Icon::bos_bank_account();
    }
    
    public function index()
    {           
        //<editor-fold defaultstate="collapsed">
        $action = "";

        $app = new App();            
        $db = $this->db;

        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower('bos_bank_account'));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title',Lang::get(array('BOS Bank Account','List')))->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array('New','BOS Bank Account')))
                ->button_set('icon','fa fa-plus')->button_set('href',get_instance()->config->base_url().'bos_bank_account/add');
        
        $cols = array(
            array("name"=>"code","label"=>"Code","data_type"=>"text","is_key"=>true)
            ,array("name"=>"bank_name","label"=>"Bank Name","data_type"=>"text")
            ,array("name"=>"account_number","label"=>"Account Number","data_type"=>"text")
            ,array("name"=>"bos_bank_account_status_text","label"=>"Status","data_type"=>"text")

        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/bos_bank_account')
                //->table_ajax_set('controls',$controls)
                ->table_ajax_set('columns',$cols);
        
        
        $app->render();
        //</editor-fold>
    }
    
    public function add(){
        $this->load->helper($this->path->bos_bank_account_engine);
        $post = $this->input->post();        
        
        if($post!= null){
            $param = array('id'=>'','method'=>'bba_add','primary_data_key'=>'bos_bank_account','data_post'=>$post);            
            SI::data_submit()->submit('bos_bank_account_engine',$param);
        }
        else{
            $this->view('','add');
        }
    }
    
    public function view($id = "",$method="view"){
        
        $this->load->helper($this->path->bos_bank_account_engine);
        $this->load->helper($this->path->bos_bank_account_data_support);
        $this->load->helper($this->path->bos_bank_account_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(is_null(Bos_Bank_Account_Data_Support::bos_bank_account_get($id))){
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
            Bos_Bank_Account_Renderer::bos_bank_account_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Bos_Bank_Account_Renderer::bos_bank_account_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
                
            }
            
            
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
    }
    
    
    public function ajax_search($method=''){
        //<editor-fold defaultstate="collapsed">
        $data = json_decode($this->input->post(), true);
        $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
        $result =array();
        switch($method){
            case 'bos_bank_account':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = $db->escape('%'.$lookup_data.'%');                
                $config = array(
                    'additional_filter'=>array(
                        
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select *
                                from bos_bank_account bba
                                where bba.status>0
                                
                        ',
                        'where'=>'
                            and (bba.code like '.$lookup_str.'
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
                    $row->bos_bank_account_status_text =
                        SI::get_status_attr(
                            SI::status_get('Bos_Bank_Account_Engine', 
                                $row->bos_bank_account_status
                            )['label']
                        );
                    
                }
                $temp_result = json_decode(json_encode($temp_result),true);
                $result = $temp_result;
                //</editor-fold>
                break;
            case 'bos_bank_account_type':
                $db = new DB();
                $q = 'select * from bos_bank_account_type where id = '.$db->escape($data['data']);
                $result = $db->query_array($q);
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
            case 'bos_bank_account_get':
                $db = new DB();
                $result = null;
                $q = '
                    select *
                    from bos_bank_account
                    where id = '.$db->escape($data['data']).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $bos_bank_account = $rs[0];
                    
                    $bos_bank_account['bos_bank_account_status_text'] = SI::get_status_attr(
                        SI::status_get('bos_bank_account_engine',$bos_bank_account['bos_bank_account_status'])['label']
                    );
                                        
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('Bos_Bank_Account_engine',
                            $bos_bank_account['bos_bank_account_status']
                        );
                    $response['bos_bank_account']  = $bos_bank_account;
                    $response['bos_bank_account_status_list'] = $next_allowed_status_list;
                }
                
                break;
            
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function bba_active($id=''){
        $this->load->helper($this->path->bos_bank_account_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'bba_active','primary_data_key'=>'bos_bank_account','data_post'=>$post);
            SI::data_submit()->submit('bos_bank_account_engine',$param);
        }        
    }
    
    public function bba_inactive($id=''){
        $this->load->helper($this->path->bos_bank_account_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'bba_inactive','primary_data_key'=>'bos_bank_account','data_post'=>$post);
            SI::data_submit()->submit('bos_bank_account_engine',$param);
        }
        
    }
    
    
}

