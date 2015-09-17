<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Delivery_Order_Final extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get('Delivery Order Final');
        get_instance()->load->helper('delivery_order_final/delivery_order_final_engine');
        $this->path = Delivery_Order_Final_Engine::path_get();
        $this->title_icon = App_Icon::delivery_order_final();
        
    }
    
    public function index()
    {           
        $action = "";

        $app = new App();            
        $db = $this->db;

        
        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower('delivery_order_final'));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title',Lang::get(array('Delivery Order Final','List')))->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array('New','Delivery Order Final')))
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        
        $reference_type_list = array(
            array('value'=>'','label'=>'ALL')
        );
        $module_list = SI::type_list_get('Delivery_Order_Final_Engine');
        foreach($module_list as $module_idx=>$module){
            $reference_type_list[] = array('value'=>$module['val'],'label'=>$module['label']);
        }
        
        $form->select_add()
                ->select_set('id','reference_type_filter')
                ->select_set('options_add',$reference_type_list)
                ;
        
        $cols = array(
            array("name"=>"code","label"=>Lang::get("Delivery Order Final Code"),"data_type"=>"text","is_key"=>true),
            array("name"=>"delivery_order_final_type","label"=>Lang::get("Type"),"data_type"=>"text"),
            array("name"=>"delivery_order_final_date","label"=>Lang::get("Delivery Order Final Date"),"data_type"=>"text"),
            array("name"=>"delivery_order_final_status","label"=>Lang::get("Status"),"data_type"=>"text"),
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/delivery_order_final')
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
        $this->load->helper($this->path->delivery_order_final_engine);
        $post = $this->input->post();
        
        $this->view('','add');
        
    }
    
    public function view($id="",$method="view"){
        
        $this->load->helper($this->path->delivery_order_final_engine);
        $this->load->helper($this->path->delivery_order_final_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Delivery_Order_Final_Engine::delivery_order_final_exists($id)){
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
            $app->set_menu('collapsed',false);
            $app->set_title($this->title);
            $app->set_breadcrumb($this->title,'delivery_order_final');
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row')->div_set('id','delivery_order_final');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Delivery_Order_Final_Renderer::delivery_order_final_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Delivery_Order_Final_Renderer::delivery_order_final_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
                
                $dofc_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#dofc_tab',"value"=>Lang::get("Delivery Order Final Confirmation")));
                $dofc_pane = $dofc_tab->div_add()->div_set('id','dofc_tab')->div_set('class','tab-pane');
                Delivery_Order_Final_Renderer::dofc_view_render($app,$dofc_pane,array("id"=>$id),$this->path);
                
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
        $limit = 10;
        switch($method){
            
            case 'delivery_order_final':
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');                
                $config = array(
                    'additional_filter'=>array(
                        array('key'=>'reference_type','query'=>'and t1.delivery_order_final_type = '),
                        
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select distinct t1.*
                                from delivery_order_final t1                    
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
                    $temp_result['data'][$i]['delivery_order_final_status'] =
                        SI::get_status_attr(
                            SI::status_get('Delivery_Order_Final_Engine', 
                                $temp_result['data'][$i]['delivery_order_final_status']
                            )['label']
                        );
                    $temp_result['data'][$i]['delivery_order_final_type'] =
                        SI::type_get('Delivery_Order_Final_Engine',
                            $temp_result['data'][$i]['delivery_order_final_type']
                        )['label'];
                }
                $result = $temp_result;
                
                break;
                
            case 'input_select_reference_search':
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $limit = 10;
                $db = new DB();
                $q = '
                    select distinct t1.id id
                        ,t1.code code
                        ,t1.sales_invoice_date
                    from sales_invoice t1
                        inner join sales_invoice_info t2 on t1.id = t2.sales_invoice_id
                        inner join sales_invoice_product t3 on t1.id = t3.sales_invoice_id
                    where t1.sales_invoice_status != "X"
                        and t2.is_delivery ="1"
                        and t1.status>0
                        and (
                            t1.code like '.$lookup_str.'                                
                        )
                        and t3.movement_outstanding_qty>0
                    order by t1.sales_invoice_date desc
                    limit '.$limit.'
                        
                ';
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){
                    $rs[$i]['reference_type'] = 'sales_invoice';
                    $rs[$i]['reference_type_name'] = SI::type_get('Delivery_Order_Final_Engine', 
                        'sales_invoice')['label'];
                    $rs[$i]['reference_code'] = $rs[$i]['code'];
                    $rs[$i]['text'] = ''
                            .$rs[$i]['code']
                            .' '
                            .SI::html_tag('strong',Tools::_date($rs[$i]['sales_invoice_date'],'F d, Y H:i:s'))
                        ;
                }
                $result['response'] = $rs;
                break;
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function data_support($method="",$submethod=""){
        //<editor-fold defaultstate="collapsed">
        //this function only used for urgently data retrieve
        get_instance()->load->helper('delivery_order/delivery_order_engine');
        get_instance()->load->helper('delivery_order_final/delivery_order_final_data_support');
        get_instance()->load->helper('product/product_engine');
        get_instance()->load->helper('product_stock_engine');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'dependency_data_get':
                //<editor-fold defaultstate="collapsed">
                $ref_id = isset($data['ref_id'])?Tools::_str($data['ref_id']):'';
                $ref_type = isset($data['ref_type'])?Tools::_str($data['ref_type']):'';
                $response = Delivery_Order_Final_Data_Support::reference_dependency_get($ref_type,$ref_id);
                if(isset($response['ref_product'])){
                    for($i = 0;$i<count($response['ref_product']);$i++){
                        $product_id = $response['ref_product'][$i]['product_id'];
                        $response['ref_product'][$i]['product_img'] = Product_Engine::img_get($product_id);
                        $response['ref_product'][$i]['qty'] = 
                            Tools::thousand_separator($response['ref_product'][$i]['qty'],5);
                        $response['ref_product'][$i]['qty_outstanding'] = 
                            Tools::thousand_separator($response['ref_product'][$i]['qty_outstanding'],5);
                    }
                }
                if(isset($response['product_stock'])){
                    for($i = 0;$i<count($response['product_stock']);$i++){
                        $response['product_stock'][$i]['qty'] = 
                            Tools::thousand_separator($response['product_stock'][$i]['qty'],5);
                    }
                }
                //</editor-fold>
                break;
            case 'delivery_order_final_get':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('product/product_engine');
                
                $response =array();
                $db = new DB();
                $delivery_order_final_id = $data['data'];
                $q = '
                    select t1.*,
                        t2.code store_code,
                        t2.name store_name                        
                    from delivery_order_final t1
                        inner join store t2 on t1.store_id = t2.id
                        
                    where t1.id = '.$db->escape($delivery_order_final_id).'
                ';
                $rs = $db->query_array($q);

                if(count($rs)>0){
                    $delivery_order_final = $rs[0];
                    $product_ordered = array();
                    $delivery_order = array();
                    $reference = array();
                    $reference_detail = array();                    
                    
                    $dof_type = $delivery_order_final['delivery_order_final_type'];
                    switch($dof_type){
                        case 'sales_invoice':
                            $sales_invoice_id = $db->fast_get('sales_invoice_delivery_order_final',array('delivery_order_final_id'=>$delivery_order_final_id))[0]['sales_invoice_id'];
                            get_instance()->load->helper('sales_pos/sales_pos_data_support');
                            $sales_invoice = Sales_Pos_Data_Support::sales_invoice_get($sales_invoice_id);
                            $reference = array('id'=>$sales_invoice['id'],
                                'text'=>SI::html_tag('strong',$sales_invoice['code'])
                            );
                            $reference_detail = Delivery_Order_Final_Data_Support::reference_detail_get($dof_type, $sales_invoice['id']);
                            break;
                    }
                    
                    $delivery_order_final['delivery_order_final_date'] = Tools::_date($delivery_order_final['delivery_order_final_date'],'F d, Y H:i');
                    $delivery_order_final['store_text'] = SI::html_tag('strong',$delivery_order_final['store_code'])
                        .' '.$delivery_order_final['store_name'];
                    $delivery_order_final['delivery_order_final_status_text'] = SI::get_status_attr(
                            SI::status_get('Delivery_Order_Final_Engine',$delivery_order_final['delivery_order_final_status'])['label']
                        );
                    
                    $q = '
                        select 
                            t1.id warehouse_to_id,
                            t1.code warehouse_to_code, 
                            t1.name warehouse_to_name, 
                            t4.name warehouse_to_type_name,
                            t2.contact_name warehouse_to_contact_name,
                            t2.address warehouse_to_address,
                            t2.phone warehouse_to_phone
                            
                        from warehouse t1
                            inner join delivery_order_warehouse_to t2 
                                on t1.id = t2.warehouse_id
                            inner join delivery_order_final_delivery_order t3
                                on t3.delivery_order_id = t2.delivery_order_id
                            inner join warehouse_type t4 on t4.id = t1.warehouse_type_id
                        where t3.delivery_order_final_id = '.$db->escape($delivery_order_final_id).' limit 1
                    ';
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        $delivery_order_final = array_merge($delivery_order_final, $rs[0]);
                        $delivery_order_final['warehouse_to_id'] = $rs[0]['warehouse_to_id'];
                        $delivery_order_final['warehouse_to_text'] = SI::html_tag('strong',
                            $rs[0]['warehouse_to_code']).' '.$rs[0]['warehouse_to_name'];
                        
                        
                    }
                    
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('Delivery_Order_Final_Engine',
                            $delivery_order_final['delivery_order_final_status']
                        );
                                
                    switch($delivery_order_final['delivery_order_final_type']){
                        case 'sales_invoice':
                            $sales_invoice_id = $db->fast_get('sales_invoice_delivery_order_final',array('delivery_order_final_id'=>$delivery_order_final_id))[0]['sales_invoice_id'];
                            $product_ordered = Delivery_Order_Final_Data_Support::
                                sales_invoice_product_get($sales_invoice_id);
                            for($i = 0;$i<count($product_ordered);$i++){
                                $product_ordered[$i]['product_img'] = 
                                    Product_Engine::img_get($product_ordered[$i]['product_id']);
                                $product_ordered[$i]['qty'] = Tools::thousand_separator($product_ordered[$i]['qty'],5);
                            }
                            
                            $delivery_order = Delivery_Order_Final_Data_Support::
                                delivery_order_get($delivery_order_final_id);
                            for($i = 0;$i<count($delivery_order);$i++){
                                for($j = 0;$j<count($delivery_order[$i]['product']);$j++){
                                    $delivery_order[$i]['product'][$j]['qty'] = 
                                        Tools::thousand_separator($delivery_order[$i]['product'][$j]['qty']);
                                    $delivery_order[$i]['delivery_order_status_text'] = 
                                        SI::get_status_attr(
                                            SI::status_get('Delivery_Order_Engine', 
                                                $delivery_order[$i]['delivery_order_status'])['label']
                                        );
                                }
                            }
                            break;
                    }
                    
                    $response['reference'] = $reference;
                    $response['reference_detail'] = $reference_detail;
                    $response['delivery_order'] = $delivery_order;
                    $response['product_ordered'] = $product_ordered;// to fill up product_table
                    $response['delivery_order_final'] = $delivery_order_final;
                    $response['delivery_order_final_status_list'] = $next_allowed_status_list;
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
    
    public function delivery_order_final_add(){
        $this->load->helper($this->path->delivery_order_final_engine);
        $post = $this->input->post();
        if($post!= null){
            Delivery_Order_Final_Engine::submit('','delivery_order_final_add',$post);
        }
    }
    
    public function delivery_order_final_process($id){
        $this->load->helper($this->path->delivery_order_final_engine);
        $post = $this->input->post();
        if($post!= null){
            Delivery_Order_Final_Engine::submit($id,'delivery_order_final_process',$post);
        }
    }
    
    public function delivery_order_final_done($id){
        $this->load->helper($this->path->delivery_order_final_engine);
        $post = $this->input->post();
        if($post!= null){
            Delivery_Order_Final_Engine::submit($id,'delivery_order_final_done',$post);
        }
    }
    
    public function delivery_order_final_confirmed($id){
        $this->load->helper($this->path->delivery_order_final_engine);
        $post = $this->input->post();
        if($post!= null){
            Delivery_Order_Final_Engine::submit($id,'delivery_order_final_confirmed',$post);
        }
    }
    
    public function delivery_order_final_canceled($id){
        $this->load->helper($this->path->delivery_order_final_engine);
        $post = $this->input->post();
        if($post!= null){
            Delivery_Order_Final_Engine::submit($id,'delivery_order_final_canceled',$post);
        }
    }
    
    public function delivery_order_final_print($id,$prm1=''){
        $this->load->helper('delivery_order_final/delivery_order_final_print');
        $post = $this->input->post();
        Delivery_Order_Final_Print::delivery_order_final_print(null,$id);
    }
}

?>