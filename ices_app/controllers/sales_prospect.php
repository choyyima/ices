<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sales_Prospect extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get('Sales Prospect');
        get_instance()->load->helper('sales_prospect/sales_prospect_engine');
        $this->path = Sales_Prospect_Engine::path_get();
        $this->title_icon = App_Icon::sales_prospect();
        
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
        $form = $row->form_add()->form_set('title',Lang::get('Sales Prospect'))->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array('New','Sales Prospect')))
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        
        $cols = array(
            array("name"=>"code","label"=>Lang::get("Sales Prospect Code"),"data_type"=>"text","is_key"=>true)
            ,array("name"=>"customer_name","label"=>Lang::get("Customer"),"data_type"=>"text")
            ,array("name"=>"sales_prospect_date","label"=>Lang::get("Sales Prospect Date"),"data_type"=>"text")
            ,array("name"=>"grand_total","label"=>Lang::get("Grand Total"),"data_type"=>"text",'row_attrib'=>array('style'=>'text-align:right'))
            ,array("name"=>"sales_prospect_status_name","label"=>Lang::get("Status"),"data_type"=>"text")
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/sales_prospect')
                ->table_ajax_set('columns',$cols)
                ->table_ajax_set('key_column','id')
                ->filter_set(array(
                        array('id'=>'reference_type_filter','field'=>'reference_type')
                    ))
                ;        
        $app->render();
        
    }
    
    public function add(){
        $this->load->helper($this->path->sales_prospect_engine);
        $post = $this->input->post();
        
        $this->view('','add');
        
    }
    
    public function view($id="",$method="view"){
        
        $this->load->helper($this->path->sales_prospect_engine);
        $this->load->helper($this->path->sales_prospect_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Sales_Prospect_Engine::sales_prospect_exists($id)){
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
            $app->set_breadcrumb($this->title,'sales_prospect');
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row')->div_set('id','sales_prospect');            

            $content = $row->div_add()->div_set("span","12");

            Sales_Prospect_Renderer::sales_prospect_render($app,$content,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
            
                
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
            case'sales_prospect':
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
                    select distinct t1.*, t2.name customer_name
                    from sales_prospect t1
                        inner join customer t2 on t1.customer_id = t2.id
                    where t1.status>0
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
                    $rs[$i]['grand_total'] = Tools::thousand_separator($rs[$i]['grand_total'],5);
                    $rs[$i]['sales_prospect_status_name'] 
                            = SI::get_status_attr(
                                    SI::status_get('Sales_Prospect_Engine',$rs[$i]['sales_prospect_status'])['label']
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
                        and t2.product_price_list_id = '.$db->escape($price_list_id).' 
                        and t1.id not in ('.$q_excluded_product.')
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
        get_instance()->load->helper('sales_pos/sales_pos_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        switch($method){
            case 'sales_prospect_current_status':
                $db = new DB();
                $q = 'select sales_prospect_status from sales_prospect where id = '.$db->escape($data['data']);
                $rs = $db->query_array_obj($q);
                if(is_null($rs)){
                    $success = 0;
                    $msg[] = $db->_error_message();
                }
                else{
                    if(count($rs)>0){
                        $result['response'] = $rs[0]->sales_prospect_status;
                    }                  
                    else
                        $result['response'] = '';
                }
                break;
            case 'default_status_get':    
                get_instance()->load->helper($this->path->sales_prospect_engine);
                $result = SI::status_default_status_get('Sales_Prospect_Engine');
                if(isset($result['label'])){
                    $result['label'] = SI::get_status_attr($result['label']);
                }
                break;
            case 'next_allowed_status':
                get_instance()->load->helper($this->path->sales_prospect_engine);
                $curr_status_val = isset($data['data'])?$data['data']:'';
                $allowed_status = SI::status_next_allowed_status_get('Sales_Prospect_Engine',$curr_status_val);
                $num_of_res = count($allowed_status);
                for($i = 0;$i<$num_of_res;$i++){
                    if(Security_Engine::get_controller_permission(
                        User_Info::get()['user_id']
                            ,'sales_prospect'
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

                $response = Sales_Pos_Data_Support::extra_charge_message_get($data);
                $result['response'] = $response;
                break;
            case 'expedition_weight_get':
                $response = Sales_Pos_Data_Support::expedition_weight_message_get($data);
                $result['response'] = $response;
                break;
            case 'product_unit_dependency_get':

                get_instance()->load->helper('product_stock_engine');
                get_instance()->load->helper('master/warehouse_engine');
                $response = array();                
                $product_id = isset($data['product_id'])?$data['product_id']:'';
                $unit_id = isset($data['unit_id'])?$data['unit_id']:'';
                $response['mult_qty'] = Tools::thousand_separator(
                        Sales_Pos_Data_Support::multiplication_qty_get($product_id, $unit_id)
                        ,2,true);
                $warehouse_bos = Warehouse_Engine::BOS_get('id');
                
                $response['stock_qty'] = Tools::thousand_separator(
                        Product_Stock_Engine::stock_sum_get('stock_sales_available',$product_id, $unit_id,$warehouse_bos)
                    ,2,true);

                $result['response'] = $response;
                break;
            case 'modal_product':
                $response = array();
                $result['response'] = Sales_Pos_Data_Support::modal_product_generate($data);
                break;
            case 'sales_prospect_get':
                get_instance()->load->helper('product_stock_engine');
                get_instance()->load->helper('product/product_engine');
                get_instance()->load->helper('master/warehouse_engine');
                $db = new DB();
                $response = array();
                $status = 1;
                $sales_prospect_id = isset($data['sales_prospect_id'])?
                    (is_string($data['sales_prospect_id'])?$data['sales_prospect_id']:''):'';
                
                $sales_prospect = array();
                $q = '
                    select *
                    from sales_prospect 
                    where id = '.$db->escape($sales_prospect_id).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    $rs[0]['total_product'] = Tools::thousand_separator($rs[0]['total_product'],5);
                    $rs[0]['extra_charge'] = Tools::thousand_separator($rs[0]['extra_charge'],5);
                    $rs[0]['delivery_cost_estimation'] = Tools::thousand_separator($rs[0]['delivery_cost_estimation'],5);
                    $rs[0]['grand_total'] = Tools::thousand_separator($rs[0]['grand_total'],5);
                    $rs[0]['sales_prospect_status_name'] = SI::status_get('Sales_Prospect_Engine', $rs[0]['sales_prospect_status'])['label'];
                    $rs[0]['sales_prospect_status_name'] = SI::get_status_attr($rs[0]['sales_prospect_status_name']);
                    $rs[0]['sales_prospect_date'] = Date('F m, Y H:i:s',strtotime($rs[0]['sales_prospect_date']));
                    $sales_prospect = $rs[0];
                    
                }
                else $status = 0;
                
                
                if($status === 1){
                    $sales_prospect_info = array();
                    $q = '
                        select t1.*, t2.code sales_inquiry_by_text
                        from sales_prospect_info t1 
                            inner join sales_inquiry_by t2 on t1.sales_inquiry_by_id = t2.id
                        where t1.sales_prospect_id = '.$db->escape($sales_prospect_id)
                            
                            
                    .'';
                    $rs = $db->query_array($q);
                    
                    $sales_prospect_info = $rs[0];
                    
                    
                    $customer = array();
                    $q = '
                        select t2.*
                        from sales_prospect t1
                            inner join customer t2 on t1.customer_id = t2.id
                        where t1.id = '.$db->escape($sales_prospect_id).'
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
                        where id = '.$db->escape($sales_prospect_info['product_price_list_id']).'
                    ';
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        $price_list = $rs[0];
                        $price_list['price_list_text'] = $price_list['name'];
                    }
                    
                    $expedition = array();
                    $q = '
                        select t1.id, t1.code, t1.name
                        from expedition t1 
                            inner join sales_prospect_info t2 on t1.id = t2.expedition_id
                        where t2.sales_prospect_id = '.$db->escape($sales_prospect['id']).'
                    ';
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        $expedition = $rs[0];
                        $expedition['expedition_text'] = SI::html_tag('strong',$expedition['code']).' '.$expedition['name'];
                    }
                    
                    $is_delivery = $sales_prospect_info['is_delivery'];
                    
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
                        from sales_prospect_product t1
                            inner join product t2 on t1.product_id = t2.id
                            inner join unit t3 on t1.unit_id = t3.id
                            inner join unit t4 on t1.expedition_weight_unit_id = t4.id
                        where t1.sales_prospect_id = '.$sales_prospect_id.'
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
                            'product_text'=>SI::html_tag('strong',$rs[$i]['product_code'])
                                .' '.$rs[$i]['product_name'],
                            'mult_qty'=>  Tools::thousand_separator($mult_qty,5),
                            'qty'=>Tools::thousand_separator($rs[$i]['qty'],5),
                            'unit_name'=>$rs[$i]['unit_code'],
                            'amount'=>Tools::thousand_separator($rs[$i]['amount'],5),
                            'subtotal'=>Tools::thousand_separator($rs[$i]['subtotal'],5),
                            'expedition_weight'=>Tools::thousand_separator($rs[$i]['expedition_weight_qty'],5).' '.$rs[$i]['expedition_weight_unit_code'],                            
                            'total_stock'=> Tools::thousand_separator(Product_Stock_Engine::stock_sum_get('stock_sales_available',$rs[$i]['product_id'], $rs[$i]['unit_id'], $warehouse_list),5)
                        );
                        $weight_total+=floatval($rs[$i]['expedition_weight_qty']);
                        $weight_total_unit_name=$rs[$i]['expedition_weight_unit_code'];
                    }
                    
                    $has_discount = $price_list['is_discount'];
                    if(floatval($sales_prospect['discount']) === 0){
                        $has_discount = '0';
                    }
                    
                    $additional_cost  = array();
                    $q = '
                        select description, amount
                        from sales_prospect_additional_cost
                        where sales_prospect_id = '.$db->escape($sales_prospect_id).'
                    ';
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        for($i = 0;$i<count($rs);$i++){
                            $rs[$i]['description'] = $rs[$i]['description'].' ('.Tools::currency_get().')';
                            $rs[$i]['amount'] = Tools::thousand_separator($rs[$i]['amount']);
                        }
                        $additional_cost = $rs;
                    }
                    $response['sales_info'] = $sales_prospect_info;
                    $response['has_discount'] = $has_discount;
                    $response['weight_total'] = Tools::thousand_separator($weight_total,5).' '.$weight_total_unit_name;
                    $response['expedition'] = $expedition;
                    $response['sales_prospect'] = $sales_prospect;
                    $response['customer'] = $customer;
                    $response['price_list'] = $price_list;
                    $response['is_delivery'] = $is_delivery;
                    $response['product'] = $product;
                    $response['additional_cost'] = $additional_cost;
                    $response['extra_charge_msg'] = $sales_prospect_info['extra_charge_msg'];
                }
                $result['response']  = $response;
                break;
            case 'sales_pos_get':
                $db = new DB();
                $response = '';
                $sales_prospect_id = isset($data['sales_prospect_id'])?
                        $data['sales_prospect_id']:'';
                $q = '
                    select sii.sales_invoice_id 
                    from sales_invoice_info sii
                        inner join sales_invoice si on si.id = sii.sales_invoice_id
                    where sii.reference_id = '.$db->escape($sales_prospect_id).'
                        and sii.reference_type = "sales_prospect"
                        and si.sales_invoice_status = "invoiced"
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0) $response = $rs[0]['sales_invoice_id'];
                $result['response'] =  $response;
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
    
    public function sales_prospect_mail($module=''){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->sales_prospect_engine);
        $post = $this->input->post();
        $data = json_decode($post,TRUE);
        $result = array('success'=>1,'msg'=>array());
        $success = 1;        
        $msg = array();
        switch($module){
            case 'sales_prospect':
                $sales_prospect_id = isset($data['sales_prospect_id'])?Tools::_str($data['sales_prospect_id']):'';
                $temp_result = Sales_Prospect_Engine::sales_prospect_mail($data);
                $success = $temp_result['success'];
                $msg = $temp_result['msg'];                
                
                break;
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function sales_prospect_print($module='',$id='',$prm1=''){
        $this->load->helper($this->path->sales_prospect_print);
        $post = $this->input->post();
        switch($module){
            case 'sales_prospect':
                Sales_Prospect_Print::prospect_print($id);
                break;
        }
    }
    
    public function sales_prospect_add(){
        $this->load->helper($this->path->sales_prospect_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>'','method'=>'sales_prospect_add','primary_data_key'=>'sales_prospect','data_post'=>$post);
            SI::data_submit()->submit('sales_prospect_engine',$param);
            
        }
    }
    
    public function sales_prospect_canceled($id=''){
        $this->load->helper($this->path->sales_prospect_engine);
        $post = $this->input->post();
        if($post!= null){
            $param = array('id'=>$id,'method'=>'sales_prospect_canceled','primary_data_key'=>'sales_prospect','data_post'=>$post);            
            SI::data_submit()->submit('sales_prospect_engine',$param);
        }
    }
}

?>