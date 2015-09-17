<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sales_Order extends MY_Controller {
        

    private $title='Sales Order';
    private $title_icon = 'fa fa-columns';
    private $path = array(
        'index'=>''
        ,'sales_order_engine'=>''
        ,'sales_movement_engine'=>''
        ,'ajax_search'=>''
        
    );
    
    function __construct(){
        parent::__construct();
        $this->path = json_decode(json_encode($this->path));
        $this->path->index=  get_instance()->config->base_url().'sales_order/';
        $this->path->sales_order_engine=  'transaction/sales/sales_order_engine';
        $this->path->sales_movement_engine=  'transaction/sales/sales_movement_engine';
        $this->path->ajax_search=  $this->path->index.'ajax_search/';
        
    }
    public function add(){
        $this->load->helper($this->path->sales_order_engine);
        $post = $this->input->post();
        if($post != null){
            $post = json_decode($post,TRUE);
            $data = $post;
            $data['so']['id'] = "";
            $result = Sales_Order_Engine::save($data);
            echo json_encode($result);
            die();
        }
        $this->view('','add');
    }

    public function edit($id=""){
        if(strlen($id)>0){ 
            $this->load->helper($this->path->sales_order_engine);
            $post = $this->input->post();
            if($post != null){
                $post = json_decode($post,TRUE);
                $data = $post;
                $data['so']['id'] = $id;
                $result = Sales_Order_Engine::save($data);
                echo json_encode($result);
                die();
            }
            $this->view($id,'edit');
        }
    }

    public function delete($id=""){
        
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
        $form = $row->form_add()->form_set('title','Sales List')->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value','New Sales Order')
                ->button_set('icon','fa fa-plus')->button_set('href',get_instance()->config->base_url().'sales_order/add');
        
        $status_filter_opts = array(
            array('value'=>'O','label'=>'OPENED')
            ,array('value'=>'I','label'=>'INVOICED')
            ,array('value'=>'D','label'=>'DELIVERED')
            ,array('value'=>'X','label'=>'CANCELED')
        );
        
        $form->select_add()
                ->select_set('id','sales_order_status_filter')
                //->select_set('label','Movement Status')
                ->select_set('options_add',$status_filter_opts)
                ;
        
        $cols = array(
            array("name"=>"code","label"=>"Code","data_type"=>"text","is_key"=>true)
            ,array("name"=>"customer_name","label"=>"Customer","data_type"=>"text")
            ,array("name"=>"date","label"=>"Date","data_type"=>"text")
            ,array("name"=>"sales_order_status","label"=>"Status","data_type"=>"text")
            ,array("name"=>"total","label"=>"Total","data_type"=>"text")           

        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
                ->table_ajax_set('base_href',$this->path->index.'view')
                ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/sales_order')
                ->table_ajax_set('columns',$cols)
                ->filter_set(array(array('id'=>'sales_order_status_filter','field'=>'sales_order_status')))
                ;        
        $js = ' $("#sales_order_status_filter").on("change",function(){
                    ajax_table.methods.data_show(1);
                }) 
                
                
                
            ';
        $app->js_set($js);
        $app->render();
        
    }
    
    public function view($id = "",$method = "view"){
        $this->load->helper($this->path->sales_order_engine);
        $this->load->helper($this->path->sales_movement_engine);
        $action = "";
        if(strlen($id)>0){
            if(Sales_Order_Engine::get($id) == null){
                Message::set('error',array("Data doesn't exist"));
                redirect($this->path->index);
            }
        }
        
        $data = array(
            'id'=>$id
        );
        
        $app = new App();            
        $db = $this->db;

        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower($this->title));
        $app->set_content_header($this->title,$this->title_icon,$action);
        $row = $app->engine->div_add()->div_set('class','row');            
        $init_state = true;       
        
        
        $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();
        
        $order_tab = $nav_tab->nav_tab_set('items_add'
                ,array("id"=>'#order_tab',"value"=>"Order",'class'=>'active'));
        $order_pane = $order_tab->div_add()->div_set('id','order_tab')->div_set('class','tab-pane active');
        
        if($method=='view'){
            $movement_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#movement_tab',"value"=>"Movement"));
            $movement_pane = $movement_tab->div_add()->div_set('id','movement_tab')->div_set('class','tab-pane');

            $invoice_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#invoice_tab',"value"=>"Invoice"));
            $invoice_pane = $invoice_tab->div_add()->div_set('id','invoice_tab')->div_set('class','tab-pane');

            $receipt_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#receipt_tab',"value"=>"Receipt"));
            $receipt_pane = $receipt_tab->div_add()->div_set('id','receipt_tab')->div_set('class','tab-pane');
            
            //Sales_Movement_Engine::movement_render($app,$movement_pane,array("id"=>$id),$this->path,$method);
        }
        
        Sales_Order_Engine::order_render($app,$order_pane,array("id"=>$id),$this->path,$method);

        $app->render();
        
    }
    
    
    public function ajax_search($method){
        $data = json_decode($this->input->post(), true);
        $result =array();
        switch($method){
            case 'sales_order':
                $db = new DB();
                $records_page = $data['records_page'];
                $page = $data['page'];
                $lookup_str = $db->escape('%'.$data['data'].'%');

                $additional_filter = 'sales_order_status = '.$db->escape($data['additional_filter']['sales_order_status']);
                $q = '
                    select t1.id,t1.code,t1.date
                    ,case sales_order_status 
                        when "X" then "CANCELED" 
                            WHEN "O" then "OPENED" 
                            WHEN "D" then "DELIVERED"
                        end sales_order_status
                    ,format(sum(t2.price * t2.qty),2) total
                    ,t3.name customer_name
                    from sales_order t1
                    inner join sales_order_detail t2 on t1.id = t2.sales_order_id
                    inner join customer t3 on t3.id = t1.customer_id
                    where t1.status>0
                ';
                $q_group = ' group by t1.id, t1.code, t1.date, sales_order_status
                    ,case sales_order_status when "X" then "CANCELED" WHEN "O" then "OPENED" end 
                    ';
                $q_where=' 
                    and (t1.code like '.$lookup_str.'
                        or t1.date like '.$lookup_str.'
                        or t1.sales_order_status like '.$lookup_str.'
                        or t3.name like '.$lookup_str.'
                    )
                    and '.$additional_filter.'
                ';
                
                $extra='';
                if(strlen($data['sort_by'])>0) {$extra.=' order by '.$data['sort_by'];}
                else {$extra.=' order by t1.code desc';}
                $extra .= '  limit '.(($page-1)*$records_page).', '.($records_page);
                $q_total_row = $q.$q_where.$q_group;
                $q_data = $q.$q_where.$q_group.$extra;
                $total_rows = $db->select_count($q_total_row,null,null);
                $result = array("header"=>array("total_rows"=>$total_rows),"data"=>$db->query_array($q_data));
                
                break;
            case 'so_item':
                $db= new DB();
                $exceptions = '';
                $q = '
                    select distinct t1.id id, t1.name text 
                    from item t1
                    inner join item_unit t2 on t1.id = t2.item_id
                    where t1.status>0 
                        and( 
                            t1.name like '.$db->escape('%'.$data['data'].'%').'
                            or t1.code like '.$db->escape('%'.$data['data'].'%').'
                        )
                    order by t1.name
                    limit 100
                    ';
                $result = $db->query_array($q);
                break;
            case 'so_customer_detail':
                $db = new DB();
                $q= '
                    select t1.id,t1.address
                        ,t1.phone,t1.phone2,t1.phone3,t1.phone4,t1.phone5
                        ,t1.city,group_concat(t3.name SEPARATOR ", ") customer_type_name
                    from customer t1
                        left outer join customer_customer_type t2 on t1.id = t2.customer_id
                        left outer join customer_type t3 on t2.customer_type_id = t3.id
                        
                    where t1.id = '.$db->escape($data['data']).'
                    group by t1.id, t1.phone,t1.phone2,t1.phone3,t1.phone4,t1.phone5,t1.address, t1.city
                ';
                $result = $db->query_array($q);
                
                for($i = 0;$i<count($result);$i++){
                    if(strlen(str_replace(' ','',$result[$i]['phone2']))>0){                        
                        $result[$i]['phone'] .= ', '.$result[$i]['phone2'];
                    }
                    if(strlen(str_replace(' ','',$result[$i]['phone3']))>0){                        
                        $result[$i]['phone'] .= ', '.$result[$i]['phone3'];
                    }
                    if(strlen(str_replace(' ','',$result[$i]['phone4']))>0){                        
                        $result[$i]['phone'] .= ', '.$result[$i]['phone4'];
                    }
                    if(strlen(str_replace(' ','',$result[$i]['phone5']))>0){                        
                        $result[$i]['phone'] .= ', '.$result[$i]['phone5'];
                    }
                }
                
                break;
            case 'so_item_detail':
                $db = new DB();
                $q= '
                    select t1.id item_id, t1.name item_name 
                    from item t1
                    where t1.id = '.$db->escape($data['data']).'
                ';
                $result = $db->query_array($q);
                break;            
            case 'so_item_unit_detail':
                $db = new DB();
                $q= '
                    select t3.id unit_id, t3.name unit_name
                    from item t1
                        inner join item_unit t2 on t1.id = t2.item_id
                        inner join unit t3 on t3.id = t2.unit_id
                    where t3.status>0 and t1.id = '.$db->escape($data['data']).'
                ';
                $result = $db->query_array($q);
                break;            
            case 'so_customer_item_price_list':
                $db = new DB();
                $customer_id = $data['customer_id'];
                $item_id = $data['item_id'];
                $unit_id = $data['unit_id'];
                $q = '
                    select distinct t1.id item_price_list_id, t1.name item_price_list_name
                        , t2.price_from, t2.price_to
                    from item_price_list t1
                        inner join item_price_list_detail t2 on t1.id = t2.item_price_list_id
                        inner join customer_type t3 on t1.customer_type_id = t3.id
                        inner join customer_customer_type t4 on t4.customer_type_id = t3.id
                        inner join customer t5 on t5.id = t4.customer_id
                        inner join item t6 on t6.id = t2.item_id
                        inner join unit t7 on t7.id = t2.unit_id
                    where t5.id = '.$db->escape($customer_id).' 
                        and t2.unit_id = '.$db->escape($unit_id).'
                        and t2.item_id = '.$db->escape($item_id).'
                ';
                $result = $db->query_array($q);
                break;
            case 'so_customer':
                $db = new DB();
                $q = '
                    select distinct t1.id id, concat(t1.code,", ",t1.name) text 
                    from customer t1                   
                    where t1.status>0 
                        and( 
                            t1.name like '.$db->escape('%'.$data['data'].'%').'
                            or t1.code like '.$db->escape('%'.$data['data'].'%').'
                            or replace(t1.phone,"-","") like '.$db->escape('%'.$data['data'].'%').'
                            or replace(t1.phone2,"-","") like '.$db->escape('%'.$data['data'].'%').'
                            or replace(t1.phone3,"-","") like '.$db->escape('%'.$data['data'].'%').'
                        )
                    order by t1.name
                    limit 100
                ';
                $result = $db->query_array($q);
                break;
            
            case 'so_approval':
                $db = new DB();
                $q = '
                    select t1.id id, concat(t1.code,", ",t1.name,", ",t1.notes) text 
                    from approval t1
                        inner join approval_type t2 on t1.approval_type_id = t2.id
                    where t2.code = "SO" 
                        and ( t1.code like '.$db->escape('%'.$data['data'].'%').'
                              or t1.name like '.$db->escape('%'.$data['data'].'%').'
                              or t1.notes like '.$db->escape('%'.$data['data'].'%').'
                            )
                        and t1.used = 0
                        and t1.due_date > now()
                ';
                $result = $db->query_array($q);
                break;
            
            case 'movement_so':
                $db = new DB();
                $q = '
                    select * from sales_order where id = '.$db->escape($data['data']).'
                ';
                $result = $db->query_array($q);
                break;
            
            case 'movement_so_detail':
                $db = new DB();
                $q = '
                    select 
                        t2.id item_id
                        , t2.name item_name, t3.id unit_id, t3.name unit_name
                        , format(t1.qty,2) ordered_qty
                        , format(t1.price,2) price
                        ,format(t1.qty * t1.price,2) sub_total
                        ,COALESCE(format(t4.sent_qty,2),0) sent_qty
                        ,format(t1.qty - COALESCE(t4.sent_qty,0),2) qty
                        ,format(t1.qty - COALESCE(t4.sent_qty,0),2) available_qty
                    from sales_order_detail t1
                        inner join item t2 on t1.item_id = t2.id
                        inner join unit t3 on t3.id = t1.unit_id
                        left outer join (
                                select tt4.id so_id, tt2.item_id, tt2.unit_id, sum(tt2.qty) sent_qty
                                from movement tt1
                                        inner join movement_detail tt2 on tt1.id = tt2.movement_id
                                        inner join sales_order_movement tt3 on tt3.movement_id = tt1.id
                                        inner join sales_order tt4 on tt4.id = tt3.sales_order_id
                                where  tt1.movement_status != "X"
                                group by tt2.item_id, tt2.unit_id, tt4.id
                        ) t4 on t4.item_id = t1.item_id and t4.unit_id = t1.unit_id and t4.so_id = t1.sales_order_id
                    where t1.sales_order_id = '.$db->escape($data['data']).'
                ';
                $result = $db->query_array($q);
                break;
            
            case 'movement_movement':
                $db = new DB();
                $q = '
                    select t1.*, t2.id warehouse_id, t2.name warehouse_name
                        ,case t1.movement_status 
                            when "X" then "CANCELLED"
                            when "D" then "DELIVERED"
                        end movement_status_name
                    from movement  t1
                    left outer join warehouse t2 on t1.movement_to_warehouse_id = t2.id
                    where t1.id = '.$db->escape($data['data']).'
                ';
                $result = $db->query_array($q);
                break;
            
            case 'movement_movement_detail':
                $db = new DB();
                $q = '
                    select t1.item_id, t1.unit_id 
                        ,t2.name item_name
                        ,t3.name unit_name
                        ,format(t1.qty,2) qty
                    from movement_detail t1
                        inner join item t2 on t1.item_id = t2.id
                        inner join unit t3 on t3.id = t1.unit_id
                        inner join movement t4 on t4.id = t1.movement_id
                    where t4.id = '.$db->escape($data['data']).'
                        
                ';
                $result = $db->query_array($q);
                break;
            
        }
        
        echo json_encode($result);
    }
}

