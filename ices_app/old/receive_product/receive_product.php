<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Receive_Product extends MY_Controller {
    
    private $title='';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        $this->title = Lang::get('Receive Product');
        get_instance()->load->helper('receive_product/receive_product_engine');
        $this->path = Receive_Product_Engine::path_get();
        $this->title_icon = App_Icon::receive_product();
        
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
        $form = $row->form_add()->form_set('title',Lang::get('Receive Product List'))->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get('New Receive Product'))
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        
        $reference_type_list = array(
            array('value'=>'','label'=>'ALL')
            ,array('value'=>'purchase_invoice','label'=>Lang::get('Purchase Invoice'))
            ,array('value'=>'rma','label'=>'Return Merchandise Authorization')
            
            
        );
        
        $form->select_add()
                ->select_set('id','reference_type_filter')
                ->select_set('options_add',$reference_type_list)
                ;
        
        $cols = array(
            array("name"=>"code","label"=>Lang::get("Receive Product Code"),"data_type"=>"text","is_key"=>true)
            ,array("name"=>"supplier_name","label"=>Lang::get("Supplier"),"data_type"=>"text")
            ,array("name"=>"receive_product_date","label"=>Lang::get("Receive Product Date"),"data_type"=>"text")
            ,array("name"=>"receive_product_warehouse_from_name","label"=>Lang::get("From Warehouse"),"data_type"=>"text")            
            ,array("name"=>"receive_product_warehouse_to_name","label"=>Lang::get("To Warehouse"),"data_type"=>"text")            
            ,array("name"=>"receive_product_status_name","label"=>Lang::get("Status"),"data_type"=>"text")
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/receive_product')
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
        $this->load->helper($this->path->receive_product_engine);
        $post = $this->input->post();
        
        $this->view('','add');
        
    }
    
    public function view($id="",$method="view"){
        
        $this->load->helper($this->path->receive_product_engine);
        $this->load->helper($this->path->receive_product_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!Receive_Product_Engine::receive_product_exists($id)){
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
            $app->set_breadcrumb($this->title,strtolower($this->title));
            $app->set_content_header($this->title,$this->title_icon,$action);
            $row = $app->engine->div_add()->div_set('class','row');            

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');
            Receive_Product_Renderer::receive_product_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                Receive_Product_Renderer::receive_product_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
            }
            
            
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
        
        
    }
    
    public function ajax_search($method="",$submethod=""){
        $data = json_decode($this->input->post(), true);
        $result =array();
        switch($method){
            case 'receive_product':
                $db = new DB();
                $records_page = $data['records_page'];
                $page = $data['page'];
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $reference_type = isset($data['additional_filter']['reference_type'])?
                        $data['additional_filter']['reference_type']:'';
                $final_rs = array();
                
                $q_reference_type_filter='';
                switch($reference_type){
                    case 'purchase_invoice':
                        $q_reference_type_filter = ' and t1.receive_product_type = "purchase_invoice" ';
                        break;
                    case 'rma':
                        $q_reference_type_filter = ' and t1.receive_product_type = "rma" ';
                        break;
                    
                    
                }
                
                $q = '
                select * from (
                    select distinct t1.*
                        ,case t1.receive_product_status 
                            when "O" then "OPENED"
                            when "D" then "DELIVERED"
                            when "X" then "CANCELED"
                            when "P" then "POSTPONED"
                            when "R" then "RECEIVED"
                            end receive_product_status_name
                        ,case 
                            when t5.name is not null then
                                t5.name
                            else 
                                case when t14.name is not null then
                                    t14.name
                                else null
                                end
                            end supplier_name
                        ,t6.name receive_product_warehouse_to_name
                        ,t8.name receive_product_warehouse_from_name
                    from receive_product t1
                    
                        
                        
                        inner join receive_product_warehouse_to t9 on t9.receive_product_id = t1.id
                        inner join warehouse t6 on t9.warehouse_id = t6.id
                        inner join receive_product_warehouse_from t10 on t10.receive_product_id = t1.id
                        inner join warehouse t8 on t10.warehouse_id = t8.id
                        
                        left outer join purchase_invoice_receive_product t3 
                            on t3.receive_product_id = t1.id
                        left outer join purchase_invoice t4 
                            on t4.id = t3.purchase_invoice_id
                        left outer join supplier t5 on t5.id = t4.supplier_id
                        
                        left outer join rma_receive_product t11
                            on t11.receive_product_id = t1.id                    
                        left outer join rma t12
                            on t12.id = t11.rma_id
                        left outer join rma_supplier t13 on t13.rma_id = t12.id
                        
                        left outer join supplier t14 on t14.id = t13.supplier_id
                        
                    where t1.status>0
                ';
                $q_group = ' )tfinal
                    ';
                $q_where=' 
                    and (t1.code like '.$lookup_str.'
                        or t5.name like '.$lookup_str.'
                    )
                    '.$q_reference_type_filter.'
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
                    $rs[$i]['receive_product_status_name'] 
                            = SI::get_status_attr(
                                    $rs[$i]['receive_product_status_name']
                                );
                }
                $final_rs = $rs;
                $result = array("header"=>array("total_rows"=>$total_rows),"data"=>$final_rs);
                
                break;
            
            case 'input_select_reference_search':
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $limit = 10;
                $q = '
                    select distinct t1.id id
                            ,t1.code reference_code
                            ,"purchase_invoice" reference_type
                            ,"Purchase Invoice" reference_type_name
                            ,t1.grand_total
                            ,t2.code supplier_code
                            ,t2.name supplier_name
                            ,t2.phone supplier_phone
                    from purchase_invoice t1
                        inner join supplier t2 on t1.supplier_id = t2.id
                    where t1.purchase_invoice_status = "I"
                        and t1.code like '.$lookup_str.'
                    limit 0, '.$limit.'
                        
                ';
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){

                    $rs[$i]['grand_total'] = Tools::thousand_separator($rs[$i]['grand_total'],2,true);
                    
                    $rs[$i]['text'] = ''
                            .$rs[$i]['reference_code']
                            .' <span class="pull-right">'
                            .' Supplier: <strong>'.$rs[$i]['supplier_name'].' '.$rs[$i]['supplier_phone'].'</strong>'
                            .' Grand Total ('.Tools::currency_get().'): <strong>'.$rs[$i]['grand_total'].'</strong>'
                            
                            .'</span>'
                        ;
                    $result['response'][]  = $rs[$i];
                }
                
                $q = '
                    select distinct t1.id id
                            ,t1.code reference_code
                            ,"rma" reference_type
                            ,"Return Merchandise Authorization" reference_type_name
                            ,t3.code supplier_code
                            ,t3.name supplier_name
                            ,t3.phone supplier_phone
                    from rma t1
                        left outer join rma_supplier t2 on t1.id = t2.rma_id
                        left outer join supplier t3 on t3.id = t2.supplier_id
                    where t1.rma_status = "O"
                        and t1.code like '.$lookup_str.'
                    limit 0, '.$limit.'
                        
                ';
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){
                    $rs[$i]['text'] = ''
                            .$rs[$i]['reference_code']
                            .' <span class="pull-right">'
                            .' Supplier: <strong>'.$rs[$i]['supplier_name'].' '.$rs[$i]['supplier_phone'].'</strong>'
                            .'</span>'
                        ;
                    $result['response'][]  = $rs[$i];
                }
                
                break;
                
            case 'purchase_invoice':
                switch($submethod){
                    

                    

                    
                }
                break;// end of mehod purchase_invoice
            
            
        }
        
        echo json_encode($result);
    }
    
    public function data_support($method="",$submethod=""){
        //this function only used for urgently data retrieve
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        switch($method){
            case 'receive_product_init_get':
                $receive_product_id = isset($data['data'])?$data['data']:'';
                $db = new DB();
                $q = '
                    select distinct t1.id receive_product_id, t1.code, t1.receive_product_type
                    from receive_product t1
                    where t1.id = '.$db->escape($receive_product_id).'
                ';
                $rs = $db->query_array($q);
                if(is_null($rs)){
                    $success = 0;
                    $msg[] = $db->_error_message();
                }
                else{
                    if(count($rs)>0){
                        $reference_type = '';
                        switch($rs[0]['receive_product_type']){
                            case 'purchase_invoice':
                                $q = '
                                    select t1.*
                                    from purchase_invoice t1
                                        inner join purchase_invoice_receive_product t2 on t1.id = t2.purchase_invoice_id
                                    where t2.receive_product_id='.$db->escape($rs[0]['receive_product_id']).'
                                ';
                                $rs_purchase_invoice = $db->query_array_obj($q)[0];
                                $rs[0]['reference_type'] = 'purchase_invoice';// mandatory
                                $rs[0]['id'] = $rs_purchase_invoice->id;
                                $rs[0]['text']= $rs_purchase_invoice->code;
                                $rs[0]['reference_type_name'] = 'Purchase Invoice';
                                $rs[0]['reference_code'] = $rs_purchase_invoice->code;                            
                                $rs[0]['grand_total'] = Tools::thousand_separator($rs_purchase_invoice->grand_total,2,true);
                                break;
                            case 'rma':
                                $q = '
                                    select t1.*
                                    from rma t1
                                        inner join rma_receive_product t2 on t1.id = t2.rma_id
                                    where t2.receive_product_id='.$db->escape($rs[0]['receive_product_id']).'
                                
                                ';
                                $rs_rma = $db->query_array_obj($q)[0];
                                $rs[0]['reference_type'] = 'rma';// mandatory
                                $rs[0]['id'] = $rs_rma->id;
                                $rs[0]['text']= $rs_rma->code;
                                $rs[0]['reference_type_name'] = 'Return Merchandise Authorization';
                                $rs[0]['reference_code'] = $rs_rma->code;                            
                                break;
                        }                         
                        
                        
                        $result['response'] = $rs[0];
                    }
                }
                break;        
            case 'receive_product_current_status':
                $db = new DB();
                $q = 'select receive_product_status from receive_product where id = '.$db->escape($data['data']);
                $rs = $db->query_array_obj($q);
                if(is_null($rs)){
                    $success = 0;
                    $msg[] = $db->_error_message();
                }
                else{
                    if(count($rs)>0){
                        $result['response'] = $rs[0]->receive_product_status;
                    }                  
                    else
                        $result['response'] = '';
                }
                break;
            
            case 'purchase_invoice':   
                get_instance()->load->helper('receive_product/receive_product_purchase_invoice_engine');
                switch($submethod){
                    case 'default_status_get':                       
                        $result = Receive_Product_Purchase_Invoice_Engine::receive_product_purchase_invoice_status_default_status_get();
                        if(isset($result['label'])){
                            $result['label'] = SI::get_status_attr($result['label']);
                        }
                        break;
                    case 'next_allowed_status':
                        $curr_status_val = $data['data'];
                        $allowed_status = Receive_Product_Purchase_Invoice_Engine::receive_product_purchase_invoice_status_next_allowed_status_get($curr_status_val);
                        $num_of_res = count($allowed_status);
                        for($i = 0;$i<$num_of_res;$i++){
                            if(Security_Engine::get_controller_permission(
                                User_Info::get()['user_id']
                                    ,'receive_product'
                                    ,strtolower($allowed_status[$i]['method']))){
                                    $allowed_status[$i]['label'] = SI::get_status_attr($allowed_status[$i]['label']);
                            }
                            else{
                                unset($allowed_status[$i]);
                            }
                        }
                        $result['response'] = $allowed_status;
                        break;
                    
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
                            $result['response'] = $rs[0];
                        }
                        break;
                    case 'warehouse_from_detail_get':
                        $db = new DB();
                        $rma_id = $data['purchase_invoice_id'];
                        $q = ' 
                            select distinct t2.name contact_name, t2.address, t2.phone
                            from purchase_invoice t1                                
                                inner join supplier t2 on t2.id = t1.supplier_id
                            where t1.id = '.$rma_id.'
                        ';
                        $rs = $db->query_array($q);
                        if(is_null($rs)){
                            $success = 0;
                            $msg[] = $db->_error_message();
                        }
                        else{
                            if(count($rs)>0){
                                $result['response'] = $rs[0];
                                $q = '
                                    select t1.code, t1.name, concat(t2.code," - ", t2.name) type 
                                    from warehouse t1 
                                        inner join warehouse_type t2 on t1.warehouse_type_id = t2.id 
                                    where t1.code = "WS"';
                                $rs = $db->query_array($q);
                                $result['response']['code'] = $rs[0]['code'];
                                $result['response']['name'] = $rs[0]['name'];
                                $result['response']['type'] = $rs[0]['type'];
                                
                            }
                        }
                        break;
                    case 'purchase_invoice_detail_get':
                        $db = new DB();
                        $q = '
                            select grand_total
                                ,date_format(purchase_invoice_date,"%Y-%m-%d") purchase_invoice_date
                                ,date_format(purchase_invoice_date,"%H:%i") purchase_invoice_time 
                            from purchase_invoice where id = '.$db->escape($data['data']).' 
                                
                        ';
                        $rs = $db->query_array($q);
                        if(is_null($rs)){
                            $success = 0;
                            $msg[] = $db->_error_message();
                        }
                        else{
                            if(count($rs)>0){
                                $rs[0]['grand_total'] = Tools::thousand_separator($rs[0]['grand_total'],2,true);
                                $result['response'] = $rs[0];
                            }                  
                        }                       
                        
                        break;
                    case 'purchase_invoice_product_outstanding_get':
                        get_instance()->load->helper('receive_product/receive_product_engine');
                        $path = Receive_Product_Engine::path_get();
                        get_instance()->load->helper($path->receive_product_purchase_invoice_engine);
                        $db = new DB();
                        $purchase_invoice_id = $data['data'];
                        $q = '
                            select t1.product_id, t2.name product_name, t1.qty invoiced_qty
                                ,0 max_qty
                                , t1.unit_id, t3.name unit_name
                            from purchase_invoice_product t1
                                inner join product t2 on t1.product_id = t2.id
                                inner join unit t3 on t1.unit_id = t3.id
                            where t1.purchase_invoice_id = '.$db->escape($purchase_invoice_id).' order by t2.name
                        ';
                        $rs = $db->query_array($q);
                        for($i = 0;$i<count($rs);$i++){
                            $rs[$i]['invoiced_qty'] = Tools::thousand_separator($rs[$i]['invoiced_qty'],2,true);
                            $rs[$i]['max_qty'] = Receive_Product_Purchase_Invoice_Engine::
                                    purchase_invoice_max_qty_get(
                                        $rs[$i]['product_id'], $rs[$i]['unit_id'], $purchase_invoice_id
                                    );
                            $rs[$i]['max_qty'] = Tools::thousand_separator($rs[$i]['max_qty'],2,true);
                            $filename = 'img/product/'.$rs[$i]['product_id'].'.jpg';
                            $rs[$i]['product_img'] = '<img src = "'.Tools::img_load($filename,false).'"></img>';
                        }
                        $result['response'] = $rs;

                        break;
                    case 'receive_product_get':
                        $db = new DB();

                        $q = '
                            select distinct t1.code, t1.receive_product_status, t1.receive_product_date
                                ,t8.id warehouse_from_id
                                ,t8.code warehouse_from_code
                                ,t8.name warehouse_from_name
                                ,concat(t9.code, " - ",t9.name) warehouse_from_type
                                ,t7.reference_code warehouse_from_reference_code
                                ,t7.contact_name warehouse_from_contact_name
                                ,t7.address warehouse_from_address
                                ,t7.phone warehouse_from_phone
                                ,t2.id warehouse_to_id
                                ,t2.name warehouse_to_name
                                ,t4.id purchase_invoice_id, t4.code purchase_invoice_code
                                ,t1.receive_product_status 
                                ,t1.notes    
                                ,t1.cancellation_reason
                                ,t1.store_id
                                ,t5.name store_name
                                
                                ,t7.contact_name warehouse_from_contact_name
                                ,t7.reference_code warehouse_from_reference_code
                                ,t7.address warehouse_from_address
                                ,t7.phone warehouse_from_phone
                            from receive_product t1                                
                                
                                inner join purchase_invoice_receive_product t3 on  t3.receive_product_id = t1.id
                                inner join purchase_invoice t4 on t4.id =  t3.purchase_invoice_id
                                inner join store t5 on t5.id = t1.store_id
                                inner join receive_product_warehouse_to t6 on t1.id = t6.receive_product_id
                                inner join warehouse t2 on t6.warehouse_id = t2.id
                                inner join receive_product_warehouse_from t7 on t7.receive_product_id = t1.id
                                inner join warehouse t8 on t8.id = t7.warehouse_id
                                inner join warehouse_type t9 on t8.warehouse_type_id = t9.id
                            where t1.id = '.$db->escape($data['data']).'
                        ';
                        $rs = $db->query_array($q);
                        if(is_null($rs)){
                            $success = 0;
                            $msg[] = $db->_error_message();
                        }
                        else{
                            if(count($rs)>0){
                                get_instance()->load->helper('receive_product/receive_product_purchase_invoice_engine');
                                $status_list = Receive_Product_Purchase_Invoice_Engine::receive_product_purchase_invoice_status_list_get();
                                for($i = 0;$i<count($status_list);$i++){
                                    if($status_list[$i]['val'] === $rs[0]['receive_product_status']){
                                        $rs[0]['receive_product_status_name'] = 
                                                SI::get_status_attr($status_list[$i]['label']);
                                    }
                                }
                                $result = $rs[0];
                            }
                        }

                        break;
                        
                    case 'receive_product_product_get':
                        $db = new DB();
                        $q = '
                            select t1.product_id, t2.name product_name, t1.unit_id, t3.name unit_name, t1.qty
                            from receive_product_product t1
                                inner join product t2 on t1.product_id = t2.id
                                inner join unit t3 on t1.unit_id = t3.id
                            where t1.receive_product_id = '.$data['data'].'
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
                            $result['response'] = $rs;
                        }
                        break;

                }
                break;
        
            case 'rma':   
                get_instance()->load->helper('receive_product/receive_product_rma_engine');
                switch($submethod){
                    case 'default_status_get':                       
                        $result = Receive_Product_RMA_Engine::receive_product_rma_status_default_status_get();
                        if(isset($result['label'])){
                            $result['label'] = SI::get_status_attr($result['label']);
                        }
                        break;
                    case 'next_allowed_status':
                        $curr_status_val = $data['data'];
                        $allowed_status = Receive_Product_RMA_Engine::receive_product_rma_status_next_allowed_status_get($curr_status_val);
                        $num_of_res = count($allowed_status);
                        for($i = 0;$i<$num_of_res;$i++){
                            if(Security_Engine::get_controller_permission(
                                User_Info::get()['user_id']
                                    ,'receive_product'
                                    ,strtolower($allowed_status[$i]['method']))){
                                    $allowed_status[$i]['label'] = SI::get_status_attr($allowed_status[$i]['label']);
                            }
                            else{
                                unset($allowed_status[$i]);
                            }
                        }
                        $result['response'] = $allowed_status;
                        break;
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
                            $result['response'] = $rs[0];
                        }
                        break;
                    case 'warehouse_from_detail_get':
                        $db = new DB();
                        $rma_id = $data['rma_id'];
                        $q = ' 
                            select distinct t3.name contact_name, t3.address, t3.phone
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
                                $result['response'] = $rs[0];
                                $q = '
                                    select t1.code, t1.name, concat(t2.code," - ", t2.name) type 
                                    from warehouse t1 
                                        inner join warehouse_type t2 on t1.warehouse_type_id = t2.id 
                                    where t1.code = "WS"';
                                $rs = $db->query_array($q);
                                $result['response']['code'] = $rs[0]['code'];
                                $result['response']['name'] = $rs[0]['name'];
                                $result['response']['type'] = $rs[0]['type'];
                                
                            }
                        }
                        break;
                    case 'rma_detail_get':
                        $db = new DB();
                        $q = '
                            select 
                                date_format(t1.rma_date,"%Y-%m-%d") rma_date
                                ,date_format(t1.rma_date,"%H:%i") rma_time 
                                ,concat(t3.name," ",t3.phone) supplier
                            from rma t1 
                                inner join rma_supplier t2 on t1.id = t2.rma_id
                                inner join supplier t3 on t3.id = t2.supplier_id
                            where t1.id = '.$db->escape($data['data']).' 
                        ';
                        $rs = $db->query_array($q);
                        if(is_null($rs)){
                            $success = 0;
                            $msg[] = $db->_error_message();
                        }
                        else{
                            if(count($rs)>0){
                                $result['response'] = $rs[0];
                            }                  
                        }                       
                        
                        break;
                    case 'rma_product_outstanding_get':
                        get_instance()->load->helper('receive_product/receive_product_engine');
                        $path = Receive_Product_Engine::path_get();
                        get_instance()->load->helper($path->receive_product_rma_engine);
                        $db = new DB();
                        $rma_id = $data['data'];
                        $q = '
                            select t1.product_id, t2.name product_name, t1.qty rma_qty
                                ,0 max_qty
                                , t1.unit_id, t3.name unit_name
                            from rma_product t1
                                inner join product t2 on t1.product_id = t2.id
                                inner join unit t3 on t1.unit_id = t3.id
                            where t1.rma_id = '.$db->escape($rma_id).' order by t2.name
                        ';
                        $rs = $db->query_array($q);
                        for($i = 0;$i<count($rs);$i++){
                            $rs[$i]['rma_qty'] = Tools::thousand_separator($rs[$i]['rma_qty'],2,true);
                            $rs[$i]['max_qty'] = Receive_Product_RMA_Engine::
                                    rma_max_qty_get(
                                        $rs[$i]['product_id'], $rs[$i]['unit_id'], $rma_id
                                    );
                            $rs[$i]['max_qty'] = Tools::thousand_separator($rs[$i]['max_qty'],2,true);
                            $filename = 'img/product/'.$rs[$i]['product_id'].'.jpg';
                            $rs[$i]['product_img'] = '<img src = "'.Tools::img_load($filename,false).'"></img>';
                        }
                        $result['response'] = $rs;

                        break;
                    case 'receive_product_get':
                        $db = new DB();

                        $q = '
                            select distinct t1.code, t1.receive_product_status, t1.receive_product_date
                                ,t8.id warehouse_from_id
                                ,t8.code warehouse_from_code
                                ,t8.name warehouse_from_name
                                ,concat(t9.code, " - ",t9.name) warehouse_from_type
                                ,t7.reference_code warehouse_from_reference_code
                                ,t7.contact_name warehouse_from_contact_name
                                ,t7.address warehouse_from_address
                                ,t7.phone warehouse_from_phone
                                ,t2.id warehouse_to_id
                                ,t2.name warehouse_to_name
                                ,t4.id rma_id, t4.code rma_code
                                ,t1.receive_product_status 
                                ,t1.notes    
                                ,t1.cancellation_reason
                                ,t1.store_id
                                ,t5.name store_name
                                
                                ,t7.contact_name warehouse_from_contact_name
                                ,t7.reference_code warehouse_from_reference_code
                                ,t7.address warehouse_from_address
                                ,t7.phone warehouse_from_phone
                            from receive_product t1                                
                                
                                inner join rma_receive_product t3 on  t3.receive_product_id = t1.id
                                inner join rma t4 on t4.id =  t3.rma_id
                                inner join store t5 on t5.id = t1.store_id
                                inner join receive_product_warehouse_to t6 on t1.id = t6.receive_product_id
                                inner join warehouse t2 on t6.warehouse_id = t2.id
                                inner join receive_product_warehouse_from t7 on t7.receive_product_id = t1.id
                                inner join warehouse t8 on t8.id = t7.warehouse_id
                                inner join warehouse_type t9 on t8.warehouse_type_id = t9.id
                            where t1.id = '.$db->escape($data['data']).'
                        ';
                        $rs = $db->query_array($q);
                        if(is_null($rs)){
                            $success = 0;
                            $msg[] = $db->_error_message();
                        }
                        else{
                            if(count($rs)>0){
                                get_instance()->load->helper('receive_product/receive_product_rma_engine');
                                $status_list = Receive_Product_RMA_Engine::receive_product_rma_status_list_get();
                                for($i = 0;$i<count($status_list);$i++){
                                    if($status_list[$i]['val'] === $rs[0]['receive_product_status']){
                                        $rs[0]['receive_product_status_name'] = 
                                                SI::get_status_attr($status_list[$i]['label']);
                                    }
                                }
                                $result = $rs[0];
                            }
                        }

                        break;
                        
                    case 'receive_product_product_get':
                        $db = new DB();
                        $q = '
                            select t1.product_id, t2.name product_name, t1.unit_id, t3.name unit_name, t1.qty
                            from receive_product_product t1
                                inner join product t2 on t1.product_id = t2.id
                                inner join unit t3 on t1.unit_id = t3.id
                            where t1.receive_product_id = '.$data['data'].'
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
                            $result['response'] = $rs;
                        }
                        break;

                }
                break;
        
                
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        echo json_encode($result);
    }
    
    public function printing($method="",$id=''){
        get_instance()->load->helper($this->path->receive_product_print);
        switch($method){
            case 'receive_product':
                Receive_Product_Print::print_receive_product($id);
                break;
        }
    }
    
    public function purchase_invoice_add(){
        $this->load->helper($this->path->receive_product_purchase_invoice_engine);
        $post = $this->input->post();
        if($post!= null){
            Receive_Product_Purchase_Invoice_Engine::purchase_invoice_submit('','purchase_invoice_add',$post);
        }
    }
    
    public function purchase_invoice_opened($id){
        $this->load->helper($this->path->receive_product_purchase_invoice_engine);
        $post = $this->input->post();
        if($post!= null){
            Receive_Product_Purchase_Invoice_Engine::purchase_invoice_submit($id,'purchase_invoice_opened',$post);
        }
    }
    
    public function purchase_invoice_delivered($id){
        $this->load->helper($this->path->receive_product_purchase_invoice_engine);
        $post = $this->input->post();
        if($post!= null){
            Receive_Product_Purchase_Invoice_Engine::purchase_invoice_submit($id,'purchase_invoice_delivered',$post);
        }
    }
    
    public function purchase_invoice_postponed($id){
        $this->load->helper($this->path->receive_product_purchase_invoice_engine);
        $post = $this->input->post();
        if($post!= null){
            Receive_Product_Purchase_Invoice_Engine::purchase_invoice_submit($id,'purchase_invoice_postponed',$post);
        }
    }
    
    public function purchase_invoice_canceled($id){
        $this->load->helper($this->path->receive_product_purchase_invoice_engine);
        $post = $this->input->post();
        if($post!= null){
            Receive_Product_Purchase_Invoice_Engine::purchase_invoice_submit($id,'purchase_invoice_canceled',$post);
        }
    }
    
    public function purchase_invoice_received($id){
        $this->load->helper($this->path->receive_product_purchase_invoice_engine);
        $post = $this->input->post();
        if($post!= null){
            Receive_Product_Purchase_Invoice_Engine::purchase_invoice_submit($id,'purchase_invoice_received',$post);
        }
    }
    
    public function rma_add(){
        $this->load->helper($this->path->receive_product_rma_engine);
        $post = $this->input->post();
        if($post!= null){
            Receive_Product_RMA_Engine::rma_submit('','rma_add',$post);
        }
    }
    
    public function rma_opened($id){
        $this->load->helper($this->path->receive_product_rma_engine);
        $post = $this->input->post();
        if($post!= null){
            Receive_Product_RMA_Engine::rma_submit($id,'rma_opened',$post);
        }
    }
    
    public function rma_delivered($id){
        $this->load->helper($this->path->receive_product_rma_engine);
        $post = $this->input->post();
        if($post!= null){
            Receive_Product_RMA_Engine::rma_submit($id,'rma_delivered',$post);
        }
    }
    
    public function rma_postponed($id){
        $this->load->helper($this->path->receive_product_rma_engine);
        $post = $this->input->post();
        if($post!= null){
            Receive_Product_RMA_Engine::rma_submit($id,'rma_postponed',$post);
        }
    }
    
    public function rma_canceled($id){
        $this->load->helper($this->path->receive_product_rma_engine);
        $post = $this->input->post();
        if($post!= null){
            Receive_Product_RMA_Engine::rma_submit($id,'rma_canceled',$post);
        }
    }
    
    public function rma_received($id){
        $this->load->helper($this->path->receive_product_rma_engine);
        $post = $this->input->post();
        if($post!= null){
            Receive_Product_RMA_Engine::rma_submit($id,'rma_received',$post);
        }
    }
    
}

?>