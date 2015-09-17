<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Product_Stock_Opname_Renderer {
        
        public static function modal_product_stock_opname_render($app,$modal){
            $modal->header_set(array('title'=>Lang::get(array('Refill - ','Checking Result Form')),'icon'=>App_Icon::product_stock_opname()));
            $components = self::product_stock_opname_components_render($app, $modal,true);
        }
        
        public static function product_stock_opname_render($app,$form,$data,$path,$method){
            get_instance()->load->helper('product_stock_opname/product_stock_opname_engine');
            $path = Product_Stock_Opname_Engine::path_get();
            $id = $data['id'];
            $components = self::product_stock_opname_components_render($app, $form,false);
            $back_href = $path->index;
            
            $form->button_add()->button_set('value','BACK')
                ->button_set('icon',App_Icon::btn_back())
                ->button_set('href',$back_href)
                ->button_set('class','btn btn-default')
                ;

            $js = '
                <script>
                    $("#pso_method").val("'.$method.'");
                    $("#pso_id").val("'.$id.'");
                </script>
            ';             
            $app->js_set($js);
            
            $js = '                
                    pso_init();
                    pso_bind_event();
                    pso_components_prepare(); 
            ';
            $app->js_set($js);
            
        }
        
        public static function product_stock_opname_components_render($app,$form,$is_modal){
            
            get_instance()->load->helper('product_stock_opname/product_stock_opname_engine');
            $path = Product_Stock_Opname_Engine::path_get();            
            $components = array();
            $db = new DB();
            
            $id_prefix = Product_Stock_Opname_Engine::$prefix_id;
            
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
            
            $form->datetimepicker_add()->datetimepicker_set('label',Lang::get(array('Product Stock Opname','Date')))
                ->datetimepicker_set('id',$id_prefix.'_product_stock_opname_date')
                ->datetimepicker_set('value',Tools::_date('','F d, Y H:i')) 
                ->datetimepicker_set('disable_all',true)
                ->datetimepicker_set('hide_all',true)
            ;
            
            $warehouse_list = array();
            $warehouse_list_raw = Warehouse_Engine::BOS_get();
            foreach($warehouse_list_raw as $idx=>$row){
                $warehouse_list[] = array(
                    'id'=>$row['id'],
                    'data'=>SI::html_tag('strong',$row['code']).' '.$row['name']
                );
                
            }
            
            $warehouse_list = array_merge($warehouse_list);
            
            $form->input_add()->input_set('label',Lang::get('Checker'))
                ->input_set('id',$id_prefix.'_checker')
                ->input_set('icon','fa fa-user')
                ->input_set('hide_all',true)
                ->input_set('disable_all',true)
                ;
            
            $form->input_select_add()
                ->input_select_set('label',Lang::get('Warehosue'))
                ->input_select_set('icon',App_Icon::warehouse())
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_warehouse')
                ->input_select_set('data_add',$warehouse_list)
                ->input_select_set('value',array())
                ->input_select_set('disable_all',true)
                ->input_select_set('hide_all',true)
                ;
            
            $components[$id_prefix.'_status'] = $form->input_select_add()
                ->input_select_set('label','Status')
                ->input_select_set('icon','fa fa-info')
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_product_stock_opname_status')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('hide_all',true)
                ->input_select_set('is_module_status',true)
                ;
            
            $product_tbl = $form->table_input_add();
            $product_tbl->table_input_set('id',$id_prefix.'_product_table')
            ->label_set('value',Lang::get(array('Product')))
            ->table_input_set('columns',array(
                'col_name'=>'product_type'
                ,'th'=>array('val'=>'','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'div','class'=>'','visible'=>false
                )
            ))
            ->table_input_set('columns',array(
                'col_name'=>'product','col_id_exists'=>true
                ,'th'=>array('val'=>'Product')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'','attr'=>array('original'=>'')
                )
            ))
            ->table_input_set('columns',array(
                'col_name'=>'unit','col_id_exists'=>true
                ,'th'=>array('val'=>'Unit','col_style'=>'width:100px')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'','attr'=>array('original'=>''))
            ))
            ->table_input_set('columns',array(
                'col_name'=>'outstanding_qty_old'
                ,'th'=>array('val'=>Lang::get(array('Old Outstd.')),'col_style'=>'width:100px;text-align:right','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'div','class'=>'','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right','visible'=>false)
            ))
            ->table_input_set('columns',array(
                'col_name'=>'outstanding_qty'
                ,'th'=>array('val'=>Lang::get(array('Outstanding')),'col_style'=>'width:100px;text-align:right')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'form-control','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right')
            ))            
            ->table_input_set('columns',array(
                'col_name'=>'floor_1_qty'
                ,'th'=>array('val'=>Lang::get(array('Lt 1')),'col_style'=>'width:75px;text-align:right')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'form-control','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right')
            ))
            ->table_input_set('columns',array(
                'col_name'=>'floor_2_qty'
                ,'th'=>array('val'=>Lang::get(array('Lt 2')),'col_style'=>'width:75px;text-align:right')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'form-control','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right')
            ))
            ->table_input_set('columns',array(
                'col_name'=>'floor_3_qty'
                ,'th'=>array('val'=>Lang::get(array('Lt 3')),'col_style'=>'width:75px;text-align:right')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'form-control','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right')
            ))
            ->table_input_set('columns',array(
                'col_name'=>'floor_4_qty'
                ,'th'=>array('val'=>Lang::get(array('Lt 4')),'col_style'=>'width:75px;text-align:right')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'form-control','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right')
            ))
            ->table_input_set('columns',array(
                'col_name'=>'total_qty_old'
                ,'th'=>array('val'=>Lang::get(array('Old Total')),'col_style'=>'width:100px;text-align:right','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'div','class'=>'','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right','visible'=>false)
            ))
            ->table_input_set('columns',array(
                'col_name'=>'total_qty'
                ,'th'=>array('val'=>Lang::get(array('Total')),'col_style'=>'width:100px;text-align:right')
                ,'td'=>array('val'=>'0.00','tag'=>'div','class'=>'','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right')
            ))            
            ->table_input_set('columns',array(
                'col_name'=>'stock_bad_qty_old'
                ,'th'=>array('val'=>Lang::get(array('Old BS')),'col_style'=>'width:100px;text-align:right','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'div','class'=>'','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right','visible'=>false)
            ))
            ->table_input_set('columns',array(
                'col_name'=>'stock_bad_qty'
                ,'th'=>array('val'=>Lang::get(array('Bad Stock')),'col_style'=>'width:100px;text-align:right')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'form-control','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right')
            ))
            
            ->table_input_set('new_row',true)
            ;
            
            $form->textarea_add()->textarea_set('label','Description')
                    ->textarea_set('id',$id_prefix.'_description')
                    ->textarea_set('value','')
                    ->textarea_set('attrib',array())       
                    ->textarea_set('disable_all',true)
                    ->textarea_set('hide_all',true)
                    ;
            
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
            
            $form->button_add()
                    ->button_set('class','btn btn-default pull-right')
                    ->button_set('icon',APP_ICON::printer())
                    ->button_set('value','PRINT')
                    ->button_set('id',$id_prefix.'_print')
                    ->button_set('style','margin-left:5px')
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
            
            $js = get_instance()->load->view('product_stock_opname/'.$id_prefix.'_product_js',$param,TRUE);
            $app->js_set($js);            
            $js = get_instance()->load->view('product_stock_opname/'.$id_prefix.'_basic_function_js',$param,TRUE);
            $app->js_set($js);
            return $components;
            
        }
        
        public static function product_stock_opname_status_log_render($app,$form,$data,$path){
            //<editor-fold defaultstate="collapsed">
            $config=array(
                'module_name'=>'product_stock_opname',
                'module_engine'=>'Product_Stock_Opname_Engine',
                'id'=>$data['id']
            );
            SI::form_renderer()->status_log_tab_render($form, $config);
            //</editor-fold>
        }
        
    }
    
?>