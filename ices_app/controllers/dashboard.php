<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    private $title = 'Dashboard';
    private $title_icon = '';
    private $path = array();

    function __construct() {
        parent::__construct();
        get_instance()->load->helper('dashboard/dashboard_engine');
        $this->path = Dashboard_Engine::path_get();
        $this->title_icon = App_Icon::dashboard();
    }

    public function index() {
        get_instance()->load->helper($this->path->dashboard_renderer);
        $action = "";

        $app = new App();
        $db = $this->db;

        $app->set_title($this->title);
        $app->set_breadcrumb($this->title, strtolower($this->title));
        $app->set_content_header($this->title, $this->title_icon, $action);
        $pane = $app->engine->div_add()->div_set('class', 'row');

        Dashboard_Renderer::dashboard_render($app, $pane);

        $app->render();
    }

    public function data_support($method = "", $submethod = "") {
        //<editor-fold defaultstate="collapsed">
        //this function only used for urgently data retrieve
        get_instance()->load->helper('dashboard/dashboard_data_support');
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
