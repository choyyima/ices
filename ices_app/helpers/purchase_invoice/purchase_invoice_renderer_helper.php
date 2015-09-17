<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Purchase_Invoice_Renderer {
        
        public static function modal_purchase_invoice_render($app,$modal){
            $modal->header_set(array('title'=>'Purchase Invoice','icon'=>App_Icon::purchase_invoice()));
            $components = self::purchase_invoice_components_render($app, $modal,true);
        }
        
        public static function purchase_invoice_render($app,$form,$data,$path,$method){
            get_instance()->load->helper('purchase_invoice/purchase_invoice_engine');
            $path = Purchase_Invoice_Engine::path_get();
            $id = $data['id'];
            $components = self::purchase_invoice_components_render($app, $form,false);
            $back_href = $path->index;
            
            $form->button_add()->button_set('value','BACK')
                ->button_set('icon',App_Icon::btn_back())
                ->button_set('href',$back_href)
                ->button_set('class','btn btn-default')
                ;

            $js = '
                <script>
                    $("#purchase_invoice_method").val("'.$method.'");
                    $("#purchase_invoice_id").val("'.$id.'");
                </script>
            ';             
            $app->js_set($js);
            
            $js = '                
                    purchase_invoice_init();
                    purchase_invoice_bind_event();
                    purchase_invoice_components_prepare(); 
            ';
            $app->js_set($js);
            
        }
        
        public static function purchase_invoice_components_render($app,$form,$is_modal){
            //<editor-fold defaultstate="collapsed">
            get_instance()->load->helper('purchase_invoice/purchase_invoice_engine');
            $path = Purchase_Invoice_Engine::path_get();            
            $components = array();
            $db = new DB();
            
            $id_prefix = 'purchase_invoice';
            
            $components['id'] = $form->input_add()->input_set('id',$id_prefix.'_id')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;
            
            $disabled = array('disable'=>'');
                                    
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
            
            $form->input_add()->input_set('label',Lang::get('Code'))
                    ->input_set('id',$id_prefix.'_code')
                    ->input_set('icon','fa fa-info')
                    ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
                    ->input_set('hide_all',true)
                ;
            
            $form->input_select_add()
                ->input_select_set('label','Supplier')
                ->input_select_set('icon',APP_Icon::user())
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_supplier')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('hide_all',true)                    
                ->input_select_set('disable_all',true)  
                ->input_select_set('ajax_url',$path->ajax_search.'input_select_supplier_search/')
                ;
            
            $form->input_add()->input_set('label',Lang::get('Reference Code'))
                    ->input_set('id',$id_prefix.'_reference_code')
                    ->input_set('icon','fa fa-info')
                    ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
                    ->input_set('hide_all',true)
                ;
            
            $form->datetimepicker_add()->datetimepicker_set('label',Lang::get('Purchase Invoice Date'))
                    ->datetimepicker_set('id',$id_prefix.'_purchase_invoice_date')
                    ->datetimepicker_set('disable_all',true)
                    ->datetimepicker_set('hide_all',true)
                ;
            
            $form->datetimepicker_add()->datetimepicker_set('label',Lang::get('Product Arrival Date'))
                    ->datetimepicker_set('id',$id_prefix.'_product_arrival_date')
                    ->datetimepicker_set('disable_all',true)
                    ->datetimepicker_set('hide_all',true)
                ;
            
            $components['purchase_invoice_status'] = $form->input_select_add()
                ->input_select_set('label','Status')
                ->input_select_set('icon','fa fa-info')
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_purchase_invoice_status')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('is_module_status',true)
                ->input_select_set('hide_all',true)                    
                ;
            
            $form->custom_component_add()->src_set('purchase_invoice/view/product_table_view');
            $form->custom_component_add()->src_set('purchase_invoice/view/expense_table_view');
           
            
            $form->textarea_add()->textarea_set('label','Notes')
                    ->textarea_set('id','purchase_invoice_notes')
                    ->textarea_set('value','')
                    ->textarea_set('attrib',array())       
                    ->textarea_set('disable_all',true)
                    
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
                ,'component_prefix_id'=>$id_prefix
            );
            
            
            
            if($is_modal){
                $param['detail_tab'] = '#modal_'.$id_prefix.' .modal-body';
                $param['view_url'] = '';
                $param['window_scroll'] = '#modal_'.$id_prefix;
            }
            
            $js = get_instance()->load->view('purchase_invoice/'.$id_prefix.'_basic_function_js',$param,TRUE);
            $app->js_set($js);
            return $components;
            //</editor-fold>
            
        }
        
        public static function purchase_invoice_status_log_render($app,$form,$data,$path){
            $config=array(
                'module_name'=>'purchase_invoice',
                'module_engine'=>'Purchase_Invoice_Engine',
                'id'=>$data['id']
            );
            SI::form_renderer()->status_log_tab_render($form, $config);
        }
        
        public static function receive_product_view_render($app, $form, $data, $path){
            //<editor-fold defaultstate="collapsed">
            get_instance()->load->helper('receive_product/receive_product_engine');
            get_instance()->load->helper('receive_product/receive_product_renderer');
            $id = $data['id'];
            $db = new DB();
            $rs = $db->fast_get('purchase_invoice',array('id'=>$id));
            if(count($rs)>0) {
                $purchase_invoice = $rs[0];            
                $form->form_group_add();
                if($purchase_invoice['purchase_invoice_status'] != 'X'){
                    if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'receive_product','add')){
                    $form->button_add()->button_set('class','primary')
                            ->button_set('value','New Receive Product')
                            ->button_set('icon','fa fa-plus')
                            ->button_set('attrib',array(
                                'data-toggle'=>"modal" 
                                ,'data-target'=>"#modal_receive_product"
                            ))
                            ->button_set('disable_after_click',false)
                            ->button_set('id','receive_product_new')
                        ;
                    }
                }
                $form->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
                $tbl = $form->table_add();
                $tbl->table_set('class','table');
                $tbl->table_set('id','receive_product_view_table');
                $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
                $tbl->table_set('columns',array("name"=>"code","label"=>"Receive Product Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center'),"is_key"=>true));
                $tbl->table_set('columns',array("name"=>"receive_product_date","label"=>"Receive Product Date",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"receive_product_status_text","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('data key','id');

                $q = '
                    select distinct NULL row_num
                        ,t3.*
                        ,t3.code customer_bill_code
                        ,t7.code warehouse_to_code
                        ,t7.name warehouse_to_name
                    from purchase_invoice t1
                        inner join purchase_invoice_receive_product t2 on t1.id = t2.purchase_invoice_id
                        inner join receive_product t3 on t2.receive_product_id = t3.id
                        inner join receive_product_warehouse_from t4 on t3.id = t4.receive_product_id
                        inner join warehouse t5 on t5.id = t4.warehouse_id
                        inner join receive_product_warehouse_to t6 on t3.id = t6.receive_product_id
                        inner join warehouse t7 on t7.id = t6.warehouse_id
                        
                    where t1.id = '.$id.' order by t3.id desc

                ';
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){
                    
                    $rs[$i]['row_num'] = $i+1;
                    $rs[$i]['receive_product_status_text'] = SI::get_status_attr(
                        SI::status_get('Receive_Product_Engine', $rs[$i]['receive_product_status'])['label']
                    );
                }
                $tbl->table_set('data',$rs);
                

                $modal_receive_product = $app->engine->modal_add()->id_set('modal_receive_product')->width_set('75%');

                Receive_Product_Renderer::modal_receive_product_render(
                        $app
                        ,$modal_receive_product
                        
                    );


                $param = array(
                    'index_url'=>$path->index
                    ,'ajax_search'=>$path->ajax_search
                    ,'reference_id'=>$id
                    ,'reference_text'=>$purchase_invoice['code']
                    ,'reference_type'=>'purchase_invoice'
                );

                $js = get_instance()->load->view('purchase_invoice/receive_product_js',$param,TRUE);
                $app->js_set($js);
                
            }
            //</editor-fold>
        }

        public static function pra_view_render($app,$form,$data,$path){
            //<editor-fold defaultstate="collapsed">
            
            get_instance()->load->helper('purchase_receipt_allocation/purchase_receipt_allocation_engine');
            get_instance()->load->helper('purchase_receipt_allocation/purchase_receipt_allocation_renderer');
            $id = $data['id'];
            $db = new DB();
            $rs = $db->fast_get('purchase_invoice',array('id'=>$id));
            if(count($rs)>0) {
                $purchase_invoice = $rs[0];            
                $form->form_group_add();
                if($purchase_invoice['purchase_invoice_status'] != 'X'){
                    if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'purchase_receipt_allocation','add')){
                    $form->button_add()->button_set('class','primary')
                            ->button_set('value','New Purchase Receipt Allocation')
                            ->button_set('icon','fa fa-plus')
                            ->button_set('attrib',array(
                                'data-toggle'=>"modal" 
                                ,'data-target'=>"#modal_purchase_receipt_allocation"
                            ))
                            ->button_set('disable_after_click',false)
                            ->button_set('id','purchase_receipt_allocation_new')
                        ;
                    }
                }
                $form->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
                $tbl = $form->table_add();
                $tbl->table_set('class','table');
                $tbl->table_set('id','purchase_receipt_allocation_table');
                $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
                $tbl->table_set('columns',array("name"=>"purchase_receipt_code","label"=>"Purchase Receipt Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"code","label"=>"Allocation Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center'),"is_key"=>true));
                $tbl->table_set('columns',array("name"=>"allocated_amount","label"=>"Allocated Amount (Rp.)",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"purchase_receipt_allocation_status_text","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('data key','id');

                $q = '
                    select distinct NULL row_num
                        ,t1.*
                        ,t2.code purchase_invoice_code
                        ,t4.code purchase_receipt_code
                    from purchase_receipt_allocation t1
                        inner join purchase_invoice t2 on t1.purchase_invoice_id = t2.id
                        inner join purchase_receipt t4 on t4.id = t1.purchase_receipt_id
                    where t2.id = '.$id.' order by t1.moddate desc

                ';
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){
                    $rs[$i]['purchase_receipt_allocation_type_text'] = SI::type_get('Purchase_Receipt_Allocation_Engine',
                        $rs[$i]['purchase_receipt_allocation_type'])['label'];
                    $rs[$i]['row_num'] = $i+1;
                    $rs[$i]['allocated_amount'] = Tools::thousand_separator($rs[$i]['allocated_amount'],2,true);
                    $rs[$i]['purchase_receipt_allocation_status_text'] = SI::get_status_attr(
                        SI::status_get('Purchase_Receipt_Allocation_Engine', $rs[$i]['purchase_receipt_allocation_status'])['label']
                    );
                }
                $tbl->table_set('data',$rs);
                

                $modal_purchase_receipt_allocation = $app->engine->modal_add()->id_set('modal_purchase_receipt_allocation')->width_set('75%');

                Purchase_Receipt_Allocation_Renderer::modal_purchase_receipt_allocation_render(
                        $app
                        ,$modal_purchase_receipt_allocation
                    );


                $param = array(
                    'index_url'=>$path->index
                    ,'ajax_search'=>$path->ajax_search
                    ,'reference_id'=>$purchase_invoice['id']
                    ,'reference_text'=>$purchase_invoice['code']
                    ,'reference_type'=>'purchase_invoice'
                    ,'supplier_id'=>$purchase_invoice['supplier_id']
                );

                $js = get_instance()->load->view('purchase_invoice/purchase_receipt_allocation_js',$param,TRUE);
                $app->js_set($js);
                
            }
            //</editor-fold>
        }
        
    }
    
?>