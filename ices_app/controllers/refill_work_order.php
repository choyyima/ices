<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Work_Order extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get('Refill - ').Lang::get('Work Order');
        get_instance()->load->helper('Refill_Work_Order/Refill_Work_Order_Engine');
        $this->path = Refill_Work_Order_Engine::path_get();
        $this->title_icon = App_Icon::refill_work_order();
        
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
        $form = $row->form_add()->form_set('title',Lang::get(array('Work Order','List')))->form_set('span','12');
        $form->form_group_add();
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array('New','Work Order')))
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        $cols = array(
            array("name"=>"code","label"=>Lang::get("Code"),"data_type"=>"text","is_key"=>true),
            array("name"=>"refill_work_order_date","label"=>Lang::get("Date"),"data_type"=>"text"),
            array("name"=>"refill_work_order_status","label"=>Lang::get("Status"),"data_type"=>"text"),
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/refill_work_order')
                ->table_ajax_set('columns',$cols)
                ->table_ajax_set('key_column','id')
                ->filter_set(array(
                        array('id'=>'reference_type_filter','field'=>'reference_type')
                    ))
                ;        

        $app->render();
    }
    
    
    public function add(){        
        $this->load->helper($this->path->refill_work_order_engine);
        $active_id =Refill_Work_Order_Engine::refill_work_order_active_get(); 
        if($active_id!== null){
            redirect($this->path->index.'view/'.$active_id);
        }
        
        $post = $this->input->post();      
        $default_status = SI::status_default_status_get('Refill_Work_Order_Engine')['val'];
        $user_id = User_Info::get()['user_id'];
        $db = new DB();
        $q = '
            select t1.id
            from refill_work_order t1
                inner join working_order_info t2 on t1.id = t2.refill_work_order_id
            where t1.refill_work_order_status === '.$db->escape($default_status).'
                and t2.creator_id = '.$db->escape($user_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            redirect(get_instance()->config->base_url().'refill_work_order/view'.$rs[0]);
        }
        
        $this->view('','add');
        
    }
    
    
    public function view($id="",$method="view"){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->refill_work_order_engine);
        $this->load->helper($this->path->refill_work_order_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Refill_Work_Order_Engine::refill_work_order_exists($id)){
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
            $app->set_breadcrumb($this->title,'refill_work_order');
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row')->div_set('id','refill_work_order');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Refill_Work_Order_Renderer::refill_work_order_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Refill_Work_Order_Renderer::Refill_Work_Order_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
            
                $customer_deposit_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#customer_deposit_tab',"value"=>"Customer Deposit"));
                $customer_deposit_pane = $customer_deposit_tab->div_add()->div_set('id','customer_deposit_tab')->div_set('class','tab-pane');
                Refill_Work_Order_Renderer::refill_work_order_customer_deposit_view_render($app,$customer_deposit_pane,array("id"=>$id),$this->path);
            
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
            
            case 'refill_work_order':
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
                                from refill_work_order t1    
                                inner join refill_work_order_product rwop 
                                        on t1.id = rwop.refill_work_order_id
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
                        'order'=>'order by code desc'
                    ),
                );                
                $temp_result = SI::form_data()->ajax_table_search($config, $data);
                for($i = 0;$i<count($temp_result['data']);$i++){
                    $temp_result['data'][$i]['refill_work_order_status'] =
                        SI::get_status_attr(
                            SI::status_get('Refill_Work_Order_Engine', 
                                $temp_result['data'][$i]['refill_work_order_status']
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
            case 'input_select_refill_product_category_search':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('refill_product_price_list/refill_product_price_list_data_support');
                $response = array();
                $db = new DB();
                $lookup_str = isset($data['data'])?$data['data']:'';
                $q = '
                    select t1.*
                    from refill_product_category t1
                    where t1.status>0 and t1.refill_product_category_status = "active"
                        and (
                            t1.code like '.$db->escape('%'.$lookup_str.'%').'
                            or t1.name like '.$db->escape('%'.$lookup_str.'%').' 
                        )
                ';
                $rs = $db->query_array($q);
                foreach($rs as $idx=>$rs_item){
                    $response[] = array(
                        'id'=>$rs_item['id'],
                        'text'=>SI::html_tag('strong',$rs_item['code']).' '.$rs_item['name'],
                        'product_medium'=>  Refill_Product_Price_List_Data_Support::
                            refill_product_category_dependency_get($rs_item['id'])
                    );
                    
                    
                    
                    
                    
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
        get_instance()->load->helper('refill_work_order/refill_work_order_engine');
        get_instance()->load->helper('refill_work_order/refill_work_order_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        $response = array();
        switch($method){
            case 'customer_detail_get':
                //<editor-fold defaultstate="collapsed">
                $response = array();
                $customer_id = isset($data['customer_id'])?Tools::_str($data['customer_id']):'';
                $response['customer_detail'] = Refill_Work_Order_Data_Support::customer_detail_get($customer_id);
                //</editor-fold>
                break;
            case 'refill_work_order_get':
                //<editor-fold defaultstate="collapsed">
                $response =array();
                $db = new DB();
                $refill_work_order_id = Tools::_str($data['data']);
                $q = '
                    select t1.*,
                        t2.code store_code,
                        t2.name store_name,
                        t3.id customer_id,
                        t3.code customer_code,
                        t3.name customer_name
                    from refill_work_order t1
                        inner join store t2 on t1.store_id = t2.id
                        inner join customer t3 
                            on t1.customer_id = t3.id
                    where t1.id = '.$db->escape($refill_work_order_id).'
                ';
                $rs = $db->query_array($q);

                if(count($rs)>0){
                    $refill_work_order = $rs[0];    
                    $refill_work_order['total_estimated_amount'] = Tools::thousand_separator($refill_work_order['total_estimated_amount']);
                    $refill_work_order['total_deposit_amount'] = Tools::thousand_separator($refill_work_order['total_deposit_amount']);
                    $refill_work_order_info = Refill_Work_Order_Data_Support::refill_work_order_info_get($refill_work_order['id']);
                    $refill_work_order['refill_work_order_date'] = Tools::_date($refill_work_order['refill_work_order_date'],'F d, Y H:i');
                    $refill_work_order['store_text'] = SI::html_tag('strong',$refill_work_order['store_code'])
                        .' '.$refill_work_order['store_name'];
                    
                    $rwo_product = Refill_Work_Order_Data_Support::refill_work_order_product_get($refill_work_order['id']);
                    for($i = 0;$i<count($rwo_product);$i++){
                        $rwo_product[$i]['rpc_text'] = SI::html_tag('strong',$rwo_product[$i]['rpc_code']).' '.$rwo_product[$i]['rpc_name'];
                        $rwo_product[$i]['rpm_text'] = SI::html_tag('strong',$rwo_product[$i]['rpm_code']).' '.$rwo_product[$i]['rpm_name'];
                        $rwo_product[$i]['capacity_unit_text'] = SI::html_tag('strong',$rwo_product[$i]['capacity_unit_code']).' '.$rwo_product[$i]['capacity_unit_name'];
                        $rwo_product[$i]['estimated_amount'] = Tools::thousand_separator($rwo_product[$i]['estimated_amount']);
                        $rwo_product[$i]['capacity'] = Tools::thousand_separator($rwo_product[$i]['capacity']);
                        $rwo_product[$i]['refill_work_order_product_status_text'] = 
                                SI::type_get('refill_work_order_engine', $rwo_product[$i]['refill_work_order_product_status'],'$rwo_product_status')['label']
                        ;
                    }
                    $refill_work_order['refill_work_order_status_text'] = SI::get_status_attr(
                            SI::status_get('Refill_Work_Order_Engine',$refill_work_order['refill_work_order_status'])['label']
                        );
                    $refill_work_order['customer_text'] = SI::html_tag('strong',$refill_work_order['customer_code'])
                        .' '.$refill_work_order['customer_name'];
                    
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('Refill_Work_Order_Engine',
                            $refill_work_order['refill_work_order_status']
                        );
                    
                    $product_condition = Refill_Work_Order_Engine::$product_condition;
                    
                    $response['refill_work_order'] = $refill_work_order;
                    $response['rwo_product'] = $rwo_product;
                    $response['refill_work_order_info'] = $refill_work_order_info;
                    $response['refill_work_order_status_list'] = $next_allowed_status_list;
                    $response['product_condition'] = $product_condition;
                }
                //</editor-fold>
                break;
            case 'product_price_get':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('refill_product_price_list/refill_product_price_list_data_support');
                $customer_id = isset($data['customer_id'])?Tools::_str($data['customer_id']):'';
                $product_category_id = isset($data['product_category_id'])?Tools::_str($data['product_category_id']):'';
                $product_medium_id = isset($data['product_medium_id'])?Tools::_str($data['product_medium_id']):'';
                $capacity_unit_id = isset($data['capacity_unit_id'])?Tools::_str($data['capacity_unit_id']):'';
                $capacity = isset($data['capacity'])?Tools::_str($data['capacity']):'';
                $price = Refill_Product_Price_List_Data_Support::product_price_get($customer_id,$product_category_id, $product_medium_id, $capacity_unit_id,$capacity);
                $response = $price;
                //</editor-fold>
                break;
            
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function refill_work_order_add(){        
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>'','method'=>'refill_work_order_add','primary_data_key'=>'refill_work_order','data_post'=>$post);            
            SI::data_submit()->submit('refill_work_order_engine',$param);
            
        }
        
    }
    
    public function refill_work_order_initialized($id){
        
        $this->load->helper($this->path->refill_work_order_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'refill_work_order_initialized','primary_data_key'=>'refill_work_order','data_post'=>$post);            
            SI::data_submit()->submit('refill_work_order_engine',$param);
        }
    }
    
    public function refill_work_order_process($id){
        
        $this->load->helper($this->path->refill_work_order_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'refill_work_order_process','primary_data_key'=>'refill_work_order','data_post'=>$post);            
            SI::data_submit()->submit('refill_work_order_engine',$param);

        }
    }
    
    public function refill_work_order_canceled($id){
        
        $this->load->helper($this->path->refill_work_order_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'refill_work_order_canceled','primary_data_key'=>'refill_work_order','data_post'=>$post);            
            SI::data_submit()->submit('refill_work_order_engine',$param);

        }
    }
    
    public function refill_work_order_print($id,$module,$prm1=''){
        $this->load->helper($this->path->refill_work_order_print);
        $post = $this->input->post();
        switch($module){
            case 'refill_work_order_form':
                Refill_Work_Order_Print::refill_work_order_form_print($id);
                break;
        }
    }
}

?>