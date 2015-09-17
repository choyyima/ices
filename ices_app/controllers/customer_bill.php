<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer_Bill extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get('Customer Bill');
        get_instance()->load->helper('customer_bill/customer_bill_engine');
        $this->path = Customer_Bill_Engine::path_get();
        $this->title_icon = App_Icon::customer_bill();
        
    }
    
    public function index()
    {           
        $action = "";

        $app = new App();            
        $db = $this->db;

        
        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower($this->title));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title',Lang::get('Customer Bill List'))->form_set('span','12');
        $form->form_group_add();
        $cols = array(
            array("name"=>"code","label"=>Lang::get("Code"),"data_type"=>"text","is_key"=>true),
            array("name"=>"customer_bill_date","label"=>Lang::get("Customer Bill Date"),"data_type"=>"text"),
            array("name"=>"amount","label"=>Lang::get("Amount"),"data_type"=>"text",'attribute'=>array('style'=>"text-align:right"),'row_attrib'=>array('style'=>'text-align:right')),
            array("name"=>"outstanding_amount","label"=>Lang::get("Outstanding Amount"),"data_type"=>"text",'attribute'=>array('style'=>"text-align:right"),'row_attrib'=>array('style'=>'text-align:right')),
            array("name"=>"customer_bill_status_text","label"=>Lang::get("Status"),"data_type"=>"text"),
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/customer_bill')
                ->table_ajax_set('columns',$cols)
                ->table_ajax_set('key_column','id')
                ->filter_set(array(
                        array('id'=>'reference_type_filter','field'=>'reference_type')
                    ))
                ;        
        
        
        $app->render();
    }
    
    
    public function add(){
        /*
        $this->load->helper($this->path->customer_bill_engine);
        $post = $this->input->post();
        
        $this->view('','add');
        */
    }
    
    
    public function view($id="",$method="view"){
        
        $this->load->helper($this->path->customer_bill_engine);
        $this->load->helper($this->path->customer_bill_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Customer_Bill_Engine::customer_bill_exists($id)){
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
            $app->set_breadcrumb($this->title,'customer_bill');
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row')->div_set('id','customer_bill');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Customer_Bill_Renderer::customer_bill_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Customer_Bill_Renderer::customer_bill_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
                
                $customer_deposit_allocation_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#customer_deposit_allocation_tab',"value"=>"Customer Deposit Allocation"));
                $customer_deposit_allocation_pane = $customer_deposit_allocation_tab->div_add()->div_set('id','customer_deposit_allocation_tab')->div_set('class','tab-pane');
                Customer_Bill_Renderer::customer_deposit_allocation_view_render($app,$customer_deposit_allocation_pane,array("id"=>$id),$this->path);
                
                $sales_receipt_allocation_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#sra_tab',"value"=>"Sales Receipt Allocation"));
                $sales_receipt_allocation_pane = $sales_receipt_allocation_tab->div_add()->div_set('id','sra_tab')->div_set('class','tab-pane');
                Customer_Bill_Renderer::sra_view_render($app,$sales_receipt_allocation_pane,array("id"=>$id),$this->path);
                
                
            }            
            
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
        
        
    }
    
    public function ajax_search($method="",$submethod=""){
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $success = 1;
        $msg = [];
        $limit = 10;
        switch($method){
            
            case 'customer_bill':
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');                
                $config = array(
                    'reference_type'=>array(
                        
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select distinct t1.*
                                from customer_bill t1                    
                                where t1.status>0
                        ',
                        'where'=>'
                            and (t1.code like '.$lookup_str.'
                            )
                        ',
                        'group'=>'
                            )tfinal
                        ',
                        'order'=>'order by code desc'
                    ),
                );                
                $temp_result = SI::form_data()->ajax_table_search($config, $data);
                for($i = 0;$i<count($temp_result['data']);$i++){
                    $temp_result['data'][$i]['customer_bill_status_text'] =
                        SI::get_status_attr(
                            SI::status_get('Customer_Bill_Engine', 
                                $temp_result['data'][$i]['customer_bill_status']
                            )['label']
                        );
                    $temp_result['data'][$i]['amount'] = 
                        Tools::thousand_separator($temp_result['data'][$i]['amount'],5);
                    $temp_result['data'][$i]['outstanding_amount'] = 
                        Tools::thousand_separator($temp_result['data'][$i]['outstanding_amount'],5);
                    
                }
                $result = $temp_result;
                
                break;
            
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        echo json_encode($result);
    }
    
    public function data_support($method="",$submethod=""){
        //this function only used for urgently data retrieve
        get_instance()->load->helper('customer_bill/customer_bill_engine');
        get_instance()->load->helper('customer_bill/customer_bill_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'customer_bill_get':
                $response =array();
                $db = new DB();
                $customer_bill_id = $data['data'];
                $q = '
                    select t1.*,
                        t2.code store_code,
                        t2.name store_name,
                        t3.id customer_id,
                        t3.code customer_code,
                        t3.name customer_name
                    from customer_bill t1
                        inner join store t2 on t1.store_id = t2.id
                        inner join customer t3 
                            on t1.customer_id = t3.id
                    where t1.id = '.$db->escape($customer_bill_id).'
                ';
                $rs = $db->query_array($q);

                if(count($rs)>0){
                    $customer_bill = $rs[0];                    
                    $cb_type = $customer_bill['customer_bill_type'];
                    $reference = array('type'=>$cb_type,'id'=>'','text'=>'');
                    $reference_detail = array();
                    switch($reference['type']){
                        case 'delivery_order_final_confirmation':
                            $q = '
                                select t1.*
                                from delivery_order_final_confirmation t1
                                    inner join dofc_cb t2 
                                    on t1.id = t2.delivery_order_final_confirmation_id
                                where t2.customer_bill_id = '.$db->escape($customer_bill['id']).'
                            ';
                            $rs = $db->query_array($q);
                            if(count($rs)>0){
                                $reference['id'] = $rs[0]['id'];
                                $reference['text'] = SI::html_tag('strong',$rs[0]['code']);
                                $reference_detail = Customer_Bill_Data_Support::reference_detail_get($reference['type'], $reference['id']);
                            }
                            break;
                    }
                    $customer_bill['customer_bill_date'] = Tools::_date($customer_bill['customer_bill_date'],'F d, Y H:i');
                    $customer_bill['store_text'] = SI::html_tag('strong',$customer_bill['store_code'])
                        .' '.$customer_bill['store_name'];
                    $customer_bill['customer_bill_status_text'] = SI::get_status_attr(
                            SI::status_get('Customer_Bill_Engine',$customer_bill['customer_bill_status'])['label']
                        );
                    $customer_bill['customer_text'] = SI::html_tag('strong',$customer_bill['customer_code'])
                        .' '.$customer_bill['customer_name'];
                    $customer_bill['amount'] = Tools::thousand_separator($customer_bill['amount']);
                    $customer_bill['outstanding_amount'] = Tools::thousand_separator($customer_bill['outstanding_amount']);
                    
                    
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('Customer_Bill_Engine',
                            $customer_bill['customer_bill_status']
                        );
                    
                    $response['reference'] = $reference;
                    $response['reference_detail'] = $reference_detail;
                    $response['customer_bill'] = $customer_bill;
                    $response['customer_bill_status_list'] = $next_allowed_status_list;
                }
                
                break;
            
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
    }
    
    public function customer_bill_add(){
        
        $this->load->helper($this->path->customer_bill_engine);
        $post = $this->input->post();
        if($post!= null){
            Customer_Bill_Engine::submit('','customer_bill_add',$post);
        }
        
    }
    
    public function customer_bill_invoiced($id){
        
        $this->load->helper($this->path->customer_bill_engine);
        $post = $this->input->post();
        if($post!= null){
            Customer_Bill_Engine::submit($id,'customer_bill_invoiced',$post);
        }
        
        
    }
    
    public function customer_bill_canceled($id){
        
        $this->load->helper($this->path->customer_bill_engine);
        $post = $this->input->post();
        if($post!= null){
            Customer_Bill_Engine::submit($id,'customer_bill_canceled',$post);
        }
        
        
    }
}

?>