<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

$my_param = array(
    'file_path' => APPPATH . 'controllers/ices/dashboard.php',
    'src_class' => 'Dashboard',
    'src_extends_class' => '',
    'dst_class' => 'Dashboard_Parent',
    'dst_extends_class' => '',
);
$my_content = my_load_and_rename_class($my_param);

class Dashboard extends Dashboard_Parent {

    private $title = "Dashboard";
    private $icon = "";
    private $path = array();

    function __construct() {
        parent::__construct();
        get_instance()->load->helper('phone_book/dashboard/dashboard_engine');
        $this->path = Dashboard_Engine::path_get();
        $this->icon = App_Icon::dashboard();
    }

    public function index() {
        get_instance()->load->helper($this->path->dashboard_renderer);
        
        $app = new App();
        $db = $this->db;
        
        $app->set_title($this->title);
        $app->set_breadcrumb($this->title, $this->title);
        $app->set_content_header($this->title, $this->icon);
        $panel = $app->engine->div_add()->div_set('class','row');
        
        Dashboard_Renderer::dashboard_render($app, $panel);
        $app->render();
    }
    
    public function data_support($method = "", $submethod = "") {
        //<editor-fold defaultstate="collapsed">
        //this function only used for urgently data retrieve
        get_instance()->load->helper('phone_book/dashboard/dashboard_data_support');
        $data = json_decode($this->input->post(), true);
        $result = array('success' => 1, 'msg' => []);
        $msg = [];
        $success = 1;
        $response = array();
        switch ($method) {
            case 'data_get':
                $response = array();
                $module = isset($data['module']) ? Tools::_arr($data['module']) : array();

                foreach ($module as $idx => $row) {
                    if (method_exists('Dashboard_Data_Support', $row . '_get')) {
                        if (Security_Engine::get_controller_permission(User_Info::get()['user_id']
                                        , 'dashboard', $row)) {
                            $response[] = eval('return Dashboard_Data_Support::' . $row . '_get();');
                        }
                    }
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