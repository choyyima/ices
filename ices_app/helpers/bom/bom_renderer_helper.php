<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class BOM_Renderer {
        
        public static function modal_bom_render($app,$modal){
            $modal->header_set(array('title'=>'Expedition','icon'=>App_Icon::bom()));
            $components = self::bom_components_render($app, $modal,true);
            
            
        }
        
        public static function bom_render($app,$form,$data,$path,$method){
            get_instance()->load->helper('bom/bom_engine');
            $path = BOM_Engine::path_get();
            $id = $data['id'];
            $components = self::bom_components_render($app, $form,false);
            $back_href = $path->index;
            
            $form->button_add()->button_set('value','BACK')
                ->button_set('icon',App_Icon::btn_back())
                ->button_set('href',$back_href)
                ->button_set('class','btn btn-default')
                ;

            $js = '
                <script>
                    $("#bom_method").val("'.$method.'");
                    $("#bom_id").val("'.$id.'");
                </script>
            ';             
            $app->js_set($js);
            
            $js = '                
                    bom_init();
                    bom_bind_event();
                    bom_components_prepare(); 
            ';
            $app->js_set($js);
            
        }
        
        public static function bom_components_render($app,$form,$is_modal){
            get_instance()->load->helper('product/product_engine');
            get_instance()->load->helper('bom/bom_engine');
            $path = BOM_Engine::path_get();            
            $components = array();
            $db = new DB();
            
            $id_prefix = 'bom';
            
            $components['id'] = $form->input_add()->input_set('id',$id_prefix.'_id')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;

            
            $form->input_add()->input_set('id',$id_prefix.'_method')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;            
            
            $form->input_add()->input_set('label',Lang::get('Code'))
                    ->input_set('id',$id_prefix.'_code')
                    ->input_set('icon','fa fa-info')
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                    ->input_set('attrib',array('style'=>'font-weight:bold'))
                    
                ;
            
            
            $bom_type_list = array();
            $raw_list = SI::type_list_get('BOM_Engine');
            foreach($raw_list as $idx=>$row){
                $bom_type_list[] = array('id'=>$row['val'],'data'=>$row['label']);
            }
            
            $form->input_select_add()
                ->input_select_set('label',Lang::get('Type'))
                ->input_select_set('icon',App_Icon::info())
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_type')
                ->input_select_set('data_add',$bom_type_list)
                ->input_select_set('allow_empty',false)
                ->input_select_set('value',array())
            ;
            
            $form->input_add()->input_set('label',Lang::get('Name'))
                    ->input_set('id',$id_prefix.'_name')
                    ->input_set('icon','fa fa-info')
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            
            
            
            $components[$id_prefix.'_status'] = $form->input_select_add()
                    ->input_select_set('label','Status')
                    ->input_select_set('icon','fa fa-user')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id',$id_prefix.'_bom_status')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                     ->input_select_set('hide_all',true)
                    ;
            
            $tbl_res_product = $form->table_input_add();
            $tbl_res_product->table_input_set('id',$id_prefix.'_result_product_table')
                ->label_set('value','Result Product')
                    ->table_input_set('columns',array(
                    'col_name'=>''
                    ,'th'=>array('val'=>'','class'=>'table-row-num')
                    ,'td'=>array('val'=>'','tag'=>'','class'=>'','attr'=>array('original'=>'')
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
                    'col_name'=>'qty'
                    ,'th'=>array('val'=>'Qty','col_style'=>'width:200px;text-align:right')
                    ,'td'=>array('val'=>'','tag'=>'input','class'=>'form-control','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right')
                ))
                ->table_input_set('columns',array(
                    'col_name'=>''
                    ,'th'=>array('val'=>'','class'=>'table-action')
                    ,'td'=>array('val'=>'','tag'=>'','class'=>'','attr'=>array('original'=>'')
                    )
                ))
                ->table_input_set('new_row',false)
                ->table_input_set('row_num',false)
                ->main_div_set('hide_all',true)
                ;
            
            $tbl_comp_product = $form->table_input_add();
            $tbl_comp_product->table_input_set('id',$id_prefix.'_component_product_table')
                ->label_set('value','Component Product')
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
                    'col_name'=>'qty'
                    ,'th'=>array('val'=>'Qty','col_style'=>'width:200px;text-align:right')
                    ,'td'=>array('val'=>'','tag'=>'input','class'=>'form-control','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right')
                ))
                ->table_input_set('new_row',true)
                ->table_input_set('row_num',true)
                ->main_div_set('hide_all',true)
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
                $param['detail_tab'] = '#modal_bom .modal-body';
                $param['view_url'] = '';
                $param['window_scroll'] = '#modal_bom';
            }
            
            

            $js = get_instance()->load->view('bom/bom_result_product_js',$param,TRUE);
            $app->js_set($js);
            
            $js = get_instance()->load->view('bom/bom_component_product_js',$param,TRUE);
            $app->js_set($js);
            
            $js = get_instance()->load->view('bom/bom_basic_function_js',$param,TRUE);
            $app->js_set($js);
            
            return $components;
            
        }
        
        public static function bom_status_log_render($app,$form,$data,$path){
            $config=array(
                'module_name'=>'bom',
                'module_engine'=>'Bom_Engine',
                'id'=>$data['id']
            );
            SI::form_renderer()->status_log_tab_render($form, $config);
        }
        
    }
    
?>