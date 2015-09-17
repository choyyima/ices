<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class RMA extends MY_Controller {
    
    private $title='Return Merchandise Authorization';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        get_instance()->load->helper('rma/rma_engine');
        $this->path = RMA_Engine::path_get();
        $this->title_icon = App_Icon::rma();
        
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
        $form = $row->form_add()->form_set('title','RMA List')->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value','New RMA')
                ->button_set('icon','fa fa-plus')->button_set('href',$this->path->index.'add');
        
        $status_filter_opts = array(
            array('value'=>'','label'=>'ALL')
            ,array('value'=>'O','label'=>'OPENED')
            ,array('value'=>'C','label'=>'CLOSED')
            ,array('value'=>'X','label'=>'CANCELED')
            
        );
        
        $form->select_add()
                ->select_set('id','rma_status_filter')
                ->select_set('options_add',$status_filter_opts)
                ;
        
        $cols = array(
            array("name"=>"code","label"=>"RMA Code","data_type"=>"text","is_key"=>true)
            ,array("name"=>"supplier_name","label"=>"Supplier","data_type"=>"text")
            ,array("name"=>"rma_date","label"=>"RMA Date","data_type"=>"text")
            ,array("name"=>"rma_status_name","label"=>"Status","data_type"=>"text")
        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/rma')
                ->table_ajax_set('columns',$cols)
                ->table_ajax_set('key_column','id')
                ->filter_set(array(
                        array('id'=>'rma_status_filter','field'=>'rma_status')
                    ))
                ;        
        $js = ' $("#rma_status_filter").on("change",function(){
                    ajax_table.methods.data_show(1);
                }); 
            ';
        $app->js_set($js);
        $app->render();
        
    }
    
    public function add(){
        $this->load->helper($this->path->rma_engine);
        $post = $this->input->post();
        
        $this->view('','add');
        
    }
    
    

    
    public function view($id="",$method="view"){

        $this->load->helper($this->path->rma_engine);
        $this->load->helper($this->path->rma_renderer);
        
        $action = $method;
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont){
            if(in_array($method,array('view'))){
                if(!RMA_Engine::rma_exists($id)){
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
            RMA_Renderer::rma_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method === 'view'){
                
                $delivery_order_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#delivery_order_view_tab',"value"=>Lang::get("Delivery Order")));
                $delivery_order_view_pane = $delivery_order_tab->div_add()->div_set('id','delivery_order_view_tab')->div_set('class','tab-pane');
                RMA_Renderer::delivery_order_view_render($app,$delivery_order_view_pane,array("id"=>$id),$this->path);
                
                $receive_product_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#receive_product_view_tab',"value"=>Lang::get("Receive Product")));
                $receive_product_view_pane = $receive_product_tab->div_add()->div_set('id','receive_product_view_tab')->div_set('class','tab-pane');
                RMA_Renderer::receive_product_view_render($app,$receive_product_view_pane,array("id"=>$id),$this->path);
                
                
                $history_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#status_log_tab',"value"=>"Status Log"));
                $history_pane = $history_tab->div_add()->div_set('id','status_log_tab')->div_set('class','tab-pane');
                RMA_Renderer::rma_status_log_render($app,$history_pane,array("id"=>$id),$this->path);
            }
            
            
            $app->render();
        }
        else{
            redirect($this->path->index);
        }
        
        
    }
    
    
    public function ajax_search($method="",$submethod=""){
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $success = 1;
        $msg = [];
        switch($method){
            case 'rma':
                $db = new DB();
                $records_page = $data['records_page'];
                $page = $data['page'];
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $additional_filter = '1=1';
                $final_rs = array();
                if(isset($data['additional_filter']['rma_status'])){
                    if($data['additional_filter']['rma_status'] != '')
                        $additional_filter = 'rma_status = '
                            .$db->escape($data['additional_filter']['rma_status']);
                }
                
                $q = '
                select * from (
                    select distinct t1.*
                        ,case t1.rma_status 
                            when "O" then "OPENED"
                            when "C" then "CLOSED"
                            when "X" then "CANCELED"
                            end rma_status_name
                        ,t5.name supplier_name
                    from rma t1
                        inner join purchase_invoice_rma t3 
                            on t3.rma_id = t1.id
                        inner join purchase_invoice t4 
                            on t4.id = t3.purchase_invoice_id
                        inner join supplier t5 on t5.id = t4.supplier_id
                    where t1.status>0
                ';
                $q_group = ' )tfinal
                    ';
                $q_where=' 
                    and (t1.code like '.$lookup_str.'
                        or t5.name like '.$lookup_str.'

                    )
                    and '.$additional_filter.'
                ';

                $extra='';
                if(strlen($data['sort_by'])>0) {$extra.=' order by '.$data['sort_by'];}
                else {$extra.=' order by moddate desc';}
                $extra .= '  limit '.(($page-1)*$records_page).', '.($records_page);
                $q_total_row = $q.$q_where.$q_group;
                $q_data = $q.$q_where.$q_group.$extra;
                $total_rows = $db->select_count($q_total_row,null,null);
                $rs = $db->query_array($q_data);


                $total_rs = count($rs);

                for($i = 0;$i<$total_rs;$i++){
                    $rs[$i]['rma_status_name'] 
                            = SI::get_status_attr(
                                    $rs[$i]['rma_status_name']
                                );
                }
                $final_rs = $rs;
                $result = array("header"=>array("total_rows"=>$total_rows),"data"=>$final_rs);
                
                break;
            
            case 'input_select_reference_search':
                $db = new DB();
                $lookup_str = $db->escape('%'.$data['data'].'%');

                $q = '
                    select distinct t1.id id
                            ,t1.code
                            ,t1.grand_total
                    from purchase_invoice t1    
                        inner join purchase_invoice_receive_product t2 on t1.id = t2.purchase_invoice_id
                        inner join receive_product t3 on t3.id = t2.receive_product_id
                    where t3.receive_product_status = "R"
                        and t1.code like '.$lookup_str.'                        
                        
                ';
                $rs = $db->query_array($q);
                if(is_null($rs)){
                    $success = 0;
                    $msg[] = $db->_error_message();
                }
                else{
                    for($i = 0;$i<count($rs);$i++){
                        $rs[$i]['reference_type'] = 'purchase_invoice';
                        $rs[$i]['reference_type_name'] = 'Purchase Invoice';
                        $rs[$i]['reference_code'] = $rs[$i]['code'];
                        $rs[$i]['grand_total'] = Tools::thousand_separator($rs[$i]['grand_total'],2,true);
                        $rs[$i]['text'] = ''
                                .$rs[$i]['code']
                                .' <span class="pull-right">'
                                .' Grand Total ('.Tools::currency_get().'): <strong>'.$rs[$i]['grand_total'].'</strong>'
                                .'</span>'
                            ;

                    }
                    $result['response'] = $rs;
                }
                break;
            
            
                
                
            case 'input_select_supplier_search':
                $db = new DB();
                $q = '
                    select id id, name text
                    from supplier
                    where name like '.$db->escape('%'.$data['data'].'%').'
                ';
                $rs = $db->query_array($q);
                if(is_null($rs)){
                    $success = 0;
                    $msg[] = $db->_error_message();
                }
                else{
                    $result['response'] = $rs;
                }
                break;// end of mehod purchase_invoice
            
            
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        echo json_encode($result);
    }
    
    public function data_support($method="",$submethod=""){
        //this function only used for urgently data retrieve
        get_instance()->load->helper('rma/rma_engine');
        $path = RMA_Engine::path_get();
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[]);
        $msg=[];
        $success = 1;
        
        switch($method){
            case 'rma_current_status':
                $db = new DB();
                $q = 'select rma_status from rma where id = '.$db->escape($data['data']);
                $rs = $db->query_array_obj($q);
                if(is_null($rs)){
                    $success = 0;
                    $msg[] = $db->_error_message();
                }
                else{
                    if(count($rs)>0){
                        $result['response'] = $rs[0]->rma_status;
                    }                  
                    else
                        $result['response'] = '';
                }
                break;
            case 'rma_init_get':
                $rma_id = isset($data['data'])?$data['data']:'';
                $db = new DB();
                $q = '
                    select distinct t1.id rma_id, t1.code
                        ,case when t2.id is null then 0 else 1 end is_purchase_invoice 
                    from rma t1
                        left outer join purchase_invoice_rma t2 
                            on t1.id = t2.rma_id
                    where t1.id = '.$db->escape($rma_id).'
                ';
                $rs = $db->query_array($q);
                if(is_null($rs)){
                    $success = 0;
                    $msg[] = $db->_error_message();
                }
                else{
                    if(count($rs)>0){
                        $reference_type = '';
                        if($rs[0]['is_purchase_invoice'] === '1'){
                            $q = '
                                select t1.*
                                from purchase_invoice t1
                                    inner join purchase_invoice_rma t2 on t1.id = t2.purchase_invoice_id
                                where t2.rma_id='.$db->escape($rs[0]['rma_id']).'
                            ';
                            $rs_purchase_invoice = $db->query_array_obj($q)[0];
                            $rs[0]['reference_type'] = 'purchase_invoice';// mandatory
                            $rs[0]['id'] = $rs_purchase_invoice->id;
                            $rs[0]['text']= $rs_purchase_invoice->code;
                            $rs[0]['reference_type_name'] = 'Purchase Invoice';
                            $rs[0]['reference_code'] = $rs_purchase_invoice->code;                            
                            $rs[0]['grand_total'] = Tools::thousand_separator($rs_purchase_invoice->grand_total,2,true);
                            
                        }                         
                        
                        
                        $result['response'] = $rs[0];
                    }
                }
                break;            
            case 'purchase_invoice':   
                get_instance()->load->helper($path->rma_purchase_invoice_engine);
                switch($submethod){
                    case 'default_status_get':                       
                        $result = RMA_Purchase_Invoice_Engine::rma_purchase_invoice_status_default_status_get();
                        if(isset($result['label'])){
                            $result['label'] = SI::get_status_attr($result['label']);
                        }
                        break;
                    case 'next_allowed_status':
                        $curr_status_val = $data['data'];
                        $allowed_status = RMA_Purchase_Invoice_Engine::rma_purchase_invoice_status_next_allowed_status_get($curr_status_val);
                        $num_of_res = count($allowed_status);
                        for($i = 0;$i<$num_of_res;$i++){
                            if(Security_Engine::get_controller_permission(
                                User_Info::get()['user_id']
                                    ,'rma'
                                    ,strtolower($allowed_status[$i]['method']))){
                                    $allowed_status[$i]['label'] = SI::get_status_attr($allowed_status[$i]['label']);
                            }
                            else{
                                unset($allowed_status[$i]);
                            }
                        }
                        $result['response'] = $allowed_status;
                        break;
                    case 'received_product_available_get':
                        $db = new DB();
                        $purchase_invoice_id = $data['data'];
                        $q = '
                            select tf1.product_id, tf1.product_name
                                ,tf1.unit_id, tf1.unit_name
                                ,0 max_qty
                            from(
                                select t4.product_id, t5.name product_name
                                    ,t4.unit_id, t6.name unit_name
                                    ,sum(t4.qty) received_qty
                                from 
                                    purchase_invoice_receive_product t2 
                                    inner join receive_product t3 
                                        on t3.id = t2.receive_product_id and t3.receive_product_status = "R"
                                    inner join receive_product_product t4 
                                        on t4.receive_product_id = t3.id
                                    inner join product t5 on t4.product_id = t5.id
                                    inner join unit t6 on t4.unit_id = t6.id


                                where t2.purchase_invoice_id = '.$db->escape($purchase_invoice_id).' 
                                group by t4.product_id, t5.name ,t4.unit_id, t6.name 
                            ) tf1
                            order by tf1.product_name

                        ';
                        $rs = $db->query_array($q);
                        if(is_null($rs)){
                            $success = 0;
                            $msg[] = $db->_error_message();
                        }
                        else{
                            for($i = 0;$i<count($rs);$i++){
                                $rs[$i]['max_qty'] = RMA_Purchase_Invoice_Engine::receive_product_max_qty(
                                        $rs[$i]['product_id'],$rs[$i]['unit_id'],$purchase_invoice_id);
                                $rs[$i]['max_qty'] = Tools::thousand_separator($rs[$i]['max_qty'],2,true);
                                $filename = 'img/product/'.$rs[$i]['product_id'].'.jpg';
                                $rs[$i]['product_img'] = '<img src = "'.Tools::img_load($filename,false).'"></img>';
                            }
                            $result['response'] = $rs;
                        }
                        break;    
                    case 'purchase_invoice_detail_get':
                        $db = new DB();
                        $q = '
                            select distinct t1.grand_total
                                ,date_format(t1.purchase_invoice_date,"%Y-%m-%d") purchase_invoice_date
                                ,date_format(t1.purchase_invoice_date,"%H:%i") purchase_invoice_time 
                                ,t2.id supplier_id
                                ,t2.name supplier_name
                            from purchase_invoice t1
                                inner join supplier t2 on t1.supplier_id = t2.id
                            where t1.id = '.$db->escape($data['data']);
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
                            else
                                $result['response'] = array();
                        }                       
                        
                        break;
                        
                    case 'rma_get':
                        $db = new DB();

                        $q = '
                            select distinct t1.code, t1.rma_status, t1.rma_date
                                ,t4.id purchase_invoice_id, t4.code purchase_invoice_code
                                ,t1.rma_status 
                                ,t1.notes    
                                ,t1.cancellation_reason
                                ,t1.store_id
                                ,t5.name store_name
                                ,t7.id supplier_id
                                ,t7.name supplier_name
                            from rma t1
                                inner join purchase_invoice_rma t3 on  t3.rma_id = t1.id
                                inner join purchase_invoice t4 on t4.id =  t3.purchase_invoice_id
                                inner join store t5 on t5.id = t1.store_id
                                inner join rma_supplier t6 on t1.id = t6.rma_id
                                inner join supplier t7 on t7.id = t6.supplier_id
                            where t1.id = '.$db->escape($data['data']).'
                        ';
                        $rs = $db->query_array($q);
                        if(is_null($rs)){
                            $success = 0;
                            $msg[] = $db->_error_message();
                        }
                        else{
                            if(count($rs)>0){
                                get_instance()->load->helper('rma/rma_purchase_invoice_engine');
                                $status_list = RMA_Purchase_Invoice_Engine::rma_purchase_invoice_status_list_get();
                                for($i = 0;$i<count($status_list);$i++){
                                    if($status_list[$i]['val'] === $rs[0]['rma_status']){
                                        $rs[0]['rma_status_name'] = 
                                                SI::get_status_attr($status_list[$i]['label']);
                                    }
                                }
                                $result = $rs[0];
                            }
                        }

                        break;
                        
                    case 'rma_product_get':
                        $db = new DB();
                        $q = '
                            select t1.product_id, t2.name product_name, t1.unit_id, t3.name unit_name, t1.qty
                            from rma_product t1
                                inner join product t2 on t1.product_id = t2.id
                                inner join unit t3 on t1.unit_id = t3.id
                            where t1.rma_id = '.$data['data'].'
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
        get_instance()->load->helper($this->path->rma_print);
        switch($method){
            case 'rma':
                RMA_Print::print_rma($id);
                break;
        }
    }
    
    public function purchase_invoice_add(){
        $this->load->helper($this->path->rma_purchase_invoice_engine);
        $post = $this->input->post();
        if($post!= null){
            RMA_Purchase_Invoice_Engine::purchase_invoice_submit('','purchase_invoice_add',$post);
        }
    }
    
    public function purchase_invoice_opened($id){
        $this->load->helper($this->path->rma_purchase_invoice_engine);
        $post = $this->input->post();
        if($post!= null){
            RMA_Purchase_Invoice_Engine::purchase_invoice_submit($id,'purchase_invoice_opened',$post);
        }
    }
    
    public function purchase_invoice_closed($id){
        $this->load->helper($this->path->rma_purchase_invoice_engine);
        $post = $this->input->post();
        if($post!= null){
            RMA_Purchase_Invoice_Engine::purchase_invoice_submit($id,'purchase_invoice_closed',$post);
        }
    }
    
    
    public function purchase_invoice_canceled($id){
        $this->load->helper($this->path->rma_purchase_invoice_engine);
        $post = $this->input->post();
        if($post!= null){
            RMA_Purchase_Invoice_Engine::purchase_invoice_submit($id,'purchase_invoice_canceled',$post);
        }
    }
    
}

?>