<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Stock_Transfer_Renderer {
        
        public static function modal_stock_transfer_render($app,$modal){
            $modal->header_set(array('title'=>'Stock Transfer','icon'=>App_Icon::stock_transfer()));
            $components = self::stock_transfer_components_render($app, $modal,true);
        }
        
        public static function stock_transfer_render($app,$form,$data,$path,$method){
            //<editor-fold defaultstsate="collapsed">
            get_instance()->load->helper('stock_transfer/stock_transfer_Engine');
            $path = stock_transfer_Engine::path_get();
            $id = $data['id'];
            $components = self::stock_transfer_components_render($app, $form,false);
            $back_href = $path->index;
            
            $form->button_add()->button_set('value','BACK')
                ->button_set('icon',App_Icon::btn_back())
                ->button_set('href',$back_href)
                ->button_set('class','btn btn-default')
                ;

            
            $modal_customer = $app->engine->modal_add()->id_set('modal_customer')
                ->width_set('75%')
                ->footer_attr_set(array('style'=>'display:none'));
            get_instance()->load->helper('customer/customer_renderer');
            Customer_Renderer::modal_customer_render($app, $modal_customer);
            $app->js_set('
                <script>
                    customer_init();            
                    customer_bind_event();
                </script>    
            ');
            
            $js = '
                <script>
                    $("#stock_transfer_method").val("'.$method.'");
                    $("#stock_transfer_id").val("'.$id.'");
                </script>
            ';             
            $app->js_set($js);
            
            $js = '                
                    stock_transfer_init();
                    stock_transfer_bind_event();
                    stock_transfer_components_prepare(); 
            ';
            $app->js_set($js);
            //</editor-fold>
        }
        
        public static function stock_transfer_components_render($app,$form,$is_modal){
            //<editor-fold defaultstate="collapsed">
            get_instance()->load->helper('stock_transfer/stock_transfer_Engine');
            $path = stock_transfer_Engine::path_get();            
            $components = array();
            $db = new DB();
            
            $id_prefix = 'stock_transfer';
            
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
            
            $form->datetimepicker_add()->datetimepicker_set('label',Lang::get(array('Stock Transfer','Date')))
                    ->datetimepicker_set('id',$id_prefix.'_stock_transfer_date')
                    ->datetimepicker_set('value','') 
                    ->datetimepicker_set('disable_all',true)
                    ->datetimepicker_set('hide_all',true)
                ;
            
            $form->input_add()->input_set('label',Lang::get('Requestor Name'))
                    ->input_set('id',$id_prefix.'_requestor_name')
                    ->input_set('icon','fa fa-user')
                    ->input_set('attrib',array('disabled'=>''))
                    ->input_set('value','')
                    ->input_set('disable_all',true)
                ;
            
            $form->input_select_add()
                ->input_select_set('label',Lang::get('From Warehouse'))
                ->input_select_set('icon',App_Icon::warehouse())
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_warehouse_from')
                ->input_select_set('value',array())
                ->input_select_set('disable_all',true)
                ->input_select_set('ajax_url',$path->ajax_search.'input_select_warehouse_search')
            ;
            
            $form->input_select_add()
                ->input_select_set('label',Lang::get('To Warehouse'))
                ->input_select_set('icon',App_Icon::warehouse())
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_warehouse_to')
                ->input_select_set('value',array())
                ->input_select_set('ajax_url',$path->ajax_search.'input_select_warehouse_search')
                ->input_select_set('disable_all',true)
            ;
            
            $form->input_select_add()
                ->input_select_set('label','Status')
                ->input_select_set('icon','fa fa-info')
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_stock_transfer_status')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('is_module_status',true)
                ->input_select_set('hide_all',true)                    
                ;
            
            
            
            $table = $form->form_group_add()->table_add();
            $table->table_set('id',$id_prefix.'_registered_product_table');
            $table->table_set('class','table fixed-table');
            $table->div_set('label','Registered Product');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"product_id","label"=>"",'col_attrib'=>array('class'=>'hidden')));
            $table->table_set('columns',array("name"=>"product_img","label"=>"",'header_class'=>'product-img','col_attrib'=>array('style'=>'')));
            $table->table_set('columns',array("name"=>"product_name","label"=>"Product",'col_attrib'=>array('style'=>'')));
            $table->table_set('columns',array("name"=>"unit_id","label"=>"",'col_attrib'=>array('style'=>'text-align:center;display:none')));            
            $table->table_set('columns',array("name"=>"unit_name","label"=>"Unit",'col_attrib'=>array('style'=>'width:150px')));
            $table->table_set('columns',array("name"=>"stock_qty","label"=>"Stock Qty",'col_attrib'=>array('style'=>'text-align:right;width:150px')));
            $table->table_set('columns',array("name"=>"qty","label"=>"Qty",'col_attrib'=>array('style'=>'text-align:right')));
            $table->table_set('columns',array("name"=>"action","label"=>"",'header_class'=>'table-action','col_attrib'=>array('style'=>'text-align:center;')));

            $table->table_set('hide_all',true);
            
            $form->textarea_add()->textarea_set('label','Notes')
                    ->textarea_set('id','stock_transfer_notes')
                    ->textarea_set('value','')
                    ->textarea_set('attrib',array())       
                    ->textarea_set('disable_all',true)
                    
                    ;
                        
            $form->hr_add()->hr_set('class','');
            
            $form->button_add()->button_set('value','Submit')
                            ->button_set('id',$id_prefix.'_submit')
                            ->button_set('icon',App_Icon::detail_btn_save())
                        ;
            
            $form->button_add()
                    ->button_set('class','btn btn-default pull-right')
                    ->button_set('icon',APP_ICON::printer())
                    ->button_set('value','PRINT')
                    ->button_set('id',$id_prefix.'_btn_print')
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
            
            $js = get_instance()->load->view('stock_transfer/'.$id_prefix.'_basic_function_js',$param,TRUE);
            $app->js_set($js);
            return $components;
            //</editor-fold>
        }
        
        public static function stock_transfer_status_log_render($app,$form,$data,$path){
            //<editor-fold defaultstate="collapsed">
            $config=array(
                'module_name'=>'stock_transfer',
                'module_engine'=>'stock_transfer_Engine',
                'id'=>$data['id']
            );
            SI::form_renderer()->status_log_tab_render($form, $config);
            //</editor-fold>
        }
        
    }
    
?>