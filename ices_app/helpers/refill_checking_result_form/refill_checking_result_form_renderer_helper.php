<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Refill_Checking_Result_Form_Renderer {
        
        public static function modal_refill_checking_result_form_render($app,$modal){
            $modal->header_set(array('title'=>Lang::get(array('Refill - ','Checking Result Form')),'icon'=>App_Icon::refill_checking_result_form()));
            $components = self::refill_checking_result_form_components_render($app, $modal,true);
        }
        
        public static function refill_checking_result_form_render($app,$form,$data,$path,$method){
            get_instance()->load->helper('refill_checking_result_form/refill_checking_result_form_engine');
            $path = Refill_Checking_Result_Form_Engine::path_get();
            $id = $data['id'];
            $components = self::refill_checking_result_form_components_render($app, $form,false);
            $back_href = $path->index;
            
            $form->button_add()->button_set('value','BACK')
                ->button_set('icon',App_Icon::btn_back())
                ->button_set('href',$back_href)
                ->button_set('class','btn btn-default')
                ;

            $js = '
                <script>
                    $("#rcrf_method").val("'.$method.'");
                    $("#rcrf_id").val("'.$id.'");
                </script>
            ';             
            $app->js_set($js);
            
            $js = '                
                    rcrf_init();
                    rcrf_bind_event();
                    rcrf_components_prepare(); 
            ';
            $app->js_set($js);
            
        }
        
        public static function refill_checking_result_form_components_render($app,$form,$is_modal){
            
            get_instance()->load->helper('refill_checking_result_form/refill_checking_result_form_engine');
            $path = Refill_Checking_Result_Form_Engine::path_get();            
            $components = array();
            $db = new DB();
            
            $id_prefix = Refill_Checking_Result_Form_Engine::$prefix_id;
            
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
                        
            $form->datetimepicker_add()->datetimepicker_set('label',Lang::get(array('Checking Result Form','Date')))
                    ->datetimepicker_set('id',$id_prefix.'_refill_checking_result_form_date')
                    ->datetimepicker_set('value',Tools::_date('','F d, Y H:i')) 
                    ->datetimepicker_set('disable_all',true)
                    ->datetimepicker_set('hide_all',true)
                ;
            
            
            $form->input_add()->input_set('label',Lang::get('Checker'))
                    ->input_set('id',$id_prefix.'_checker')
                    ->input_set('icon','fa fa-user')
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            $components['refill_checking_result_form_status'] = $form->input_select_add()
                ->input_select_set('label','Status')
                ->input_select_set('icon','fa fa-info')
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_refill_checking_result_form_status')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('is_module_status',true)
                ->input_select_set('module_prefix_id',$id_prefix)
                ->input_select_set('module_status_field','refill_checking_result_form_status')
                ->input_select_set('hide_all',true)
                ;
            
            $table = $form->form_group_add()->table_add();
            $table->div_set('label','Product');
            $table->table_set('id',$id_prefix.'_product_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"product_type","label"=>"",'col_attrib'=>array('class'=>'hidden')));
            $table->table_set('columns',array("name"=>"product_id","label"=>"",'col_attrib'=>array('class'=>'hidden')));
            $table->table_set('columns',array("name"=>"product_marking_code","label"=>"Marking Code",'col_attrib'=>array('style'=>'text-align:left;width:225px')));
            $table->table_set('columns',array("name"=>"product_info","label"=>"Product Info",'col_attrib'=>array('style'=>'text-align:left;')));
            $table->table_set('columns',array("name"=>"estimated_amount","label"=>"Estimated Amount",'col_attrib'=>array('style'=>'text-align:right;width:150px')));
            $table->table_set('columns',array("name"=>"amount","label"=>"Amount",'col_attrib'=>array('style'=>'text-align:right;width:150px')));
            $table->table_set('columns',array("name"=>"action","label"=>"",'col_attrib'=>array('class'=>'table-action')));
            
            
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
                        
            $product_condition = array();
            foreach(Refill_Checking_Result_Form_Engine::$product_condition as $row){
                $product_condition[] = array(
                    'id'=>$row['val'],
                    'text'=>$row['label']
                );
            }
            
            $param = array(
                'ajax_url'=>$path->index.'ajax_search/'
                ,'index_url'=>$path->index
                ,'detail_tab'=>'#detail_tab'
                ,'view_url'=>$path->index.'view/'
                ,'window_scroll'=>'body'
                ,'data_support_url'=>$path->index.'data_support/'
                ,'common_ajax_listener'=>get_instance()->config->base_url().'common_ajax_listener/'
                ,'component_prefix_id'=>$id_prefix
                ,'product_condition'=>$product_condition
            );
            
            
            
            if($is_modal){
                $param['detail_tab'] = '#modal_'.$id_prefix.' .modal-body';
                $param['view_url'] = '';
                $param['window_scroll'] = '#modal_'.$id_prefix;
            }
            
            $js = get_instance()->load->view('refill_checking_result_form/'.$id_prefix.'_basic_function_js',$param,TRUE);
            $app->js_set($js);
            return $components;
            
        }
        
        public static function refill_checking_result_form_status_log_render($app,$form,$data,$path){
            //<editor-fold defaultstate="collapsed">
            $config=array(
                'module_name'=>'refill_checking_result_form',
                'module_engine'=>'Refill_Checking_Result_Form_Engine',
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
            $rs = $db->fast_get('refill_checking_result_form',array('id'=>$id));
            if(count($rs)>0) {
                $rcrf = $rs[0];            
                $form->form_group_add();
                if($rcrf['refill_checking_result_form_status'] != 'X'){
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
                        inner join rcrf_do on do.id = rcrf_do.delivery_order_id
                    where rcrf_do.refill_checking_result_form_id = '.$db->escape($id).'
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
                        'id'=>$rcrf['id']
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
                    ,'reference_id'=>$rcrf['id']
                    ,'reference_text'=>$rcrf['code']
                    ,'reference_type'=>'refill_checking_result_form'
                );
                
                $js = get_instance()->load->view('refill_checking_result_form/delivery_order_js',$param,TRUE);
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
            $rs = $db->fast_get('refill_checking_result_form',array('id'=>$id));
            if(count($rs)>0) {
                $rcrf = $rs[0];            
                $form->form_group_add();
                if($rcrf['refill_checking_result_form_status'] != 'X'){
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
                        inner join rcrf_rp on rp.id = rcrf_rp.receive_product_id
                    where rcrf_rp.refill_checking_result_form_id = '.$db->escape($id).'
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
                        'id'=>$rcrf['id']
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
                    ,'reference_id'=>$rcrf['id']
                    ,'reference_text'=>$rcrf['code']
                    ,'reference_type'=>'refill_checking_result_form'
                );
                
                $js = get_instance()->load->view('refill_checking_result_form/receive_product_js',$param,TRUE);
                $app->js_set($js);
                
            }
            //</editor-fold>
        }
    }
    
?>