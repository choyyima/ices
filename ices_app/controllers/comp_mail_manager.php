<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Comp_Mail_Manager extends MY_Controller {
        
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get(array('Mail Manager'),true,true,false,false,true);
        get_instance()->load->helper('comp_mail_manager/comp_mail_manager_engine');
        $this->path = Comp_Mail_Manager_Engine::path_get();
        $this->title_icon = App_Icon::mail();
    }
    
    public function index()
    {
        $this->load->helper($this->path->comp_mail_manager_engine);
        $post = $this->input->post();        
        $this->view('','view');
    }
        
    public function view($id = "",$method="view"){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->comp_mail_manager_engine);
        $this->load->helper($this->path->comp_mail_manager_data_support);
        $this->load->helper($this->path->comp_mail_manager_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
        
            if($method=='add') $id = '';
            $data = array(
                'id'=>$id
            );
            
            $app = new App();            
            $app->set_title($this->title);
            $app->set_menu('collapsed',false);
            $app->set_breadcrumb($this->title,strtolower('comp_mail_manager'));
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Comp_Mail_Manager_Renderer::comp_mail_manager_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            
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
        $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
        $result =array('success'=>1,'msg'=>[]);
        $success = 1;
        $msg = [];
        $response = array();
        $limit = 10;
        switch($method){
                            

        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function data_support($method="",$submethod=""){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('comp_mail_manager/comp_mail_manager_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[],'response'=>array());
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'comp_mail_manager_get':
                get_instance()->load->helper('comp_mail_manager/comp_mail_manager_engine');
                get_instance()->load->helper('product/product_engine');
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $response = array();
                $q = '
                    select cm.*
                    from company_mail cm
                    where cm.status>0
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $comp_mail_manager = $rs;
                    $response['comp_mail_manager'] = $comp_mail_manager;
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
    
    public function comp_mail_manager_save(){
        $this->load->helper($this->path->comp_mail_manager_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>'','method'=>'comp_mail_manager_save','primary_data_key'=>'comp_mail_manager','data_post'=>$post);            
            SI::data_submit()->submit('comp_mail_manager_engine',$param);            
        }        
    }
    
}

