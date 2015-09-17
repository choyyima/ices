<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Invoice extends MY_Controller {
        
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get(array('Refill - Invoice'),true,true,false,false,true);
        get_instance()->load->helper('refill_invoice/refill_invoice_engine');
        $this->path = Refill_Invoice_Engine::path_get();
        $this->title_icon = App_Icon::refill_invoice();
    }
    
    public function index()
    {           

        $action = "";

        $app = new App();            
        $db = $this->db;

        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower('refill_invoice'));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title',Lang::get(array('Refill - Invoice','List')))
            ->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array(array('val'=>'New','grammar'=>'adj'),array('val'=>'Refill Invoice'))))
                ->button_set('icon','fa fa-plus')->button_set('href',get_instance()->config->base_url().'refill_invoice/add');
        
        $cols = array(
            array("name"=>"code","label"=>"Code","data_type"=>"text","is_key"=>true)
            ,array("name"=>"refill_invoice_date","label"=>"Invoice Date","data_type"=>"text")
            ,array("name"=>"grand_total_amount","label"=>"Grand Total Amount","data_type"=>"text",'attribute'=>array('style'=>'text-align:right'),'row_attrib'=>array('style'=>'text-align:right'))
            ,array("name"=>"refill_invoice_status_text","label"=>"Status","data_type"=>"text")
            

        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/refill_invoice')
                //->table_ajax_set('controls',$controls)
                ->table_ajax_set('columns',$cols);
        
        
        $app->render();
    }
    
    public function add(){
        $this->load->helper($this->path->refill_invoice_engine);
        $post = $this->input->post();        
        $this->view('','add');
        
    }
    
    public function view($id = "",$method="view"){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->refill_invoice_engine);
        $this->load->helper($this->path->refill_invoice_data_support);
        $this->load->helper($this->path->refill_invoice_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Refill_Invoice_Data_Support::refill_invoice_exists($id)){
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
            $app->set_menu('collapsed',false);
            $app->set_breadcrumb($this->title,strtolower('refill_invoice'));
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Refill_Invoice_Renderer::refill_invoice_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Refill_Invoice_Renderer::refill_invoice_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
                
                $customer_deposit_allocation_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#cda_tab',"value"=>"Customer Deposit Allocation"));
                $pra_pane = $customer_deposit_allocation_tab->div_add()->div_set('id','cda_tab')->div_set('class','tab-pane');
                Refill_Invoice_Renderer::cda_view_render($app,$pra_pane,array("id"=>$id),$this->path);
                
                $refill_receipt_allocation_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#refill_receipt_allocation_tab',"value"=>"Refill Receipt Allocation"));
                $refill_receipt_allocation_pane = $refill_receipt_allocation_tab->div_add()->div_set('id','refill_receipt_allocation_tab')->div_set('class','tab-pane');
                Refill_Invoice_Renderer::refill_receipt_allocation_view_render($app,$refill_receipt_allocation_pane,array("id"=>$id),$this->path);
                
                
            }
            
            
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
        //</editor-fold>
    }
    
    
    public function ajax_search($method){
        //<editor-fold defaultstate="collapsed">
        $data = json_decode($this->input->post(), true);
        $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
        $result =array('success'=>1,'msg'=>[]);
        $success = 1;
        $msg = [];
        $response = array();
        $limit = 10;
        switch($method){
            case 'refill_invoice':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = $db->escape('%'.$lookup_data.'%');                
                $config = array(
                    'additional_filter'=>array(
                        
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select ri.*
                                from refill_invoice ri
                                where ri.status>0
                                
                        ',
                        'where'=>'
                            and (ri.code like '.$lookup_str.'
                            )
                        ',
                        'group'=>' 
                            )tfinal
                        ',
                        'order'=>'order by id desc'
                    ),
                );                
                $temp_result = SI::form_data()->ajax_table_search($config, $data,array('output_type'=>'object'));
                $t_data = $temp_result->data;
                foreach($t_data as $i=>$row){
                    $row->refill_invoice_status_text =
                        SI::get_status_attr(
                            SI::status_get('Refill_Invoice_Engine', 
                                $row->refill_invoice_status
                            )['label']
                        );
                    $row->grand_total_amount = Tools::thousand_separator($row->grand_total_amount,2);
                }
                $temp_result = json_decode(json_encode($temp_result),true);
                $result = $temp_result;
                //</editor-fold>
                break;
            case 'input_select_reference_search':
                //<editor-fold defaultstate="collapsed">
                $response = array();
                $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
                
                //<editor-fold defaultstate="collapsed" desc="Refill Work Order">
                get_instance()->load->helper('refill_invoice/refill_invoice_data_support');
                $response = Refill_Invoice_Data_Support::reference_search($lookup_data);
                
                //</editor-fold>
                //</editor-fold>
                break;
            case 'input_select_component_product_search':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('refill_invoice/refill_invoice_data_support');
                $response = array();
                $lookup_str = isset($data['data'])?Tools::_str($data['data']):'';
                $warehouse_id = isset($data['extra_param']['warehouse_id'])?
                    Tools::_str($data['extra_param']['warehouse_id']):'';
                $response = Refill_Invoice_Data_Support::product_search($lookup_str,$warehouse_id);
                //</editor-fold>
                break;
            case 'input_select_result_product_search':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('refill_invoice/refill_invoice_data_support');
                $response = array();
                $lookup_str = isset($data['data'])?Tools::_str($data['data']):'';
                $warehouse_id = isset($data['extra_param']['warehouse_id'])?
                    Tools::_str($data['extra_param']['warehouse_id']):'';
                $response = Refill_Invoice_Data_Support::product_search($lookup_str,$warehouse_id);
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
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('refill_invoice/refill_invoice_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[],'response'=>array());
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'refill_invoice_get':
                get_instance()->load->helper('refill_invoice/refill_invoice_engine');
                get_instance()->load->helper('product/product_engine');
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $response = array();
                $q = '
                    select t1.*
                        ,t2.id store_id
                        ,t2.code store_code
                        ,t2.name store_name
                        ,c.code customer_code
                        ,c.name customer_name
                    from refill_invoice t1
                        inner join store t2 on t1.store_id = t2.id
                        inner join customer c on t1.customer_id = c.id
                    where t1.id = '.$db->escape($data['data']).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $rs[0]['store_text'] = $rs[0]['store_name'];                    
                    $refill_invoice = $rs[0];
                    $refill_invoice['customer_text'] = SI::html_tag('strong',$refill_invoice['customer_code']).' '.$refill_invoice['customer_name'];
                    $refill_invoice_id = $refill_invoice['id'];
                    $refill_invoice_type = $refill_invoice['refill_invoice_type'];
                    $reference_id = $refill_invoice['reference_id'];
                    
                    $reference = Refill_Invoice_Data_Support::reference_get($refill_invoice_type,$reference_id);
                    $reference_detail = Refill_Invoice_Data_Support::reference_detail_get($refill_invoice_type,$reference_id);
                    $refill_invoice['refill_invoice_type_text'] = SI::type_get('refill_invoice_engine',$refill_invoice_type)['label'];
                    $refill_invoice['refill_invoice_status_text'] = SI::get_status_attr(
                            SI::status_get('Refill_Invoice_Engine',$refill_invoice['refill_invoice_status'])['label']
                        );
                    
                    $ri_product = Refill_Invoice_Data_Support::ri_product_get($refill_invoice_id);
                    $ri_product = json_decode(json_encode($ri_product));
                    foreach($ri_product as $i=>$row){
                        $row->product_text = $row->product_marking_code;
                        $row->unit_text = SI::html_tag('strong',$row->unit_code);
                    }
                    $ri_product = json_decode(json_encode($ri_product),true);
                    
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('refill_invoice_engine',
                            $refill_invoice['refill_invoice_status']
                        );
                    
                    $response['reference'] = $reference;
                    $response['reference_detail'] = $reference_detail;
                    $response['refill_invoice'] = $refill_invoice;
                    $response['ri_product'] = $ri_product;
                    $response['refill_invoice_status_list'] = $next_allowed_status_list;
                    
                }
                
                
                //</editor-fold>
                break;
            case 'reference_dependency_get':
                //<editor-fold defaultstate="collapsed">
                $response = array();
                $reference_type = isset($data['reference_type'])?Tools::_str($data['reference_type']):'';
                $reference_id = isset($data['reference_id'])?Tools::_str($data['reference_id']):'';
                $response = Refill_Invoice_Data_Support::reference_dependency_get($reference_type, $reference_id);
                //</editor-fold>
                break;
            case 'available_expected_result_product_get':
                //<editor-fold defaultstate="collapsed">
                $reference_id = isset($data['reference_id'])?Tools::_str($data['reference_id']):'';
                $warehouse_id = isset($data['warehouse_id'])?Tools::_str($data['warehouse_id']):'';
                $t_prod = Refill_Invoice_Data_Support::available_expected_result_product_get($reference_id,$warehouse_id);
                if(count($t_prod)>0){
                    $t_prod = json_decode(json_encode($t_prod));
                    foreach($t_prod as $i=>$row){
                        $row->product_text = SI::html_tag('strong',$row->product_code)
                            .' '.$row->product_name;
                        $row->unit_text = SI::html_tag('strong',$row->unit_code)
                            .' '.$row->unit_name;
                        $row->bom_text = SI::html_tag('strong',$row->bom_code)
                            .' '.$row->bom_name;
                        
                    }
                    $t_prod = json_decode(json_encode($t_prod),true);
                    $response = $t_prod;
                }
                //</editor-fold>
                break;
            case 'available_component_product_get':
                //<editor-fold defaultstate="collapsed">
                $response = array();
                $module_type = isset($data['module_type'])?Tools::_str($data['module_type']):'';
                $warehouse_id = isset($data['warehouse_id'])?Tools::_str($data['warehouse_id']):'';
                $expected_product = isset($data['expected_product'])?Tools::_arr($data['expected_product']):array();
                
                $t_comp_prod = Refill_Invoice_Data_Support::available_component_product_get(
                    $module_type,$warehouse_id,$expected_product
                );
                
                if(count($t_comp_prod)>0){
                    $t_comp_prod = json_decode(json_encode($t_comp_prod));
                    foreach($t_comp_prod as $i=>$row){
                        $row->product_text = SI::html_tag('strong',$row->product_code)
                            .' '.$row->product_name;
                        $row->unit_text = SI::html_tag('strong',$row->unit_code)
                            .' '.$row->unit_name;
                        $row->product_img = Product_Engine::img_get($row->product_id);
                    }
                    $response = $t_comp_prod;
                }
                $response = $t_comp_prod;
                //</editor-fold>
                break;
            case 'available_result_product_get':
                //<editor-fold defaultstate="collapsed">
                $response = array();
                $refill_invoice_id = isset($data['refill_invoice_id'])?Tools::_str($data['refill_invoice_id']):'';
                
                $t_res_prod = Refill_Invoice_Data_Support::available_result_product_get(
                    $refill_invoice_id
                );
                
                if(count($t_res_prod)>0){
                    $t_res_prod = json_decode(json_encode($t_res_prod));
                    foreach($t_res_prod as $i=>$row){
                        $row->product_text = SI::html_tag('strong',$row->product_code)
                            .' '.$row->product_name;
                        $row->unit_text = SI::html_tag('strong',$row->unit_code)
                            .' '.$row->unit_name;
                        $row->product_img = Product_Engine::img_get($row->product_id);
                        $row->stock_location_text = SI::type_get('refill_invoice_engine', $row->stock_location,'$stock_location_list')['label'];
                    }
                    $response = $t_res_prod;
                }
                $response = $t_res_prod;
                //</editor-fold>
                break;
            case 'available_scrap_product_get':
                //<editor-fold defaultstate="collapsed">
                $response = array();
                $refill_invoice_id = isset($data['refill_invoice_id'])?Tools::_str($data['refill_invoice_id']):'';
                
                $t_res_prod = Refill_Invoice_Data_Support::available_scrap_product_get(
                    $refill_invoice_id
                );
                
                if(count($t_res_prod)>0){
                    $t_res_prod = json_decode(json_encode($t_res_prod));
                    foreach($t_res_prod as $i=>$row){
                        $row->product_text = SI::html_tag('strong',$row->product_code)
                            .' '.$row->product_name;
                        $row->unit_text = SI::html_tag('strong',$row->unit_code)
                            .' '.$row->unit_name;
                        $row->product_img = Product_Engine::img_get($row->product_id);
                        $row->stock_location_text = SI::type_get('refill_invoice_engine', $row->stock_location,'$stock_location_list')['label'];
                    }
                    $response = $t_res_prod;
                }
                $response = $t_res_prod;
                //</editor-fold>
                break;
        }
        
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function refill_invoice_add(){
        $this->load->helper($this->path->refill_invoice_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>'','method'=>'refill_invoice_add','primary_data_key'=>'refill_invoice','data_post'=>$post);            
            SI::data_submit()->submit('refill_invoice_engine',$param);
            
        }        
    }
    
    public function refill_invoice_process($id=''){
        $this->load->helper($this->path->refill_invoice_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'refill_invoice_process','primary_data_key'=>'refill_invoice','data_post'=>$post);
            SI::data_submit()->submit('refill_invoice_engine',$param);
        }        
    }
    
    public function refill_invoice_done($id=''){
        $this->load->helper($this->path->refill_invoice_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'refill_invoice_done','primary_data_key'=>'refill_invoice','data_post'=>$post);
            SI::data_submit()->submit('refill_invoice_engine',$param);
        }
        
    }
    
    public function refill_invoice_canceled($id=''){
        $this->load->helper($this->path->refill_invoice_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'refill_invoice_canceled','primary_data_key'=>'refill_invoice','data_post'=>$post);
            SI::data_submit()->submit('refill_invoice_engine',$param);
        }
        
    }
    
    public function refill_invoice_print($id,$module,$prm1=''){
        $this->load->helper($this->path->refill_invoice_print);
        $post = $this->input->post();
        switch($module){
            case 'invoice':
                Refill_Invoice_Print::invoice_print($id);
                break;
            case 'payment':
                Refill_Invoice_Print::payment_print($id);
                break;
           
        }
    }
}

