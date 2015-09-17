<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Customer_Bill_Renderer {
        
        public static function modal_customer_bill_render($app,$modal){
            $modal->header_set(array('title'=>'Customer Bill','icon'=>App_Icon::money()));
            $components = self::customer_bill_components_render($app, $modal,true);
        }
        
        public static function customer_bill_render($app,$form,$data,$path,$method){
            get_instance()->load->helper('customer_bill/customer_bill_engine');
            $path = Customer_Bill_Engine::path_get();
            $id = $data['id'];
            $components = self::customer_bill_components_render($app, $form,false);
            $back_href = $path->index;
            
            $form->button_add()->button_set('value','BACK')
                ->button_set('icon',App_Icon::btn_back())
                ->button_set('href',$back_href)
                ->button_set('class','btn btn-default')
                ;

            $js = '
                <script>
                    $("#customer_bill_method").val("'.$method.'");
                    $("#customer_bill_id").val("'.$id.'");
                </script>
            ';             
            $app->js_set($js);
            
            $js = '                
                    customer_bill_init();
                    customer_bill_bind_event();
                    customer_bill_components_prepare(); 
            ';
            $app->js_set($js);
            
        }
        
        public static function customer_bill_components_render($app,$form,$is_modal){
            
            get_instance()->load->helper('customer_bill/customer_bill_engine');
            $path = Customer_Bill_Engine::path_get();            
            $components = array();
            $db = new DB();
            
            $id_prefix = 'customer_bill';
            
            $components['id'] = $form->input_add()->input_set('id',$id_prefix.'_id')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;
            
            $form->input_add()->input_set('id',$id_prefix.'_type')
                    ->input_set('value','')
                    ->input_set('hide_all',true)
                    ;
            
            $disabled = array('disable'=>'');
            
            
            $reference_detail = array(
                
            );
                        
            $form->input_add()->input_set('id',$id_prefix.'_method')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;            
            $db = new DB();
            
            $store_list = array();
            $q = 'select id id, name data from store where status>0';            
            $store_list = $db->query_array($q);
            
            
            $form->input_select_add()
                    ->input_select_set('label',Lang::get('Store'))
                    ->input_select_set('icon',App_Icon::store())
                    ->input_select_set('min_length','0')
                    ->input_select_set('id',$id_prefix.'_store')
                    ->input_select_set('data_add',$store_list)
                    ->input_select_set('value',array())
                    ->input_select_set('disable_all',true)
                    ->input_select_set('hide_all',true)                                        
                ;
            
            $form->input_select_detail_add()
                ->input_select_set('label',Lang::get('Reference'))
                ->input_select_set('icon',App_Icon::info())
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_reference')
                ->input_select_set('min_length','1')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('ajax_url',$path->ajax_search.'input_select_reference_search/')
                ->input_select_set('disable_all',true)
                 ->input_select_set('hide_all',true)
                ->detail_set('rows',$reference_detail)
                ->detail_set('id',$id_prefix."_reference_detail")
                ->detail_set('ajax_url','')                    
            ;
            
            
            
            $form->input_add()->input_set('label',Lang::get('Code'))
                    ->input_set('id',$id_prefix.'_code')
                    ->input_set('icon','fa fa-info')
                    ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
                    ->input_set('hide_all',true)
                ;
            
            $form->datetimepicker_add()->datetimepicker_set('label',Lang::get('Customer Bill Date'))
                    ->datetimepicker_set('id',$id_prefix.'_customer_bill_date')
                    ->datetimepicker_set('value',Tools::_date('','F d, Y H:i')) 
                    ->datetimepicker_set('disable_all',true)
                    ->datetimepicker_set('hide_all',true)
                ;
            
            $form->input_select_add()
                ->input_select_set('label','Customer')
                ->input_select_set('icon',APP_Icon::user())
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_customer')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('hide_all',true)                    
                ->input_select_set('disable_all',true)                    
                ;
            
            $form->input_add()->input_set('label',Lang::get('Amount '))
                    ->input_set('id',$id_prefix.'_amount')
                    ->input_set('icon',App_Icon::money())
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            $form->input_add()->input_set('label',Lang::get('Outstanding Amount '))
                    ->input_set('id',$id_prefix.'_outstanding_amount')
                    ->input_set('icon',App_Icon::money())
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            
            $components['customer_bill_status'] = $form->input_select_add()
                ->input_select_set('label','Status')
                ->input_select_set('icon','fa fa-info')
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_customer_bill_status')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('is_module_status',true)
                ->input_select_set('hide_all',true)                    
                ;
                        
            $form->hr_add()->hr_set('class','');
            
            $form->button_add()->button_set('value','Submit')
                            ->button_set('id',$id_prefix.'_submit')
                            ->button_set('icon',App_Icon::detail_btn_save())
                        ;
                        
            $param = array(
                'ajax_url'=>$path->index.'ajax_search/'
                ,'index_url'=>$path->index
                ,'detail_tab'=>'#detail_tab'
                ,'view_url'=>$path->index.'view/'
                ,'window_scroll'=>'body'
                ,'data_support_url'=>$path->index.'data_support/'
                ,'common_ajax_listener'=>get_instance()->config->base_url().'common_ajax_listener/'
            );
            
            
            
            if($is_modal){
                $param['detail_tab'] = '#modal_'.$id_prefix.' .modal-body';
                $param['view_url'] = '';
                $param['window_scroll'] = '#modal_'.$id_prefix;
            }
            
            $js = get_instance()->load->view('customer_bill/'.$id_prefix.'_basic_function_js',$param,TRUE);
            $app->js_set($js);
            return $components;
            
        }
        
        public static function customer_bill_status_log_render($app,$form,$data,$path){
            $config=array(
                'module_name'=>'customer_bill',
                'module_engine'=>'Customer_Bill_Engine',
                'id'=>$data['id']
            );
            SI::form_renderer()->status_log_tab_render($form, $config);
        }
        
        public static function customer_bill_allocation_view_render($app,$form,$data,$path){
            get_instance()->load->helper('customer_bill_allocation/customer_bill_allocation_engine');
            get_instance()->load->helper('customer_bill_allocation/customer_bill_allocation_renderer');
            $id = $data['id'];
            $db = new DB();
            $rs = $db->fast_get('customer_bill',array('id'=>$id));
            if(count($rs)>0) {
                $customer_bill = $rs[0];            
                $form->form_group_add();
                if($customer_bill['customer_bill_status'] != 'X'){
                    if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'customer_bill_allocation','add')){
                    $form->button_add()->button_set('class','primary')
                            ->button_set('value','New Customer Bill Allocation')
                            ->button_set('icon','fa fa-plus')
                            ->button_set('attrib',array(
                                'data-toggle'=>"modal" 
                                ,'data-target'=>"#modal_customer_bill_allocation"
                            ))
                            ->button_set('disable_after_click',false)
                            ->button_set('id','customer_bill_allocation_new')
                        ;
                    }
                }
                $form->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
                $tbl = $form->table_add();
                $tbl->table_set('class','table');
                $tbl->table_set('id','customer_bill_allocation_table');
                $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center'),"is_key"=>true));            
                $tbl->table_set('columns',array("name"=>"customer_bill_allocation_type_text","label"=>"Reference Type",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"reference_code","label"=>"Reference Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"code","label"=>"Allocation Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"allocated_amount","label"=>"Allocated Amount (Rp.)",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"customer_bill_allocation_status_text","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('data key','id');

                $q = '
                    select distinct NULL row_num
                        ,t1.*
                        ,t2.code sales_invoice_code
                        ,t3.code customer_bill_code

                    from customer_bill_allocation t1
                        left outer join sales_invoice t2 on t1.sales_invoice_id = t2.id
                        left outer join customer_bill t3 on t1.customer_bill_id = t3.id
                        inner join customer_bill t4 on t4.id = t1.customer_bill_id
                    where t4.id = '.$id.' order by t1.moddate desc

                ';
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){
                    $rs[$i]['reference_code'] = $rs[$i][$rs[$i]['customer_bill_allocation_type'].'_code'];
                    $rs[$i]['customer_bill_allocation_type_text'] = SI::type_get('Customer_Bill_Allocation_Engine',
                        $rs[$i]['customer_bill_allocation_type'])['label'];
                    $rs[$i]['row_num'] = $i+1;
                    $rs[$i]['allocated_amount'] = Tools::thousand_separator($rs[$i]['allocated_amount'],2,true);
                    $rs[$i]['customer_bill_allocation_status_text'] = SI::get_status_attr(
                        SI::status_get('Customer_Bill_Allocation_Engine', $rs[$i]['customer_bill_allocation_status'])['label']
                    );
                }
                $tbl->table_set('data',$rs);
                

                $modal_customer_bill_allocation = $app->engine->modal_add()->id_set('modal_customer_bill_allocation')->width_set('75%');

                $customer_bill_allocation_data = array(
                    'customer_bill'=>array(
                        'id'=>$customer_bill['id']
                    )                
                );
                $customer_bill_allocation_data = json_decode(json_encode($customer_bill_allocation_data));

                Customer_Bill_Allocation_Renderer::modal_customer_bill_allocation_render(
                        $app
                        ,$modal_customer_bill_allocation
                        ,$customer_bill_allocation_data
                    );


                $param = array(
                    'index_url'=>$path->index
                    ,'ajax_search'=>$path->ajax_search
                    ,'customer_bill_id'=>$customer_bill['id']
                    ,'customer_bill_text'=>$customer_bill['code']
                );

                $js = get_instance()->load->view('customer_bill/customer_bill_allocation_js',$param,TRUE);
                $app->js_set($js);
                
            }
        }
        
        public static function customer_deposit_allocation_view_render($app,$form,$data,$path){
            //<editor-fold defaultstate="collapsed">
            get_instance()->load->helper('customer_deposit_allocation/customer_deposit_allocation_engine');
            get_instance()->load->helper('customer_deposit_allocation/customer_deposit_allocation_renderer');
            $id = $data['id'];
            $db = new DB();
            $q = '
                select t1.*
                from customer_bill t1
                where t1.id = '.$db->escape($id).'
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0) {
                $customer_bill = $rs[0];            
                $form->form_group_add();
                
                if($customer_bill['customer_bill_status'] != 'X'){
                    if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'customer_deposit_allocation','add')){
                    $form->button_add()->button_set('class','primary')
                            ->button_set('value',Lang::get(array('New','Customer Deposit Allocation')))
                            ->button_set('icon','fa fa-plus')
                            ->button_set('attrib',array(
                                'data-toggle'=>"modal" 
                                ,'data-target'=>"#modal_customer_deposit_allocation"
                            ))
                            ->button_set('disable_after_click',false)
                            ->button_set('id','customer_deposit_allocation_new')
                        ;
                    }
                }
                
                $form->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
                $tbl = $form->table_add();
                $tbl->table_set('class','table');
                $tbl->table_set('id','customer_deposit_allocation_table');
                $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
                $tbl->table_set('columns',array("name"=>"customer_deposit_code","label"=>"Customer Deposit Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"code","label"=>"Allocation Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center'),"is_key"=>true));
                $tbl->table_set('columns',array("name"=>"allocated_amount","label"=>"Allocated Amount (Rp.)",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"customer_deposit_allocation_status_text","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('data key','id');

                $q = '
                    select distinct NULL row_num
                        ,t1.*
                        ,t3.code customer_deposit_code
                        ,t3.id customer_deposit_id
                    from customer_deposit_allocation t1
                        inner join customer_bill t2 on t1.customer_bill_id = t2.id
                        inner join customer_deposit t3 on t3.id = t1.customer_deposit_id
                    where t2.id = '.$db->escape($id).' order by t1.moddate desc

                ';
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){
                    $rs[$i]['customer_deposit_allocation_type_text'] = SI::type_get('Customer_Deposit_Allocation_Engine',
                        $rs[$i]['customer_deposit_allocation_type'])['label'];
                    $rs[$i]['row_num'] = $i+1;
                    $rs[$i]['allocated_amount'] = Tools::thousand_separator($rs[$i]['allocated_amount'],2,true);
                    $rs[$i]['customer_deposit_allocation_status_text'] = SI::get_status_attr(
                        SI::status_get('Customer_Deposit_Allocation_Engine', $rs[$i]['customer_deposit_allocation_status'])['label']
                    );
                }
                $tbl->table_set('data',$rs);
                
                
                $modal_customer_deposit_allocation = $app->engine->modal_add()->id_set('modal_customer_deposit_allocation')->width_set('75%');

                $customer_deposit_allocation_data = array(
                    'customer_deposit'=>array(
                        'id'=>''
                    )                
                );
                $customer_deposit_allocation_data = json_decode(json_encode($customer_deposit_allocation_data));

                Customer_Deposit_Allocation_Renderer::modal_customer_deposit_allocation_render(
                        $app
                        ,$modal_customer_deposit_allocation
                        ,$customer_deposit_allocation_data
                    );

                
                $param = array(
                    'index_url'=>$path->index
                    ,'ajax_search'=>$path->ajax_search
                    ,'reference_id'=>$customer_bill['id']
                    ,'reference_text'=>$customer_bill['code']
                    ,'reference_type'=>'customer_bill'
                    ,'customer_id'=>$customer_bill['customer_id']
                );

                $js = get_instance()->load->view('customer_bill/customer_deposit_allocation_js',$param,TRUE);
                $app->js_set($js);
                
            }
            //</editor-fold>
        }
        
        public static function sra_view_render($app,$form,$data,$path){
            //<editor-fold defaultstate="collapsed">
            
            get_instance()->load->helper('sales_receipt_allocation/sales_receipt_allocation_engine');
            get_instance()->load->helper('sales_receipt_allocation/sales_receipt_allocation_renderer');
            $id = $data['id'];
            $db = new DB();
            $rs = $db->fast_get('customer_bill',array('id'=>$id));
            if(count($rs)>0) {
                $customer_bill = $rs[0];            
                $form->form_group_add();
                if($customer_bill['customer_bill_status'] != 'X'){
                    if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'sales_receipt_allocation','add')){
                    $form->button_add()->button_set('class','primary')
                            ->button_set('value',Lang::get(array('New','Sales Receipt Allocation')))
                            ->button_set('icon','fa fa-plus')
                            ->button_set('attrib',array(
                                'data-toggle'=>"modal" 
                                ,'data-target'=>"#modal_sales_receipt_allocation"
                            ))
                            ->button_set('disable_after_click',false)
                            ->button_set('id','sales_receipt_allocation_new')
                        ;
                    }
                }
                $form->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
                $tbl = $form->table_add();
                $tbl->table_set('class','table');
                $tbl->table_set('id','sales_receipt_allocation_table');
                $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
                $tbl->table_set('columns',array("name"=>"sales_receipt_code","label"=>"Purchase Receipt Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"code","label"=>"Allocation Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center'),"is_key"=>true));
                $tbl->table_set('columns',array("name"=>"allocated_amount","label"=>"Allocated Amount (Rp.)",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"sales_receipt_allocation_status_text","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('data key','id');

                $q = '
                    select distinct NULL row_num
                        ,t1.*
                        ,t2.code sales_invoice_code
                        ,t4.code sales_receipt_code

                    from sales_receipt_allocation t1
                        inner join customer_bill t2 on t1.customer_bill_id = t2.id
                        inner join sales_receipt t4 on t4.id = t1.sales_receipt_id
                    where t2.id = '.$id.' order by t1.moddate desc

                ';
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){
                    $rs[$i]['sales_receipt_allocation_type_text'] = SI::type_get('Sales_Receipt_Allocation_Engine',
                        $rs[$i]['sales_receipt_allocation_type'])['label'];
                    $rs[$i]['row_num'] = $i+1;
                    $rs[$i]['allocated_amount'] = Tools::thousand_separator($rs[$i]['allocated_amount'],2,true);
                    $rs[$i]['sales_receipt_allocation_status_text'] = SI::get_status_attr(
                        SI::status_get('Sales_Receipt_Allocation_Engine', $rs[$i]['sales_receipt_allocation_status'])['label']
                    );
                }
                $tbl->table_set('data',$rs);
                

                $modal_sales_receipt_allocation = $app->engine->modal_add()->id_set('modal_sales_receipt_allocation')->width_set('75%');

                Sales_Receipt_Allocation_Renderer::modal_sales_receipt_allocation_render(
                        $app
                        ,$modal_sales_receipt_allocation
                    );


                $param = array(
                    'index_url'=>$path->index
                    ,'ajax_search'=>$path->ajax_search
                    ,'reference_id'=>$customer_bill['id']
                    ,'reference_text'=>$customer_bill['code']
                    ,'reference_type'=>'customer_bill'
                    ,'customer_id'=>$customer_bill['customer_id']
                );

                $js = get_instance()->load->view('customer_deposit/sales_receipt_allocation_js',$param,TRUE);
                $app->js_set($js);
                
            }
            //</editor-fold>
        }
        
        
    }
    
?>