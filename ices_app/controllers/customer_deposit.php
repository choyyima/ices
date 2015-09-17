<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer_Deposit extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get('Customer Deposit');
        get_instance()->load->helper('customer_deposit/customer_deposit_engine');
        $this->path = Customer_Deposit_Engine::path_get();
        $this->title_icon = App_Icon::customer_deposit();
        
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
        $form = $row->form_add()->form_set('title',Lang::get(array('Customer Deposit','List')))->form_set('span','12');
        $form->form_group_add();
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array(array('val'=>'New','grammar'=>'adj','uc_first'=>'true'),'Customer Deposit')))
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        $cols = array(
            array("name"=>"code","label"=>Lang::get("Code"),"data_type"=>"text","is_key"=>true),
            array("name"=>"customer_deposit_type_text","label"=>Lang::get("Type"),"data_type"=>"text"),
            array("name"=>"customer_deposit_date","label"=>Lang::get("Date"),"data_type"=>"text"),
            array("name"=>"amount","label"=>Lang::get("Amount"),"data_type"=>"text",'attribute'=>array('style'=>"text-align:right"),'row_attrib'=>array('style'=>'text-align:right')),
            array("name"=>"outstanding_amount","label"=>Lang::get("Outstanding Amount"),"data_type"=>"text",'attribute'=>array('style'=>"text-align:right"),'row_attrib'=>array('style'=>'text-align:right')),
            array("name"=>"customer_deposit_status","label"=>Lang::get("Status"),"data_type"=>"text"),
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/customer_deposit')
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
        
        $this->load->helper($this->path->customer_deposit_engine);
        $post = $this->input->post();
        
        $this->view('','add');
        
    }
    
    
    public function view($id="",$method="view"){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->customer_deposit_engine);
        $this->load->helper($this->path->customer_deposit_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Customer_Deposit_Engine::customer_deposit_exists($id)){
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
            $app->set_breadcrumb($this->title,'customer_deposit');
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row')->div_set('id','customer_deposit');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Customer_Deposit_Renderer::customer_deposit_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Customer_Deposit_Renderer::customer_deposit_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
                
                $customer_deposit_allocation_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#customer_deposit_allocation_tab',"value"=>"Customer Deposit Allocation"));
                $customer_deposit_allocation_pane = $customer_deposit_allocation_tab->div_add()->div_set('id','customer_deposit_allocation_tab')->div_set('class','tab-pane');
                Customer_Deposit_Renderer::customer_deposit_allocation_view_render($app,$customer_deposit_allocation_pane,array("id"=>$id),$this->path);
                
                $customer_refund_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#customer_refund_tab',"value"=>"Customer Refund"));
                $customer_refund_pane = $customer_refund_tab->div_add()->div_set('id','customer_refund_tab')->div_set('class','tab-pane');
                Customer_Deposit_Renderer::customer_refund_view_render($app,$customer_refund_pane,array("id"=>$id),$this->path);
            }            
            
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
        //</editor-fold>
        
    }
    
    public function ajax_search($method="",$submethod=""){
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[],'response'=>array());
        $success = 1;
        $msg = [];
        $response = array();
        $limit = 10;
        switch($method){
            
            case 'customer_deposit':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');                
                $config = array(
                    'reference_type'=>array(
                        array('val'=>'sales_invoice','query'=>'and t1.delivery_order_type = "sales_invoice"'),
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select distinct t1.*
                                from customer_deposit t1                    
                                where t1.status>0
                        ',
                        'where'=>'
                            and (t1.code like '.$lookup_str.'
                            )
                        ',
                        'group'=>'
                            )tfinal
                        ',
                        'order'=>'order by customer_deposit_date desc'
                    ),
                );                
                $temp_result = SI::form_data()->ajax_table_search($config, $data);
                for($i = 0;$i<count($temp_result['data']);$i++){
                    $temp_result['data'][$i]['customer_deposit_type_text'] = 
                        SI::module_type_get('customer_deposit_engine', 
                            $temp_result['data'][$i]['customer_deposit_type']
                        )['label'];
                    $temp_result['data'][$i]['customer_deposit_status'] =
                        SI::get_status_attr(
                            SI::status_get('Customer_Deposit_Engine', 
                                $temp_result['data'][$i]['customer_deposit_status']
                            )['label']
                        );
                    $temp_result['data'][$i]['amount'] = 
                        Tools::thousand_separator($temp_result['data'][$i]['amount'],5);
                    $temp_result['data'][$i]['outstanding_amount'] = 
                        Tools::thousand_separator($temp_result['data'][$i]['outstanding_amount'],5);
                    
                }
                $result = $temp_result;
                //</editor-fold>
                break;
            case 'input_select_reference_search':
                //<editor-fold defaultstate="collapsed">
                $response = array();
                $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
                
                //<editor-fold defaultstate="collapsed" desc="Refill Work Order">
                get_instance()->load->helper('refill_work_order/refill_work_order_data_support');
                $temp_result = Refill_Work_Order_Data_Support::rwo_total_deposit_incomplete_search($lookup_data);
                if(count($temp_result)>0){
                    foreach($temp_result as $idx=>$row){
                        $response[] = array(
                            'id'=>$row['id'],
                            'text'=>SI::html_tag('strong',$row['code'])
                                .' '.'Total Estimated Amount: '.Tools::thousand_separator($row['total_estimated_amount'])
                                .' '.'Total Deposit Amount: '.Tools::thousand_separator($row['total_deposit_amount']),
                            'reference_type'=>'refill_work_order'
                        );
                    }
                }
                //</editor-fold>
                
                break;
            
            case 'input_select_customer_search':
                //<editor-fold defaultstate="collapsed">
                $response = array();
                $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
                
                //<editor-fold defaultstate="collapsed" desc="Refill Work Order">
                get_instance()->load->helper('customer/customer_data_support');
                $temp_result = Customer_Data_Support::customer_active_search($lookup_data);
                if(count($temp_result)>0){
                    foreach($temp_result as $idx=>$row){
                        $response[] = array(
                            'id'=>$row['id'],
                            'text'=>SI::html_tag('strong',$row['code']).' '.$row['name']
                        );
                    }
                }
                //</editor-fold>
                
                break;
                
                //</editor-fold>

            
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
    }
    
    public function data_support($method="",$submethod=""){
        //this function only used for urgently data retrieve
        get_instance()->load->helper('customer_deposit/customer_deposit_engine');
        get_instance()->load->helper('customer_deposit/customer_deposit_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'customer_deposit_get':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('payment_type/payment_type_data_support');
                $response =array();
                $db = new DB();
                $customer_deposit_id = Tools::_str($data['data']);
                $q = '
                    select t1.*,
                        t2.code store_code,
                        t2.name store_name,
                        t3.id customer_id,
                        t3.code customer_code,
                        t3.name customer_name
                        ,bba.code bos_bank_account_code
                        ,pt.code payment_type_code
                    from customer_deposit t1
                        inner join store t2 on t1.store_id = t2.id
                        inner join customer t3 
                            on t1.customer_id = t3.id
                        left outer join bos_bank_account bba 
                            on bba.id = t1.bos_bank_account_id
                        left outer join payment_type pt on t1.payment_type_id  = pt.id
                    where t1.id = '.$db->escape($customer_deposit_id).'
                ';
                $rs = $db->query_array($q);

                if(count($rs)>0){
                    $customer_deposit = $rs[0];                    
                    $cd_type = $customer_deposit['customer_deposit_type'];
                    $reference = array('type'=>$cd_type,'id'=>'','text'=>'');
                    $reference_detail = array();
                    switch($reference['type']){
                        case 'delivery_order_final_confirmation':
                            $q = '
                                select t1.*
                                from delivery_order_final_confirmation t1
                                    inner join dofc_cd t2 
                                    on t1.id = t2.delivery_order_final_confirmation_id
                                where t2.customer_deposit_id = '.$db->escape($customer_deposit['id']).'
                            ';
                            $rs = $db->query_array($q);
                            if(count($rs)>0){
                                $reference['id'] = $rs[0]['id'];
                                $reference['text'] = SI::html_tag('strong',$rs[0]['code']);
                                $reference_detail = Customer_Deposit_Data_Support::reference_detail_get($reference['type'], $reference['id']);
                            }
                            break;
                        case 'refill_work_order':
                            $q = '
                                select rwo.*
                                from refill_work_order rwo
                                    inner join rwo_cd 
                                    on rwo.id = rwo_cd.refill_work_order_id
                                where rwo_cd.customer_deposit_id = '.$db->escape($customer_deposit['id']).'
                            ';
                            $rs = $db->query_array($q);
                            if(count($rs)>0){
                                $reference['id'] = $rs[0]['id'];
                                $reference['text'] = $rs[0]['code'];
                                $reference_detail = Customer_Deposit_Data_Support::reference_detail_get('refill_work_order', $reference['id']);
                            }
                            break;
                    }
                    
                    $customer_deposit['customer_deposit_date'] = Tools::_date($customer_deposit['customer_deposit_date'],'F d, Y H:i');
                    $customer_deposit['deposit_date'] = is_null($customer_deposit['deposit_date'])?
                        null:Tools::_date($customer_deposit['deposit_date'],'F d, Y H:i');
                    $customer_deposit['store_text'] = SI::html_tag('strong',$customer_deposit['store_code'])
                        .' '.$customer_deposit['store_name'];
                    $customer_deposit['customer_deposit_status_text'] = SI::get_status_attr(
                            SI::status_get('Customer_Deposit_Engine',$customer_deposit['customer_deposit_status'])['label']
                        );
                    $customer_deposit['customer_text'] = SI::html_tag('strong',$customer_deposit['customer_code'])
                        .' '.$customer_deposit['customer_name'];
                    $customer_deposit['bos_bank_account_text'] = $customer_deposit['bos_bank_account_code'];
                    $customer_deposit['amount'] = Tools::thousand_separator($customer_deposit['amount']);
                    $customer_deposit['outstanding_amount'] = Tools::thousand_separator($customer_deposit['outstanding_amount']);
                    $customer_deposit['payment_type_text'] = Payment_Type_Data_Support::payment_type_get($customer_deposit['payment_type_id']) === null?
                            null:Payment_Type_Data_Support::payment_type_get($customer_deposit['payment_type_id'])['code'];
                    
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('Customer_Deposit_Engine',
                            $customer_deposit['customer_deposit_status']
                        );
                    
                    $response['reference'] = $reference;
                    $response['reference_detail'] = $reference_detail;
                    $response['customer_deposit'] = $customer_deposit;
                    $response['customer_deposit_status_list'] = $next_allowed_status_list;
                }
                //</editor-fold>
                break;
            case 'input_select_payment_type_get':
                //<editor-fold defaultstate="collapsed">
                $response = array();
                $customer_id = isset($data['customer_id'])?$data['customer_id']:'';
                $payment_type_arr = Customer_Deposit_Data_Support::customer_payment_type_get($customer_id);
                if(count($payment_type_arr)>0){
                    foreach($payment_type_arr as $payment_type_idx=>$payment_type){
                        $response[] = array(
                            'id'=>$payment_type['id'],
                            'text'=>SI::html_tag('strong',$payment_type['code']),
                            'code'=>$payment_type['code'],
                        );
                    }
                }
                //</editor-fold>
                break;
            case 'input_select_reference_dependency_get':
                //<editor-fold defaultstate="collapsed">
                $response = array();
                $reference_type = isset($data['reference_type'])?Tools::_str($data['reference_type']):'';
                $reference_id = isset($data['reference_id'])?Tools::_str($data['reference_id']):'';
                switch($reference_type){
                    case 'refill_work_order':
                        get_instance()->load->helper('refill_work_order/refill_work_order_data_support');
                        $rwo = Refill_Work_Order_Data_Support::refill_work_order_get($reference_id);
                        if(count($rwo)>0){
                            $response = array(
                                'customer_id'=>$rwo['customer_id'],
                                'customer_text'=>SI::html_tag('strong',$rwo['customer_code']).' '.$rwo['customer_name'].' '.$rwo['customer_phone'],
                                'total_estimated_amount'=>$rwo['total_estimated_amount'],
                                'total_deposit_amount'=>$rwo['total_deposit_amount'],
                            );
                        }
                        break;
                }
                //</editor-fold>
                break;
            case 'input_select_reference_detail_get':
                //<editor-fold defaultstate="collapsed">
                $reference_type = isset($data['reference_type'])?Tools::_str($data['reference_type']):'';
                $reference_id = isset($data['reference_id'])?Tools::_str($data['reference_id']):'';
                switch($reference_type){
                    case 'refill_work_order':
                        get_instance()->load->helper('refill_work_order/refill_work_order_data_support');
                        $response = Customer_Deposit_Data_Support::reference_detail_get($reference_type, $reference_id);
                        break;
                }
                //</editor-fold>
                break;
            
            
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
    }
    
    public function customer_deposit_add(){
        
        $this->load->helper($this->path->customer_deposit_engine);
        $post = $this->input->post();
        if($post!= null){
            Customer_Deposit_Engine::submit('','customer_deposit_add',$post);
        }
        
    }
    
    public function customer_deposit_invoiced($id){
        
        $this->load->helper($this->path->customer_deposit_engine);
        $post = $this->input->post();
        if($post!= null){
            Customer_Deposit_Engine::submit($id,'customer_deposit_invoiced',$post);
        }
        
        
    }
    
    public function customer_deposit_canceled($id){
        
        $this->load->helper($this->path->customer_deposit_engine);
        $post = $this->input->post();
        if($post!= null){
            Customer_Deposit_Engine::submit($id,'customer_deposit_canceled',$post);
        }
        
        
    }
}

?>