<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Delivery_Extra_Charge_Renderer {
        
        public static function delivery_extra_charge_components_render($app,$form,$is_modal){
            get_instance()->load->helper('product_price_list/delivery_extra_charge/delivery_extra_charge_engine');
            $path = Delivery_Extra_Charge_Engine::path_get();            
            $components = array();
            
            $db = new DB();
            
            $form->input_add()->input_set('id','delivery_extra_charge_id')
                ->input_set('hide',true)
                ->input_set('value','')
                ;
            
            $form->input_add()->input_set('id','delivery_extra_charge_method')
                ->input_set('hide',true)
                ->input_set('value','')
                ;
            
            $form->input_add()->input_set('id','delivery_extra_charge_reference')
                ->input_set('hide',true)
                ->input_set('value','')
                ;
           
            $form->input_add()->input_set('id','delivery_extra_charge_description')
                ->input_set('label','Description')
                ->input_set('icon','fa fa-info')
                ->input_set('value','')
                ->input_set('hide_all',true)
                ->input_set('disable_all',true)
                ;
            
            $form->input_add()->input_set('id','delivery_extra_charge_min_qty')
                ->input_set('label','Min. Qty')
                ->input_set('icon','fa fa-info')
                ->input_set('value','')
                ->input_set('hide_all',true)
                ->input_set('disable_all',true)
                ->input_set('input_mask_type','numeric')
                ;
            
            $unit_list = array();
            $q = 'select * from unit where status>0';
            $rs = $db->query_array($q);
            foreach($rs as $idx=>$row){
                $unit_list[] = array('id'=>$row['id'], 'data'=>SI::html_tag('strong',$row['code']).' '.$row['name']);
            }
            
            $form->input_select_add()
                    ->input_select_set('label','Unit')
                    ->input_select_set('icon','fa fa-info')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','delivery_extra_charge_unit')
                    ->input_select_set('data_add',$unit_list)
                    ->input_select_set('value',array())
                    ->input_select_set('hide_all',true)

                    ;
            
            $form->input_add()->input_set('id','delivery_extra_charge_amount')
                    ->input_set('label','Amount')
                    ->input_set('icon','fa fa-info')
                    ->input_set('value','')
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                    ->input_set('input_mask_type','numeric')
                    ;
            
            $form->hr_add()->hr_set('class','');
            
            $form->button_add()->button_set('value','Submit')
                            ->button_set('id','delivery_extra_charge_btn_submit')
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
                $param['detail_tab'] = '#modal_delivery_extra_charge .modal-body';
                $param['view_url'] = '';
                $param['window_scroll'] = '#modal_delivery_extra_charge';
            }
            
            
            $js = get_instance()->load->view('product_price_list/delivery_extra_charge/delivery_extra_charge_basic_function_js',$param,TRUE);
            $app->js_set($js);
            return $components;
        }
        
        
    }
?>