<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product extends MY_Controller {
        

    private $title='';
    private $title_icon = '';
    private $path = array(
        'index'=>''
        ,'product_engine'=>''
        ,'product_subcategory_engine'=>''
        ,'ajax_search'=>''
        ,'product_js'=>''
    );
    
    function __construct(){
        parent::__construct();
        get_instance()->load->helper('product/product_engine');
        $this->title = Lang::get('Product');
        $this->path = Product_Engine::path_get();
        $this->title_icon = APP_ICON::product();
        
    }
    
    public function dashboard_product_buffer_stock_watcher(){
        //<editor-fold defaultstate="collapsed">
        $data = json_decode($this->input->post(), true);
        $result = null;
        $db = new DB();
        $q = '
            select t1.product_id, t1.unit_id, t4.code product_name, t5.code unit_name
                , t3.qty product_stock_qty
                ,t1.qty buffer_stock_qty
                ,t1.qty - t3.qty product_qty_difference
            from product_buffer_stock t1
                inner join (
                    select t31.id product_id, t33.id unit_id,coalesce(sum(t34.qty),0) qty
                    from product t31
                        inner join product_unit t32 on t31.id = t32.product_id
                        inner join unit t33 on t32.unit_id = t33.id
                        left outer join product_stock_sales_available t34 
                            on t31.id = t34.product_id 
                                and t33.id = t34.unit_id
                                and t34.status>0
                    where t31.status>0
                    group by t31.id, t33.id
                ) t3 on t3.product_id = t1.product_id and t1.unit_id = t3.unit_id
                inner join product t4 on t4.id = t1.product_id
                inner join unit t5 on t5.id = t1.unit_id
            where t1.qty - t3.qty >0
                and t4.status>0
                and t4.product_status = "active"
                and t5.status>0
            order by t4.code
            limit 100
        ';
        $rs = $db->query_array($q);
        for ($i = 0;$i<count($rs);$i++){
            $rs[$i]['row_num'] = $i+1;
            $rs[$i]['product_stock_qty'] = Tools::thousand_separator($rs[$i]['product_stock_qty'],2,true);
            $rs[$i]['buffer_stock_qty'] = Tools::thousand_separator($rs[$i]['buffer_stock_qty'],2,true);
            $rs[$i]['product_qty_difference'] = Tools::thousand_separator($rs[$i]['product_qty_difference'],2,true);
        }
        $result = $rs;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function add(){
        $this->view('','add');
    }

    public function index()
    {           
        //<editor-fold defaultstate="collapsed">
        $action = "";

        $app = new App();            
        $db = $this->db;

        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower('product'));
        $app->set_content_header($this->title,$this->title_icon,$action);

        $row = $app->engine->div_add()->div_set('class','row');            
        $form = $row->form_add()->form_set('title',Lang::get(array('Product','List')))->form_set('span','12');
        $form->form_group_add()->button_add()->button_set('class','primary')->button_set('value',Lang::get(array('New','Product')))
                ->button_set('icon','fa fa-plus')->button_set('href',get_instance()->config->base_url().'product/add');

        $status_list = array(
            array('value'=>'','label'=>'ALL')
        );
        
        $status_list_temp = SI::status_list_get('Product_Engine');
        foreach($status_list_temp as $idx=>$status){
            $status_list[] = array('value'=>$status['val'],'label'=>$status['label']);
        }
        
        
        $form->select_add()
                ->select_set('id','status_filter')
                ->select_set('options_add',$status_list)
                ;
        
        $cols = array(
            array("name"=>"code","label"=>"Code","data_type"=>"text","is_key"=>true)
            ,array("name"=>"name","label"=>"Name","data_type"=>"text")
            ,array("name"=>"subcategory","label"=>"SubCategory","data_type"=>"text")
            ,array("name"=>"unit","label"=>"Unit","data_type"=>"text")
            ,array("name"=>"product_status","label"=>"Status ","data_type"=>"text")

        );
        
        $tbl = $form->table_ajax_add();
        $tbl->table_ajax_set('id','ajax_table')
            ->table_ajax_set('base_href',$this->path->index.'view')
            ->table_ajax_set('lookup_url',$this->path->index.'ajax_search/product')
            //->table_ajax_set('controls',$controls)
            ->table_ajax_set('columns',$cols)
            ->filter_set(array(
                        array('id'=>'status_filter','field'=>'product_status')
                    ))    
            ;
        
        $js = ' $("#status_filter").on("change",function(){
                    ajax_table.methods.data_show(1);
                }); 
                $("#status_filter").val("active").change();
            ';
        $app->js_set($js);
        
        $app->render();
        //</editor-fold>
    }
    
    public function view($id = "",$method = 'view'){
        //<editor-fold defaultstate="collapsed">
        $this->load->helper($this->path->product_engine);
        $this->load->helper($this->path->product_renderer);
        $cont = true;
        
        if(!in_array($method,array('add','view'))){
            Message::set('error',array("Method error"));
            $cont = false;
        }
        
        if($cont && $method === 'view'){
            if(Product_Engine::get($id) == null){
                Message::set('error',array("Data doesn't exist"));
                redirect($this->path->index);
            }
        }
        
        if($cont){
        
            if($method=='add') $id = '';
            $data = array(
                'id'=>$id
            );
            
            $app = new App();            
            $db = $this->db;

            $app->set_title($this->title);
            $app->set_breadcrumb($this->title,strtolower($this->title));
            $app->set_content_header($this->title,$this->title_icon,$method);
            $row = $app->engine->div_add()->div_set('class','row')->div_set('id','product');                        

            $nav_tab = $row->div_add()->div_set("span","12")->nav_tab_add();

            $detail_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#detail_tab',"value"=>"Detail",'class'=>'active'));
            $detail_pane = $detail_tab->div_add()->div_set('id','detail_tab')->div_set('class','tab-pane active');        
            Product_Renderer::product_render($app,$detail_pane,array("id"=>$id),$this->path,$method);
            if($method  === 'view'){

                $unit_conversion_tab = $nav_tab->nav_tab_set('items_add'
                        ,array("id"=>'#product_unit_conversion',"value"=>"Unit Conversion",'class'=>''));
                $unit_conversion_pane = $unit_conversion_tab->div_add()->div_set('id','product_unit_conversion')->div_set('class','tab-pane');        
                Product_Engine::unit_conversion_render($app,$unit_conversion_pane,array("id"=>$id));

                $history_tab = $nav_tab->nav_tab_set('items_add'
                        ,array("id"=>'#stock_history',"value"=>"Stock History",'class'=>''));
                $history_pane = $history_tab->div_add()->div_set('id','stock_history')->div_set('class','tab-pane');        
                Product_Engine::stock_history_render($app,$history_pane,array("id"=>$id),$this->path);

                $ssah_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#ssah',"value"=>"Stock Sales Available History",'class'=>''));
                $ssah_pane = $ssah_tab->div_add()->div_set('id','ssah')->div_set('class','tab-pane');
                Product_Engine::stock_sales_available_history_render($app,$ssah_pane,array("id"=>$id),$this->path);

                $sbh_tab = $nav_tab->nav_tab_set('items_add'
                    ,array("id"=>'#sbh',"value"=>"Stock Bad History",'class'=>''));
                $sbh_pane = $sbh_tab->div_add()->div_set('id','sbh')->div_set('class','tab-pane');
                Product_Engine::stock_bad_history_render($app,$sbh_pane,array("id"=>$id),$this->path);
            }
            $app->render();       
        }
        //</editor-fold>
    }
        
    public function ajax_search($method){
        //<editor-fold defaultstate="collapsed">
        $data = json_decode($this->input->post(), true);
        $result =array('response'=>array());
        $response = array();
        switch($method){
            case 'product_buffer_stock_qty_get':
                $db = new DB();
                $q = '
                    select t1.*
                    from product_buffer_stock t1
                        inner join product t2 on t1.product_id = t2.id
                    where t2.id = '.$db->escape($data['data']).'
                ';
                $result = $db->query_array($q);
                
                break;
            case 'product_sales_multiplication_qty_get':
                $db = new DB();
                $q = '
                    select t1.*
                    from product_sales_multiplication_qty t1
                    where t1.product_id = '.$db->escape($data['data']).'
                ';
                $result = $db->query_array($q);
                
                break;
            case 'product_subcategory':
                $db= new DB();
                $q = '
                    select id id, name, code 
                    from product_subcategory 
                    where status>0 
                        and( 
                            name like '.$db->escape('%'.$data['data'].'%').'
                            or code like '.$db->escape('%'.$data['data'].'%').'
                        )
                    ';
                $result = $db->query_array($q);
                for($i = 0;$i<count($result);$i++){
                    $result[$i]['text'] = SI::html_tag('strong',$result[$i]['code']).' '.$result[$i]['name'];
                }
                break;
            case 'product':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $records_page = $data['records_page'];
                $page = $data['page'];
                $lookup_str = $db->escape('%'.$data['data'].'%');
                $product_status = isset($data['additional_filter']['product_status'])?
                        Tools::_str($data['additional_filter']['product_status']):'';
                $q = '
                    select distinct t1.id,t1.name,t1.code,t1.notes, coalesce(t2.code,"") subcategory, group_concat(t4.name SEPARATOR ", ") unit
                        ,t1.product_status

                    from product t1
                    left outer join product_subcategory t2 on t1.product_subcategory_id = t2.id
                    left outer join product_unit t3 on t1.id = t3.product_id
                    left outer join unit t4 on t4.id = t3.unit_id
                    where t1.status>0 
                ';
                $q_group = ' group by t1.id,t1.name,t1.code,t1.notes, t2.name ';
                $q_where=' 
                        and t1.product_status = '.($product_status!==''?$db->escape($product_status):'t1.product_status').'
                        and (t1.name like '.$lookup_str.' 
                        or t1.code like '.$lookup_str.' 
                        or t1.notes like '.$lookup_str.' 
                        or t1.id in (
                                select tt1.id
                                from product tt1
                                inner join product_unit tt3 on tt1.id = tt3.product_id
                                inner join unit tt4 on tt4.id = tt3.unit_id
                                where tt1.status>0
                                and tt4.name like '.$lookup_str.'
                            )
                        )';
                
                $extra='';
                if(strlen($data['sort_by'])>0) {$extra.=' order by '.$data['sort_by'];}
                else {$extra.=' order by t1.code asc';}
                $extra .= '  limit '.(($page-1)*$records_page).', '.($records_page);
                $q_total_row = $q.$q_where.$q_group;
                $q_data = $q.$q_where.$q_group.$extra;
                $total_rows = $db->select_count($q_total_row,null,null);
                $result = array("header"=>array("total_rows"=>$total_rows),"data"=>$db->query_array($q_data));
                
                for($i = 0;$i<count($result['data']);$i++){
                    $result['data'][$i]['product_status'] = SI::get_status_attr(                            
                        SI::status_get('Product_Engine',
                            $result['data'][$i]['product_status'])['label']);
                }
                //</editor-fold>
                break;
                
            case 'product_stock_history':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = $db->escape('%' . $data['data'] . '%');
                $config = array(
                    'additional_filter'=>array(
                        array('key'=>'product_id','query'=>'and t1.product_id = '),
                        array('key'=>'warehouse_id','query'=>'and t1.warehouse_id = '),
                    ),
                    'query' => array(
                        'basic' => '
                            select * from (
                                select t3.id, t2.id warehouse_id, t1.id product_id, t2.name warehouse_name
                                    ,t3.moddate
                                    , t4.name product_name, t4.code product_code
                                    ,t5.name unit_name
                                    ,t3.qty qty, t1.unit_id unit_id, t3.desc description
                                    ,t3.stock_qty_old, t3.stock_qty_new

                                from product_stock t1
                                    inner join warehouse t2 on t1.warehouse_id = t2.id
                                    inner join product_stock_history t3 on t1.id = t3.product_stock_id
                                    inner join product t4 on t1.product_id = t4.id
                                    inner join unit t5 on t5.id = t1.unit_id
                                where 1 = 1
                        ',
                        'where' => '
                            and (t3.qty like '.$lookup_str.'
                                or t3.moddate like '.$lookup_str.'
                            )
                        ',
                        'group' => '
                            )tfinal
                        ',
                        'order' => 'order by moddate desc, id desc'
                    ),
                );

                $temp_result = SI::form_data()->ajax_table_search($config, $data);
                for ($i = 0; $i < count($temp_result['data']); $i++) {
                    $temp_result['data'][$i]['qty'] = Tools::thousand_separator($temp_result['data'][$i]['qty']);
                    $temp_result['data'][$i]['stock_qty_old'] = Tools::thousand_separator($temp_result['data'][$i]['stock_qty_old']);
                    $temp_result['data'][$i]['stock_qty_new'] = Tools::thousand_separator($temp_result['data'][$i]['stock_qty_new']);
                }
                $result = $temp_result;
                //</editor-fold>
                break;
            
            case 'product_stock_sales_available_history':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = $db->escape('%' . $data['data'] . '%');
                $config = array(
                    'additional_filter'=>array(
                        array('key'=>'product_id','query'=>'and t1.product_id = '),
                        array('key'=>'warehouse_id','query'=>'and t1.warehouse_id = '),
                    ),
                    'query' => array(
                        'basic' => '
                            select * from (
                                select t3.id, t2.id warehouse_id, t1.id product_id, t2.name warehouse_name
                                    ,t3.moddate
                                    , t4.name product_name, t4.code product_code
                                    ,t5.name unit_name
                                    ,t3.qty qty, t1.unit_id unit_id, t3.desc description
                                    ,t3.stock_qty_old, t3.stock_qty_new

                                from product_stock_sales_available t1
                                    inner join warehouse t2 on t1.warehouse_id = t2.id
                                    inner join product_stock_sales_available_history t3 on t1.id = t3.product_stock_id
                                    inner join product t4 on t1.product_id = t4.id
                                    inner join unit t5 on t5.id = t1.unit_id
                                where 1 = 1
                        ',
                        'where' => '
                            and (t3.qty like '.$lookup_str.'
                                or t3.moddate like '.$lookup_str.'
                            )
                        ',
                        'group' => '
                            )tfinal
                        ',
                        'order' => 'order by moddate desc, id desc'
                    ),
                );

                $temp_result = SI::form_data()->ajax_table_search($config, $data);
                for ($i = 0; $i < count($temp_result['data']); $i++) {
                    $temp_result['data'][$i]['qty'] = Tools::thousand_separator($temp_result['data'][$i]['qty']);
                    $temp_result['data'][$i]['stock_qty_old'] = Tools::thousand_separator($temp_result['data'][$i]['stock_qty_old']);
                    $temp_result['data'][$i]['stock_qty_new'] = Tools::thousand_separator($temp_result['data'][$i]['stock_qty_new']);
                }
                $result = $temp_result;
                //</editor-fold>
                break;
                
            case 'product_stock_bad_history':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $lookup_str = $db->escape('%' . $data['data'] . '%');
                $config = array(
                    'additional_filter'=>array(
                        array('key'=>'product_id','query'=>'and t1.product_id = '),
                        array('key'=>'warehouse_id','query'=>'and t1.warehouse_id = '),
                    ),
                    'query' => array(
                        'basic' => '
                            select * from (
                                select t3.id, t2.id warehouse_id, t1.id product_id, t2.name warehouse_name
                                    ,t3.moddate
                                    , t4.name product_name, t4.code product_code
                                    ,t5.name unit_name
                                    ,t3.qty qty, t1.unit_id unit_id, t3.desc description
                                    ,t3.stock_qty_old, t3.stock_qty_new

                                from product_stock_bad t1
                                    inner join warehouse t2 on t1.warehouse_id = t2.id
                                    inner join product_stock_bad_history t3 on t1.id = t3.product_stock_id
                                    inner join product t4 on t1.product_id = t4.id
                                    inner join unit t5 on t5.id = t1.unit_id
                                where 1 = 1
                        ',
                        'where' => '
                            and (t3.qty like '.$lookup_str.'
                                or t3.moddate like '.$lookup_str.'
                            )
                        ',
                        'group' => '
                            )tfinal
                        ',
                        'order' => 'order by moddate desc, id desc'
                    ),
                );

                $temp_result = SI::form_data()->ajax_table_search($config, $data);
                for ($i = 0; $i < count($temp_result['data']); $i++) {
                    $temp_result['data'][$i]['qty'] = Tools::thousand_separator($temp_result['data'][$i]['qty']);
                    $temp_result['data'][$i]['stock_qty_old'] = Tools::thousand_separator($temp_result['data'][$i]['stock_qty_old']);
                    $temp_result['data'][$i]['stock_qty_new'] = Tools::thousand_separator($temp_result['data'][$i]['stock_qty_new']);
                }
                $result = $temp_result;
                //</editor-fold>
                break;
                
            case 'unit':
                //<editor-fold defaultstate="collapsed">
                $db= new DB();
                $q = '
                    select id id, name text 
                    from unit 
                    where status>0 
                        and( 
                            name like '.$db->escape('%'.$data['data'].'%').'
                            or code like '.$db->escape('%'.$data['data'].'%').'
                        )
                    ';
                $result = $db->query_array($q);
                //</editor-fold>
                break;
            case 'unit_id':
                //<editor-fold defaultstate="collapsed">
                $db= new DB();
                $q = '
                    select *,0 buffer_stock_qty, 1 product_sales_multiplication_qty 
                    from unit 
                    where status>0 
                        and( 
                            id = '.$data['data'].'
                        )
                    ';
                $result = $db->query_array($q);
                //</editor-fold>
                break;
            case 'input_select_expedition_search':
                //<editor-fold defaultstate="collapsed">
                $db = new DB();
                $response = array();
                $lookup_str = isset($data['data'])?$data['data']:'';
                $lookup_str = $db->escape('%'.$lookup_str.'%');
                $q = '
                    select *
                    from expedition t1
                    where (
                        t1.code like '.$lookup_str.'
                        or t1.name like '.$lookup_str.'
                    )
                    limit '.$db->row_limit.'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    for($i = 0;$i<count($rs);$i++){
                        $response[] = array(
                            'id'=>$rs[$i]['id']
                            , 'text'=>SI::html_tag('strong',$rs[$i]['code']).' '.$rs[$i]['name']
                        );
                    }
                }
                $result['response'] = $response;                
                //</editor-fold>
                break;
            case 'input_select_child_product_search':
                get_instance()->load->helper('product/product_data_support');
                $response = array();
                $lookup_str = isset($data['data'])?Tools::_str($data['data']):'';
                $trs = Product_Data_Support::registered_product_search($lookup_str,array('product_status'=>'active'));
                foreach($trs as $i=>$row){
                    $trs[$i]['id'] = $row['product_id'];
                    $trs[$i]['text'] = SI::html_tag('strong',$row['product_code'])
                        .' '.$row['product_name'];
                    foreach($row['unit'] as $i2=>$row2){
                        $trs[$i]['unit'][$i2]['id'] = $row2['unit_id'];
                        $trs[$i]['unit'][$i2]['text'] = SI::html_tag('strong',$row2['unit_code'])
                        .' '.$row2['unit_name'];
                    }
                }
                $response = $trs;
                $result['response'] = $response;
                break;

        }
        
        
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function data_support($method="",$submethod=""){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('product/product_data_support');
        $data = json_decode($this->input->post(), true);
        $result =array('success'=>1,'msg'=>[],'response'=>array());
        $msg=[];
        $success = 1;
        $response = array(); 
        switch($method){
            case 'product_get':
                $response = array();
                
                $db = new DB();
                $product_id = isset($data['data'])?Tools::_str($data['data']):'';
                $q = '
                    select t1.*, 
                        t2.id product_subcategory_id,
                        t2.code product_subcategory_code,
                        t2.name product_subcategory_name
                    from product t1
                        left outer join product_subcategory t2 on t1.product_subcategory_id = t2.id
                    where t1.id = '.$db->escape($product_id).'
                ';
                $rs = $db->query_array($q);
                if(count($rs)>0){
                    get_instance()->load->helper('product/product_engine');
                    $product_db = $rs[0];
                    $product = array(
                        'code'=>$product_db['code'],
                        'name'=>$product_db['name'],
                        'notes'=>$product_db['notes'],
                        'additional_info'=>$product_db['additional_info'],
                        'product_subcategory_id'=>$product_db['product_subcategory_id'],
                        'product_subcategory_text'=>SI::html_tag('strong',$product_db['product_subcategory_code']).
                            ' '.$product_db['product_subcategory_name'],
                        'product_status'=>$product_db['product_status'],
                        'product_status_text' => SI::get_status_attr(
                            SI::status_get('Product_Engine',$product_db['product_status'])['label']
                        ),
                        'product_img'=>''
                    );
                    $file_img = 'img/product/'.$product_id.'.jpg';
                    $product['product_img'] = Tools::img_load($file_img);
                    $product_unit = array();
                    
                    $q = '
                        select t2.*
                        from product_unit t1
                            inner join unit t2 on t1.unit_id = t2.id
                        where t1.product_id = '.$db->escape($product_id).'
                    ';
                    $rs = $db->query_array($q);
                    if(count($rs)>0){
                        foreach($rs as $idx=>$product_unit_db){
                            $product_unit[] = array(
                                'id'=>$product_unit_db['id'],
                                'text'=>$product_unit_db['code'],
                            );
                        }
                    }
                    
                    $next_allowed_status_list = SI::form_data()
                        ->status_next_allowed_status_list_get('Product_Engine',
                            $product_db['product_status']
                        );
                    
                    $product_unit_parent = array();
                    $raw_pupc = Product_Data_Support::product_unit_parent_get($product_id);
                    
                    $product_cfg = array();
                    $t_product_cfg = Product_Data_Support::product_cfg_get(array(array('product_id'=>$product_id)));
                    if(count($t_product_cfg)>0) $product_cfg = $t_product_cfg[0];
                    
                    foreach($raw_pupc as $i=>$row){
                        //<editor-fold defaultstate="collapsed">
                        $puc = array();
                        for($i = 0;$i<count($row['product_unit_child']);$i++){
                            $t_child = array(
                                'product_id'=>$row['product_unit_child'][$i]['product_id']
                                ,'product_text'=> SI::html_tag('strong',$row['product_unit_child'][$i]['product_code'])
                                    .' '.$row['product_unit_child'][$i]['product_name']
                                ,'unit_id'=>$row['product_unit_child'][$i]['unit_id']
                                ,'unit_text'=>SI::html_tag('strong',$row['product_unit_child'][$i]['unit_code'])
                                    .' '.$row['product_unit_child'][$i]['unit_name']
                                ,'qty'=>$row['product_unit_child'][$i]['qty']
                            );
                            $puc[] = $t_child;
                        }
                        $product_unit_parent[] = array(
                            'product_id'=>$row['product_id']
                            ,'product_text'=>SI::html_tag('strong',$row['product_code'])
                                .' '.$row['product_name']
                            ,'unit_id'=>$row['unit_id']
                            ,'unit_text'=>SI::html_tag('strong',$row['unit_code'])
                                .' '.$row['unit_name']
                            ,'qty'=>$row['qty']
                            ,'product_unit_child'=>$puc
                        );
                        //</editor-fold>
                    }
                    
                    $response['product_unit_parent'] = $product_unit_parent;
                    $response['product_cfg'] = $product_cfg;
                    $response['product'] = $product;
                    $response['product_unit'] = $product_unit;
                    $response['product_status_list'] = $next_allowed_status_list;
                }
                
                
                break;
            case 'product_unit_conversion':
                get_instance()->load->helper('product_unit_conversion/product_unit_conversion_engine');
                switch($submethod){
                    case 'unit_1_list':
                        $db = new DB();
                        $response = array();
                        $product_id = isset($data['product_id'])?$data['product_id']:'';
                        $q = '
                            select distinct t3.id, t3.name, t3.code
                            from product t1
                                inner join product_unit t2 on t1.id = t2.product_id
                                inner join unit t3 on t2.unit_id = t3.id
                            where t1.id = '.$db->escape($product_id).'
                                and t3.status>0
                        ';
                        $rs = $db->query_array($q);
                        if(count($rs)>0){
                            for($i = 0;$i<count($rs);$i++){                                
                                $rs[$i]['text'] = SI::html_tag('strong',$rs[$i]['code']).' '.$rs[$i]['name'];
                            }
                            $response = $rs;
                        }

                        break;
                    case 'product_unit_conversion_type_get':
                        get_instance()->load->helper('product_unit_conversion/product_unit_conversion_engine');
                        $db = new DB();
                        $product_unit_conversion_id = $data['product_unit_conversion_id'];
                        $response = array();
                        $q = 'select type from product_unit_conversion where id = '.$db->escape($product_unit_conversion_id);
                        $rs = $db->query_array($q);
                        if(count($rs)>0){
                            $type = $rs[0]['type'];
                            $type_name = Product_Unit_Conversion_Engine::type_label_get($type);
                            $response = array('id'=>$type,'text'=>$type_name);
                        }

                        break;
                    case 'product_unit_conversion_sales_moq_get':
                        $db = new DB();
                        $response = array();
                        $id = isset($data['product_unit_conversion_id'])?
                                $data['product_unit_conversion_id']:'';
                        $q = '
                            select t1.qty_1, t1.qty_2
                                , t3.id unit_id_1, t3.name unit_name_1
                                , t4.id unit_id_2, t4.name unit_name_2
                                ,t1.product_unit_conversion_status
                            from product_unit_conversion t1
                                inner join unit t3 on t1.unit_id_1 = t3.id
                                inner join unit t4 on t1.unit_id_2 = t4.id
                            where t1.id = '.$db->escape($id).'
                        ';
                        $rs = $db->query_array($q);
                        if(count($rs)>0){
                            $response = $rs[0];
                            $response['product_unit_conversion_status_text'] = SI::get_status_attr(
                                SI::status_get('Product_Unit_Conversion_Engine', 
                                    $response['product_unit_conversion_status'])['label']
                            );
                            $response['product_unit_conversion_status_list'] = SI::form_data()
                                ->status_next_allowed_status_list_get('Product_Unit_Conversion_Engine', 
                                    $response['product_unit_conversion_status']);
                        }

                        break;
                    case 'product_unit_conversion_sales_real_weight_get':
                        $db = new DB();
                        $response = array();
                        $id = isset($data['product_unit_conversion_id'])?
                                $data['product_unit_conversion_id']:'';
                        $q = '
                            select t1.qty_1, t1.qty_2
                                , t3.id unit_id_1, t3.name unit_name_1
                                , t4.id unit_id_2, t4.name unit_name_2
                                ,t1.product_unit_conversion_status
                            from product_unit_conversion t1
                                inner join unit t3 on t1.unit_id_1 = t3.id
                                inner join unit t4 on t1.unit_id_2 = t4.id
                            where t1.id = '.$db->escape($id).'
                        ';
                        $rs = $db->query_array($q);
                        if(count($rs)>0){
                            $response = $rs[0];
                            $response['product_unit_conversion_status_text'] = SI::get_status_attr(
                                SI::status_get('Product_Unit_Conversion_Engine', 
                                    $response['product_unit_conversion_status'])['label']
                            );
                            $response['product_unit_conversion_status_list'] = SI::form_data()
                                ->status_next_allowed_status_list_get('Product_Unit_Conversion_Engine', 
                                    $response['product_unit_conversion_status']);

                        }

                        break;
                    case 'product_unit_conversion_sales_expedition_weight_get':
                        $db = new DB();
                        $response = array();
                        $puc_id = isset($data['product_unit_conversion_id'])?
                                Tools::_str($data['product_unit_conversion_id']):
                                '';
                        $q = '
                            select t1.qty_1, t1.qty_2
                                , t3.id unit_id_1, t3.name unit_name_1
                                , t4.id unit_id_2, t4.name unit_name_2
                                ,t1.expedition_id 
                                , t5.code expedition_code, t5.name expedition_name
                                ,t1.product_unit_conversion_status
                            from product_unit_conversion t1
                                inner join unit t3 on t1.unit_id_1 = t3.id
                                inner join unit t4 on t1.unit_id_2 = t4.id
                                inner join expedition t5 on t1.expedition_id = t5.id
                            where t1.id = '.$db->escape($puc_id).'
                        ';
                        $rs = $db->query_array($q);
                        if(count($rs)>0){
                            $response = $rs[0];
                            $response['expedition_name'] = SI::html_tag('strong',$rs[0]['expedition_code']).' '.$rs[0]['expedition_name'];
                            $response['product_unit_conversion_status_text'] = SI::get_status_attr(
                                SI::status_get('Product_Unit_Conversion_Engine', 
                                    $response['product_unit_conversion_status'])['label']
                            );
                            $response['product_unit_conversion_status_list'] = SI::form_data()
                                ->status_next_allowed_status_list_get('Product_Unit_Conversion_Engine', 
                                    $response['product_unit_conversion_status']);

                        }

                        break;
                }
                break;
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
        //</editor-fold>
    }
    
    public function product_add(){
        
        $this->load->helper($this->path->product_engine);
        $post = $this->input->post();
        if($post!= null){
            Product_Engine::submit('','product_add',$post);
        }
        
    }
    
    public function product_active($id){
        
        $this->load->helper($this->path->product_engine);
        $post = $this->input->post();
        if($post!= null){
            Product_Engine::submit($id,'product_active',$post);
        }
        
        
    }
    
    public function product_inactive($id){
        
        $this->load->helper($this->path->product_engine);
        $post = $this->input->post();
        if($post!= null){
            Product_Engine::submit($id,'product_inactive',$post);
        }
        
        
    }
    
    public function product_unit_conversion_delete($id='',$parent_id=''){
        $this->load->helper($this->path->product_unit_conversion_engine);
        $path = Product_Unit_Conversion_Engine::delete($id);
        redirect($this->path->index.'view/'.$parent_id);
    }
    
    public function product_unit_conversion_add(){
        $this->load->helper($this->path->product_unit_conversion_engine);
        $post = $this->input->post();
        if($post!= null){
            Product_Unit_Conversion_Engine::submit('','add',$post);
        }        
    }
    
    public function product_unit_conversion_active($id){
        $this->load->helper($this->path->product_unit_conversion_engine);
        $post = $this->input->post();
        if($post!= null){
            Product_Unit_Conversion_Engine::active($id);
        }        
    }
    
    public function product_unit_conversion_inactive($id){
        $this->load->helper($this->path->product_unit_conversion_engine);
        $post = $this->input->post();
        if($post!= null){
            Product_Unit_Conversion_Engine::inactive($id);
        }        
    }
    
}

