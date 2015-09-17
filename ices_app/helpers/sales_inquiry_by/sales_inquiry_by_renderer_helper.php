<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Sales_Inquiry_By_Renderer {
        
        public static function modal_sales_inquiry_by_render($app,$modal){
            $modal->header_set(array('title'=>' Sales Inquiry By','icon'=>App_Icon::sales_inquiry_by()));
            $components = self::sales_inquiry_by_components_render($app, $modal,true);
            
            
        }
        
        public static function sales_inquiry_by_render($app,$form,$data,$path,$method){
            get_instance()->load->helper('sales_inquiry_by/sales_inquiry_by_engine');
            $path = Sales_Inquiry_By_Engine::path_get();
            $id = $data['id'];
            $components = self::sales_inquiry_by_components_render($app, $form,false);
            $back_href = $path->index;
            
            $form->button_add()->button_set('value','BACK')
                ->button_set('icon',App_Icon::btn_back())
                ->button_set('href',$back_href)
                ->button_set('class','btn btn-default')
                ;

            $js = '
                <script>
                    $("#sales_inquiry_by_method").val("'.$method.'");
                    $("#sales_inquiry_by_id").val("'.$id.'");
                </script>
            ';             
            $app->js_set($js);
            
            $js = '                
                    sales_inquiry_by_init();
                    sales_inquiry_by_bind_event();
                    sales_inquiry_by_components_prepare(); 
            ';
            $app->js_set($js);
            
        }
        
        public static function sales_inquiry_by_components_render($app,$form,$is_modal){
            
            get_instance()->load->helper('sales_inquiry_by/sales_inquiry_by_engine');
            $path = Sales_Inquiry_By_Engine::path_get();            
            $components = array();
            $db = new DB();
            $components['id'] = $form->input_add()->input_set('id','sales_inquiry_by_id')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;

            
            $form->input_add()->input_set('id','sales_inquiry_by_method')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;            
            
            $form->input_add()->input_set('label',Lang::get('Code'))
                    ->input_set('id','sales_inquiry_by_code')
                    ->input_set('icon','fa fa-info')
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                    ->input_set('attrib',array('style'=>'font-weight:bold'))
                    
                ;
            
            $form->input_add()->input_set('label',Lang::get('Name'))
                    ->input_set('id','sales_inquiry_by_name')
                    ->input_set('icon','fa fa-info')
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            $components['sales_inquiry_by_status'] = $form->input_select_add()
                    ->input_select_set('label','Status')
                    ->input_select_set('icon','fa fa-user')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','sales_inquiry_by_sales_inquiry_by_status')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                     ->input_select_set('hide_all',true)
                    ;
             
            $true_false_arr = array(
                array('id'=>'1','data'=>'True')
                ,array('id'=>'0','data'=>'False')
            ) ;
            

            $components['notes'] = $form->textarea_add()->textarea_set('label','Notes')
                    ->textarea_set('id','sales_inquiry_by_notes')
                    ->textarea_set('value','')
                    ->textarea_set('hide_all',true)
                    ->textarea_set('disable_all',true)
                ;
                    
                    
            $form->hr_add()->hr_set('class','');
            
            $form->button_add()->button_set('value','Submit')
                            ->button_set('id','sales_inquiry_by_submit')
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
                $param['detail_tab'] = '#modal_sales_inquiry_by .modal-body';
                $param['view_url'] = '';
                $param['window_scroll'] = '#modal_sales_inquiry_by';
            }
            
            $js = get_instance()->load->view('sales_inquiry_by/sales_inquiry_by_basic_function_js',$param,TRUE);
            $app->js_set($js);

            return $components;
            
        }
        
        
    }
    
?>