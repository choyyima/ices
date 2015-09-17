<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Receive_Product_Renderer_old {
        
        public static function modal_receive_product_render($app,$modal){
            $modal->header_set(array('title'=>'Receive Product','icon'=>App_Icon::info()));
            $components = self::receive_product_components_render($app, $modal,true);
            
            
        }
        
        public static function receive_product_render($app,$form,$data,$path,$method){
            get_instance()->load->helper('receive_product/receive_product_engine');
            $path = Receive_Product_Engine::path_get();
            $id = $data['id'];
            $components = self::receive_product_components_render($app, $form,false);
            $back_href = $path->index;
            
            $form->button_add()->button_set('value','BACK')
                ->button_set('icon',App_Icon::btn_back())
                ->button_set('href',$back_href)
                ->button_set('class','btn btn-default')
                ;

            $js = '
                <script>
                    $("#receive_product_method").val("'.$method.'");
                    $("#receive_product_id").val("'.$id.'");
                </script>
            ';             
            $app->js_set($js);
            
            $js = '                
                    receive_product_init();
                    receive_product_bind_event();
                    receive_product_components_prepare(); 
            ';
            $app->js_set($js);
            
        }
        
        public static function receive_product_components_render($app,$form,$is_modal){
            
            get_instance()->load->helper('receive_product/receive_product_engine');
            $path = Receive_Product_Engine::path_get();            
            $components = array();
            $db = new DB();
            $components['id'] = $form->input_add()->input_set('id','receive_product_id')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;
            
            
            
            $form->input_add()->input_set('id','receive_product_reference_type')
                    ->input_set('value','')
                    ;
            
            $disabled = array('disable'=>'');
            
            
            $purchase_invoice_detail = array(
                array('name'=>'type','label'=>'Type')
                ,array('name'=>'code','label'=>'Code')
            );
            
            $form->input_select_detail_add()
                    ->input_select_set('label',Lang::get('Reference'))
                    ->input_select_set('icon',App_Icon::info())
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','receive_product_reference')
                    ->input_select_set('min_length','1')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->input_select_set('ajax_url',$path->ajax_search.'input_select_reference_search')
                    ->detail_set('rows',$purchase_invoice_detail)
                    ->detail_set('id',"receive_product_reference_detail")
                    ->detail_set('ajax_url','')
                ;
            
            $form->input_add()->input_set('id','receive_product_method')
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
                    ->input_select_set('id','receive_product_store')
                    ->input_select_set('data_add',$store_list)
                    ->input_select_set('value',array())
                                        
                ;
            
            $asd = $form->input_add()->input_set('label','Code')
                    ->input_set('id','receive_product_code')
                    ->input_set('icon','fa fa-info')
                    ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
                ;
            
            $form->datetimepicker_add()->datetimepicker_set('label','Receive Product Date')
                    ->datetimepicker_set('id','receive_product_receive_product_date')
                    ->datetimepicker_set('value',(string)date('Y-m-d H:i')) 
                    ->div_set('id','receive_product_div_receive_product_date')
                ;
            
            $warehouse_list_from = array();
            $q = '
                select t1.id id, t1.name data 
                from warehouse t1
                    inner join warehouse_type t2 on t1.warehouse_type_id = t2.id
                where t1.status>0 
                ';            
            $warehouse_list_from = $db->query_array($q);
            
            $warehouse_from_detail = array(
                array('id'=>'receive_product_warehouse_from_code','name'=>'code','label'=>'Code','type'=>'text')
                ,array('id'=>'receive_product_warehouse_from_name','name'=>'name','label'=>'Name','type'=>'text')
                ,array('id'=>'receive_product_warehouse_from_type','name'=>'warehouse_type','label'=>'Type','type'=>'text')
                ,array('id'=>'receive_product_warehouse_from_reference_code','name'=>'contact_name','label'=>'Reference Code','type'=>'input')
                ,array('id'=>'receive_product_warehouse_from_contact_name','name'=>'contact_name','label'=>'Contact Name','type'=>'input')
                ,array('id'=>'receive_product_warehouse_from_address','name'=>'address','label'=>'Address','type'=>'input')
                ,array('id'=>'receive_product_warehouse_from_phone','name'=>'phone','label'=>'Phone','type'=>'input','attribute'=>'data-inputmask="\'mask\': \'(99) 99-999-99999\'" data-mask=""')
                
            );
            
            $form->input_select_detail_editable_add()
                    ->input_select_set('label','From Warehouse')
                    ->input_select_set('icon',App_Icon::warehouse())
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','receive_product_warehouse_from')
                    ->input_select_set('data_add',$warehouse_list_from)
                    ->input_select_set('value',array())
                    ->detail_editable_set('rows',$warehouse_from_detail)
                    ->detail_editable_set('id',"receive_product_warehouse_from_detail")
                    ->detail_editable_set('ajax_url','')
                                        
                ;
            
            $warehouse_list_to = array();
            $q = '
                select t1.id id, t1.name data 
                from warehouse t1
                    inner join warehouse_type t2 on t1.warehouse_type_id = t2.id
                where t1.status>0 and t2.code = "BOS"
                ';            
            $warehouse_list_to = $db->query_array($q);
            
            $form->input_select_add()
                    ->input_select_set('label','To Warehouse')
                    ->input_select_set('icon',App_Icon::warehouse())
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','receive_product_warehouse_to')
                    ->input_select_set('data_add',$warehouse_list_to)
                    ->input_select_set('value',array())
                    ->div_set('id','receive_product_div_warehouse_to')
                                        
                ;
            
            $components['receive_product_status'] = $form->input_select_add()
                    ->input_select_set('label','Status')
                    ->input_select_set('icon','fa fa-info')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','receive_product_receive_product_status')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->div_set('id','receive_product_div_receive_product_status')
                    ;
            
            $components['cancellation_reason']=$form->textarea_add()->textarea_set('label','Cencellation Reason')
                    ->textarea_set('id','receive_product_cancellation_reason')
                    ->textarea_set('value','')
                    ->div_set('id','receive_product_div_cancellation_reason')  
                    ;
            
            
            
            $table = $form->form_group_add()->table_add();
            $table->table_set('id','receive_product_purchase_invoice_add_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"product_id","label"=>"",'col_attrib'=>array('class'=>'hidden')));
            $table->table_set('columns',array("name"=>"product_img","label"=>"",'col_attrib'=>array('style'=>'text-align:center;width:100px')));
            $table->table_set('columns',array("name"=>"product_name","label"=>"Product",'col_attrib'=>array('style'=>'text-align:center')));
            $table->table_set('columns',array("name"=>"invoiced_qty","label"=>"Invoiced Qty",'col_attrib'=>array('style'=>'text-align:right')));
            $table->table_set('columns',array("name"=>"max_qty","label"=>"Available Qty",'col_attrib'=>array('style'=>'text-align:right')));
            $table->table_set('columns',array("name"=>"unit_id","label"=>"",'col_attrib'=>array('style'=>'text-align:center;display:none')));            
            $table->table_set('columns',array("name"=>"qty","label"=>"Qty",'col_attrib'=>array('style'=>'text-align:right')));
            $table->table_set('columns',array("name"=>"unit_name","label"=>"Unit",'col_attrib'=>array('style'=>'text-align:center')));
            
            $table = $form->form_group_add()->table_add();
            $table->table_set('id','receive_product_purchase_invoice_view_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px;text-align:center'),'attribute'=>'style="text-align:center"'));
            $table->table_set('columns',array("name"=>"product_img","label"=>"",'col_attrib'=>array('style'=>'text-align:left;width:100px;text-align:center')));
            $table->table_set('columns',array("name"=>"product_name","label"=>"Product",'col_attrib'=>array('style'=>'text-align:center')));
            $table->table_set('columns',array("name"=>"qty","label"=>"Qty",'col_attrib'=>array('style'=>'text-align:right;width:200px')));
            $table->table_set('columns',array("name"=>"unit_name","label"=>"Unit",'col_attrib'=>array('style'=>'text-align:center;width:200px;')));
            
            
            $table = $form->form_group_add()->table_add();
            $table->table_set('id','receive_product_rma_add_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"product_id","label"=>"",'col_attrib'=>array('class'=>'hidden')));
            $table->table_set('columns',array("name"=>"product_img","label"=>"",'col_attrib'=>array('style'=>'text-align:center;width:100px')));
            $table->table_set('columns',array("name"=>"product_name","label"=>"Product",'col_attrib'=>array('style'=>'text-align:center')));
            $table->table_set('columns',array("name"=>"rma_qty","label"=>"RMA Qty",'col_attrib'=>array('style'=>'text-align:right')));
            $table->table_set('columns',array("name"=>"max_qty","label"=>"Available Qty",'col_attrib'=>array('style'=>'text-align:right')));
            $table->table_set('columns',array("name"=>"unit_id","label"=>"",'col_attrib'=>array('style'=>'text-align:center;display:none')));            
            $table->table_set('columns',array("name"=>"qty","label"=>"Qty",'col_attrib'=>array('style'=>'text-align:right')));
            $table->table_set('columns',array("name"=>"unit_name","label"=>"Unit",'col_attrib'=>array('style'=>'text-align:center')));
            
            $table = $form->form_group_add()->table_add();
            $table->table_set('id','receive_product_rma_view_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px;text-align:center'),'attribute'=>'style="text-align:center"'));
            $table->table_set('columns',array("name"=>"product_img","label"=>"",'col_attrib'=>array('style'=>'text-align:left;width:100px;text-align:center')));
            $table->table_set('columns',array("name"=>"product_name","label"=>"Product",'col_attrib'=>array('style'=>'text-align:center')));
            $table->table_set('columns',array("name"=>"qty","label"=>"Qty",'col_attrib'=>array('style'=>'text-align:right;width:200px')));
            $table->table_set('columns',array("name"=>"unit_name","label"=>"Unit",'col_attrib'=>array('style'=>'text-align:center;width:200px;')));
            
            
            $form->textarea_add()->textarea_set('label','Notes')
                    ->textarea_set('id','receive_product_notes')
                    ->textarea_set('value','')
                    ->textarea_set('attrib',array())       
                    ->div_set('id','receive_product_div_notes')
                    ;
            
            
            $form->hr_add()->hr_set('class','');
            
            $form->button_add()->button_set('value','Submit')
                            ->button_set('id','receive_product_submit')
                            ->button_set('icon',App_Icon::detail_btn_save())
                        ;
            
            $form->button_add()->button_set('value','Print')
                            ->button_set('id','receive_product_print')
                            ->button_set('icon',App_Icon::printer())
                            ->button_set('class','btn btn-default pull-right')
                            ->button_set('disable_after_click',false)
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
                $param['detail_tab'] = '#modal_receive_product .modal-body';
                $param['view_url'] = '';
                $param['window_scroll'] = '#modal_receive_product';
            }
            
            $js = get_instance()->load->view('receive_product/receive_product_basic_function_js',$param,TRUE);
            $app->js_set($js);
            $js = get_instance()->load->view('receive_product/receive_product_purchase_invoice_js',$param,TRUE);
            $app->js_set($js);
            $js = get_instance()->load->view('receive_product/receive_product_rma_js',$param,TRUE);
            $app->js_set($js);
            return $components;
            
        }
        
        public static function receive_product_status_log_render($app,$form,$data,$path){
            get_instance()->load->helper('receive_product/receive_product_engine');
            $path = Receive_Product_Engine::path_get();
            get_instance()->load->helper($path->receive_product_purchase_invoice_engine);
            get_instance()->load->helper($path->receive_product_rma_engine);
            
            $id = $data['id'];
            $db = new DB();
            $q = '
                select null row_num
                    ,t1.moddate
                    ,t1.receive_product_status
                    ,t2.name user_name
                    ,case when t3.id is null then 0 else 1 end is_purchase_invoice
                    ,case when t4.id is null then 0 else 1 end is_rma
                from receive_product_status_log t1
                    inner join user_login t2 on t1.modid = t2.id
                    left outer join purchase_invoice_receive_product t3 
                        on t3.receive_product_id = t1.receive_product_id
                    left outer join rma_receive_product t4
                        on t4.receive_product_id = t1.receive_product_id
                where t1.receive_product_id = '.$id.'
                    order by moddate asc
            ';
            $rs = $db->query_array($q);
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                $receive_product_status_name = '';
                if($rs[$i]['is_purchase_invoice'] === '1'){
                    $receive_product_status_name = SI::get_status_attr(
                        Receive_Product_Purchase_Invoice_Engine::receive_product_purchase_invoice_status_get(
                            $rs[$i]['receive_product_status']
                        )['label']
                    );
                }
                if($rs[$i]['is_rma'] === '1'){
                    $receive_product_status_name = SI::get_status_attr(
                        Receive_Product_RMA_Engine::receive_product_rma_status_get(
                            $rs[$i]['receive_product_status']
                        )['label']
                    );
                }
                
                $rs[$i]['receive_product_status_name'] = $receive_product_status_name;
                        
                
            }
            $receive_product_status_log = $rs;
            
            $table = $form->form_group_add()->table_add();
            $table->table_set('id','receive_product_receive_product_status_log_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'col_attrib'=>array('style'=>'text-align:center')));
            $table->table_set('columns',array("name"=>"receive_product_status_name","label"=>"Status",'col_attrib'=>array('style'=>'text-align:center')));
            $table->table_set('columns',array("name"=>"user_name","label"=>"User",'col_attrib'=>array('style'=>'text-align:center')));
            $table->table_set('data',$receive_product_status_log);
        }
        
    }
    
?>