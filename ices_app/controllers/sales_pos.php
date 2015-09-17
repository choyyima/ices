<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sales_POS extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get('Point of Sale');
        get_instance()->load->helper('sales_pos/sales_pos_engine');
        $this->path = Sales_Pos_Engine::path_get();
        $this->title_icon = App_Icon::sales_pos();
        
    }
    
    public function index()
    {           
        $action = "";

        $app = new App();            
        $db = $this->db;

        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower('sales_pos'));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title',$this->title)->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array('New','Point of Sale')))
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        
        $cols = array(
            array("name"=>"code","label"=>Lang::get("Point of Sale Code"),"data_type"=>"text","is_key"=>true)
            ,array("name"=>"is_delivery","label"=>Lang::get("Delivery"),"data_type"=>"text")
            ,array("name"=>"customer_name","label"=>Lang::get("Customer"),"data_type"=>"text")
            ,array("name"=>"sales_invoice_date","label"=>Lang::get("Point of Sale Date"),"data_type"=>"text")
            ,array("name"=>"grand_total","label"=>Lang::get("Grand Total Amount").'('.Tools::currency_get().')',"data_type"=>"text",'attribute'=>array('style'=>'text-align:right'),'row_attrib'=>array('style'=>'text-align:right'))
            ,array("name"=>"sales_pos_status_name","label"=>Lang::get("Status"),"data_type"=>"text")
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/sales_pos')
                ->table_ajax_set('columns',$cols)
                ->table_ajax_set('key_column','id')
                ->filter_set(array(
                        array('id'=>'reference_type_filter','field'=>'reference_type')
                    ))
                ;        
        $app->render();
        
    }
    
    public function add(){
        $this->load->helper($this->path->sales_pos_engine);                
        $this->view('','add');
    }
    
    public function view($id="",$method="view"){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->sales_pos_engine);
        $this->load->helper($this->path->sales_pos_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Sales_Pos_Engine::sales_pos_exists($id)){
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
            $app->set_breadcrumb($this->title,'sales_pos');
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row')->div_set('id','sales_pos');            

            $content = $row->div_add()->div_set("span","12");

            Sales_POS_Renderer::sales_pos_render($app,$content,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
            
                
            }
            $post = $this->input->post();
            // for sales prospect to sales pos
            Assigner_Engine::value_set($app, $post);
            $js = get_instance()->load->view('sales_pos/sales_prospect_js',array(),true);
            $app->js_set($js);
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
        $limit = 10;
        switch($method){
            case'sales_pos':
                $db = new DB();
                $records_page = $data['records_page'];
                $page = $data['page'];
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $reference_type = '';
                $final_rs = array();
                $q_reference_type_filter='';
                switch($reference_type){
                    case 'rma':
                        $q_reference_type_filter = ' and t1.receive_product_type = "rma" ';
                        break;
                    
                }
                
                $q = '
                select * from (
                    select distinct t1.*, t2.name customer_name,
                        t3.is_delivery
                    from sales_invoice t1
                        inner join customer t2 on t1.customer_id = t2.id
                        inner join sales_invoice_info t3 on t1.id = t3.sales_invoice_id
                    where t1.status>0 and t3.sales_invoice_type = "sales_invoice_pos"
                ';
                $q_group = ' )tfinal
                    ';
                $q_where=' 
                    and (t1.code like '.$lookup_str.'
                        or t2.name like '.$lookup_str.'
                    )
                    
                ';
                
                $extra='';
                if(strlen($data['sort_by'])>0) {$extra.=' order by '.$data['sort_by'];}
                else {$extra.=' order by code desc';}
                $extra .= '  limit '.(($page-1)*$records_page).', '.($records_page);
                $q_total_row = $q.$q_where.$q_group;
                $q_data = $q.$q_where.$q_group.$extra;
                $total_rows = $db->select_count($q_total_row,null,null);
                $rs = $db->query_array($q_data);


                $total_rs = count($rs);

                for($i = 0;$i<$total_rs;$i++){
                    $rs[$i]['is_delivery'] = Tools::_bool($rs[$i]['is_delivery'])?'Yes':'No';
                    $rs[$i]['grand_total'] = Tools::thousand_separator($rs[$i]['grand_total'],5);
                    $rs[$i]['sales_pos_status_name'] 
                            = SI::get_status_attr(
                                    SI::status_get('Sales_Pos_Engine',$rs[$i]['sales_invoice_status'])['label']
                                );
                }
                $final_rs = $rs;
                $result = array("header"=>array("total_rows"=>$total_rows),"data"=>$final_rs);
                
                break;
            case 'input_select_store_search':
                $db = new DB();
                $q = '
                    select id id, name text
                    from store
                    where status>0
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $result['response'] = $rs;
                }
                break;
            
            case 'input_select_customer_search':
                $db = new DB();
                $lookup_str = $db->escape('%'.(isset($data['data'])?$data['data']:'').'%');
                $q = '
                    select t1.*
                    from customer t1
                    where t1.status>0 
                        and (
                            t1.code like '.$lookup_str.'
                            or t1.name like '.$lookup_str.'
                            or t1.phone like '.$lookup_str.'
                            or t1.phone2 like '.$lookup_str.'
                            or t1.phone3 like '.$lookup_str.'
                            or t1.email like '.$lookup_str.'
                            or t1.bb_pin like '.$lookup_str.'
                        )
                        and t1.customer_status = "A"
                    limit 0,'.$limit.'
                    
                ';
                $rs = $db->query_array($q);
                
                if(count($rs)>0){
                    for($i = 0;$i<count($rs);$i++){
                        $rs[$i]['text'] = SI::html_tag('strong',$rs[$i]['code']).' '.$rs[$i]['name'].' '.$rs[$i]['phone'];
                    }
                    $result['response'] = $rs;
                }                
                break;
                
            case 'input_select_approval_search':
                $db = new DB();
                $lookup_str = $db->escape('%'.(isset($data['data'])?$data['data']:'').'%');
                $q = '
                    select distinct t1.*
                    from approval t1
                        inner join approval_type t2 on t1.approval_type_id = t2.id
                    where t1.status>0
                        and t1.use < t1.limit
                        and (
                            t1.code like '.$lookup_str.'
                            or t1.name like '.$lookup_str.'
                        )
                        and t2.code = "SIP"
                    limit 0,'.$limit.'
                    
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    for($i = 0;$i<count($rs);$i++){
                        $rs[$i]['text'] = $rs[$i]['code'].' '.$rs[$i]['name'].' Limit:'.$rs[$i]['limit'].' Use:'.$rs[$i]['use'];
                    }
                    $result['response'] = $rs;
                }                
                break;
            
            case 'input_select_expedition_search':
                $db = new DB();
                $response = array();
                $q = '
                    select id, code, name
                    from expedition
                    where expedition_status = "A"
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    for($i = 0;$i<count($rs);$i++){
                        $rs[$i]['text'] = SI::html_tag('strong',$rs[$i]['code']).' '.$rs[$i]['name'];
                    }
                    $response = $rs;
                }
                $result['response'] = $response;
                break;
                
                
            case 'input_select_product_search':
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
                        inner join product_price_list_product t2 on
                            t1.id = t2.product_id
                    where t1.status>0 
                        and t1.product_status = "active"
                        and (
                            t1.code like '.$lookup_str.'
                            or t1.name like '.$lookup_str.'
                        )
                        and t1.id not in ('.$q_excluded_product.')
                        and t2.product_price_list_id = '.$db->escape($price_list_id).' 
                    order by t1.code
                    limit 0,'.$db->row_limit.'
                    
                ';
                $rs = $db->query_array($q);
                $response = array();
                if(count($rs)>0){
                    for($i = 0;$i<count($rs);$i++){
                        $rs[$i]['text'] = SI::html_tag('strong',$rs[$i]['code']).' '.$rs[$i]['name'];
                    }
                    $response = $rs;
                }            
                $result['response'] = $response;
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
        get_instance()->load->helper($this->path->sales_pos_data_support);
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        switch($method){
            case 'default_status_get':    
                get_instance()->load->helper($this->path->sales_pos_engine);
                $result = SI::status_default_status_get('Sales_Pos_Engine');
                if(isset($result['label'])){
                    $result['label'] = SI::get_status_attr($result['label']);
                }
                break;
            case 'next_allowed_status':
                get_instance()->load->helper($this->path->sales_pos_engine);
                $curr_status_val = isset($data['data'])?$data['data']:'';
                $allowed_status = SI::status_next_allowed_status_get('Sales_Pos_Engine',$curr_status_val);
                $num_of_res = count($allowed_status);
                for($i = 0;$i<$num_of_res;$i++){
                    if(Security_Engine::get_controller_permission(
                        User_Info::get()['user_id']
                            ,'sales_pos'
                            ,strtolower($allowed_status[$i]['method']))){
                            $allowed_status[$i]['label'] = SI::get_status_attr($allowed_status[$i]['label']);
                    }
                    else{
                        unset($allowed_status[$i]);
                    }
                }
                $result['response'] = $allowed_status;
                break;
            case 'customer_get':
                $db = new DB();
                
                $q = '
                    select *
                    from customer
                    where id = '.$data['data'].'
                               
                ';
                $result = $db->query_array($q);
                if(count($result)>0) $result = $result[0];
                break;
                
            case 'customer_detail_get':
                $db = new DB();
                $customer_id = $data['customer_id'];
                $response = null;
                $q = '
                    select t1.id
                        , t1.code customer_code
                        , t1.name customer_name
                        , concat(t1.phone," ",t1.phone2," ",t1.phone3) customer_phone
                        ,t1.bb_pin customer_bb_pin
                        ,t1.email customer_email
                        ,case is_credit when "1" then "True" else "False" end is_credit
                        ,case is_sales_receipt_outstanding when "1" then "True" else "False" end is_sales_receipt_outstanding
                    from customer t1
                    where t1.id = '.$customer_id.'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $response = $rs[0];
                }
                $result['response'] = $response;
                break;
            case 'price_list_list_get':
                $db = new DB();
                $customer_id = isset($data['customer_id'])?$data['customer_id']:'';
                $q = '
                    select distinct t5.id id, t5.name text
                    from customer_type t1
                        inner join customer_customer_type t2 on t1.id = t2.customer_type_id
                        inner join customer t3 on t3.id = t2.customer_id
                        inner join customer_type_product_price_list t4 on t4.customer_type_id = t1.id
                        inner join product_price_list t5 on t5.id = t4.product_price_list_id
                    where t3.id = '.$db->escape($customer_id).'
                ';
                $response = array();
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $response = $rs;
                }
                $result['response'] = $response;
                break;
            case 'price_list_get':
                $db = new DB();
                $response = array();
                $price_list_id = isset($data['price_list_id'])?$data['price_list_id']:'';
                $q = '
                    select t1.*
                    from product_price_list t1
                    where t1.id = '.$db->escape($price_list_id).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $response = $rs[0];
                }
                $result['response'] = $response;
                break;
                
            case 'product_img_get':
                $db = new DB();
                $product_id = isset($data['product_id'])? $data['product_id']:'';
                $response = array();
                $filename = 'img/product/'.$product_id.'.jpg';
                $response['product_img'] = '<img class="product-img" src = "'.Tools::img_load($filename,false).'"></img>';
                $result['response'] = $response;
                break;
                
            case 'product_unit_get':
                $db = new DB();
                $product_id = isset($data['product_id'])? $data['product_id']:'';

                $q = '
                    select t3.id unit_id, t3.code unit_code
                    from product t1
                        inner join product_unit t2 on t1.id = t2.product_id
                        inner join unit t3 on t3.id = t2.unit_id
                    where t1.id = '.$db->escape($product_id).'
                ';
                $rs = $db->query_array($q);
                $response = array();
                $response['unit'] = [];
                if(count($rs)>0){
                    for($i = 0;$i<count($rs);$i++){
                        $response['unit'][] = array(
                            'id'=>$rs[$i]['unit_id']
                            ,'code'=>$rs[$i]['unit_code']
                        );
                    }
                }
                $result['response'] = $response;
                break;
            case 'payment_type_get':
                $customer_id = isset($data['customer_id'])?$data['customer_id']:'';
                $response = Sales_Pos_Data_Support::payment_type_get($customer_id);
                $result['response'] = $response;
                break;
                
            case 'product_price_get':
                $product_id = isset($data['product_id'])?$data['product_id']:'';
                $unit_id = isset($data['unit_id'])?$data['unit_id']:'';
                $price_list_id = isset($data['price_list_id'])?$data['price_list_id']:'';
                $qty = isset($data['qty'])?$data['qty']:'';
                
                $response = Sales_Pos_Data_Support::product_price_get($price_list_id, $product_id, $unit_id, $qty);
                $response = Tools::thousand_separator($response,5,true);
                $result['response'] = $response;
                break;
            
            case 'extra_charge_get':
                get_instance()->load->helper($this->path->sales_pos_engine);
                $response = Sales_Pos_Data_Support::extra_charge_message_get($data);
                $result['response'] = $response;
                break;
            case 'expedition_weight_get':
                get_instance()->load->helper($this->path->sales_pos_engine);
                $response = Sales_Pos_Data_Support::expedition_weight_message_get($data);
                $result['response'] = $response;
                break;
            case 'product_unit_dependency_get':
                //get_instance()->load->helper('product/product_engine');
                get_instance()->load->helper('product_stock_engine');
                $response = array();                
                $product_id = isset($data['product_id'])?$data['product_id']:'';
                $unit_id = isset($data['unit_id'])?$data['unit_id']:'';
                $response['mult_qty'] = Tools::thousand_separator(
                        Sales_Pos_Data_Support::multiplication_qty_get($product_id, $unit_id)
                        ,2,true);
                $response['stock_qty'] = Tools::thousand_separator(
                        Product_Stock_Engine::stock_sum_get('stock_sales_available',$product_id, $unit_id,  Warehouse_Engine::BOS_get('id'))
                        ,2,true);

                $result['response'] = $response;
                break;
            case 'customer_deposit_get':
                get_instance()->load->helper('customer_deposit_allocation/customer_deposit_allocation_engine');
                $db = new DB();
                $response = array();
                $customer_id = isset($data['customer_id'])?
                        Tools::_str($data['customer_id']):'';
                $product_grand_total = isset($data['product_grand_total'])?
                        Tools::_str($data['product_grand_total']):'0';
                $customer_deposit_arr = Customer_Deposit_Allocation_Engine::
                        cda_allocate_amount_get($customer_id, $product_grand_total);
                for($i = 0;$i<count($customer_deposit_arr);$i++){
                    $cust_dep_id = $customer_deposit_arr[$i]['customer_deposit_id'];
                    $cust_dep = $db->fast_get('customer_deposit', array('id'=>$cust_dep_id))[0];
                    $customer_deposit_arr[$i]['customer_deposit_code'] = $cust_dep['code'];
                    $customer_deposit_arr[$i]['customer_deposit_date'] = Tools::_date($cust_dep['customer_deposit_date'],'F d, Y');
                    $customer_deposit_arr[$i]['amount'] = Tools::thousand_separator($customer_deposit_arr[$i]['amount'],5);
                    $customer_deposit_arr[$i]['allocated_amount'] = Tools::thousand_separator($customer_deposit_arr[$i]['allocated_amount'],5);
                    
                    
                }
                $response = $customer_deposit_arr;
                $result['response'] = $response;
                break;
            
            case 'warehouse_list_get':
                
                $response = array();
                $response = Warehouse_Engine::BOS_get(array('id','name'));
                $result['response'] = $response;
                break;
            
            case 'movement_product_diff_get':
                $response = array();
                $pos = isset($data['pos'])?
                        Tools::_arr($data['pos']):array();
                $movement_arr = isset($data['movement'])?
                        Tools::_arr($data['movement']):array();
                $response = Sales_Pos_Engine::movement_product_diff_get($pos, $movement_arr);
                
                $result['response'] = $response;                
                break;
            case 'sales_prospect_get':
                //<editor-fold defaultstate="collapsed">
                $id = isset($data['id'])?Tools::_str($data['id']):'';
                $response = array();
                $db = new DB();
                $rs = $db->fast_get('sales_prospect',array('id'=>$id));
                if(count($rs)>0){
                    
                    $reference_detail = Sales_Pos_Data_Support::reference_detail_get('sales_prospect',$id);
                    
                    $sales_prospect = $rs[0];
                    $q = '
                        select t1.*, t2.code sales_inquiry_by_text
                        from sales_prospect_info t1
                            inner join sales_inquiry_by t2 on t1.sales_inquiry_by_id = t2.id
                        where t1.sales_prospect_id = '.$db->escape($id).'
                    ';
                    $info = $db->query_array($q)[0];
                    
                    
                    $customer = $db->fast_get('customer',array('id'=>$rs[0]['customer_id']))[0];
                    $customer['text'] = SI::html_tag('strong',$customer['code']).' '.$customer['name'];
                    $customer['sales_receipt_outstanding'] = $customer['is_sales_receipt_outstanding']==='1'?'True':'False';
                    
                    $price_list = $db->fast_get('product_price_list',array('id'=>$info['product_price_list_id']))[0];
                    $price_list['text'] = $price_list['name'];
                    
                    $expedition = null;
                    $rs = $db->fast_get('expedition',array('id'=>$info['expedition_id']));
                    if(count($rs)>0){
                        $expedition = $rs[0];
                        $expedition['text'] = SI::html_tag('strong',$expedition['code']).' '.$expedition['name'];
                    }
                    
                    $products = array();
                    $q = '
                        select 
                            t1.product_id
                            ,t2.code product_code
                            ,t2.name product_name
                            ,t3.id unit_id
                            ,t3.code unit_code
                            ,t3.name unit_name
                            ,t1.qty
                        from sales_prospect_product t1
                            inner join product t2 on t1.product_id = t2.id
                            inner join unit t3 on t3.id = t1.unit_id
                        where t1.sales_prospect_id = '.$db->escape($id).'
                    ';
                    
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        for($i = 0;$i<count($rs);$i++){
                            $rs[$i]['product_text'] = SI::html_tag('strong',$rs[$i]['product_code'])
                                .' '.$rs[$i]['product_name'];
                            $rs[$i]['unit_text'] = $rs[$i]['unit_code'];
                            $rs[$i]['qty'] = Tools::thousand_separator($rs[$i]['qty']);
                        }
                        $products = $rs;
                    }
                    
                    $additional_cost = array();
                    $q = '
                        select *
                        from sales_prospect_additional_cost
                        where sales_prospect_id = '.$db->escape($id).'
                    ';
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        for($i = 0;$i<count($rs);$i++){
                            $rs[$i]['amount'] = Tools::thousand_separator($rs[$i]['amount'],5);
                        }
                        $additional_cost = $rs;
                    }
                                        
                    $response['reference_detail'] = $reference_detail;
                    $response['sales_info'] = $info;
                    $response['sales_prospect'] = $sales_prospect;
                    $response['customer'] = $customer;
                    $response['price_list'] = $price_list;
                    $response['expedition'] = $expedition;
                    $response['products'] = $products;
                    $response['additional_cost'] = $additional_cost;
                }
                $result['response'] = $response;
                //</editor-fold>
                break;
                
            case 'sales_pos_get':
                //<editor-fold defaultstate="collapsed">
                get_instance()->load->helper('product_stock_engine');
                get_instance()->load->helper('product/product_engine');
                get_instance()->load->helper('master/warehouse_engine');
                get_instance()->load->helper('delivery_order_final/delivery_order_final_engine');
                get_instance()->load->helper('intake_final/intake_final_engine');
                get_instance()->load->helper('customer_deposit/customer_deposit_engine');
                get_instance()->load->helper('customer_bill/customer_bill_engine');
                
                $db = new DB();
                $response = array();
                $status = 1;
                $sales_pos_id = isset($data['sales_pos_id'])?
                    (is_string($data['sales_pos_id'])?$data['sales_pos_id']:''):'';
                
                $sales_pos = array();
                $q = '
                    select t1.*,t2.*,t3.name store_text
                    from sales_invoice t1
                        inner join sales_invoice_info t2 on t1.id = t2.sales_invoice_id
                        inner join store t3 on t1.store_id = t3.id
                    where t1.id = '.$db->escape($sales_pos_id).'
                        and t2.sales_invoice_type ="sales_invoice_pos"
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $rs[0]['total_product'] = Tools::thousand_separator($rs[0]['total_product'],5);
                    $rs[0]['extra_charge'] = Tools::thousand_separator($rs[0]['extra_charge'],5);
                    $rs[0]['delivery_cost_estimation'] = Tools::thousand_separator($rs[0]['delivery_cost_estimation'],5);
                    $rs[0]['grand_total'] = Tools::thousand_separator($rs[0]['grand_total'],5);
                    $rs[0]['sales_invoice_status_name'] = SI::get_status_attr(
                        SI::status_get('Sales_Pos_Engine', $rs[0]['sales_invoice_status'])['label']
                    );
                    $rs[0]['sales_pos_date'] = Date('F m, Y H:i:s',strtotime($rs[0]['sales_invoice_date']));
                    $sales_pos = $rs[0];
                    
                }
                else $status = 0;
                
                
                if($status === 1){
                    $sales_pos_info = array();
                    $q = '
                        select t1.*,
                            t2.code approval_text, 
                            t3.code expedition_code, 
                            t3.name expedition_name,
                            t4.code sales_inquiry_by_text
                        from sales_invoice_info t1
                            left outer join approval t2 on t1.approval_id = t2.id
                            left outer join expedition t3 on t1.expedition_id = t3.id
                            inner join sales_inquiry_by t4 on t1.sales_inquiry_by_id = t4.id
                        where t1.sales_invoice_id = '.$db->escape($sales_pos_id)
                    .'';
                    $rs = $db->query_array($q);
                    $sales_pos_info = $rs[0];
                    if($sales_pos_info['reference_type'] === 'sales_prospect'){
                        get_instance()->load->helper('sales_prospect/sales_prospect_data_support');
                        $sales_prospect = Sales_Prospect_Data_Support::sales_prospect_get($sales_pos_info['reference_id']);
                        $sales_pos_info['reference_text'] = $sales_prospect['code'];
                    }
                    $reference_detail = Sales_Pos_Data_Support::reference_detail_get($sales_pos_info['reference_type'], $sales_pos_info['reference_id']);
                    
                    
                    $sales_pos_info['expedition_text'] = 
                        SI::html_tag('strong',$sales_pos_info['expedition_code']).' '.$sales_pos_info['expedition_name'];
                    
                    $customer = array();
                    $q = '
                        select t2.*
                        from sales_invoice t1
                            inner join customer t2 on t1.customer_id = t2.id
                        where t1.id = '.$db->escape($sales_pos_id).'
                    ';
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        $customer = $rs[0];
                        $customer['customer_text'] = SI::html_tag('strong',$customer['code']).' '.$customer['name'];
                        $customer['is_sales_receipt_outstanding'] = $customer['is_sales_receipt_outstanding'] === '1'?'True':'False';
                    }

                    $price_list = array();
                    $q = '
                        select id, code, name, is_discount
                        from product_price_list 
                        where id = '.$db->escape($sales_pos_info['product_price_list_id']).'
                    ';
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        $price_list = $rs[0];
                        $price_list['price_list_text'] = $price_list['name'];
                    }
                                        
                    $is_delivery = $sales_pos_info['is_delivery'];
                    
                    $product = array();
                    $warehouse_list = Warehouse_Engine::BOS_get('id');
                    
                    $q = '
                        select 
                            t1.product_id
                            ,t2.code product_code
                            ,t2.name product_name
                            , t1.qty
                            , t3.code unit_code
                            ,t1.expedition_weight_qty
                            ,t4.code expedition_weight_unit_code
                            ,t1.amount
                            ,t1.subtotal
                            ,t1.product_id
                            ,t1.unit_id
                            ,t1.movement_outstanding_qty
                        from sales_invoice_product t1
                            inner join product t2 on t1.product_id = t2.id
                            inner join unit t3 on t1.unit_id = t3.id
                            inner join unit t4 on t1.expedition_weight_unit_id = t4.id
                        where t1.sales_invoice_id = '.$sales_pos_id.'
                    ';
                    $rs = $db->query_array($q);
                    $weight_total = 0;
                    $weight_total_unit_name = 'KG';
                    for($i = 0;$i<count($rs);$i++){                        
                        $product_id = $rs[$i]['product_id'];
                        $unit_id = $rs[$i]['unit_id'];
                        $mult_qty = Sales_Pos_Data_Support::multiplication_qty_get($product_id, $unit_id);
                        $product[] = array(
                            'product_img'=>Product_Engine::img_get($product_id),
                            'product_id'=>$rs[$i]['product_id'],
                            'product_text'=>SI::html_tag('strong',$rs[$i]['product_code'])
                                .' '.$rs[$i]['product_name'],
                            'mult_qty'=>  Tools::thousand_separator($mult_qty,5),
                            'qty'=>Tools::thousand_separator($rs[$i]['qty'],5),
                            'movement_outstanding_qty'=>Tools::thousand_separator($rs[$i]['movement_outstanding_qty'],5),
                            'unit_id'=>$rs[$i]['unit_id'],
                            'unit_name'=>$rs[$i]['unit_code'],
                            'amount'=>Tools::thousand_separator($rs[$i]['amount'],5),
                            'subtotal'=>Tools::thousand_separator($rs[$i]['subtotal'],5),
                            'expedition_weight'=>Tools::thousand_separator($rs[$i]['expedition_weight_qty'],5).' '.$rs[$i]['expedition_weight_unit_code'],                            
                            'total_stock'=> Tools::thousand_separator(Product_Stock_Engine::stock_sum_get('stock_sales_available',$rs[$i]['product_id'], $rs[$i]['unit_id'], $warehouse_list),5)
                        );
                        $weight_total+=floatval($rs[$i]['expedition_weight_qty']);
                        $weight_total_unit_name=$rs[$i]['expedition_weight_unit_code'];
                    }
                    
                    $additional_cost  = array();
                    $q = '
                        select description, amount
                        from sales_invoice_additional_cost
                        where sales_invoice_id = '.$db->escape($sales_pos_id).'
                    ';
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        for($i = 0;$i<count($rs);$i++){
                            $rs[$i]['description'] = $rs[$i]['description'].' ('.Tools::currency_get().')';
                            $rs[$i]['amount'] = Tools::thousand_separator($rs[$i]['amount']);
                        }
                        $additional_cost = $rs;
                    }
                    
                    $customer_deposit = array();
                    $q='
                        select t1.id, t1.code, t1.customer_deposit_date, t1.amount,
                            t2.allocated_amount
                        from customer_deposit t1
                            inner join customer_deposit_allocation t2 on t1.id = t2.customer_deposit_id
                        where t2.sales_invoice_id = '.$db->escape($sales_pos_id).'
                            and t1.status > 0 
                            and t2.customer_deposit_allocation_status != "X"
                    ';
                    
                    $rs = $db->query_array($q);
                    if(count($rs)>0){                        
                        for($i = 0;$i<count($rs);$i++){
                            $rs[$i]['amount'] = Tools::thousand_separator($rs[$i]['amount'],5);
                            $rs[$i]['allocated_amount'] = Tools::thousand_separator($rs[$i]['allocated_amount'],5);
                        }
                        $customer_deposit = $rs;
                    }
                    
                    $payment = array();
                    $change = Tools::_float('0');
                    $q='
                        select t1.*, date(t1.sales_receipt_date) receipt_date, 
                            t2.allocated_amount,
                            t3.code payment_type_code,
                            t1.change_amount,
                            bba.code bba_code
                        from sales_receipt t1
                            inner join sales_receipt_allocation t2 on t1.id = t2.sales_receipt_id
                            inner join payment_type t3 on t3.id = t1.payment_type_id
                            left outer join bos_bank_account bba 
                                on bba.id = t1.bos_bank_account_id
                        where t2.sales_invoice_id = '.$db->escape($sales_pos_id).'
                            and t1.status > 0 
                            and t2.sales_receipt_allocation_status !="X"
                    ';
                    
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        
                        for($i = 0;$i<count($rs);$i++){
                            $rs[$i]['code'] = $rs[$i]['code'];
                            $rs[$i]['bos_bank_account_text'] = $rs[$i]['bba_code'];
                            $rs[$i]['amount'] = Tools::thousand_separator($rs[$i]['amount'],5);
                            $rs[$i]['allocated_amount'] = Tools::thousand_separator($rs[$i]['allocated_amount'],5);
                            $change+=Tools::_float($rs[$i]['change_amount']);
                        }
                        $payment = $rs;
                    }
                    
                    $final_movement = array();
                    $movement_name = '';
                    $movement_engine='';
                    if($is_delivery === '1'){
                        $movement_name = 'delivery_order';
                        $movement_engine = 'Delivery_Order';
                    }
                    else{
                        $movement_name = 'intake';
                        $movement_engine = 'Intake';
                    }
                    $q = '
                        select t1.*
                        from '.$movement_name.'_final t1
                            inner join sales_invoice_'.$movement_name.'_final t2 
                                on t2.'.$movement_name.'_final_id = t1.id
                        where t2.sales_invoice_id = '.$db->escape($sales_pos_id).'
                    ';
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        for($i = 0;$i<count($rs);$i++){
                            $temp_arr = array(
                                'id'=>$rs[$i]['id'],
                                'code'=>$rs[$i]['code'],
                                'movement_date'=>Tools::_date($rs[$i][$movement_name.'_final_date'],'F d, Y H:i'),
                                'movement'=>array(),
                                'movement_status'=>SI::status_get($movement_engine.'_Final_Engine',
                                    $rs[$i][$movement_name.'_final_status'])['label'],
                            );                            
                            $temp_arr['movement_status'] = SI::get_status_attr($temp_arr['movement_status']);
                            $final_movement[] = $temp_arr;
                        }
                    }
                    
                    for($i = 0;$i<count($final_movement);$i++){
                        $final_movement_id = $final_movement[$i]['id'];
                        $q = '
                            select distinct t1.*,t3.warehouse_id warehouse_from_id '
                                .($is_delivery?',t4.warehouse_id warehouse_to_id ':'')
                            .'from '.$movement_name.' t1
                                inner join '.$movement_name.'_final_'.$movement_name.' t2
                                    on t1.id = t2.'.$movement_name.'_id
                                inner join '.$movement_name.'_warehouse_from t3
                                    on t1.id = t3.'.$movement_name.'_id '.
                                
                                ($is_delivery?('left outer join '.$movement_name.'_warehouse_to t4
                                    on t1.id = t4.'.$movement_name.'_id '):'').
                                
                            'where t2.'.$movement_name.'_final_id = '.$db->escape($final_movement_id).'
                                
                        ';
                        $rs_mov = $db->query_array($q);
                        if(!is_null($rs_mov)){
                            foreach($rs_mov as $mov_idx=>$mov){
                                $movement_id = $mov['id'];
                                $temp_mov = array(
                                    'id'=>$movement_id,
                                    'code'=>$mov['code'],
                                    'warehouse_id'=>$mov['warehouse_from_id'],
                                    'product'=>array(),
                                );
                                
                                $q = '
                                    select * 
                                    from '.$movement_name.'_product t1
                                    where '.$movement_name.'_id = '.$db->escape($movement_id).'
                                ';
                                $rs_product = $db->query_array($q);
                                if(!is_null($rs_product)){
                                    foreach($rs_product as $p_idx=>$p){
                                        $temp_product = array(
                                            'product_id'=>$p['product_id'],
                                            'unit_id'=>$p['unit_id'],
                                            'qty'=>$p['qty'],
                                        );
                                        $temp_mov['product'][] =$temp_product;
                                    }
                                }

                                $final_movement[$i]['movement'][] = $temp_mov;
                            }
                        }
                    }
                    
                    $dofc_customer_deposit = array();
                    $q = '
                        select t1.*
                        from customer_deposit t1
                            inner join dofc_cd t2 on t1.id = t2.customer_deposit_id
                            inner join dof_dofc on t2.delivery_order_final_confirmation_id = dof_dofc.delivery_order_final_confirmation_id
			inner join sales_invoice_delivery_order_final sidof on dof_dofc.delivery_order_final_id = sidof.delivery_order_final_id
                        where sidof.sales_invoice_id = '.$db->escape($sales_pos_id).'
                    ';
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        for($i = 0 ;$i<count($rs);$i++){
                            $rs[$i]['customer_deposit_status_text'] = SI::get_status_attr(
                                SI::type_get('customer_deposit_engine', $rs[$i]['customer_deposit_status'], '$status_list')['label']);
                            $rs[$i]['amount'] = Tools::thousand_separator($rs[$i]['amount']);
                            $rs[$i]['outstanding_amount'] = Tools::thousand_separator($rs[$i]['outstanding_amount']);
                        }
                        $dofc_customer_deposit = $rs;
                    }
                    
                    $dofc_customer_bill = array();
                    $q = '
                        select t1.*
                        from customer_bill t1
                            inner join dofc_cb t2 on t1.id = t2.customer_bill_id
                            inner join dof_dofc on t2.delivery_order_final_confirmation_id = dof_dofc.delivery_order_final_confirmation_id
			inner join sales_invoice_delivery_order_final sidof on dof_dofc.delivery_order_final_id = sidof.delivery_order_final_id
                        where sidof.sales_invoice_id = '.$db->escape($sales_pos_id).'
                    ';
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        for($i = 0 ;$i<count($rs);$i++){
                            $rs[$i]['customer_bill_status_text'] = SI::get_status_attr(
                                SI::type_get('customer_bill_engine', $rs[$i]['customer_bill_status'], '$status_list')['label']);
                            $rs[$i]['amount'] = Tools::thousand_separator($rs[$i]['amount']);
                            $rs[$i]['outstanding_amount'] = Tools::thousand_separator($rs[$i]['outstanding_amount']);
                        }
                        $dofc_customer_bill = $rs;
                    }
                    
                    
                    $response['reference_detail'] = $reference_detail;
                    $response['dofc_customer_deposit'] = $dofc_customer_deposit;
                    $response['dofc_customer_bill'] = $dofc_customer_bill;                    
                    $response['weight_total'] = Tools::thousand_separator($weight_total,5).' '.$weight_total_unit_name;
                    $response['sales_pos'] = $sales_pos;
                    $response['sales_pos_info'] = $sales_pos_info;
                    $response['customer'] = $customer;
                    $response['price_list'] = $price_list;
                    $response['is_delivery'] = $is_delivery;
                    $response['product'] = $product;
                    $response['additional_cost'] = $additional_cost;
                    $response['extra_charge_msg'] = $sales_pos_info['extra_charge_msg'];
                    $response['customer_deposit'] = $customer_deposit;
                    $response['payment'] = $payment;
                    $response['change'] = Tools::thousand_separator($change,5);
                    $response['final_movement'] = $final_movement;
                }
                $result['response']  = $response;
                //</editor-fold>
                break;
                
            case 'sales_inquiry_by_get':
                $sales_inquiry_by = Sales_Pos_Data_Support::sales_inquiry_by_get();
                $response = array();
                for($i = 0;$i<count($sales_inquiry_by);$i++){
                    $response[] = array('id'=>$sales_inquiry_by[$i]['id'],'text'=>$sales_inquiry_by[$i]['code']);
                }
                $result['response'] = $response;
                break;
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function sales_pos_add(){
        $this->load->helper($this->path->sales_pos_engine);
        get_instance()->load->helper($this->path->sales_pos_data_support);
        
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>'','method'=>'sales_pos_add','primary_data_key'=>'sales_pos','data_post'=>$post);            
            SI::data_submit()->submit('sales_pos_engine',$param);
        }
        
    }
    
    public function sales_pos_mail($module=''){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->sales_pos_engine);
        $post = $this->input->post();
        $data = json_decode($post,TRUE);
        $result = array('success'=>1,'msg'=>array());
        $success = 1;        
        $msg = array();
        switch($module){
            case 'sales_pos':
                $sales_pos_id = isset($data['sales_invoice_id'])?Tools::_str($data['sales_invoice_id']):'';
                $temp_result = Sales_Pos_Engine::sales_pos_mail($data);
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];                
                
                break;
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function sales_pos_print($id,$module,$prm1=''){
        $this->load->helper($this->path->sales_pos_print);
        $post = $this->input->post();
        switch($module){
            case 'invoice':
                Sales_Pos_Print::invoice_print($id);
                break;
            case 'payment':
                Sales_Pos_Print::payment_print($id);
                break;
            case 'movement':
                $f_movement_id = $prm1;
                Sales_Pos_Print::movement_print($id,$f_movement_id);
                break;
        }
    }
}

?>