<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rpt_Refill extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get('Report Refill');
        get_instance()->load->helper('rpt_refill/rpt_refill_engine');
        $this->path = Rpt_Refill_Engine::path_get();
        $this->title_icon = App_Icon::report();
        
    }
    
    public function index(){
        
        $this->load->helper($this->path->rpt_refill_engine);
        $this->load->helper($this->path->rpt_refill_renderer);
        $this->load->helper($this->path->rpt_refill_data_support);
        
        $app = new App();    
        $app->set_title($this->title);
        $app->set_menu('collapsed',false);
        $app->set_breadcrumb($this->title,'rpt_refill');
        $app->set_content_header($this->title,$this->title_icon,'');
        $row = $app->engine->div_add()->div_set('class','row')->div_set('id','rpt_refill');            
        $form = $row->form_add()->form_set('title',Lang::get('Report Sales'))->form_set('span','12');
        Rpt_Refill_Renderer::rpt_refill_render($app,$form,array("id"=>''),$this->path,'view');
        
        $app->render();
        
    }
    
    public function form_render($method="",$submethod=""){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('rpt_refill/rpt_refill_engine');
        get_instance()->load->helper('rpt_refill/rpt_refill_renderer');
        $data = json_decode($this->input->post(), true);
        $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
        $result =array('success'=>1,'msg'=>[]);
        $success = 1;
        $msg = [];
        $response = array();
        $limit = 10;
        $submethod = Tools::_str($submethod);
        $method = Tools::_str($method);
        
        if(!(Security_Engine::get_controller_permission(User_Info::get()['user_id'], 'rpt_refill', $method)
            && SI::type_match('rpt_refill_engine',$method,'$module_type_list')
        )){
            $success = 0;
        }
        
        if($success === 1){
            $response = Rpt_Refill_Renderer::form_render($method);
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        
        //</editor-fold>
    }
    
    public function ajax_search($method="",$submethod=""){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('rpt_refill/rpt_refill_engine');
        $data = json_decode($this->input->post(), true);
        $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
        $result =array('success'=>1,'msg'=>[]);
        $success = 1;
        $msg = [];
        $response = array();
        $limit = 10;
        $submethod = Tools::_str($submethod);
        $method = Tools::_str($method);
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
        get_instance()->load->helper('rpt_refill/rpt_refill_engine');
        get_instance()->load->helper('rpt_refill/rpt_refill_data_support');
        get_instance()->load->helper('rpt_refill/rpt_refill_renderer');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        
        switch($method){
            case 'input_select_module_name':
               //<editor-fold defaultstate="collapsed">
               $module_list = SI::type_list_get('Rpt_Refill_Engine','$module_type_list');
               foreach($module_list as $idx=>$row){
                    $method = $row['method'];
                    if(Security_Engine::get_controller_permission(User_Info::get()['user_id'], 'rpt_refill', $method)
                    ){
                        $response[] = array(
                            'id'=>$row['val'],
                            'text'=>$row['label']
                        );
                    }
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
    
    public function download_excel($module_name='',$data = ''){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('rpt_refill/rpt_refill_data_support');
        get_instance()->load->helper('rpt_refill/rpt_refill_engine');
        get_instance()->load->helper('rpt_refill/rpt_refill_download_excel');
        
        $data = json_decode(urldecode(Tools::_str($data)),true);
        
        if(Security_Engine::get_controller_permission(User_Info::get()['user_id'], 'rpt_refill', Tools::_str($module_name))
            && SI::type_match('rpt_refill_engine',$module_name)
        ){            
            $param = isset($data['param'])?Tools::_arr($data['param']):array();
            if(method_exists('Rpt_Refill_Download_Excel', $module_name.'')){
                Rpt_Refill_Download_Excel::$module_name($param);;
            }
            
        }
        //</editor-fold>
    }
    
}

?>