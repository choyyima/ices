<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer_Refund extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get('Customer Refund');
        get_instance()->load->helper('customer_refund/customer_refund_engine');
        $this->path = Customer_Refund_Engine::path_get();
        $this->title_icon = App_Icon::customer_refund();
        
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
        $form = $row->form_add()->form_set('title',Lang::get('Customer Refund List'))->form_set('span','12');
        $form->form_group_add();
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get('New Customer Refund'))
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        $cols = array(
            array("name"=>"code","label"=>Lang::get("Code"),"data_type"=>"text","is_key"=>true),
            array("name"=>"customer_refund_date","label"=>Lang::get("Date"),"data_type"=>"text"),
            array("name"=>"amount","label"=>Lang::get("Amount"),"data_type"=>"text",'attribute'=>array('style'=>"text-align:right"),'row_attrib'=>array('style'=>'text-align:right')),
            array("name"=>"customer_refund_status","label"=>Lang::get("Status"),"data_type"=>"text"),
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/customer_refund')
                ->table_ajax_set('columns',$cols)
                ->table_ajax_set('key_column','id')
                ->filter_set(array(
                        array('id'=>'reference_type_filter','field'=>'reference_type')
                    ))
                ;        
        $js = ' $("#reference_type_filter").on("change",function(){
                    ajax_table.methods.data_show(1);
                }); 
            ';
        $app->js_set($js);
        $app->render();
    }
    
    
    public function add(){
        
        $this->load->helper($this->path->customer_refund_engine);
        $post = $this->input->post();
        
        $this->view('','add');
        
    }
    
    
    public function view($id="",$method="view"){
        
        $this->load->helper($this->path->customer_refund_engine);
        $this->load->helper($this->path->customer_refund_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Customer_Refund_Engine::customer_refund_exists($id)){
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
            $app->set_breadcrumb($this->title,'customer_refund');
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row')->div_set('id','customer_refund');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Customer_Refund_Renderer::customer_refund_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Customer_Refund_Renderer::customer_refund_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
            }            
            
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
        
        
    }
    
    public function ajax_search($method="",$submethod=""){
        //<editor-fold defaultstate="collapsed">
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $success = 1;
        $msg = [];
        $response = array();
        $limit = 10;
        
        switch($method){
            
            case 'customer_refund':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');                
                $config = array(
                    'additional_filter'=>array(
                        array('key'=>'reference_type','query'=>'and t1.customer_refund_type = '),
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select distinct t1.*
                                from customer_refund t1                    
                                where t1.status>0
                        ',
                        'where'=>'
                            and (t1.code like '.$lookup_str.'
                            )
                        ',
                        'group'=>'
                            )tfinal
                        ',
                        'order'=>'order by id desc'
                    ),
                );                
                $temp_result = SI::form_data()->ajax_table_search($config, $data);
                for($i = 0;$i<count($temp_result['data']);$i++){
                    $temp_result['data'][$i]['customer_refund_status'] =
                        SI::get_status_attr(
                            SI::status_get('Customer_Refund_Engine', 
                                $temp_result['data'][$i]['customer_refund_status']
                            )['label']
                        );
                    $temp_result['data'][$i]['amount'] = 
                        Tools::thousand_separator($temp_result['data'][$i]['amount'],5);
                    
                }
                $result = $temp_result;
                //</editor-fold>
                break;
            
            case 'input_select_reference_search':
                $db = new DB();

                $lookup_val = $data['data'];
                $search_param = array(
                    'lookup_val'=>$lookup_val
                );
                
                get_instance()->load->helper('customer_deposit/customer_deposit_data_support');
                $cd_arr = Customer_Deposit_Data_Support::customer_deposit_outstanding_amount_search($search_param);
                if(count($cd_arr)>0){
                    foreach($cd_arr as $idx=>$cd){
                        $response[] = array(
                            'id'=>$cd['id'],
                            'text'=>SI::html_tag('strong',$cd['code']).' '.
                            Tools::currency_get().Tools::thousand_separator($cd['outstanding_amount'],5),
                            'reference_type'=>'customer_deposit',
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
    
    public function data_support($method="",$submethod=""){
        //<editor-fold defaultstate="collapsed">
        //this function only used for urgently data retrieve
        get_instance()->load->helper('customer_refund/customer_refund_engine');
        get_instance()->load->helper('customer_refund/customer_refund_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'customer_refund_get':
                //<editor-fold defaultstate="collapsed">
                $response =array();
                $db = new DB();
                $customer_refund_id = Tools::_str($data['data']);
                $q = '
                    select t1.*,
                        t2.code store_code,
                        t2.name store_name

                    from customer_refund t1
                        inner join store t2 on t1.store_id = t2.id
                    where t1.id = '.$db->escape($customer_refund_id).'
                ';
                $rs = $db->query_array($q);

                if(count($rs)>0){
                    $customer_refund = $rs[0];   
                    $cr_type = $customer_refund['customer_refund_type'];
                    
                    $customer_refund['customer_refund_date'] = Tools::_date($customer_refund['customer_refund_date'],'F d, Y H:i');
                    $customer_refund['store_text'] = SI::html_tag('strong',$customer_refund['store_code'])
                        .' '.$customer_refund['store_name'];
                    $customer_refund['customer_refund_status_text'] = SI::get_status_attr(
                            SI::status_get('Customer_Refund_Engine',$customer_refund['customer_refund_status'])['label']
                        );

                    $customer_refund['amount'] = Tools::thousand_separator($customer_refund['amount']);
                    
                    
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('Customer_Refund_Engine',
                            $customer_refund['customer_refund_status']
                        );
                    
                    $reference = array('id'=>'','reference_type'=>'','text'=>'');
                    $reference_detail = array();
                    $q = '';
                    switch($cr_type){
                        case 'customer_deposit':
                            //get_instance()->load->helper('customer_deposit/customer_deposit_data_support');
                            $q = '
                                select t1.customer_deposit_id
                                from customer_deposit_customer_refund t1 
                                where customer_refund_id = '.$db->escape($customer_refund['id']).' 
                            ';
                            
                            $rs = $db->query_array($q);

                            if(count($rs)>0){
                                get_instance()->load->helper('customer_deposit/customer_deposit_data_support');
                                $customer_deposit = Customer_Deposit_Data_Support::customer_deposit_get($rs[0]['customer_deposit_id']);
                                
                                $reference = array(
                                    'id'=>$rs[0]['customer_deposit_id'],
                                    'text'=>SI::html_tag('strong',$customer_deposit['code']).' '.
                                        Tools::currency_get().Tools::thousand_separator($customer_deposit['amount']),
                                    'reference_type'=>'customer_deposit'
                                );
                            }
                            break;
                    }
                    
                    
                    
                    $reference_detail = Customer_Refund_Data_Support::reference_detail_get($reference['reference_type'],$reference['id']);
                    
                    $response['reference'] = $reference;
                    $response['reference_detail'] = $reference_detail;
                    $response['customer_refund'] = $customer_refund;
                    $response['customer_refund_status_list'] = $next_allowed_status_list;
                }
                //</editor-fold>
                break;
                case 'input_select_reference_detail_get':
                    $reference_id = isset($data['reference_id'])?Tools::_str($data['reference_id']):'';
                    $reference_type = isset($data['reference_type'])?Tools::_str($data['reference_type']):'';
                    $reference_detail = Customer_Refund_Data_Support::reference_detail_get($reference_type,$reference_id);
                    $response = $reference_detail;
                    break;
            
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function customer_refund_add(){
        
        $this->load->helper($this->path->customer_refund_engine);
        $post = $this->input->post();
        if($post!= null){
            Customer_Refund_Engine::submit('','customer_refund_add',$post);
        }
        
    }
    
    public function customer_refund_invoiced($id){
        
        $this->load->helper($this->path->customer_refund_engine);
        $post = $this->input->post();
        if($post!= null){
            Customer_Refund_Engine::submit($id,'customer_refund_invoiced',$post);
        }
        
        
    }
    
    public function customer_refund_canceled($id){
        
        $this->load->helper($this->path->customer_refund_engine);
        $post = $this->input->post();
        if($post!= null){
            Customer_Refund_Engine::submit($id,'customer_refund_canceled',$post);
        }
        
        
    }
}

?>