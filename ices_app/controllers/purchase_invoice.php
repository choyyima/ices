<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Purchase_Invoice extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get('Purchase Invoice');
        get_instance()->load->helper('purchase_invoice/purchase_invoice_engine');
        $this->path = Purchase_Invoice_Engine::path_get();
        $this->title_icon = App_Icon::purchase_invoice();
        
    }
    
    public function index()
    {           
        //<editor-fold defaultstate="collapsed">
        $action = "";

        $app = new App();            
        $db = $this->db;

        
        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower($this->title));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title',Lang::get('Purchase Invoice List'))->form_set('span','12');
        $form->form_group_add();
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get('New Purchase Invoice'))
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        $cols = array(
            array("name"=>"code","label"=>Lang::get("Code"),"data_type"=>"text","is_key"=>true),
            array("name"=>"purchase_invoice_date","label"=>Lang::get("Date"),"data_type"=>"text"),
            array("name"=>"grand_total","label"=>Lang::get("Grand Total"),"data_type"=>"text",'attribute'=>array('style'=>"text-align:right"),'row_attrib'=>array('style'=>'text-align:right')),
            array("name"=>"purchase_invoice_status","label"=>Lang::get("Status"),"data_type"=>"text"),
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/purchase_invoice')
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
        //</editor-fold>
    }
    
    
    public function add(){
        
        $this->load->helper($this->path->purchase_invoice_engine);
        $post = $this->input->post();
        
        $this->view('','add');
        
    }
    
    
    public function view($id="",$method="view"){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->purchase_invoice_engine);
        $this->load->helper($this->path->purchase_invoice_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Purchase_Invoice_Engine::purchase_invoice_exists($id)){
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
            $app->set_breadcrumb($this->title,'purchase_invoice');
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row')->div_set('id','purchase_invoice');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Purchase_Invoice_Renderer::purchase_invoice_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Purchase_Invoice_Renderer::purchase_invoice_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
                
                $receive_product_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#receive_product_tab',"value"=>"Receive Product"));
                $receive_product_pane = $receive_product_tab->div_add()->div_set('id','receive_product_tab')->div_set('class','tab-pane');
                Purchase_Invoice_Renderer::receive_product_view_render($app,$receive_product_pane,array("id"=>$id),$this->path);
                
                $purchase_receipt_allocation_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#pra_tab',"value"=>"Purchase Receipt Allocation"));
                $pra_pane = $purchase_receipt_allocation_tab->div_add()->div_set('id','pra_tab')->div_set('class','tab-pane');
                Purchase_Invoice_Renderer::pra_view_render($app,$pra_pane,array("id"=>$id),$this->path);
                
            }            
            
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
        //</editor-fold>
        
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
            
            case 'purchase_invoice':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');                
                $config = array(
                    'reference_type'=>array(
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select distinct t1.*
                                from purchase_invoice t1                    
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
                    $temp_result['data'][$i]['purchase_invoice_status'] =
                        SI::get_status_attr(
                            SI::status_get('Purchase_Invoice_Engine', 
                                $temp_result['data'][$i]['purchase_invoice_status']
                            )['label']
                        );
                    $temp_result['data'][$i]['grand_total'] = 
                        Tools::thousand_separator($temp_result['data'][$i]['grand_total'],5);
                    
                    
                }
                $result = $temp_result;
                //</editor-fold>
                break;
            
            case 'input_select_supplier_search':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('supplier/supplier_data_support');
                $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
                $cust_arr = Supplier_Data_Support::supplier_active_search($lookup_data);
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
                
            case 'input_select_product_search':
                //<editor-fold defaultstate="collapsed">
                $response = array();
                $limit = 10;
                $db = new DB();
                $lookup_str = $db->escape('%'.(isset($data['data'])?$data['data']:'').'%');
                $excluded_product = isset($data['excluded_product'])?$data['excluded_product']:'';
                $price_list_id = isset($data['price_list_id'])?$data['price_list_id']:'';
                $q_excluded_product='';
                foreach($excluded_product as $idx=>$val){
                    if($q_excluded_product ==='')
                        $q_excluded_product.=$db->escape($val);
                    else
                        $q_excluded_product.=','.$db->escape($val);
                }
                $q = '
                    select distinct t1.id id, t1.name, t1.code
                    from product t1
                    where t1.status>0 
                        and t1.product_status = "active"
                        and (
                            t1.code like '.$lookup_str.'
                            or t1.name like '.$lookup_str.'
                        )
                        and t1.id not in ('.$q_excluded_product.')
                            
                    order by t1.code
                    limit '.$limit.'
                    
                ';
                $rs = $db->query_array($q);
                $response = array();
                if(count($rs)>0){
                    for($i = 0;$i<count($rs);$i++){
                        $unit_arr = null;
                        
                        $product_id = $rs[$i]['id'];
                        $q='
                            select t2.id, t2.code text
                            from product_unit t1
                                inner join unit t2 on t1.unit_id = t2.id
                            where t1.product_id='.$db->escape($product_id).'
                        ';
                        $rs_unit = $db->query_array($q);
                        $unit_arr = $rs_unit;
                        $rs[$i]['unit'] = $unit_arr;
                        $rs[$i]['text'] = SI::html_tag('strong',$rs[$i]['code']).' '.$rs[$i]['name'];
                    }
                    $response = $rs;
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
        //<editor-fold defaultstate="collapsed">
        //this function only used for urgently data retrieve
        get_instance()->load->helper('purchase_invoice/purchase_invoice_engine');
        get_instance()->load->helper('purchase_invoice/purchase_invoice_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'purchase_invoice_get':
                get_instance()->load->helper('product/product_engine');
                $response =array();

                $purchase_invoice_id = Tools::_str($data['data']);
                
                $purchase_invoice = Purchase_Invoice_Data_Support::purchase_invoice_get($purchase_invoice_id);
                if(count($purchase_invoice)>0){
                    $purchase_invoice['purchase_invoice_date'] = Tools::_date($purchase_invoice['purchase_invoice_date'],'F d, Y H:i');
                    
                    $purchase_invoice['store_text'] = SI::html_tag('strong',$purchase_invoice['store_code'])
                        .' '.$purchase_invoice['store_name'];
                    $purchase_invoice['purchase_invoice_status_text'] = SI::get_status_attr(
                            SI::status_get('Purchase_Invoice_Engine',$purchase_invoice['purchase_invoice_status'])['label']
                        );
                    $purchase_invoice['supplier_text'] = SI::html_tag('strong',$purchase_invoice['supplier_code'])
                        .' '.$purchase_invoice['supplier_name'];                    
                    
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('Purchase_Invoice_Engine',
                            $purchase_invoice['purchase_invoice_status']
                        );
                    
                    $pi_info  = Purchase_Invoice_Data_Support::purchase_invoice_info_get($purchase_invoice_id);
                    $pi_info['product_arrival_date'] = Tools::_date($pi_info['product_arrival_date'],'F d, Y H:i');
                    
                    $pi_product = array();
                    $pi_product_raw = Purchase_Invoice_Data_Support::purchase_invoice_product_get($purchase_invoice_id);
                    foreach($pi_product_raw as $idx=>$product){
                        $pi_product[] = array(
                            'product_id' => $product['product_id'],
                            'product_img'=>Product_Engine::img_get($product['product_id']),
                            'product_text' => $product['product_code'],
                            'unit_id' => $product['unit_id'],
                            'unit_text' => $product['unit_code'],
                            'qty' => Tools::thousand_separator($product['qty']),
                            'amount' => Tools::thousand_separator($product['amount']),
                            'subtotal' => Tools::thousand_separator($product['subtotal']),
                            'movement_outstanding_qty' => Tools::thousand_separator($product['movement_outstanding_qty']),
                        );
                    }
                    
                    $pi_expense = array();
                    $pi_expense_raw = Purchase_Invoice_Data_Support::purchase_invoice_expense_get($purchase_invoice_id);
                    foreach($pi_expense_raw as $idx=>$expense){
                        $pi_expense[] = array(
                            'description'=>$expense['description'],
                            'amount'=>Tools::thousand_separator($expense['amount']),
                        );
                    }
                    
                    $response['info'] = $pi_info;
                    $response['purchase_invoice'] = $purchase_invoice;
                    $response['product'] = $pi_product;
                    $response['expense'] = $pi_expense;
                    $response['purchase_invoice_status_list'] = $next_allowed_status_list;
                }
                
                break;
                
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function purchase_invoice_add(){
        
        $this->load->helper($this->path->purchase_invoice_engine);
        $post = $this->input->post();
        if($post!= null){
            Purchase_Invoice_Engine::submit('','purchase_invoice_add',$post);
        }
        
    }
    
    public function purchase_invoice_invoiced($id=""){
        
        $this->load->helper($this->path->purchase_invoice_engine);
        $post = $this->input->post();
        if($post!= null){
            Purchase_Invoice_Engine::submit($id,'purchase_invoice_invoiced',$post);
        }
        
        
    }
    
    public function purchase_invoice_canceled($id=""){
        
        $this->load->helper($this->path->purchase_invoice_engine);
        $post = $this->input->post();
        if($post!= null){
            Purchase_Invoice_Engine::submit($id,'purchase_invoice_canceled',$post);
        }
        
        
    }
}

?>
