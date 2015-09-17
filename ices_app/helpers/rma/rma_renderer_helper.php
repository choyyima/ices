<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class RMA_Renderer {
        
        public static function modal_rma_render($app,$modal){
            $modal->header_set(array('title'=>'Return Merchandise Authorization','icon'=>App_Icon::info()));
            $components = self::rma_components_render($app, $modal,true);
            
            
        }
        
        public static function rma_render($app,$form,$data,$path,$method){
            get_instance()->load->helper('rma/rma_engine');
            $path = RMA_Engine::path_get();
            $id = $data['id'];
            $components = self::rma_components_render($app, $form,false);
            $back_href = $path->index;
            
            $form->button_add()->button_set('value','BACK')
                ->button_set('icon',App_Icon::btn_back())
                ->button_set('href',$back_href)
                ->button_set('class','btn btn-default')
                ;

            $js = '
                <script>
                    $("#rma_method").val("'.$method.'");
                    $("#rma_id").val("'.$id.'");
                </script>
            ';             
            $app->js_set($js);
            
            $js = '                
                    rma_init();
                    rma_bind_event();
                    rma_components_prepare(); 
            ';
            $app->js_set($js);
            
        }
        
        public static function rma_components_render($app,$form,$is_modal){
            
            get_instance()->load->helper('rma/rma_engine');
            $path = RMA_Engine::path_get();            
            $components = array();
            $db = new DB();
            $components['id'] = $form->input_add()->input_set('id','rma_id')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;
            
            
            
            $form->input_add()->input_set('id','rma_reference_type')
                    ->input_set('value','')
                    ;
            
            $disabled = array('disable'=>'');
            
            
            $purchase_invoice_detail = array(
                array('name'=>'type','label'=>'Type')
                ,array('name'=>'code','label'=>'Code')
            );
            
            $form->input_select_detail_add()
                    ->input_select_set('label','Reference')
                    ->input_select_set('icon',App_Icon::info())
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','rma_reference')
                    ->input_select_set('min_length','1')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->input_select_set('ajax_url',$path->ajax_search.'input_select_reference_search')
                    ->detail_set('rows',$purchase_invoice_detail)
                    ->detail_set('id',"rma_reference_detail")
                    ->detail_set('ajax_url','')
                ;
            
            $form->input_add()->input_set('id','rma_method')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;            
            
            $db = new DB();
            $store_list = array();
            $q = 'select id id, name data from store where status>0';            
            $store_list = $db->query_array($q);
            
            $form->input_select_add()
                    ->input_select_set('label','Store')
                    ->input_select_set('icon',App_Icon::store())
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','rma_store')
                    ->input_select_set('data_add',$store_list)
                    ->input_select_set('value',array())                                        
                ;
            
            $form->input_add()->input_set('label','Code')
                    ->input_set('id','rma_code')
                    ->input_set('icon','fa fa-info')
                    ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
                ;
            
            $form->datetimepicker_add()->datetimepicker_set('label','RMA Date')
                    ->datetimepicker_set('id','rma_rma_date')
                    ->datetimepicker_set('value',(string)date('Y-m-d H:i')) 
                ;
            
            $form->input_select_add()
                    ->input_select_set('label','Supplier')
                    ->input_select_set('icon',App_Icon::warehouse())
                    ->input_select_set('min_length','1')
                    ->input_select_set('id','rma_supplier')
                    ->input_select_set('ajax_url',$path->ajax_search.'input_select_supplier_search')
                    ->input_select_set('value',array())
                                        
                ;
            
            $components['rma_status'] = $form->input_select_add()
                    ->input_select_set('label','Status')
                    ->input_select_set('icon','fa fa-info')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','rma_rma_status')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ;
            
            $components['cancellation_reason']=$form->textarea_add()->textarea_set('label','Cencellation Reason')
                    ->textarea_set('id','rma_cancellation_reason')
                    ->textarea_set('value','')
                    ->div_set('id','rma_div_cancellation_reason')  
                    ;
            
            $form->input_select_add()
                    ->input_select_set('label','Product')
                    ->input_select_set('icon',App_Icon::product())
                    ->input_select_set('min_length','1')
                    ->input_select_set('id','rma_product')
                    ->input_select_set('ajax_url',$path->ajax_search.'input_select_product_search')
                    ->input_select_set('value',array())
                                        
                ;
            
            $table = $form->form_group_add()->table_add();
            $table->table_set('id','rma_purchase_invoice_add_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"product_id","label"=>"",'col_attrib'=>array('class'=>'hidden')));
            $table->table_set('columns',array("name"=>"product_img","label"=>"",'col_attrib'=>array('style'=>'text-align:center;width:100px')));
            $table->table_set('columns',array("name"=>"product_name","label"=>"Product",'col_attrib'=>array('style'=>'text-align:center')));
            $table->table_set('columns',array("name"=>"max_qty","label"=>"Max Qty",'col_attrib'=>array('style'=>'text-align:right')));
            $table->table_set('columns',array("name"=>"qty","label"=>"Qty",'col_attrib'=>array('style'=>'text-align:center')));
            $table->table_set('columns',array("name"=>"unit_id","label"=>"",'col_attrib'=>array('style'=>'text-align:center;display:none')));
            $table->table_set('columns',array("name"=>"unit_name","label"=>"Unit",'col_attrib'=>array('style'=>'text-align:center')));

            $table = $form->form_group_add()->table_add();
            $table->table_set('id','rma_purchase_invoice_view_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px;text-align:center'),'attribute'=>'style="text-align:center"'));
            $table->table_set('columns',array("name"=>"product_img","label"=>"",'col_attrib'=>array('style'=>'text-align:left;width:100px;text-align:center')));
            $table->table_set('columns',array("name"=>"product_name","label"=>"Product",'col_attrib'=>array('style'=>'text-align:center')));
            $table->table_set('columns',array("name"=>"qty","label"=>"Qty",'col_attrib'=>array('style'=>'text-align:right;width:200px')));
            $table->table_set('columns',array("name"=>"unit_name","label"=>"Unit",'col_attrib'=>array('style'=>'text-align:center;width:200px;')));
            
            $form->textarea_add()->textarea_set('label','Notes')
                    ->textarea_set('id','rma_notes')
                    ->textarea_set('value','')
                    ->textarea_set('attrib',array())       
                    ;
            
            
            $form->hr_add()->hr_set('class','');
            
            $form->button_add()->button_set('value','Submit')
                            ->button_set('id','rma_submit')
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
                $param['detail_tab'] = '#modal_rma .modal-body';
                $param['view_url'] = '';
                $param['window_scroll'] = '#modal_rma';
            }
            
            $js = get_instance()->load->view('rma/rma_basic_function_js',$param,TRUE);
            $app->js_set($js);
            $js = get_instance()->load->view('rma/rma_purchase_invoice_js',$param,TRUE);
            $app->js_set($js);
            return $components;
            
        }
        
        
        public static function delivery_order_view_render($app,$pane,$data,$path){
            get_instance()->load->helper('delivery_order/delivery_order_engine');
            $path = Delivery_Order_Engine::path_get();
            get_instance()->load->helper($path->delivery_order_renderer);
            get_instance()->load->helper($path->delivery_order_rma_engine);

            $id = $data['id'];
            $rma = RMA_Engine::get($id);
            $pane->form_group_add();
            if($rma->rma_status != 'X'){
                if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'delivery_order','add')){
                $pane->button_add()->button_set('class','primary')
                        ->button_set('value',Lang::get('New Delivery Order'))
                        ->button_set('icon','fa fa-plus')
                        ->button_set('attrib',array(
                            'data-toggle'=>"modal" 
                            ,'data-target'=>"#modal_delivery_order"
                        ))
                        ->button_set('disable_after_click',false)
                        ->button_set('id','delivery_order_new')
                    ;
                }
            }
            $pane->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
            $tbl = $pane->table_add();
            $tbl->table_set('class','table');
            $tbl->table_set('id','delivery_order_table');
            $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
            $tbl->table_set('columns',array("name"=>"delivery_order_code","label"=>Lang::get("Delivery Order Code"),'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center'),"is_key"=>true));
            $tbl->table_set('columns',array("name"=>"delivery_order_status_name","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"moddate","label"=>Lang::get("Modified Date"),'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('data key','id');
            
            $db = new DB();
            $q = '
                select distinct NULL row_num
                    ,t1.delivery_order_status 
                    ,t1.id
                    ,t1.moddate
                    ,t1.code delivery_order_code
                from delivery_order t1
                    inner join rma_delivery_order t2 on t1.id = t2.delivery_order_id
                    inner join rma t3 on t2.rma_id = t3.id
                where t3.id = '.$id.' order by t1.moddate desc

            ';
            $rs = $db->query_array($q);
            
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                $rs[$i]['delivery_order_status_name'] = 
                        SI::get_status_attr(
                                Delivery_Order_RMA_Engine::delivery_order_rma_status_get($rs[$i]['delivery_order_status'])['label']);
            }
            
            $tbl->table_set('data',$rs);
            
            $modal_purchase_delivery_order = $app->engine->modal_add()->id_set('modal_delivery_order')->width_set('75%')
                    ->footer_attr_set(array('style'=>'display:none'))
                    ;
            
            Delivery_Order_Renderer::modal_delivery_order_render(
                    $app
                    ,$modal_purchase_delivery_order
                );
            
            
            $param = array(
                'index_url'=>$path->index
                ,'ajax_search'=>$path->ajax_search
                ,'rma_id'=>$rma->id
                ,'rma_code'=>$rma->code
            );

            $js = get_instance()->load->view('rma/delivery_order_js',$param,TRUE);
            $app->js_set($js);
            
        }
        
        public static function receive_product_view_render($app,$pane,$data,$path){
            get_instance()->load->helper('receive_product/receive_product_engine');
            $path = Receive_Product_Engine::path_get();
            get_instance()->load->helper($path->receive_product_renderer);
            get_instance()->load->helper($path->receive_product_rma_engine);

            $id = $data['id'];
            $rma = RMA_Engine::get($id);
            $pane->form_group_add();
            if($rma->rma_status != 'X'){
                if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'receive_product','add')){
                $pane->button_add()->button_set('class','primary')
                        ->button_set('value',Lang::get('New Receive Product'))
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
            $pane->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
            $tbl = $pane->table_add();
            $tbl->table_set('class','table');
            $tbl->table_set('id','receive_product_table');
            $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
            $tbl->table_set('columns',array("name"=>"receive_product_code","label"=>Lang::get("Receive Product Code"),'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center'),"is_key"=>true));
            $tbl->table_set('columns',array("name"=>"receive_product_status_name","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"moddate","label"=>Lang::get("Modified Date"),'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('data key','id');
            
            $db = new DB();
            $q = '
                select distinct NULL row_num
                    ,t1.receive_product_status 
                    ,t1.id
                    ,t1.moddate
                    ,t1.code receive_product_code
                from receive_product t1
                    inner join rma_receive_product t2 on t1.id = t2.receive_product_id
                    inner join rma t3 on t2.rma_id = t3.id
                where t3.id = '.$id.' order by t1.moddate desc

            ';
            $rs = $db->query_array($q);
            
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                $rs[$i]['receive_product_status_name'] = 
                        SI::get_status_attr(
                                Receive_Product_RMA_Engine::receive_product_rma_status_get($rs[$i]['receive_product_status'])['label']);
            }
            
            $tbl->table_set('data',$rs);
            
            $modal_purchase_receive_product = $app->engine->modal_add()->id_set('modal_receive_product')->width_set('75%')
                    ->footer_attr_set(array('style'=>'display:none'))
                    ;
            
            Receive_Product_Renderer::modal_receive_product_render(
                    $app
                    ,$modal_purchase_receive_product
                );
            
            
            $param = array(
                'index_url'=>$path->index
                ,'ajax_search'=>$path->ajax_search
                ,'rma_id'=>$rma->id
                ,'rma_code'=>$rma->code
            );

            $js = get_instance()->load->view('rma/receive_product_js',$param,TRUE);
            $app->js_set($js);
            
        }
        
        public static function rma_status_log_render($app,$form,$data,$path){
            get_instance()->load->helper('rma/rma_engine');
            $path = RMA_Engine::path_get();
            get_instance()->load->helper($path->rma_purchase_invoice_engine);
            
            $id = $data['id'];
            $db = new DB();
            $q = '
                select null row_num
                    ,t1.moddate
                    ,t1.rma_status
                    ,t2.name user_name
                    ,case when t3.id is null then 0 else 1 end is_purchase_invoice
                from rma_status_log t1
                    inner join user_login t2 on t1.modid = t2.id
                    left outer join purchase_invoice_rma t3 on t3.rma_id = t1.rma_id
                where t1.rma_id = '.$id.'
                    order by moddate asc
            ';
            $rs = $db->query_array($q);
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                $rma_status_name = '';
                if($rs[$i]['is_purchase_invoice'] === '1'){
                    $rma_status_name = SI::get_status_attr(
                        RMA_Purchase_Invoice_Engine::rma_purchase_invoice_status_get(
                            $rs[$i]['rma_status']
                        )['label']
                    );
                }
                $rs[$i]['rma_status_name'] = $rma_status_name;
                        
                
            }
            $rma_status_log = $rs;
            
            $table = $form->form_group_add()->table_add();
            $table->table_set('id','rma_rma_status_log_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'col_attrib'=>array('style'=>'text-align:center')));
            $table->table_set('columns',array("name"=>"rma_status_name","label"=>"Status",'col_attrib'=>array('style'=>'text-align:center')));
            $table->table_set('columns',array("name"=>"user_name","label"=>"User",'col_attrib'=>array('style'=>'text-align:center')));
            $table->table_set('data',$rma_status_log);
        }
        
    }
    
?>