<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Refill_Subcon_Work_Order_Renderer {
        
        public static function modal_refill_subcon_work_order_render($app,$modal){
            $modal->header_set(array('title'=>'Refill Receipt','icon'=>App_Icon::refill_subcon_work_order()));
            $components = self::refill_subcon_work_order_components_render($app, $modal,true);
        }
        
        public static function refill_subcon_work_order_render($app,$form,$data,$path,$method){
            get_instance()->load->helper('refill_subcon_work_order/refill_subcon_work_order_engine');
            $path = Refill_Subcon_Work_Order_Engine::path_get();
            $id = $data['id'];
            $components = self::refill_subcon_work_order_components_render($app, $form,false);
            $back_href = $path->index;
            
            $form->button_add()->button_set('value','BACK')
                ->button_set('icon',App_Icon::btn_back())
                ->button_set('href',$back_href)
                ->button_set('class','btn btn-default')
                ;

            $js = '
                <script>
                    $("#rswo_method").val("'.$method.'");
                    $("#rswo_id").val("'.$id.'");
                </script>
            ';             
            $app->js_set($js);
            
            $js = '                
                    rswo_init();
                    rswo_bind_event();
                    rswo_components_prepare(); 
            ';
            $app->js_set($js);
            
        }
        
        public static function refill_subcon_work_order_components_render($app,$form,$is_modal){
            
            get_instance()->load->helper('refill_subcon_work_order/refill_subcon_work_order_engine');
            $path = Refill_Subcon_Work_Order_Engine::path_get();            
            $components = array();
            $db = new DB();
            
            $id_prefix = Refill_Subcon_Work_Order_Engine::$prefix_id;
            
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
                    ->input_set('disable_all',true)
                ;
            
            $form->input_select_add()
                ->input_select_set('label','Refill Subcontractor')
                ->input_select_set('icon',APP_Icon::user())
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_refill_subcon')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('hide_all',true)                    
                ->input_select_set('disable_all',true)  
                ->input_select_set('ajax_url',$path->ajax_search.'input_select_refill_subcon_search/')
                ;
            
            $form->datetimepicker_add()->datetimepicker_set('label',Lang::get(array('Subcon Work Order','Date')))
                    ->datetimepicker_set('id',$id_prefix.'_refill_subcon_work_order_date')
                    ->datetimepicker_set('value',Tools::_date('','F d, Y H:i')) 
                    ->datetimepicker_set('disable_all',true)
                    ->datetimepicker_set('hide_all',true)
                ;
            
            
            $components['refill_subcon_work_order_status'] = $form->input_select_add()
                ->input_select_set('label','Status')
                ->input_select_set('icon','fa fa-info')
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_refill_subcon_work_order_status')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('is_module_status',true)
                ->input_select_set('module_prefix_id',$id_prefix)
                ->input_select_set('module_status_field','refill_subcon_work_order_status')
                ->input_select_set('hide_all',true)
                ;
            
            $table = $form->form_group_add()->table_add();
            $table->div_set('label','Product');
            $table->table_set('id',$id_prefix.'_product_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"product_type","label"=>"",'col_attrib'=>array('class'=>'hidden')));
            $table->table_set('columns',array("name"=>"product_id","label"=>"",'col_attrib'=>array('class'=>'hidden')));
            $table->table_set('columns',array("name"=>"product_img","label"=>"",'header_class'=>'product-img','col_attrib'=>array('style'=>'')));
            $table->table_set('columns',array("name"=>"product_name","label"=>"Product",'col_attrib'=>array('style'=>'')));
            $table->table_set('columns',array("name"=>"product_reference_type","label"=>"",'col_attrib'=>array('class'=>'hidden')));
            $table->table_set('columns',array("name"=>"product_reference_id","label"=>"",'col_attrib'=>array('class'=>'hidden')));
            $table->table_set('columns',array("name"=>"product_reference","label"=>"Product Reference",'col_attrib'=>array('style'=>'width:250px')));
            $table->table_set('columns',array("name"=>"unit_id","label"=>"",'col_attrib'=>array('style'=>'text-align:center;display:none')));            
            $table->table_set('columns',array("name"=>"unit_name","label"=>"Unit",'col_attrib'=>array('style'=>'width:150px')));
            $table->table_set('columns',array("name"=>"stock_qty","label"=>"Stock Qty",'col_attrib'=>array('style'=>'text-align:right;width:100px')));
            $table->table_set('columns',array("name"=>"qty","label"=>"Qty",'col_attrib'=>array('style'=>'text-align:right;width:100px')));
            $table->table_set('columns',array("name"=>"movement_outstanding_qty","label"=>Lang::get(array("Undelivered","<br/>","Qty"),true,true,false,false,false),'col_attrib'=>array('style'=>'text-align:right;width:100px')));
            $table->table_set('columns',array("name"=>"action","label"=>"",'header_class'=>'table-action','col_attrib'=>array('style'=>'text-align:center;')));


            $table = $form->form_group_add()->table_add();
            $table->div_set('label',Lang::get(array('Product Result','expectation')));
            $table->table_set('id',$id_prefix.'_expected_product_result_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"product_type","label"=>"",'col_attrib'=>array('class'=>'hidden')));
            $table->table_set('columns',array("name"=>"product_id","label"=>"",'col_attrib'=>array('class'=>'hidden')));
            $table->table_set('columns',array("name"=>"product_img","label"=>"",'header_class'=>'product-img','col_attrib'=>array('style'=>'')));
            $table->table_set('columns',array("name"=>"product_name","label"=>"Product",'col_attrib'=>array('style'=>'')));
            $table->table_set('columns',array("name"=>"unit_id","label"=>"",'col_attrib'=>array('style'=>'text-align:center;display:none')));            
            $table->table_set('columns',array("name"=>"unit_name","label"=>"Unit",'col_attrib'=>array('style'=>'width:150px')));
            $table->table_set('columns',array("name"=>"qty","label"=>"Qty",'col_attrib'=>array('style'=>'text-align:right;width:100px')));
            $table->table_set('columns',array("name"=>"movement_outstanding_qty","label"=>Lang::get(array("Unreceived","<br/>","Qty"),true,true,true,false,false),'col_attrib'=>array('style'=>'text-align:right;width:100px')));
            $table->table_set('columns',array("name"=>"action","label"=>"",'header_class'=>'table-action','col_attrib'=>array('style'=>'text-align:center;')));

            
            
            $form->textarea_add()->textarea_set('label','Notes')
                    ->textarea_set('id',$id_prefix.'_notes')
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
            
            $js = get_instance()->load->view('refill_subcon_work_order/'.$id_prefix.'_basic_function_js',$param,TRUE);
            $app->js_set($js);
            return $components;
            
        }
        
        public static function refill_subcon_work_order_status_log_render($app,$form,$data,$path){
            //<editor-fold defaultstate="collapsed">
            $config=array(
                'module_name'=>'refill_subcon_work_order',
                'module_engine'=>'Refill_Subcon_Work_Order_Engine',
                'id'=>$data['id']
            );
            SI::form_renderer()->status_log_tab_render($form, $config);
            //</editor-fold>
        }
        
        public static function delivery_order_view_render($app,$form,$data,$path){
            //<editor-fold defaultstate="collapsed">
            
            get_instance()->load->helper('delivery_order/delivery_order_engine');
            get_instance()->load->helper('delivery_order/delivery_order_renderer');
            $id = $data['id'];
            $db = new DB();
            $rs = $db->fast_get('refill_subcon_work_order',array('id'=>$id));
            if(count($rs)>0) {
                $rswo = $rs[0];            
                $form->form_group_add();
                if($rswo['refill_subcon_work_order_status'] != 'X'){
                    if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'delivery_order','add')){
                    $form->button_add()->button_set('class','primary')
                            ->button_set('value',Lang::get(array(array('val'=>'New','grammar'=>'adj'),array('val'=>'Delivery Order'))))
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
                $form->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
                $tbl = $form->table_add();
                $tbl->table_set('class','table');
                $tbl->table_set('id','delivery_order_view_table');
                $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
                $tbl->table_set('columns',array("name"=>"code","label"=>"Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center'),"is_key"=>true));
                $tbl->table_set('columns',array("name"=>"delivery_order_date","label"=>Lang::get(array('Delivery Order','Date')),'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"delivery_order_status_text","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('data key','id');

                $q = '
                    select distinct NULL row_num
                        ,do.*                        
                    from delivery_order do
                        inner join rswo_do on do.id = rswo_do.delivery_order_id
                    where rswo_do.refill_subcon_work_order_id = '.$db->escape($id).'
                    order by do.id desc
                ';
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){
                    $rs[$i]['row_num'] = $i+1;
                    
                    $rs[$i]['delivery_order_status_text'] = SI::get_status_attr(
                        SI::status_get('delivery_order_engine', $rs[$i]['delivery_order_status'])['label']
                    );
                }
                $tbl->table_set('data',$rs);                
                
                $modal_delivery_order = $app->engine->modal_add()->id_set('modal_delivery_order')
                        ->width_set('90%')
                        ->footer_attr_set(array('style'=>'display:none'));

                $delivery_order_data = array(
                    'refill_work_order'=>array(
                        'id'=>$rswo['id']
                    )                
                );
                $delivery_order_data = json_decode(json_encode($delivery_order_data));

                Delivery_Order_Renderer::modal_delivery_order_render(
                        $app
                        ,$modal_delivery_order
                    );


                $param = array(
                    'index_url'=>$path->index
                    ,'ajax_search'=>$path->ajax_search
                    ,'reference_id'=>$rswo['id']
                    ,'reference_text'=>$rswo['code']
                    ,'reference_type'=>'refill_subcon_work_order'
                );
                
                $js = get_instance()->load->view('refill_subcon_work_order/delivery_order_js',$param,TRUE);
                $app->js_set($js);
                
            }
            //</editor-fold>
        }
        
        public static function receive_product_view_render($app,$form,$data,$path){
            //<editor-fold defaultstate="collapsed">
            
            get_instance()->load->helper('receive_product/receive_product_engine');
            get_instance()->load->helper('receive_product/receive_product_renderer');
            $id = $data['id'];
            $db = new DB();
            $rs = $db->fast_get('refill_subcon_work_order',array('id'=>$id));
            if(count($rs)>0) {
                $rswo = $rs[0];            
                $form->form_group_add();
                if($rswo['refill_subcon_work_order_status'] != 'X'){
                    if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'receive_product','add')){
                    $form->button_add()->button_set('class','primary')
                            ->button_set('value',Lang::get(array(array('val'=>'New','grammar'=>'adj'),array('val'=>'Receive Product'))))
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
                $tbl->table_set('columns',array("name"=>"code","label"=>"Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center'),"is_key"=>true));
                $tbl->table_set('columns',array("name"=>"receive_product_date","label"=>Lang::get(array('Receive Product','Date')),'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"receive_product_status_text","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('data key','id');

                $q = '
                    select distinct NULL row_num
                        ,rp.*                        
                    from receive_product rp
                        inner join rswo_rp on rp.id = rswo_rp.receive_product_id
                    where rswo_rp.refill_subcon_work_order_id = '.$db->escape($id).'
                    order by rp.id desc
                ';
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){
                    $rs[$i]['row_num'] = $i+1;
                    
                    $rs[$i]['receive_product_status_text'] = SI::get_status_attr(
                        SI::status_get('receive_product_engine', $rs[$i]['receive_product_status'])['label']
                    );
                }
                $tbl->table_set('data',$rs);                
                
                $modal_receive_product = $app->engine->modal_add()->id_set('modal_receive_product')
                        ->width_set('90%')
                        ->footer_attr_set(array('style'=>'display:none'));

                $receive_product_data = array(
                    'refill_work_order'=>array(
                        'id'=>$rswo['id']
                    )                
                );
                $receive_product_data = json_decode(json_encode($receive_product_data));

                Receive_Product_Renderer::modal_receive_product_render(
                        $app
                        ,$modal_receive_product
                    );


                $param = array(
                    'index_url'=>$path->index
                    ,'ajax_search'=>$path->ajax_search
                    ,'reference_id'=>$rswo['id']
                    ,'reference_text'=>$rswo['code']
                    ,'reference_type'=>'refill_subcon_work_order'
                );
                
                $js = get_instance()->load->view('refill_subcon_work_order/receive_product_js',$param,TRUE);
                $app->js_set($js);
                
            }
            //</editor-fold>
        }
    }
    
?>