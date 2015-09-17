<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Delivery_Order extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get('Delivery Order');
        get_instance()->load->helper('delivery_order/delivery_order_engine');
        $this->path = Delivery_Order_Engine::path_get();
        $this->title_icon = App_Icon::delivery_order();
        
    }
    
    public function index()
    {           
        //<editor-fold defaultstate="collapsed">
        $action = "";

        $app = new App();            
        $db = $this->db;

        
        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower('delivery_order'));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title',Lang::get(array('Delivery Order','List')))->form_set('span','12');
        
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array('New','Delivery Order')))
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        
        $reference_type_list = array(
            array('value'=>'','label'=>'ALL')
        );
        $module_list = SI::type_list_get('Delivery_Order_Engine');
        foreach($module_list as $module_idx=>$module){
            $reference_type_list[] = array('value'=>$module['val'],'label'=>$module['label']);
        }
        
        
        $form->select_add()
                ->select_set('id','reference_type_filter')
                ->select_set('options_add',$reference_type_list)
                ;
        
        $cols = array(
            array("name"=>"code","label"=>Lang::get("Delivery Order Code"),"data_type"=>"text","is_key"=>true),
            array("name"=>"delivery_order_type","label"=>Lang::get("Type"),"data_type"=>"text"),
            //,array("name"=>"supplier_name","label"=>Lang::get("Supplier"),"data_type"=>"text"),
            array("name"=>"delivery_order_date","label"=>Lang::get("Delivery Order Date"),"data_type"=>"text"),
            array("name"=>"delivery_order_warehouse_from_name","label"=>Lang::get("From Warehouse"),"data_type"=>"text"),
            array("name"=>"delivery_order_warehouse_to_name","label"=>Lang::get("To Warehouse"),"data_type"=>"text"),        
            array("name"=>"delivery_order_status","label"=>Lang::get("Status"),"data_type"=>"text"),
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/delivery_order')
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
        
        $this->load->helper($this->path->delivery_order_engine);
        $post = $this->input->post();
        
        $this->view('','add');
        
    }
    
    public function view($id="",$method="view"){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->delivery_order_engine);
        $this->load->helper($this->path->delivery_order_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Delivery_Order_Engine::delivery_order_exists($id)){
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
            $app->set_menu('collapsed',true);
            $app->set_title($this->title);
            $app->set_breadcrumb($this->title,strtolower($this->title));
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Delivery_Order_Renderer::delivery_order_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Delivery_Order_Renderer::delivery_order_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
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
        get_instance()->load->helper('delivery_order/delivery_order_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success' =>1,'msg'=>[],'response'=>array());
        $success = 1;
        $msg = [];
        $response = array();
        switch($method){
            case 'delivery_order':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');
                
                $config = array(
                    'additional_filter'=>array(
                        array('key'=>'reference_type','query'=>'and t1.delivery_order_type = '),
                    ),
                    'query'=>array(
                        'basic'=>'
                            select * from (
                                select distinct t1.*
                                    ,t4.name supplier_name
                                    ,t5.name delivery_order_warehouse_to_name
                                    ,t6.name delivery_order_warehouse_from_name

                                from delivery_order t1                    
                                    inner join delivery_order_warehouse_to t7 on t7.delivery_order_id = t1.id
                                    inner join warehouse t5 on t7.warehouse_id = t5.id
                                    inner join delivery_order_warehouse_from t8 on t8.delivery_order_id = t1.id
                                    inner join warehouse t6 on t8.warehouse_id = t6.id

                                    left outer join rma_delivery_order t2 
                                        on t2.delivery_order_id = t1.id
                                    left outer join rma t3 
                                        on t3.id = t2.rma_id                        
                                    left outer join rma_supplier t9 on t9.rma_id = t3.id
                                    left outer join supplier t4 on t4.id = t9.supplier_id

                                where t1.status>0
                        ',
                        'where'=>'
                            and (t1.code like '.$lookup_str.'
                                or t5.name like '.$lookup_str.'
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
                    $temp_result['data'][$i]['delivery_order_status'] =
                        SI::get_status_attr(
                            SI::status_get('Delivery_Order_Engine', 
                                $temp_result['data'][$i]['delivery_order_status']
                            )['label']
                        );
                    $temp_result['data'][$i]['delivery_order_type'] =
                        SI::type_get('Delivery_Order_Engine',
                            $temp_result['data'][$i]['delivery_order_type']
                        )['label'];
                }
                $result = $temp_result;
                //</editor-fold>
                break;
            
            case 'input_select_reference_search':
                $db = new DB();
                $lookup_data = isset($data['data'])?Tools::_str($data['data']):'';
                $response = Delivery_Order_Data_Support::reference_search($lookup_data);                
                break;
            case 'rma':
                switch($submethod){
                    
                }
                break;// end of mehod rma
            
            
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
        $data = json_decode($this->input->post(), true);
        get_instance()->load->helper('delivery_order/delivery_order_engine');
        get_instance()->load->helper('delivery_order/delivery_order_data_support');
        $result =array('success'=>1,'msg'=>[],'response'=>array());
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'reference_dependency_data_get':
                //<editor-fold defaultstate="collapsed">
                $response = array();
                $reference_id = isset($data['reference_id'])?Tools::_str($data['reference_id']):'';
                $reference_type = isset($data['reference_type'])?Tools::_str($data['reference_type']):'';
                $reference_detail = Delivery_Order_Data_Support::reference_detail_get($reference_type,$reference_id,null);
                $warehouse_to = Delivery_Order_Data_Support::warehouse_to_list_get($reference_type, $reference_id);               
                        
                $response['reference_detail'] = $reference_detail;
                $response['warehouse_to'] = $warehouse_to;
                
                //</editor-fold>
                break;
            case 'product_list_get':
                $reference_id = isset($data['reference_id'])?Tools::_str($data['reference_id']):'';
                $reference_type = isset($data['reference_type'])?Tools::_str($data['reference_type']):'';
                $warehouse_id = isset($data['warehouse_id'])?Tools::_str($data['warehouse_id']):'';
                $response = Delivery_Order_Data_Support::reference_product_list_get($reference_type, $reference_id,$warehouse_id);
                break;
            case 'rma':   
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('delivery_order/delivery_order_rma_engine');
                switch($submethod){
                    case 'warehouse_supplier_get':
                        $db = new DB();
                        $q = '
                            select t1.id, t1.name text
                            from warehouse t1
                            where t1.code = "WS" and t1.status>0
                        ';
                        $rs = $db->query_array($q);
                        if(is_null($rs)){
                            $success = 0;
                            $msg[] = $db->_error_message();
                        }
                        else{
                            $response = $rs[0];
                        }
                        break;
                    case 'warehouse_to_detail_get':
                        $db = new DB();
                        $rma_id = $data['rma_id'];
                        $q = ' 
                            select distinct name contact_name, address, phone
                            from rma t1
                                inner join rma_supplier t2 on t1.id = t2.rma_id
                                inner join supplier t3 on t3.id = t2.supplier_id
                            where t1.id = '.$rma_id.'
                        ';
                        $rs = $db->query_array($q);
                        if(is_null($rs)){
                            $success = 0;
                            $msg[] = $db->_error_message();
                        }
                        else{
                            if(count($rs)>0){
                                $response = $rs[0];
                                $q = '
                                    select t1.code, t1.name, concat(t2.code," - ", t2.name) type 
                                    from warehouse t1 
                                        inner join warehouse_type t2 on t1.warehouse_type_id = t2.id 
                                    where t1.code = "WS"';
                                $rs = $db->query_array($q);
                                $response['code'] = $rs[0]['code'];
                                $response['name'] = $rs[0]['name'];
                                $response['type'] = $rs[0]['type'];
                                
                            }
                        }
                        break;
                    case 'rma_detail_get':
                        $db = new DB();
                        $q = '
                            select distinct date_format(t1.rma_date,"%Y-%m-%d") rma_date
                                ,date_format(t1.rma_date,"%H:%i") rma_time 
                                ,concat(t3.name," ",t3.phone) supplier
                            from rma t1 
                                inner join rma_supplier t2 on t1.id = t2.rma_id
                                inner join supplier t3 on t2.supplier_id = t3.id
                            where t1.id = '.$db->escape($data['data']).'
                        ';
                        $rs = $db->query_array($q);
                        if(is_null($rs)){
                            $success = 0;
                            $msg[] = $db->_error_message();
                        }
                        else{
                            if(count($rs)>0){
                                $response = $rs[0];
                            }                  
                        }                       
                        
                        break;
                        
                    case 'rma_product_available_get':
                        $db = new DB();
                        $rma_id = isset($data['rma_id'])?$data['rma_id']:'';
                        $warehouse_id = isset($data['warehouse_id'])?$data['warehouse_id']:'';
                        $q = '
                            select t1.product_id
                                , t2.name product_name
                                , t1.qty rma_qty
                                , 0 max_qty
                                , t1.unit_id, t3.name unit_name
                            from rma_product t1
                                inner join product t2 on t1.product_id = t2.id
                                inner join unit t3 on t1.unit_id = t3.id
                            where t1.rma_id = '.$db->escape($rma_id).' 
                                order by t2.name
                        ';
                        $rs = $db->query_array($q);
                        for($i = 0;$i<count($rs);$i++){
                            $product_id = $rs[$i]['product_id'];
                            $unit_id = $rs[$i]['unit_id'];
                            $rs[$i]['rma_qty'] = Tools::thousand_separator($rs[$i]['rma_qty'],2,true);
                            $rs[$i]['max_qty'] = Delivery_Order_RMA_Engine::
                                    rma_product_max_qty($product_id, $unit_id, $rma_id, $warehouse_id);
                            $rs[$i]['max_qty'] = Tools::thousand_separator($rs[$i]['max_qty'],2,true);
                            $filename = 'img/product/'.$rs[$i]['product_id'].'.jpg';
                            $rs[$i]['product_img'] = '<img src = "'.Tools::img_load($filename,false).'"></img>';
                        }
                        $response = $rs;

                        break;

                    case 'delivery_order_product_get':
                        $db = new DB();
                        $q = '
                            select t1.product_id, t2.name product_name, t1.unit_id, t3.name unit_name, t1.qty
                            from delivery_order_product t1
                                inner join product t2 on t1.product_id = t2.id
                                inner join unit t3 on t1.unit_id = t3.id
                            where t1.delivery_order_id = '.$data['data'].'
                                order by t2.name asc
                        ';
                        $rs = $db->query_array($q);
                        if(is_null($rs)){
                            $success = 0;
                            $msg[]=$db->_error_message();
                        }
                        else{
                            for($i = 0;$i<count($rs);$i++){
                                $rs[$i]['qty'] = Tools::thousand_separator($rs[$i]['qty'],2,true);
                                $filename = 'img/product/'.$rs[$i]['product_id'].'.jpg';
                                $rs[$i]['product_img'] = '<img src = "'.Tools::img_load($filename,false).'"></img>';
                            }
                            $response = $rs;
                        }
                        break;
                    
                }
                //</editor-fold>
                break;
            
            case 'delivery_order_get':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('product/product_engine');
                get_instance()->load->helper('product/product_data_support');
                $response =array();
                $db = new DB();
                $delivery_order_id = $data['data'];
                $q = '
                    select t1.*,
                        t2.code store_code,
                        t2.name store_name,
                        t4.id warehouse_from_id,
                        t4.code warehouse_from_code,
                        t4.name warehouse_from_name,
                        t5.contact_name warehouse_to_contact_name,
                        t5.address warehouse_to_address,
                        t5.phone warehouse_to_phone,
                        t6.id warehouse_to_id,
                        t6.code warehouse_to_code,
                        t6.name warehouse_to_name,
                        t7.code warehouse_to_type_code,
                        t7.name warehouse_to_type_name,
                        t1.delivery_order_status
                    from delivery_order t1
                        inner join store t2 on t1.store_id = t2.id
                        inner join delivery_order_warehouse_from t3 on t1.id = t3.delivery_order_id
                        inner join warehouse t4 on t4.id = t3.warehouse_id
                        inner join delivery_order_warehouse_to t5 on t1.id = t5.delivery_order_id
                        inner join warehouse t6 on t6.id = t5.warehouse_id
                        inner join warehouse_type t7 on t7.id = t6.warehouse_type_id
                    where t1.id = '.$db->escape($delivery_order_id).'
                ';
                $rs = $db->query_array($q);

                if(count($rs)>0){
                    $delivery_order = $rs[0];
                    $delivery_order_id = $delivery_order['id'];
                    $product = array();
                    $reference = array();
                    
                    switch($delivery_order['delivery_order_type']){
                        case 'rma':
                            $q = '
                                select t1.*
                                from rma t1
                                    inner join rma_delivery_order t2 on t1.id = t2.rma_id
                                where t2.delivery_order_id='.$db->escape($rs[0]['delivery_order_id']).'
                            ';
                            $rs = $db->query_array_obj($q)[0];
                            if(count($rs)>0){
                                $reference['id'] = $rs->id;
                                $reference['text']= $rs->code;
                                $reference['reference_type'] = 'rma';// mandatory                            
                                
                            }
                            break;
                        case 'sales_invoice':
                            $q= '
                                select t1.*
                                from sales_invoice t1
                                    inner join sales_invoice_delivery_order_final t2
                                        on t1.id = t2.sales_invoice_id
                                    inner join delivery_order_final_delivery_order t3
                                        on t2.delivery_order_final_id = t3.delivery_order_final_id
                                            and t3.delivery_order_id = '.$db->escape($delivery_order_id).'
                            ';
                            $rs = $db->query_array($q);
                            if(count($rs)>0){
                                $reference['id'] = $rs[0]['id'];
                                $reference['text']= $rs[0]['code'];
                                $reference['reference_type'] = 'sales_invoice';// mandatory                            
                                
                            }
                            break;
                        case 'refill_subcon_work_order':
                            $q= '
                                select rswo.*
                                from rswo_do
                                    inner join refill_subcon_work_order rswo 
                                        on rswo.id = rswo_do.refill_subcon_work_order_id
                                where rswo_do.delivery_order_id = '.$db->escape($delivery_order_id).'
                            ';
                            $rs = $db->query_array($q);
                            if(count($rs)>0){
                                $reference['id'] = $rs[0]['id'];
                                $reference['text']= $rs[0]['code'];
                                $reference['reference_type'] = 'refill_subcon_work_order';// mandatory                            
                                
                            }
                            break;
                            
                    }
                    
                    $reference_detail = Delivery_Order_Data_Support::reference_detail_get(
                        $reference['reference_type'],
                        $reference['id'],
                        $delivery_order_id
                    );
                    
                    $delivery_order['warehouse_from_text'] = SI::html_tag('strong',
                        $delivery_order['warehouse_from_code']).' '.$delivery_order['warehouse_from_name'];
                    $delivery_order['warehouse_to_text'] = SI::html_tag('strong',
                        $delivery_order['warehouse_to_code']).' '.$delivery_order['warehouse_to_name'];
                    $delivery_order['delivery_order_date'] = Tools::_date($delivery_order['delivery_order_date'],'F d, Y H:i');
                    $delivery_order['store_text'] = SI::html_tag('strong',$delivery_order['store_code'])
                        .' '.$delivery_order['store_name'];
                    $delivery_order['delivery_order_status_text'] = SI::get_status_attr(
                            SI::status_get('Delivery_Order_Engine',$delivery_order['delivery_order_status'])['label']
                        );
                    
                    $product = Delivery_Order_Data_Support::delivery_order_product_get($delivery_order['id']);                    
                    
                    $next_allowed_status_list = SI::form_data()
                    ->status_next_allowed_status_list_get('Delivery_Order_Engine',
                        $delivery_order['delivery_order_status']
                    );

                    $response['reference'] = $reference;
                    $response['reference_detail'] = $reference_detail;
                    $response['product'] = $product;
                    $response['delivery_order'] = $delivery_order;
                    $response['delivery_order_status_list'] = $next_allowed_status_list;
                    
                }
                $response = $response;
                //</editor-fold>
                break;
            
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function delivery_order_print($id='',$module){
        $this->load->helper($this->path->delivery_order_print);
        $post = $this->input->post();
        switch($module){
            case 'delivery_order_form':
                Delivery_Order_Print::delivery_order_print($id);
                break;
        }
        
    }
    
    public function delivery_order_add(){        
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>'','method'=>'delivery_order_add','primary_data_key'=>'delivery_order','data_post'=>$post);            
            SI::data_submit()->submit('delivery_order_engine',$param);            
        }
        
    }
    
    public function delivery_order_process($id){
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'delivery_order_process','primary_data_key'=>'delivery_order','data_post'=>$post);            
            SI::data_submit()->submit('delivery_order_engine',$param);            
        }
    }
    
    public function delivery_order_done($id){
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'delivery_order_done','primary_data_key'=>'delivery_order','data_post'=>$post);            
            SI::data_submit()->submit('delivery_order_engine',$param);            
        }
    }
    
    public function delivery_order_canceled($id){
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'delivery_order_canceled','primary_data_key'=>'delivery_order','data_post'=>$post);            
            SI::data_submit()->submit('delivery_order_engine',$param);            
        }
    }
    /*
    public function rma_add(){
        $this->load->helper($this->path->delivery_order_rma_engine);
        $post = $this->input->post();
        if($post!= null){
            Delivery_Order_RMA_Engine::rma_submit('','rma_add',$post);
        }
    }
    
    public function rma_opened($id){
        $this->load->helper($this->path->delivery_order_rma_engine);
        $post = $this->input->post();
        if($post!= null){
            Delivery_Order_RMA_Engine::rma_submit($id,'rma_opened',$post);
        }
    }
    
    public function rma_delivered($id){
        $this->load->helper($this->path->delivery_order_rma_engine);
        $post = $this->input->post();
        if($post!= null){
            Delivery_Order_RMA_Engine::rma_submit($id,'rma_delivered',$post);
        }
    }
    
    public function rma_postponed($id){
        $this->load->helper($this->path->delivery_order_rma_engine);
        $post = $this->input->post();
        if($post!= null){
            Delivery_Order_RMA_Engine::rma_submit($id,'rma_postponed',$post);
        }
    }
    
    public function rma_canceled($id){
        $this->load->helper($this->path->delivery_order_rma_engine);
        $post = $this->input->post();
        if($post!= null){
            Delivery_Order_RMA_Engine::rma_submit($id,'rma_canceled',$post);
        }
    }
    
    public function rma_received($id){
        $this->load->helper($this->path->delivery_order_rma_engine);
        $post = $this->input->post();
        if($post!= null){
            Delivery_Order_RMA_Engine::rma_submit($id,'rma_received',$post);
        }
    }
    */
}

?>