<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class U_Profile extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get('User Profile');
        get_instance()->load->helper('u_profile/u_profile_engine');
        $this->path = U_Profile_Engine::path_get();
        $this->title_icon = App_Icon::user();
        
    }
    
    public function index(){        
        $this->load->helper($this->path->u_profile_engine);
        $this->load->helper($this->path->u_profile_renderer);
        $this->load->helper($this->path->u_profile_data_support);       
        
        $app = new App();    
        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,'u_profile');
        $app->set_content_header($this->title,$this->title_icon,'');
        $row = $app->engine->div_add()->div_set('class','row')->div_set('id','u_profile');            
        $form = $row->form_add()->form_set('title',Lang::get('User Profile'))->form_set('span','12');
        U_Profile_Renderer::u_profile_render($app,$form,array("id"=>User_Info::get()['user_id']),$this->path,'view');
        
        $app->render();        
    }
    
    public function ajax_search($method){
        
    }
    
    public function data_support($method="",$submethod=""){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('u_profile/u_profile_engine');
        get_instance()->load->helper('u_profile/u_profile_data_support');
        get_instance()->load->helper('u_profile/u_profile_renderer');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        
        switch($method){
            case 'report_table_get':
                $module_name = isset($data['module_name'])?Tools::_str($data['module_name']):'';
                if(U_Profile_Data_Support::module_name_exists($module_name)){
                    $response = U_Profile_Renderer::report_table_render($module_name);
                }
                break;
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function u_profile_update(){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->u_profile_engine);
        $post = $this->input->post();
        $id = User_Info::get()['user_id'];
        if($post!= null){
            $param = array('id'=>$id,'method'=>'u_profile_update',
                'primary_data_key'=>'u_profile',
                'data_post'=>$post
            );
            SI::data_submit()->submit('u_profile_engine',$param);

        }
        //</editor-fold>
    }
    
}

?>