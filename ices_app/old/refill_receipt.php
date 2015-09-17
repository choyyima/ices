<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Receipt extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get('Refill - Receipt');
        get_instance()->load->helper('refill_receipt/refill_receipt_engine');
        $this->path = Refill_Receipt_Engine::path_get();
        $this->title_icon = App_Icon::refill_receipt();
        
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
        $form = $row->form_add()->form_set('title',Lang::get('Refill Receipt').' List')->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array(array('val'=>'New','grammar'=>'adj','uc_first'=>'true'),'Receipt')))
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        
        $form->form_group_add();
        $cols = array(
            array("name"=>"code","label"=>Lang::get("Code"),"data_type"=>"text","is_key"=>true),
            array("name"=>"creator","label"=>Lang::get("Creator"),"data_type"=>"text",'attribute'=>array('style'=>"text-align:left"),'row_attrib'=>array('style'=>'text-align:left')),
            array("name"=>"refill_receipt_date","label"=>Lang::get("Refill Receipt")." Date","data_type"=>"text"),            
            array("name"=>"description","label"=>Lang::get("Description"),"data_type"=>"text",'attribute'=>array('style'=>"text-align:left"),'row_attrib'=>array('style'=>'text-align:left')),
            
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/refill_receipt')
                ->table_ajax_set('columns',$cols)
                ->table_ajax_set('key_column','id')
                ->filter_set(array(
                        array('id'=>'reference_type_filter','field'=>'reference_type')
                    ))
                ;        
        
        
        $app->render();
    }
    
    
    public function add(){
        
        $this->load->helper($this->path->refill_receipt_engine);
        $post = $this->input->post();
        
        $this->view('','add');
        
    }
    
    
    public function view($id="",$method="view"){
        
        $this->load->helper($this->path->refill_receipt_engine);
        $this->load->helper($this->path->refill_receipt_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Refill_Receipt_Engine::refill_receipt_exists($id)){
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
            $app->set_breadcrumb($this->title,'refill_receipt');
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row')->div_set('id','refill_receipt');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Refill_Receipt_Renderer::refill_receipt_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Refill_Receipt_Renderer::refill_receipt_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
                
                
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
            case 'refill_receipt':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');                
                $config = array(
                    'additional_filter'=>array(
                        
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select distinct t1.*
                                from refill_receipt t1                    
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
                    $temp_result['data'][$i]['refill_receipt_status_text'] =
                        SI::get_status_attr(
                            SI::status_get('Refill_Receipt_Engine', 
                                $temp_result['data'][$i]['refill_receipt_status']
                            )['label']
                        );
                    
                }
                $result = $temp_result;
                //</editor-fold>
                break;
            
            case 'input_select_customer_search':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('customer/customer_data_support');
                $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
                $cust_arr = Customer_Data_Support::customer_active_search($lookup_data);
                if(count($cust_arr)>0){
                    foreach($cust_arr as $cust_idx=>$cust){
                        $response[] = array(
                            'id'=>$cust['id'],
                            'text'=>SI::html_tag('strong',$cust['code']).' '.
                            $cust['name'].' '.$cust['phone'].' '.$cust['bb_pin'],
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
    
    public function data_support($method="",$submethod=""){
        //this function only used for urgently data retrieve
        get_instance()->load->helper('refill_receipt/refill_receipt_engine');
        get_instance()->load->helper('refill_receipt/refill_receipt_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'refill_receipt_get':
                //<editor-fold defaultstate="collapsed">
                $response =array();
                $db = new DB();
                $refill_receipt_id = $data['data'];
                $q = '
                    select t1.*,
                        t2.code store_code,
                        t2.name store_name
                    from refill_receipt t1
                        inner join store t2 on t1.store_id = t2.id

                    where t1.id = '.$db->escape($refill_receipt_id).'
                ';
                $rs = $db->query_array($q);

                if(count($rs)>0){
                    $refill_receipt = $rs[0];                    
                    $module_name = $refill_receipt['module_name'];
                    $module_action = $refill_receipt['module_action'];
                    $reference = array('id'=>'','text'=>'');
                    $reference_detail = array();
                    $extra_data = array();
                    switch($module_name.'_'.$module_action){
                        case 'sales_invoice_pos_cancel':
                            $q = '
                                select t1.id, t1.code
                                from sales_invoice t1
                                where t1.id = '.$db->escape($refill_receipt['reference_id']).'
                            ';
                            $rs = $db->query_array($q);
                            if(count($rs)>0){
                                $reference['id'] = $rs[0]['id'];
                                $reference['text'] = $rs[0]['code'];
                            }
                            $reference_detail = Refill_Receipt_Data_Support::reference_detail_get($module_name, $module_action, $refill_receipt['reference_id']);

                            break;
                        case 'product_stock_opname':
                            $q = '
                                select t1.id, t1.code
                                from product_stock_opname t1
                                where t1.id = '.$db->escape($refill_receipt['reference_id']).'
                            ';
                            $rs = $db->query_array($q);
                            if(count($rs)>0){
                                $reference['id'] = $rs[0]['id'];
                                $reference['text'] = $rs[0]['code'];
                            }
                            $reference_detail = Refill_Receipt_Data_Support::reference_detail_get($module_name, $module_action, $refill_receipt['reference_id']);

                            $q = 'select * from product_stock_opname_product where product_stock_opname_id = '.$db->escape($refill_receipt['reference_id']);
                            $rs = $db->query_array($q);
                            if(count($rs)>0){
                                $extra_data['product_stock_opname_product'] = $rs;
                            }
                            
                            break;    
                            
                    }
                    $refill_receipt['module_name_text'] = Refill_Receipt_Data_Support::module_get($module_name)['name']['label'];
                    $refill_receipt['module_action_text'] = Refill_Receipt_Data_Support::module_action_get($module_name, $module_action)['label'];
                    $refill_receipt['refill_receipt_date'] = Tools::_date($refill_receipt['refill_receipt_date'],'F d, Y H:i');
                    $refill_receipt['store_text'] = SI::html_tag('strong',$refill_receipt['store_code'])
                        .' '.$refill_receipt['store_name'];
                    $refill_receipt['refill_receipt_status_text'] = SI::get_status_attr(
                            SI::status_get('Refill_Receipt_Engine',$refill_receipt['refill_receipt_status'])['label']
                        );
                    
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('Refill_Receipt_Engine',
                            $refill_receipt['refill_receipt_status']
                        );
                    
                    $response['reference'] = $reference;
                    $response['reference_detail'] = $reference_detail;
                    $response['refill_receipt'] = $refill_receipt;
                    $response['refill_receipt_status_list'] = $next_allowed_status_list;
                    $response['extra_data'] = $extra_data;
                }
                //</editor-fold>
                break;
            case 'input_select_reference_dependency_get':
                $response = array();
                $module_type = isset($data['module_type'])?Tools::_str($data['module_type']):'';
                $reference_id = isset($data['reference_id'])?Tools::_str($data['reference_id']):'';
                switch($module_type){
                    case 'deposit':
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
                
                break;
            case 'input_select_reference_detail_get':
                $module_type = isset($data['module_type'])?Tools::_str($data['module_type']):'';
                $reference_id = isset($data['reference_id'])?Tools::_str($data['reference_id']):'';
                $reference_detail = Refill_Receipt_Data_Support::reference_detail_get($module_type, $reference_id);
                $response = $reference_detail;
                break;
            case 'input_select_payment_type_get':
                //<editor-fold defaultstate="collapsed">
                $response = array();
                $customer_id = isset($data['customer_id'])?$data['customer_id']:'';
                $payment_type_arr = Refill_Receipt_Data_Support::customer_payment_type_get($customer_id);
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
                
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
    }
    
    public function refill_receipt_add(){
        
        $this->load->helper($this->path->refill_receipt_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>'','method'=>'refill_receipt_add','primary_data_key'=>'refill_receipt','data_post'=>$post);            
            SI::data_submit()->submit('refill_receipt_engine',$param);
        }
        
    }
    
    public function refill_receipt_done($id){
        
        $this->load->helper($this->path->refill_receipt_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'refill_receipt_done','primary_data_key'=>'refill_receipt','data_post'=>$post);            
            SI::data_submit()->submit('refill_receipt_engine',$param);
        }
        
    }
}

?>