<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Sales_Receipt extends MY_Controller {

    private $title = '';
    private $title_icon = '';
    private $path = array();

    function __construct() {
        parent::__construct();
        $this->title = Lang::get('Sales Receipt');
        get_instance()->load->helper('sales_receipt/sales_receipt_engine');
        $this->path = Sales_Receipt_Engine::path_get();
        $this->title_icon = App_Icon::sales_receipt();
    }

    public function index() {
        $action = "";

        $app = new App();
        $db = $this->db;


        $app->set_title($this->title);
        $app->set_breadcrumb($this->title, strtolower($this->title));
        $app->set_content_header($this->title, $this->title_icon, $action);

        $row = $app->engine->div_add()->div_set('class', 'row');
        $form = $row->form_add()->form_set('title', Lang::get(array('Sales Receipt', 'List')))->form_set('span', '12');
        $form->form_group_add();
        $form->form_group_add()->button_add()->button_set('class', 'primary')->button_set('value', Lang::get(array('New', 'Sales Receipt')))
                ->button_set('icon', 'fa fa-plus')->button_set('href', $this->path->index . 'add');
        $cols = array(
            array("name" => "code", "label" => Lang::get("Code"), "data_type" => "text", "is_key" => true),
            array("name" => "sales_receipt_date", "label" => Lang::get("Date"), "data_type" => "text"),
            array("name" => "payment_type_text", "label" => Lang::get("Payment Type"), "data_type" => "text"),
            array("name" => "amount", "label" => Lang::get("Amount"), "data_type" => "text", 'attribute' => array('style' => "text-align:right"), 'row_attrib' => array('style' => 'text-align:right')),
            array("name" => "outstanding_amount", "label" => Lang::get("Outstanding Amount"), "data_type" => "text", 'attribute' => array('style' => "text-align:right"), 'row_attrib' => array('style' => 'text-align:right')),
            array("name" => "change_amount", "label" => Lang::get("Change Amount"), "data_type" => "text", 'attribute' => array('style' => "text-align:right"), 'row_attrib' => array('style' => 'text-align:right')),
            array("name" => "sales_receipt_status", "label" => Lang::get("Status"), "data_type" => "text"),
        );

        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id', 'ajax_table')
                ->table_ajax_set('base_href', $this->path->index . 'view')
                ->table_ajax_set('lookup_url', $this->path->index . 'ajax_search/sales_receipt')
                ->table_ajax_set('columns', $cols)
                ->table_ajax_set('key_column', 'id')
                ->filter_set(array(
                    array('id' => 'reference_type_filter', 'field' => 'reference_type')
                ))
        ;
        $js = ' $("#reference_type_filter").on("change",function(){
                    ajax_table.methods.data_show(1);
                }); 
            ';
        $app->js_set($js);
        $app->render();
    }

    public function add() {

        $this->load->helper($this->path->sales_receipt_engine);
        $post = $this->input->post();

        $this->view('', 'add');
    }

    public function view($id = "", $method = "view") {
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->sales_receipt_engine);
        $this->load->helper($this->path->sales_receipt_data_support);
        $this->load->helper($this->path->sales_receipt_renderer);

        $action = $method;
        $cont = true;

        if (!in_array($method, array('add', 'view'))) {
            Message::set('error', array("Method error"));
            $cont = false;
        }

        if ($cont) {
            if (in_array($method, array('view'))) {
                if (!Sales_Receipt_Data_Support::sales_receipt_exists($id)) {
                    Message::set('error', array("Data doesn't exist"));
                    $cont = false;
                }
            }
        }

        if ($cont) {

            if ($method == 'add')
                $id = '';
            $data = array(
                'id' => $id
            );

            $app = new App();
            $app->set_title($this->title);
            $app->set_breadcrumb($this->title, 'sales_receipt');
            $app->set_content_header($this->title, $this->title_icon, $action);
            $row = $app->engine->div_add()->div_set('class', 'row')->div_set('id', 'sales_receipt');

            $nav_tab = $row->div_add()->div_set("span", "12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    , array("id" => '#detail_tab', "value" => "Detail", 'class' => 'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id', 'detail_tab')->div_set('class', 'tab-pane active');
            Sales_Receipt_Renderer::sales_receipt_render($app, $detail_pane, array("id" => $id), $this->path, $method);
            if ($method === 'view') {
                $history_tab = $nav_tab->nav_tab_set('items_add'
                        , array("id" => '#status_log_tab', "value" => "Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id', 'status_log_tab')->div_set('class', 'tab-pane');
                Sales_Receipt_Renderer::sales_receipt_status_log_render($app, $history_pane, array("id" => $id), $this->path);

                $sales_receipt_allocation_tab = $nav_tab->nav_tab_set('items_add'
                        , array("id" => '#sales_receipt_allocation_tab', "value" => "Sales Receipt Allocation"));
                $sales_receipt_allocation_pane = $sales_receipt_allocation_tab->div_add()->div_set('id', 'sales_receipt_allocation_tab')->div_set('class', 'tab-pane');
                Sales_Receipt_Renderer::sales_receipt_allocation_view_render($app, $sales_receipt_allocation_pane, array("id" => $id), $this->path);
            }

            $app->render();
        } else {
            redirect($this->path->index);
        }
        //</editor-fold>
    }

    public function ajax_search($method = "", $submethod = "") {
        //<editor-fold defaultstate="collapsed">
        $data = json_decode($this->input->post(), true);
        $result = array('success' => 1, 'msg' => []);
        $success = 1;
        $msg = [];
        $response = array();
        $limit = 10;

        switch ($method) {

            case 'sales_receipt':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = $db->escape('%' . $data['data'] . '%');
                $config = array(
                    'additional_filter' => array(
                        array('key' => 'reference_type', 'query' => 'and t1.payment_type_id = '),
                    ),
                    'query' => array(
                        'basic' => '
                            select * from (
                                select distinct t1.*
                                    ,t2.code payment_type_text
                                    ,bba.code bba_code
                                from sales_receipt t1       
                                    inner join payment_type t2 on t1.payment_type_id = t2.id
                                    left outer join bos_bank_account bba 
                                        on bba.id = t1.bos_bank_account_id
                                where t1.status>0
                        ',
                        'where' => '
                            and (t1.code like ' . $lookup_str . '
                            )
                        ',
                        'group' => '
                            )tfinal
                        ',
                        'order' => 'order by id desc'
                    ),
                );
                $temp_result = SI::form_data()->ajax_table_search($config, $data);
                for ($i = 0; $i < count($temp_result['data']); $i++) {
                    $temp_result['data'][$i]['sales_receipt_status'] = SI::get_status_attr(
                                    SI::status_get('Sales_Receipt_Engine', $temp_result['data'][$i]['sales_receipt_status']
                                    )['label']
                    );
                    $temp_result['data'][$i]['amount'] = Tools::thousand_separator($temp_result['data'][$i]['amount'], 5);
                    $temp_result['data'][$i]['outstanding_amount'] = Tools::thousand_separator($temp_result['data'][$i]['outstanding_amount'], 5);
                    $temp_result['data'][$i]['change_amount'] = Tools::thousand_separator($temp_result['data'][$i]['change_amount'], 5);
                }
                $result = $temp_result;
                //</editor-fold>
                break;

            case 'input_select_customer_search':
                get_instance()->load->helper('customer/customer_data_support');
                $lookup_data = isset($data['data']) ? Tools::_str($data['data']) : '';
                $cust_arr = Customer_Data_Support::customer_active_search($lookup_data);
                if (count($cust_arr) > 0) {
                    foreach ($cust_arr as $cust_idx => $cust) {
                        $response[] = array(
                            'id' => $cust['id'],
                            'text' => SI::html_tag('strong', $cust['code']) . ' ' .
                            $cust['name'] . ' ' . $cust['phone'] . ' ' . $cust['bb_pin'],
                        );
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

    public function data_support($method = "", $submethod = "") {
        //<editor-fold defaultstate="collapsed">
        //this function only used for urgently data retrieve
        get_instance()->load->helper('sales_receipt/sales_receipt_engine');
        get_instance()->load->helper('sales_receipt/sales_receipt_data_support');
        $data = json_decode($this->input->post(), true);
        $result = array('success' => 1, 'msg' => []);
        $msg = [];
        $success = 1;
        $response = array();
        switch ($method) {
            case 'sales_receipt_get':
                $response = array();
                $db = new DB();
                $sales_receipt_id = Tools::_str($data['data']);
                $q = '
                    select t1.*,
                        t2.code store_code,
                        t2.name store_name,
                        t3.id customer_id,
                        t3.code customer_code,
                        t3.name customer_name,
                        pt.id payment_type_id,
                        pt.code payment_type_code
                        ,bba.code bos_bank_account_code
                    from sales_receipt t1
                        inner join store t2 on t1.store_id = t2.id
                        inner join customer t3 
                            on t1.customer_id = t3.id
                        inner join payment_type pt on t1.payment_type_id  = pt.id
                        left outer join bos_bank_account bba 
                            on bba.id = t1.bos_bank_account_id
                    where t1.id = ' . $db->escape($sales_receipt_id) . '
                ';
                $rs = $db->query_array($q);

                if (count($rs) > 0) {
                    $sales_receipt = $rs[0];
                    $sales_receipt['sales_receipt_date'] = Tools::_date($sales_receipt['sales_receipt_date'], 'F d, Y H:i');
                    $sales_receipt['deposit_date'] = is_null($sales_receipt['deposit_date']) ?
                            null : Tools::_date($sales_receipt['deposit_date'], 'F d, Y H:i');
                    $sales_receipt['store_text'] = SI::html_tag('strong', $sales_receipt['store_code'])
                            . ' ' . $sales_receipt['store_name'];
                    $sales_receipt['sales_receipt_status_text'] = SI::get_status_attr(
                                    SI::status_get('Sales_Receipt_Engine', $sales_receipt['sales_receipt_status'])['label']
                    );
                    $sales_receipt['customer_text'] = SI::html_tag('strong', $sales_receipt['customer_code'])
                            . ' ' . $sales_receipt['customer_name'];
                    $sales_receipt['bos_bank_account_text'] = $sales_receipt['bos_bank_account_code'];
                    $sales_receipt['amount'] = Tools::thousand_separator($sales_receipt['amount']);
                    $sales_receipt['outstanding_amount'] = Tools::thousand_separator($sales_receipt['outstanding_amount']);
                    $sales_receipt['change_amount'] = Tools::thousand_separator($sales_receipt['change_amount']);
                    $sales_receipt['payment_type_text'] = SI::html_tag('strong', $sales_receipt['payment_type_code']);

                    $next_allowed_status_list = SI::form_data()
                            ->status_next_allowed_status_list_get('Sales_Receipt_Engine', $sales_receipt['sales_receipt_status']
                    );

                    $response['sales_receipt'] = $sales_receipt;
                    $response['sales_receipt_status_list'] = $next_allowed_status_list;
                }

                break;

            case 'input_select_payment_type_get':
                $response = array();
                $customer_id = isset($data['customer_id']) ? $data['customer_id'] : '';
                $payment_type_arr = Sales_Receipt_Data_Support::customer_payment_type_get($customer_id);
                if (count($payment_type_arr) > 0) {
                    foreach ($payment_type_arr as $payment_type_idx => $payment_type) {
                        $response[] = array(
                            'id' => $payment_type['id'],
                            'text' => SI::html_tag('strong', $payment_type['code']),
                            'code' => $payment_type['code'],
                        );
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

    public function sales_receipt_add() {

        $this->load->helper($this->path->sales_receipt_engine);
        $post = $this->input->post();
        if ($post != null) {
            $param = array('id' => '', 'method' => 'sales_receipt_add', 'primary_data_key' => 'sales_receipt', 'data_post' => $post);
            SI::data_submit()->submit('sales_receipt_engine', $param);
        }
    }

    public function sales_receipt_invoiced($id = '') {

        $this->load->helper($this->path->sales_receipt_engine);
        $post = $this->input->post();
        if ($post != null) {
            $param = array('id' => $id, 'method' => 'sales_receipt_invoiced', 'primary_data_key' => 'sales_receipt', 'data_post' => $post);
            SI::data_submit()->submit('sales_receipt_engine', $param);
        }
    }

    public function sales_receipt_canceled($id = '') {

        $this->load->helper($this->path->sales_receipt_engine);
        $post = $this->input->post();
        if ($post != null) {
            $param = array('id' => $id, 'method' => 'sales_receipt_canceled', 'primary_data_key' => 'sales_receipt', 'data_post' => $post);
            SI::data_submit()->submit('sales_receipt_engine', $param);
        }
    }

}

?>