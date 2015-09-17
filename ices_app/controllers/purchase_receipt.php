<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Purchase_Receipt extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get('Purchase Receipt');
        get_instance()->load->helper('purchase_receipt/purchase_receipt_engine');
        $this->path = Purchase_Receipt_Engine::path_get();
        $this->title_icon = App_Icon::purchase_receipt();
        
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
        $form = $row->form_add()->form_set('title',Lang::get('Purchase Receipt List'))->form_set('span','12');
        $form->form_group_add();
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get('New Purchase Receipt'))
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        $cols = array(
            array("name"=>"code","label"=>Lang::get("Code"),"data_type"=>"text","is_key"=>true),
            array("name"=>"purchase_receipt_date","label"=>Lang::get("Date"),"data_type"=>"text"),
            array("name"=>"amount","label"=>Lang::get("Amount"),"data_type"=>"text",'attribute'=>array('style'=>"text-align:right"),'row_attrib'=>array('style'=>'text-align:right')),
            array("name"=>"outstanding_amount","label"=>Lang::get("Outstanding Amount"),"data_type"=>"text",'attribute'=>array('style'=>"text-align:right"),'row_attrib'=>array('style'=>'text-align:right')),
            array("name"=>"change_amount","label"=>Lang::get("Change Amount"),"data_type"=>"text",'attribute'=>array('style'=>"text-align:right"),'row_attrib'=>array('style'=>'text-align:right')),
            array("name"=>"purchase_receipt_status","label"=>Lang::get("Status"),"data_type"=>"text"),
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/purchase_receipt')
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
        
        $this->load->helper($this->path->purchase_receipt_engine);
        $post = $this->input->post();
        
        $this->view('','add');
        
    }
    
    
    public function view($id="",$method="view"){
        
        $this->load->helper($this->path->purchase_receipt_engine);
        $this->load->helper($this->path->purchase_receipt_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Purchase_Receipt_Engine::purchase_receipt_exists($id)){
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
            $app->set_breadcrumb($this->title,'purchase_receipt');
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row')->div_set('id','purchase_receipt');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Purchase_Receipt_Renderer::purchase_receipt_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Purchase_Receipt_Renderer::purchase_receipt_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
                
                $purchase_receipt_allocation_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#purchase_receipt_allocation_tab',"value"=>"Purchase Receipt Allocation"));
                $purchase_receipt_allocation_pane = $purchase_receipt_allocation_tab->div_add()->div_set('id','purchase_receipt_allocation_tab')->div_set('class','tab-pane');
                Purchase_Receipt_Renderer::purchase_receipt_allocation_view_render($app,$purchase_receipt_allocation_pane,array("id"=>$id),$this->path);
                
                
                
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
            
            case 'purchase_receipt':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');                
                $config = array(
                    'additional_filter'=>array(
                        array('key'=>'reference_type','query'=>'and t1.payment_type_id = '),
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select distinct t1.*
                                from purchase_receipt t1                    
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
                    $temp_result['data'][$i]['purchase_receipt_status'] =
                        SI::get_status_attr(
                            SI::status_get('Purchase_Receipt_Engine', 
                                $temp_result['data'][$i]['purchase_receipt_status']
                            )['label']
                        );
                    $temp_result['data'][$i]['amount'] = 
                        Tools::thousand_separator($temp_result['data'][$i]['amount'],5);
                    $temp_result['data'][$i]['outstanding_amount'] = 
                        Tools::thousand_separator($temp_result['data'][$i]['outstanding_amount'],5);
                    $temp_result['data'][$i]['change_amount'] = 
                        Tools::thousand_separator($temp_result['data'][$i]['change_amount'],5);
                    
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
        get_instance()->load->helper('purchase_receipt/purchase_receipt_engine');
        get_instance()->load->helper('purchase_receipt/purchase_receipt_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'purchase_receipt_get':
                $response =array();
                $db = new DB();
                $purchase_receipt_id = Tools::_str($data['data']);
                $q = '
                    select t1.*,
                        t2.code store_code,
                        t2.name store_name,
                        t3.id supplier_id,
                        t3.code supplier_code,
                        t3.name supplier_name,
                        pt.id payment_type_id,
                        pt.code payment_type_code
                    from purchase_receipt t1
                        inner join store t2 on t1.store_id = t2.id
                        inner join supplier t3 
                            on t1.supplier_id = t3.id
                        inner join payment_type pt on pt.id = t1.payment_type_id
                    where t1.id = '.$db->escape($purchase_receipt_id).'
                ';
                $rs = $db->query_array($q);

                if(count($rs)>0){
                    $purchase_receipt = $rs[0];                    
                    $purchase_receipt['purchase_receipt_date'] = Tools::_date($purchase_receipt['purchase_receipt_date'],'F d, Y H:i');
                    $purchase_receipt['store_text'] = SI::html_tag('strong',$purchase_receipt['store_code'])
                        .' '.$purchase_receipt['store_name'];
                    $purchase_receipt['purchase_receipt_status_text'] = SI::get_status_attr(
                            SI::status_get('Purchase_Receipt_Engine',$purchase_receipt['purchase_receipt_status'])['label']
                        );
                    $purchase_receipt['supplier_text'] = SI::html_tag('strong',$purchase_receipt['supplier_code'])
                        .' '.$purchase_receipt['supplier_name'];
                    $purchase_receipt['amount'] = Tools::thousand_separator($purchase_receipt['amount']);
                    $purchase_receipt['outstanding_amount'] = Tools::thousand_separator($purchase_receipt['outstanding_amount']);
                    $purchase_receipt['change_amount'] = Tools::thousand_separator($purchase_receipt['change_amount']);
                    $purchase_receipt['payment_type_text'] = SI::html_tag('strong',$purchase_receipt['payment_type_code']);
                    
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('Purchase_Receipt_Engine',
                            $purchase_receipt['purchase_receipt_status']
                        );
                    
                    $response['purchase_receipt'] = $purchase_receipt;
                    $response['purchase_receipt_status_list'] = $next_allowed_status_list;
                }
                
                break;
                
                case 'input_select_payment_type_get':
                    $response = array();
                    $payment_type_arr = Purchase_Receipt_Data_Support::supplier_payment_type_get();
                    if(count($payment_type_arr)>0){
                        foreach($payment_type_arr as $payment_type_idx=>$payment_type){
                            $response[] = array(
                                'id'=>$payment_type['id'],
                                'text'=>SI::html_tag('strong',$payment_type['code']),
                                'code'=>$payment_type['code'],
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
    
    public function purchase_receipt_add(){
        
        $this->load->helper($this->path->purchase_receipt_engine);
        $post = $this->input->post();
        if($post!= null){
            Purchase_Receipt_Engine::submit('','purchase_receipt_add',$post);
        }
        
    }
    
    public function purchase_receipt_invoiced($id){
        
        $this->load->helper($this->path->purchase_receipt_engine);
        $post = $this->input->post();
        if($post!= null){
            Purchase_Receipt_Engine::submit($id,'purchase_receipt_invoiced',$post);
        }
        
        
    }
    
    public function purchase_receipt_canceled($id){
        
        $this->load->helper($this->path->purchase_receipt_engine);
        $post = $this->input->post();
        if($post!= null){
            Purchase_Receipt_Engine::submit($id,'purchase_receipt_canceled',$post);
        }
        
        
    }
}

?>