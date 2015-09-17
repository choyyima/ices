<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class User_Login extends MY_Controller {

    private $index_url = "";
    private $title = 'User Login';
    private $title_icon = 'fa fa-user';
    private $path = array(
        'index' => ''
        , 'user_login_engine' => ''
        , 'ajax_search' => ''
        , 'approval_js' => ''
    );

    function __construct() {
        parent::__construct();
        $this->index_url = get_instance()->config->base_url() . 'user_login';
        $this->path = json_decode(json_encode($this->path));
        $this->path->index = get_instance()->config->base_url() . 'user_login/';
        $this->path->user_login_engine = 'security/user_login_engine';
        $this->path->ajax_search = $this->path->index . 'ajax_search/';
    }

    public function add() {
        $this->edit();
    }

    public function edit($id = "") {
        $this->load->helper('security/user_login_engine');
        $title = "User Login";
        $action = "Add";
        $db = new DB();
        if (strlen($id) > 0)
            $action = 'Edit';
        if ($action != 'Add' && User_Login_Engine::get($id) == null) {
            Message::set('error', array("Data doesn't exist"));
            redirect($this->index_url);
        }

        $data = array(
            'id' => $id
            , 'name' => ''
            , 'password' => ''
            , 'first_name' => ''
            , 'last_name' => ''
            , 'u_group_id' => ""
            , 'default_store_id' => ''
            , 'store' => array()
        );


        $post = $this->input->post();
        $app = new App();
        $app->set_title($title);
        $app->set_breadcrumb($title, strtolower($title));
        $app->set_content_header($title, $action);
        $init_state = true;

        if ($post != null) {
            $init_state = false;
            if(is_string($post)){
                if(json_decode($post)!= null){
                    $post = json_decode($post,true);
                }
            }
            $data['id'] = $id;
            $data['first_name'] = $post['first_name'];
            $data['last_name'] = $post['last_name'];
            $data['name'] = $post['name'];
            $data['password'] = $post['password'];
            $data['u_group_id'] = $post['u_group_id'];
            $data['default_store_id'] = $post['default_store_id'];
            $data['store'] = $post['store'];
            $result = User_Login_Engine::save($data);
            
            echo json_encode($result);
            die();
            
        }

        $path = array(
            'index' => $this->index_url
        );
        $row = $app->engine->div_add()->div_set('class', 'row');
        $form = $row->form_add()->form_set('title', 'Detail')->form_set('span', '12');

        User_Login_Engine::add_edit_render(json_decode(json_encode($path)), $data, $form);

        $form->control_set($method = 'button', 'user_login_submit', 'primary', 'submit', '', 'Submit', App_Icon::btn_save());
        $form->control_set($method = 'button', '', 'default', 'button', $this->index_url, 'Back', App_Icon::btn_back());
        
        $param = array('index'=>$this->path->index);
        $js = get_instance()->load->view('security/user_login/user_login_js',$param,TRUE);
        $app->js_set($js);
        
        $app->render();
    }

    public function delete($id = "") {
        $db = new DB();
        $q = '
            select 1
            from user_login
            where id = ' . $db->escape($id) . '
                and lower(name) = "root"
        ';

        if (count($db->query_array($q)) > 0) {
            Message::set('error', array('Are You Crazy? DO NOT EVER DELETE ROOT!!!'));
            redirect($this->index_url);
        }

        $data = array(
            "id" => $id
            , "status" => 0
        );
        $this->load->helper('security/user_login_engine');
        $result = User_Login_Engine::Save($data);
        if ($result['success'] == 1) {
            redirect($this->index_url);
        }
    }

    public function index() {

        $title = "User Login";
        $action = "";

        $app = new App();
        $db = $this->db;

        $app->set_title($title);
        $app->set_breadcrumb($title, strtolower($title));
        $app->set_content_header($title, $action);

        $row = $app->engine->div_add()->div_set('class', 'row');
        $form = $row->form_add()->form_set('title', 'User Login List')->form_set('span', '12');
        $form->form_group_add()->button_add()->button_set('class', 'primary')->button_set('value', 'New User Login')
                ->button_set('icon', 'fa fa-plus')->button_set('href', get_instance()->config->base_url() . 'user_login/add');

        $controls = array(
            array(
                "label" => 'Delete'
                , "base_url" => $this->index_url . '/delete'
                , "confirmation" => true
            )
        );

        $cols = array(
            array("name" => "name", "label" => "Name", "data_type" => "text", "is_key" => true)
            , array("name" => "first_name", "label" => "First Name", "data_type" => "text")
            , array("name" => "last_name", "label" => "Last Name", "data_type" => "text")
            , array("name" => "u_group", "label" => "User Group", "data_type" => "text")
        );

        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id', 'ajax_table')
                ->table_ajax_set('base_href', $this->index_url . '/view')
                ->table_ajax_set('lookup_url', $this->index_url . '/ajax/user_login_search')
                //->table_ajax_set('controls',$controls)
                ->table_ajax_set('columns', $cols);


        $app->render();
    }

    public function view($id = "") {
        $this->load->helper($this->path->user_login_engine);
        $action = "View";

        if (User_Login_Engine::get($id) == null) {
            Message::set('error', array("Data doesn't exist"));
            redirect($this->path->index);
        }

        $app = new App();
        $db = $this->db;

        $app->set_title($this->title);
        $app->set_breadcrumb($this->title, strtolower($this->title));
        $app->set_content_header($this->title, $this->title_icon, $action);
        $row = $app->engine->div_add()->div_set('class', 'row');

        $nav_tab = $row->div_add()->div_set("span", "12")->nav_tab_add();

        $detail_tab = $nav_tab->nav_tab_set('items_add'
                , array("id" => '#detail', "value" => "Detail", 'class' => 'active'));
        $detail_pane = $detail_tab->div_add()->div_set('id', 'detail')->div_set('class', 'tab-pane active');
        User_Login_Engine::detail_render($detail_pane, array("id" => $id));

        $app->render();
    }

    public function ajax($method) {
        $data = json_decode($this->input->post(), true);
        $result = array();
        switch ($method) {
            case 'u_group_search':
                $db = new DB();
                $q = 'select id id, name text from u_group where status>0 and lower(name) <> "root" and name like ' . $db->escape('%' . $data['data'] . '%');
                $result = $db->query_array($q);
                break;
            case 'user_login_search':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $records_page = $data['records_page'];
                $page = $data['page'];
                $lookup_str = $db->escape('%' . $data['data'] . '%');
                $q = '
                    select t1.id, t1.name name,t1.first_name first_name, t1.last_name,t3.name u_group

                    from user_login t1
                    left outer join user_login_u_group t2 on t1.id = t2.user_login_id
                    left outer join u_group t3 on t3.id = t2.u_group_id
                    where t1.status>0
                ';

                $q_where = ' and (t1.name like ' . $lookup_str . ' 
                        or t1.first_name like ' . $lookup_str . ' 
                        or t1.last_name like ' . $lookup_str . ' 
                        )';

                $extra = '';
                if (strlen($data['sort_by']) > 0) {
                    $extra.=' order by ' . $data['sort_by'];
                } else {
                    $extra.=' order by t1.id asc';
                }
                $extra .= ' limit ' . (($page - 1) * $records_page) . ', ' . ($records_page);
                $q_total_row = $q . $q_where;
                $q_data = $q . $q_where . $extra;
                $total_rows = $db->select_count($q_total_row, null, null);
                $result = array("header" => array("total_rows" => $total_rows), "data" => $db->query_array($q_data));
                //</editor-fold>
                break;
            case 'store_detail_get':
                $db = new DB();
                $lookup_str = isset($data['data']) ? Tools::_str($data['data']) : '';
                $q = 'select * from store where status>0 and id = ' . $db->escape($data['data']);
                $rs = $db->query_array($q);
                if (count($rs) > 0) {
                    foreach ($rs as $idx => $row) {
                        $result[] = array(
                            'id' => $row['id'],
                            'store_text' => SI::html_tag('strong', $row['code']) . ' ' . $row['name']
                        );
                    }
                }
                break;
        }

        echo json_encode($result);
    }

}
