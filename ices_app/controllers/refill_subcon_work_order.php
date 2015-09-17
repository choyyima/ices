<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Subcon_Work_Order extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get(array('Refill - ','Subcon Work Order'),true,true,false,false,true);
        get_instance()->load->helper('refill_subcon_work_order/refill_subcon_work_order_engine');
        $this->path = Refill_Subcon_Work_Order_Engine::path_get();
        $this->title_icon = App_Icon::refill_subcon_work_order();
        
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
        $form = $row->form_add()->form_set('title',Lang::get(array('Subcon Work Order','List')))->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array(array('val'=>'New','grammar'=>'adj','uc_first'=>'true'),'Subcon Work Order')))
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        
        $form->form_group_add();
        $cols = array(
            array("name"=>"code","label"=>Lang::get("Code"),"data_type"=>"text","is_key"=>true),
            array("name"=>"refill_subcon_name","label"=>Lang::get("Subcontractor"),"data_type"=>'text'),
            array("name"=>"refill_subcon_work_order_date","label"=>Lang::get(array("Subcon Work Order","Date")),"data_type"=>"text"),            
            array("name"=>"refill_subcon_work_order_status_text","label"=>Lang::get(array("Status")),"data_type"=>"text"),            
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/refill_subcon_work_order')
                ->table_ajax_set('columns',$cols)
                ->table_ajax_set('key_column','id')
                ->filter_set(array(
                        array('id'=>'reference_type_filter','field'=>'reference_type')
                    ))
                ;        
        
        
        $app->render();
    }
    
    
    public function add(){
        
        $this->load->helper($this->path->refill_subcon_work_order_engine);
        $post = $this->input->post();
        
        $this->view('','add');
        
    }
    
    
    public function view($id="",$method="view"){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->refill_subcon_work_order_engine);
        $this->load->helper($this->path->refill_subcon_work_order_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Refill_Subcon_Work_Order_Engine::refill_subcon_work_order_exists($id)){
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
            $app->set_menu('collapsed', false);
            $app->set_breadcrumb($this->title,'refill_subcon_work_order');
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row')->div_set('id','refill_subcon_work_order');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Refill_Subcon_Work_Order_Renderer::refill_subcon_work_order_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Refill_Subcon_Work_Order_Renderer::refill_subcon_work_order_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
                
                $delivery_order_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#delivery_order_tab',"value"=>Lang::get("Delivery Order")));
                $delivery_order_pane = $history_tab->div_add()->div_set('id','delivery_order_tab')->div_set('class','tab-pane');
                Refill_Subcon_Work_Order_Renderer::delivery_order_view_render($app,$delivery_order_pane,array("id"=>$id),$this->path);
                
                $receive_product_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#receive_product_tab',"value"=>Lang::get("Receive Product")));
                $receive_product_pane = $history_tab->div_add()->div_set('id','receive_product_tab')->div_set('class','tab-pane');
                Refill_Subcon_Work_Order_Renderer::receive_product_view_render($app,$receive_product_pane,array("id"=>$id),$this->path);
                
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
        $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
        $result =array('success'=>1,'msg'=>[]);
        $success = 1;
        $msg = [];
        $response = array();
        $limit = 10;
        switch($method){            
            case 'refill_subcon_work_order':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = $db->escape('%'.$lookup_data.'%');                
                $config = array(
                    'additional_filter'=>array(
                        
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select distinct t1.*, rs.name refill_subcon_name
                                from refill_subcon_work_order t1   
                                    inner join refill_subcon rs on t1.refill_subcon_id = rs.id
                                    inner join rswo_product rswop 
                                        on t1.id = rswop.refill_subcon_work_order_id
                                    left outer join refill_work_order_product rwop
                                        on rwop.id = rswop.product_id
                                where t1.status>0
                        ',
                        'where'=>'
                            and (t1.code like '.$lookup_str.'
                                or rwop.product_marking_code like '.$lookup_str.'
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
                    $temp_result['data'][$i]['refill_subcon_work_order_status_text'] =
                        SI::get_status_attr(
                            SI::status_get('Refill_Subcon_Work_Order_Engine', 
                                $temp_result['data'][$i]['refill_subcon_work_order_status']
                            )['label']
                        );
                    
                }
                $result = $temp_result;
                //</editor-fold>
                break;
            
            case 'input_select_refill_subcon_search':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('refill_subcon/refill_subcon_data_support');                
                $refill_subcon_arr = Refill_Subcon_Data_Support::refill_subcon_active_search($lookup_data);
                if(count($refill_subcon_arr)>0){
                    foreach($refill_subcon_arr as $idx=>$row){
                        $response[] = array(
                            'id'=>$row['id'],
                            'text'=>SI::html_tag('strong',$row['code']).' '.
                            $row['name'].' '.$row['phone'].' '.$row['bb_pin'],
                        );
                    }
                }
                //</editor-fold>
                break;
                
            case 'input_select_product_search':
                $response = array();        
                get_instance()->load->helper('refill_subcon_work_order/refill_subcon_work_order_data_support');
                $response = Refill_Subcon_Work_Order_Data_Support::product_search($lookup_data);
                break;
            case 'input_select_rswo_product_reference_search':
                $response = array();        
                get_instance()->load->helper('refill_subcon_work_order/refill_subcon_work_order_data_support');
                $response = Refill_Subcon_Work_Order_Data_Support::product_reference_search($lookup_data);
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
        get_instance()->load->helper('refill_subcon_work_order/refill_subcon_work_order_engine');
        get_instance()->load->helper('refill_subcon_work_order/refill_subcon_work_order_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'refill_subcon_work_order_get':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('product/product_data_support');
                $response =array();
                $db = new DB();
                $rswo_id = $data['data'];
                $q = '
                    select t1.*,
                        t2.code store_code,
                        t2.name store_name,
                        rs.id refill_subcon_id,
                        rs.code refill_subcon_code,
                        rs.name refill_subcon_name
                    from refill_subcon_work_order t1
                        inner join store t2 on t1.store_id = t2.id
                        inner join refill_subcon rs on t1.refill_subcon_id = rs.id

                    where t1.id = '.$db->escape($rswo_id).'
                ';
                $rs = $db->query_array($q);

                if(count($rs)>0){
                    $rswo = $rs[0];                    
                    
                    $rswo['refill_subcon_work_order_date'] = Tools::_date($rswo['refill_subcon_work_order_date'],'F d, Y H:i');
                    $rswo['store_text'] = SI::html_tag('strong',$rswo['store_code'])
                        .' '.$rswo['store_name'];
                    $rswo['refill_subcon_text'] = SI::html_tag('strong',$rswo['refill_subcon_code'])
                        .' '.$rswo['refill_subcon_name'];
                    $rswo['refill_subcon_work_order_status_text'] = SI::get_status_attr(
                            SI::status_get('Refill_Subcon_Work_Order_Engine',$rswo['refill_subcon_work_order_status'])['label']
                        );
                    
                    $rswo_product = Refill_Subcon_Work_Order_Data_Support::rswo_product_get($rswo['id']);
                    for($i = 0;$i<count($rswo_product);$i++){
                        //<editor-fold defaultstate="collapsed" desc="Refill Product">
                        $p_type = $rswo_product[$i]['product_type'];
                        $p_reference_type = $rswo_product[$i]['product_reference_type'];
                        switch($p_type ){
                            case 'registered_product':
                                $rswo_product[$i]['product_text'] = 
                                    SI::html_tag('strong',$rswo_product[$i]['registered_product_code'])
                                    .' '.Product_Data_Support::product_type_get('registered_product')['label']
                                    .' - '.$rswo_product[$i]['registered_product_name']
                                ;
                                $rswo_product[$i]['product_img'] = Product_Engine::img_get($rswo_product[$i]['product_id']);
                                break;
                            case 'refill_work_order_product':
                                $rswo_product[$i]['product_text'] = 
                                    SI::html_tag('strong',$rswo_product[$i]['rwop_product_marking_code'])
                                    .' '.Product_Data_Support::product_type_get('refill_work_order_product')['label']
                                    .' - '.$rswo_product[$i]['rwop_rpc_code']
                                    .' '.$rswo_product[$i]['rwop_rpm_code']
                                    .' '.Tools::thousand_separator($rswo_product[$i]['rwop_capacity'])
                                    .' '.$rswo_product[$i]['rwop_capacity_unit_code']
                                ;
                                break;
                        }
                        
                        switch($p_reference_type){
                            case 'refill_work_order_product':
                                $rswo_product[$i]['product_reference_text'] = 
                                    $rswo_product[$i]['product_reference_rwop_product_marking_code']
                                ;
                                break;
                        }
                        
                        $rswo_product[$i]['unit_text']  =SI::html_tag('strong',$rswo_product[$i]['unit_code'])
                                .' '.$rswo_product[$i]['unit_name']
                                ;
                        $rswo_product[$i]['qty'] = Tools::thousand_separator($rswo_product[$i]['qty']);
                        $rswo_product[$i]['movement_outstanding_qty'] = Tools::thousand_separator($rswo_product[$i]['movement_outstanding_qty']);
                        //</editor-fold>
                    }
                    
                    $rswo_expected_product_result = Refill_Subcon_Work_Order_Data_Support::rswo_expected_product_result_get($rswo['id']);
                    for($i = 0;$i<count($rswo_expected_product_result);$i++){
                        //<editor-fold defaultstate="collapsed" desc="Refill Expected Product Result">
                        $p_type = $rswo_expected_product_result[$i]['product_type'];
                        switch($p_type ){
                            case 'registered_product':
                                $rswo_expected_product_result[$i]['product_text'] = 
                                    SI::html_tag('strong',$rswo_expected_product_result[$i]['registered_product_code'])
                                    .' '.Product_Data_Support::product_type_get('registered_product')['label']
                                    .' - '.$rswo_expected_product_result[$i]['registered_product_name']
                                ;
                                $rswo_expected_product_result[$i]['product_img'] = Product_Engine::img_get($rswo_expected_product_result[$i]['product_id']);
                                break;
                            case 'refill_work_order_product':
                                $rswo_expected_product_result[$i]['product_text'] = 
                                    SI::html_tag('strong',$rswo_expected_product_result[$i]['rwop_product_marking_code'])
                                    .' '.Product_Data_Support::product_type_get('refill_work_order_product')['label']
                                    .' - '.$rswo_expected_product_result[$i]['rwop_rpc_code']
                                    .' '.$rswo_expected_product_result[$i]['rwop_rpm_code']
                                    .' '.Tools::thousand_separator($rswo_expected_product_result[$i]['rwop_capacity'])
                                    .' '.$rswo_expected_product_result[$i]['rwop_capacity_unit_code']
                                ;
                                break;
                        }
                        $rswo_expected_product_result[$i]['unit_text']  =
                            SI::html_tag('strong',$rswo_expected_product_result[$i]['unit_code'])
                                .' '.$rswo_expected_product_result[$i]['unit_name']
                        ;
                        $rswo_expected_product_result[$i]['qty'] = Tools::thousand_separator($rswo_expected_product_result[$i]['qty']);
                        $rswo_expected_product_result[$i]['movement_outstanding_qty'] = Tools::thousand_separator($rswo_expected_product_result[$i]['movement_outstanding_qty']);
                        //</editor-fold>
                    }
                    
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('Refill_Subcon_Work_Order_Engine',
                            $rswo['refill_subcon_work_order_status']
                        );
                    
                    
                    $response['rswo'] = $rswo;
                    $response['rswo_product'] = $rswo_product;
                    $response['rswo_expected_product_result'] = $rswo_expected_product_result;
                    $response['refill_subcon_work_order_status_list'] = $next_allowed_status_list;
                    
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
                $reference_detail = Refill_Subcon_Work_Order_Data_Support::reference_detail_get($module_type, $reference_id);
                $response = $reference_detail;
                break;
            case 'input_select_payment_type_get':
                //<editor-fold defaultstate="collapsed">
                $response = array();
                $customer_id = isset($data['customer_id'])?$data['customer_id']:'';
                $payment_type_arr = Refill_Subcon_Work_Order_Data_Support::customer_refill_subcon_work_order_type_get($customer_id);
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
        //</editor-fold>
    }
    
    public function rswo_add(){
        // <editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->refill_subcon_work_order_engine);
        $post = $this->input->post();
        if ($post != null) {
            $param = array('id' => '', 'method' => 'rswo_add',
                'primary_data_key' => 'rswo',
                'data_post' => $post
            );
            SI::data_submit()->submit('refill_subcon_work_order_engine', $param);
        }
        // </editor-fold>
    }
    
    public function rswo_done($id){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->refill_subcon_work_order_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'rswo_done',
                'primary_data_key'=>'rswo',
                'data_post'=>$post
            );
            SI::data_submit()->submit('refill_subcon_work_order_engine',$param);

        }
        //</editor-fold>
    }
    
    public function rswo_canceled($id){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->refill_subcon_work_order_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'rswo_canceled',
                'primary_data_key'=>'rswo',
                'data_post'=>$post
            );
            SI::data_submit()->submit('refill_subcon_work_order_engine',$param);

        }
        //</editor-fold>
    }
}

?>