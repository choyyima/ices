<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mf_Work_Process_Renderer {

    public static function modal_mf_work_process_render($app,$modal){
        $modal->header_set(array('title'=>Lang::get('Manufacturing Work Process'),'icon'=>App_Icon::mf_work_process()));
        $modal->width_set('95%');
        $components = self::mf_work_process_components_render($app, $modal,true);


    }

    public static function mf_work_process_render($app,$form,$data,$path,$method){
        get_instance()->load->helper('mf_work_process/mf_work_process_engine');
        $path = Mf_Work_Process_Engine::path_get();
        $id = $data['id'];
        $components = self::mf_work_process_components_render($app, $form,false);
        $back_href = $path->index;

        $form->button_add()->button_set('value','BACK')
            ->button_set('icon',App_Icon::btn_back())
            ->button_set('href',$back_href)
            ->button_set('class','btn btn-default')
            ;

        $js = '
            <script>
                $("#mf_work_process_method").val("'.$method.'");
                $("#mf_work_process_id").val("'.$id.'");
            </script>
        ';             
        $app->js_set($js);

        $js = '                
                mf_work_process_init();
                mf_work_process_bind_event();
                mf_work_process_components_prepare(); 
        ';
        $app->js_set($js);

    }

    public static function mf_work_process_components_render($app,$form,$is_modal){
        get_instance()->load->helper('product/product_engine');
        get_instance()->load->helper('mf_work_process/mf_work_process_engine');
        $path = Mf_Work_Process_Engine::path_get();            
        $components = array();
        $db = new DB();

        $id_prefix = Mf_Work_Process_Engine::$prefix_id;

        $components['id'] = $form->input_add()->input_set('id',$id_prefix.'_id')
                ->input_set('hide',true)
                ->input_set('value','')
                ;


        $form->input_add()->input_set('id',$id_prefix.'_method')
                ->input_set('hide',true)
                ->input_set('value','')
                ;            

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
                    ->input_select_set('allow_empty',false)
                ;
        
        $form->input_add()->input_set('label',Lang::get('Code'))
                ->input_set('id',$id_prefix.'_code')
                ->input_set('icon','fa fa-info')
                ->input_set('hide_all',true)
                ->input_set('disable_all',true)
                ->input_set('attrib',array('style'=>'font-weight:bold'))

            ;

        $form->input_add()->input_set('id',$id_prefix.'_type')
                ->input_set('hide',true)
                ->input_set('value','')
                ;
        
        $form->input_select_detail_add()
            ->input_select_set('label',Lang::get('Reference'))
            ->input_select_set('icon',App_Icon::info())
            ->input_select_set('min_length','0')
            ->input_select_set('id',$id_prefix.'_reference')
            ->input_select_set('data_add',array())
            ->input_select_set('ajax_url',$path->ajax_search.'input_select_reference_search')
            ->input_select_set('allow_empty',false)
            ->input_select_set('value',array())
            ->input_select_set('disable_all',true)
            ->detail_set('id',$id_prefix.'_reference_detail')
        ;
        
        $raw_warehouse = Warehouse_Engine::BOS_get();
        $warehouse_list = array();
        foreach($raw_warehouse as $i => $row){
            $warehouse_list[] = array(
                'id'=>$row['id'],
                'data'=>SI::html_tag('strong',$row['code']).' '.$row['name']
            );
        }
        
        $form->input_select_add()
                    ->input_select_set('label',Lang::get('Warehouse'))
                    ->input_select_set('icon',App_Icon::warehouse())
                    ->input_select_set('min_length','0')
                    ->input_select_set('id',$id_prefix.'_warehouse')
                    ->input_select_set('data_add',$warehouse_list)
                    ->input_select_set('value',$warehouse_list[0])
                    ->input_select_set('disable_all',true)
                    ->input_select_set('hide_all',true)
                    ->input_select_set('allow_empty',false)
                ;
        
        $form->datetimepicker_add()->datetimepicker_set('label',Lang::get(array('Start','Date')))
            ->datetimepicker_set('id',$id_prefix.'_start_date')
            ->datetimepicker_set('value',Tools::_date('','F d, Y H:i')) 
            ->datetimepicker_set('disable_all',true)
            ->datetimepicker_set('hide_all',true)
        ;
        
        $form->datetimepicker_add()->datetimepicker_set('label',Lang::get(array('End','Date')))
            ->datetimepicker_set('id',$id_prefix.'_end_date')
            ->datetimepicker_set('value',Tools::_date('','F d, Y H:i')) 
            ->datetimepicker_set('disable_all',true)
            ->datetimepicker_set('hide_all',true)
        ;
        
        $components[$id_prefix.'_status'] = $form->input_select_add()
                ->input_select_set('label','Status')
                ->input_select_set('icon','fa fa-info')
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_mf_work_process_status')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('hide_all',true)
                ->input_select_set('is_module_status',true)
                ;
        
        $tbl_worker = $form->table_input_add();
        $tbl_worker->table_input_set('id',$id_prefix.'_worker_table')
            ->label_set('value',Lang::get(array('Worker')))
            ->table_input_set('columns',array(
                'col_name'=>'name','col_id_exists'=>false
                ,'th'=>array('val'=>'Name')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'form-control','attr'=>array()
                )
            ))     
            ->main_div_set('hide_all',true)
            ;
                
        
        $form->input_add()->input_set('label',Lang::get('Checker'))
            ->input_set('id',$id_prefix.'_checker')
            ->input_set('icon','fa fa-user')
            ->input_set('hide_all',true)
            ->input_set('disable_all',true)
        ;
        
        $form->comp_sir_add()
            ->input_select_set('id',$id_prefix.'_sir')
            ->input_select_set('hide_all',true)
            ->detail_set('module_name',array('val'=>'mf_work_process','label'=>Lang::get('Manufacturing - Work Process')))
            ->detail_set('module_action',array('val'=>'free_rules','label'=>Lang::get('Free Rules')))
        ;
        
        
        $modal_expected_result_product = $app->engine->modal_add()
                ->width_set('95%')
                ->id_set($id_prefix.'_modal_expected_result_product')
                ->modal_button_footer_add($id_prefix.'_modal_expected_result_product_btn_submit','button','btn btn-primary pull-left',  App_Icon::submit(),'Submit')
                ->modal_button_footer_add($id_prefix.'_modal_expected_result_product_btn_cancel','button','btn btn-default pull-left',  App_Icon::cancel(),'Cancel')
                ;
        $tbl_res_product = $modal_expected_result_product->table_input_add();
        $tbl_res_product->table_input_set('id',$id_prefix.'_expected_result_product_table')

            ->label_set('value',Lang::get(array('Expected','Result','Product')))
            ->table_input_set('columns',array(
                'col_name'=>'reference_type'
                ,'th'=>array('val'=>'','class'=>'','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'','visible'=>false
                )
            ))
            ->table_input_set('columns',array(
                'col_name'=>'reference_id'
                ,'th'=>array('val'=>'','class'=>'','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'','visible'=>false
                )
            ))
            ->table_input_set('columns',array(
                'col_name'=>'product_img'
                ,'th'=>array('val'=>'','class'=>'product-img')
                ,'td'=>array('val'=>Product_Engine::img_get(null),'tag'=>'','class'=>'','attr'=>array('original'=>'')
                )
            )) 
            ->table_input_set('columns',array(
                'col_name'=>'product_type'
                ,'th'=>array('val'=>'','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'','visible'=>false
                )
            ))
            ->table_input_set('columns',array(
               'col_name'=>'product_id'
               ,'th'=>array('val'=>'','visible'=>false)
               ,'td'=>array('val'=>'','tag'=>'span','class'=>'','visible'=>false
               )
           ))
            ->table_input_set('columns',array(
                'col_name'=>'product'
                ,'th'=>array('val'=>'Product')
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'','attr'=>array('original'=>'')
                )
            ))
            ->table_input_set('columns',array(
                   'col_name'=>'unit_id'
                   ,'th'=>array('val'=>'','visible'=>false)
                   ,'td'=>array('val'=>'','tag'=>'span','class'=>'','visible'=>false
                   )
               ))
            ->table_input_set('columns',array(
                'col_name'=>'unit'
                ,'th'=>array('val'=>'Unit','col_style'=>'width:100px')
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'','attr'=>array('original'=>''))
            ))
            ->table_input_set('columns',array(
                'col_name'=>'ordered_qty'
                ,'th'=>array('val'=>Lang::get(array('Ordered','Qty')),'col_style'=>'width:100px;text-align:right')
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right')
            ))
            ->table_input_set('columns',array(
                'col_name'=>'outstanding_qty'
                ,'th'=>array('val'=>Lang::get('Manufacturing Outstanding Qty'),'col_style'=>'width:100px;text-align:right')
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right')
            ))
                ->table_input_set('columns',array(
                'col_name'=>'max_qty'
                ,'th'=>array('val'=>Lang::get(array('Max. Qty')),'col_style'=>'width:100px;text-align:right')
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right')
            ))
            ->table_input_set('columns',array(
                'col_name'=>'qty'
                ,'th'=>array('val'=>'Qty','col_style'=>'width:150px;text-align:right')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'form-control','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right')
            ))
            ->table_input_set('columns',array(
                'col_name'=>'bom_id'
                ,'th'=>array('val'=>'','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'','visible'=>false
                )
            ))
            ->table_input_set('columns',array(
                'col_name'=>'bom'
                ,'th'=>array('val'=>'Bill of Material','col_style'=>'width:150px')
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'','attr'=>array('original'=>''))
            ))
            ->table_input_set('new_row',false)
            ;
        
        $form_group = $form->div_add()->div_set('class','form-group hide_all');
        $form_group->label_add()->label_set('value',Lang::get(array('Component','Product')));
        $form_group->button_add()//->button_set('value','Set '.Lang::get(array('Component','Product')))
                ->button_set('id',$id_prefix.'_btn_set_component_product')
                ->button_set('class','btn btn-default pull-right hide_all')
                ->button_set('disable_after_click',false)
                ;
        $tbl_comp_product = $form_group->table_input_add();
        $tbl_comp_product->table_input_set('id',$id_prefix.'_component_product_table')
            ->main_div_set('class','')
            ->label_set('value','')
            ->table_input_set('columns',array(
                'col_name'=>'product_img'
                ,'th'=>array('val'=>'','class'=>'product-img')
                ,'td'=>array('val'=>Product_Engine::img_get(null),'tag'=>'','class'=>'','attr'=>array('original'=>'')
                )
            )) 
            ->table_input_set('columns',array(
                'col_name'=>'product_type'
                ,'th'=>array('val'=>'','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'','visible'=>false
                )
            ))
            ->table_input_set('columns',array(
                'col_name'=>'product_id'
                ,'th'=>array('val'=>'','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'','visible'=>false
                )
            ))
            ->table_input_set('columns',array(
                'col_name'=>'product'
                ,'th'=>array('val'=>'Product')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'','attr'=>array('original'=>'')
                )
            ))
            ->table_input_set('columns',array(
                'col_name'=>'unit_id'
                ,'th'=>array('val'=>'','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'','visible'=>false
                )
            ))
            ->table_input_set('columns',array(
                'col_name'=>'unit'
                ,'th'=>array('val'=>'Unit','col_style'=>'width:200px')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'','attr'=>array('original'=>''))
            ))
            ->table_input_set('columns',array(
               'col_name'=>'stock_location_id'
               ,'th'=>array('val'=>'','visible'=>false)
               ,'td'=>array('val'=>'','tag'=>'span','class'=>'','visible'=>false
               )
           ))
            ->table_input_set('columns',array(
                'col_name'=>'stock_location'
                ,'th'=>array('val'=>'Stock Location','col_style'=>'width:200px')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'','attr'=>array('original'=>''))
            ))
            ->table_input_set('columns',array(
                'col_name'=>'warehouse_stock_qty'
                ,'th'=>array('val'=>Lang::get(array('Warehouse','Stock','Qty')),'col_style'=>'width:150px;text-align:right')
                ,'td'=>array('val'=>'0.00','tag'=>'span','class'=>'','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right')
            ))
            ->table_input_set('columns',array(
                'col_name'=>'qty'
                ,'th'=>array('val'=>Lang::get(array('Qty','Being Used'),true,true,false,false,true),'col_style'=>'width:200px;text-align:right')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'form-control','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right')
            ))

            ;

        $tbl_res_product = $form->table_input_add();
        $tbl_res_product->table_input_set('id',$id_prefix.'_result_product_table')
            ->main_div_set('class','form-group hide_all')
            ->label_set('value','Result Product')
            ->table_input_set('columns',array(
                'col_name'=>'product_img'
                ,'th'=>array('val'=>'','class'=>'product-img')
                ,'td'=>array('val'=>Product_Engine::img_get(null),'tag'=>'','class'=>'','attr'=>array('original'=>'')
                )
            )) 
            ->table_input_set('columns',array(
                'col_name'=>'product_type'
                ,'th'=>array('val'=>'','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'','visible'=>false
                )
            ))   
            ->table_input_set('columns',array(
                'col_name'=>'product_id'
                ,'th'=>array('val'=>'','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'','visible'=>false
                )
            ))    
            ->table_input_set('columns',array(
                'col_name'=>'product'
                ,'th'=>array('val'=>'Product')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'','attr'=>array('original'=>'')
                )
            ))
            ->table_input_set('columns',array(
                'col_name'=>'unit_id'
                ,'th'=>array('val'=>'','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'','visible'=>false
                )
            ))
            ->table_input_set('columns',array(
                'col_name'=>'unit'
                ,'th'=>array('val'=>'Unit','col_style'=>'width:200px')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'','attr'=>array('original'=>''))
            ))
            ->table_input_set('columns',array(
                'col_name'=>'stock_location_id'
                ,'th'=>array('val'=>'','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'','visible'=>false
                )
            ))
            ->table_input_set('columns',array(
                'col_name'=>'stock_location'
                ,'th'=>array('val'=>'Stock Location','col_style'=>'width:200px')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'','attr'=>array('original'=>''))
            ))
            ->table_input_set('columns',array(
                'col_name'=>'qty'
                ,'th'=>array('val'=>Lang::get(array('Qty','Result'),true,true,false,false,true),'col_style'=>'width:200px;text-align:right')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'form-control','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right')
            ))
            ;

        $tbl_comp_product = $form->table_input_add();
        $tbl_comp_product->table_input_set('id',$id_prefix.'_scrap_product_table')
            ->label_set('value',Lang::get(array('Scrap','Product')))
            ->main_div_set('hide_all',true)
            ->table_input_set('columns',array(
                'col_name'=>'product_img'
                ,'th'=>array('val'=>'','class'=>'product-img')
                ,'td'=>array('val'=>Product_Engine::img_get(null),'tag'=>'','class'=>'','attr'=>array('original'=>'')
                )
            )) 
            ->table_input_set('columns',array(
                'col_name'=>'product_type'
                ,'th'=>array('val'=>'','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'','visible'=>false
                )
            ))
            ->table_input_set('columns',array(
                'col_name'=>'product_id'
                ,'th'=>array('val'=>'','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'','visible'=>false
                )
            ))
            ->table_input_set('columns',array(
                'col_name'=>'product'
                ,'th'=>array('val'=>'Product')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'','attr'=>array('original'=>'')
                )
            ))
            ->table_input_set('columns',array(
                'col_name'=>'unit_id'
                ,'th'=>array('val'=>'','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'','visible'=>false
                )
            ))
            ->table_input_set('columns',array(
                'col_name'=>'unit'
                ,'th'=>array('val'=>'Unit','col_style'=>'width:200px')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'','attr'=>array('original'=>''))
            ))
            ->table_input_set('columns',array(
                'col_name'=>'stock_location_id'
                ,'th'=>array('val'=>'','visible'=>false)
                ,'td'=>array('val'=>'','tag'=>'span','class'=>'','visible'=>false
                )
            ))
            ->table_input_set('columns',array(
                'col_name'=>'stock_location'
                ,'th'=>array('val'=>'Stock Location','col_style'=>'width:200px')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'','attr'=>array('original'=>''))
            ))
            ->table_input_set('columns',array(
                'col_name'=>'qty'
                ,'th'=>array('val'=>Lang::get(array('Qty','Leftover'),true,true,false,false,true),'col_style'=>'width:200px;text-align:right')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'form-control','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right')
            ))

            ;
        
        $components['notes'] = $form->textarea_add()->textarea_set('label','Notes')
                ->textarea_set('id',$id_prefix.'_notes')
                ->textarea_set('value','')
                ->textarea_set('hide_all',true)
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
            $param['detail_tab'] = '#modal_mf_work_process .modal-body';
            $param['view_url'] = '';
            $param['window_scroll'] = '#modal_mf_work_process';
        }


        $js = get_instance()->load->view('mf_work_process/mf_work_process_expected_result_product_js',$param,TRUE);
        $app->js_set($js);

        $stock_location_list = array();
        $temp = SI::type_list_get('mf_work_process_engine','$stock_location_list');
        foreach($temp as $i=>$row){
            $stock_location_list[] = array(
                'id'=>$row['val'],
                'text'=>$row['label']
            );
        }

        $js = get_instance()->load->view('mf_work_process/mf_work_process_component_product_js',array('stock_location_list'=>$stock_location_list),TRUE);
        $app->js_set($js);

        $js = get_instance()->load->view('mf_work_process/mf_work_process_scrap_product_js',array('stock_location_list'=>$stock_location_list),TRUE);
        $app->js_set($js);
        
        $js = get_instance()->load->view('mf_work_process/mf_work_process_result_product_js',array('stock_location_list'=>$stock_location_list),TRUE);
        $app->js_set($js);
        
        $js = get_instance()->load->view('mf_work_process/mf_work_process_worker_js',$param,TRUE);
        $app->js_set($js);
        
        $js = get_instance()->load->view('mf_work_process/mf_work_process_basic_function_js',$param,TRUE);
        $app->js_set($js);

        return $components;

    }

    public static function mf_work_process_status_log_render($app,$form,$data,$path){
        $config=array(
            'module_name'=>'mf_work_process',
            'module_engine'=>'mf_work_process_engine',
            'id'=>$data['id']
        );
        SI::form_renderer()->status_log_tab_render($form, $config);
    }

}
    
?>