<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Delivery_MOP_Renderer {
        
        public static function delivery_mop_components_render($app,$form,$is_modal){
            get_instance()->load->helper('product_price_list/delivery_mop/delivery_mop_engine');
            $path = Delivery_MOP_Engine::path_get();            
            $components = array();
            
            $db = new DB();
            
            $form->input_add()->input_set('id','delivery_mop_id')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;
            
            $form->input_add()->input_set('id','delivery_mop_method')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;
            
            $form->input_add()->input_set('id','delivery_mop_reference')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;
            
            
            
            $type_list = array(
                array('id'=>'mixed','data'=>'Mixed')
                ,array('id'=>'separated','data'=>'Separated')
            );
            
            $form->input_select_add()->input_select_set('id','delivery_mop_calculation_type')
                    ->input_select_set('label','Calculation Type')
                    ->input_select_set('icon',App_Icon::info())
                    ->input_select_set('min_length','0')
                    ->input_select_set('data_add',$type_list)
                    ->input_select_set('value',array())
                    ;
            
            $form->input_add()->input_set('id','delivery_mop_code')
                    ->input_set('label','Code')
                    ->input_set('icon','fa fa-info')
                    ->input_set('value','')
                    ;
            
            $true_false = [];
            $true_false[] = array('id'=>'1','data'=>'True');
            $true_false[] = array('id'=>'0','data'=>'False');
            
            $form->input_add()->input_set('id','delivery_mop_mixed_amount')
                    ->input_set('label','Amount')
                    ->input_set('icon','fa fa-info')
                    ->input_set('value','')
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                    ;
            
            $table = $form->form_group_add()->table_add();
            $table->table_set('id','delivery_mop_mixed_product_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"is_selected","label"=>"",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"product_id","label"=>"",'col_attrib'=>array('style'=>'display:none;')));
            $table->table_set('columns',array("name"=>"product_name","label"=>"Product",'col_attrib'=>array('style'=>'')));

            
            $table = $form->form_group_add()->table_add();
            $table->table_set('id','delivery_mop_separated_product_table');
            $table->table_set('class','table fixed-table');
            //$table->table_set('columns',array("name"=>"is_selected","label"=>"",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:50px')));
            $table->table_set('columns',array("name"=>"product_id","label"=>"",'col_attrib'=>array('style'=>'display:none;')));
            $table->table_set('columns',array("name"=>"product_name","label"=>"Product",'col_attrib'=>array('style'=>'')));
            $table->table_set('columns',array("name"=>"unit_id","label"=>"",'col_attrib'=>array('style'=>'display:none;')));
            $table->table_set('columns',array("name"=>"unit_name","label"=>"Unit",'col_attrib'=>array('style'=>'')));
            $table->table_set('columns',array("name"=>"amount","label"=>"Amount",'col_attrib'=>array('style'=>'')));
            
            $form->hr_add()->hr_set('class','');
            
            $form->button_add()->button_set('value','Submit')
                            ->button_set('id','delivery_mop_btn_submit')
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
                $param['detail_tab'] = '#modal_delivery_mop .modal-body';
                $param['view_url'] = '';
                $param['window_scroll'] = '#modal_delivery_mop';
            }
            
            
            $js = get_instance()->load->view('product_price_list/delivery_mop/delivery_mop_basic_function_js',$param,TRUE);
            $app->js_set($js);
            $js = get_instance()->load->view('product_price_list/delivery_mop/delivery_mop_mixed_basic_function_js',$param,TRUE);
            $app->js_set($js);
            $js = get_instance()->load->view('product_price_list/delivery_mop/delivery_mop_separated_basic_function_js',$param,TRUE);
            $app->js_set($js);
            return $components;
        }
        
        
    }
?>