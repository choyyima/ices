<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Print_Form extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get(array('Print Form'),true,true,false,false,true);
        get_instance()->load->helper('print_form/print_form_engine');
        $this->path = Print_Form_Engine::path_get();
        $this->title_icon = App_Icon::print_form();
        
    }
    
    public function index()
    {           
        $this->load->helper($this->path->print_form_engine);
        $this->load->helper($this->path->print_form_renderer);
        $this->load->helper($this->path->print_form_data_support);
        
        $app = new App();    
        $app->set_title($this->title);
        $app->set_menu('collapsed',true);
        $app->set_breadcrumb($this->title,'print_form');
        $app->set_content_header($this->title,$this->title_icon,'');
        $row = $app->engine->div_add()->div_set('class','row')->div_set('id','print_form');            
        $form = $row->form_add()->form_set('title',$this->title)->form_set('span','12');
        Print_Form_Renderer::print_form_render($app,$form,array("id"=>''),$this->path,'view');
        
        $app->render();
    }
    
    
    public function add(){
        
        $this->load->helper($this->path->print_form_engine);
        $post = $this->input->post();
        
        $this->view('','add');
        
    }
    
    public function ajax_search($method="",$submethod=""){
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
        //this function only used for urgently data retrieve
        get_instance()->load->helper('print_form/print_form_engine');
        get_instance()->load->helper('print_form/print_form_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
                            
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function print_form_print($module=''){
        $this->load->helper($this->path->print_form_print);
        $post = $this->input->post();
        switch($module){
            case 'stock_opname':
                Print_Form_Print::stock_opname_print();
                break;
        }
    }
    
}

?>