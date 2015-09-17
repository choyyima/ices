<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Common_Ajax_Listener extends MY_ICES_Controller {

    public function index() {
        
    }

    public function controller_permission_check($class = "", $method = "") {
        $uid = User_Info::get()['user_id'];
        $permission = Security_Engine::get_controller_permission(
                        $uid
                        , $class
                        , $method
        );
        echo json_encode(array('result' => $permission));
    }

    public function load_js($id) {
        $result = null;
        $pack_js_media = get_instance()->config->config['MY_']['pack_js_media'];
        switch ($pack_js_media) {
            case 'file':
                $filename = 'js_file/dynamic/' . $id . '.txt';
                $f = fopen($filename, 'r');
                if ($f) {
                    $result = fread($f, filesize($filename));
                    fclose($f);
                    unlink($filename);
                }
                break;
        }

        echo $result;
    }

    public function is_timeout() {
        $result = array('response' => '', 'msg' => []);
        $msg = [];
        $response = Security_Engine::is_timeout();

        $result['response'] = $response;
        $result['msg'] = $msg;

        echo json_encode($result);
    }

    public function module_status($method = '') {
        $data = json_decode($this->input->post(), TRUE);

        $result = array('success' => 1, 'msg' => []);
        $msg = [];
        $success = 1;
        $response = array();

        $id = isset($data['id']) ? Tools::_str($data['id']) : '';
        $module = isset($data['module']) ? Tools::_str($data['module']) : '';
        $tbl = isset($data['table']) ? Tools::_str($data['table']) : $module;
        $field = isset($data['field']) ? Tools::_str($data['field']) : $module . '_status';

        switch ($method) {
            case 'current_status_get':
                $db = new DB();
                $response = '';

                $q = 'select ' . $field . '_status from ' . $tbl . ' where id = ' . $db->escape($id);
                $rs = $db->query_array($q);
                if (count($rs) > 0) {
                    $result['response'] = $rs[0]['status'];
                }

                break;
            case 'default_status_get':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $response = array();
                try {
                    $module_engine = $module . '_engine';
                    get_instance()->load->helper(ICES_Engine::$app['app_base_dir'] . $module . '/' . $module_engine);
                    $module_engine = Tools::class_name_get($module_engine);

                    $response = SI::type_default_type_get($module_engine, '$status_list');

                    if (isset($response['text'])) {
                        $response['text'] = SI::get_status_attr($response['text']);
                    }
                } catch (Exception $e) {
                    
                }
                $result['response'] = $response;
                //</editor-fold>
                break;
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        echo json_encode($result);
    }

}

?>