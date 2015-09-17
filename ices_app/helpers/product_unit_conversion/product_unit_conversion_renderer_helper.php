<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Product_Unit_Conversion_Renderer {
        public static function product_unit_conversion_components_render($app,$form,$is_modal){
            get_instance()->load->helper('product_unit_conversion/product_unit_conversion_engine');
            
            $path = Product_Unit_Conversion_Engine::path_get();            
            $components = array();
            
            $db = new DB();
            
            $form->input_add()->input_set('id','product_unit_conversion_id')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;
            
            $form->input_add()->input_set('id','product_unit_conversion_method')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;
            
            $form->input_add()->input_set('id','product_unit_conversion_reference')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;
            
            foreach(Product_Unit_Conversion_Engine::$type as $idx=>$type){
                $unit_conversion_type_list[] = array(
                    'id'=>$type['val'],'data'=>$type['label']
                );
            }
            
            
            $form->input_select_add()->input_select_set('id','product_unit_conversion_type')
                ->input_select_set('label','Type')
                ->input_select_set('icon',App_Icon::info())
                ->input_select_set('min_length','0')
                ->input_select_set('data_add',$unit_conversion_type_list)
                ->input_select_set('value',array())
                
            ;
            
            $expedition_columns = array(
                array(
                    "name"=>"code"
                    ,"label"=>"Code"
                )
                ,array(
                    "name"=>"name"
                    ,"label"=>"Name"
                )
            );
            
            $expedition_type_ist = $form->input_select_add();
            $expedition_type_ist->input_select_set('name','expediton_id')
                ->input_select_set('id','product_unit_conversion_expedition')
                ->input_select_set('label',Lang::get('Expedition'))
                ->input_select_set('icon','fa fa-tag')
                ->input_select_set('min_length','1')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array("id"=>"","data"=>""))
                ->input_select_set('hide_all',true)
                ->input_select_set('ajax_url',$path->ajax_search.'input_select_expedition_search')
                ;
            
            $form->input_add()->input_set('id','product_unit_conversion_qty_1')
                    ->input_set('label','Qty 1')
                    ->input_set('icon','fa fa-info')
                    ->input_set('value','')
                    ->input_set('disable_all',true)
                    ->input_set('hide_all',true)
                    ;            
            
            $form->input_select_add()->input_select_set('id','product_unit_conversion_unit_1')
                ->input_select_set('label','Unit 1')
                ->input_select_set('icon',App_Icon::info())
                ->input_select_set('min_length','0')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('hide_all',true)
            ;
            
            $form->input_add()->input_set('id','product_unit_conversion_qty_2')
                    ->input_set('label','Qty 2')
                    ->input_set('icon','fa fa-info')
                    ->input_set('value','')
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                    ;
            
            $unit_list = array();
            $rs = $db->query_array('select id, code, name from unit where status>0');
            
            if(count($rs)>0){
                for($i = 0;$i<count($rs);$i++){
                    $unit_list[] = array(
                        'id' =>$rs[$i]['id']
                        ,'data' =>SI::html_tag('strong',$rs[$i]['code']).' '.$rs[$i]['name']
                    );
                }
            }
            
            $form->input_select_add()->input_select_set('id','product_unit_conversion_unit_2')
                ->input_select_set('label','Unit 2')
                ->input_select_set('icon',App_Icon::info())
                ->input_select_set('min_length','0')
                ->input_select_set('data_add',$unit_list)
                ->input_select_set('value',array())
                ->input_select_set('hide_all',true)
            ;

            $form->input_select_add()
                    ->input_select_set('label','Status')
                    ->input_select_set('icon','fa fa-info')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','product_unit_conversion_status')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->input_select_set('hide_all',true)
                    ->input_select_set('disable_all',true)
                    ;
            
            $form->hr_add()->hr_set('class','');
            
            $form->button_add()->button_set('value','Submit')
                            ->button_set('id','product_unit_conversion_btn_submit')
                            ->button_set('icon',App_Icon::detail_btn_save())
                        ;
            
            $param = array(
                'ajax_url'=>$path->index.'ajax_search/'
                ,'index_url'=>$path->index
                ,'detail_tab'=>'#detail_tab'
                ,'view_url'=>$path->index.'view/'
                ,'window_scroll'=>'body'
                ,'data_support_url'=>$path->data_support
                ,'common_ajax_listener'=>get_instance()->config->base_url().'common_ajax_listener/'    
            );
            
            if($is_modal){
                $param['detail_tab'] = '#modal_product_unit_conversion .modal-body';
                $param['view_url'] = '';
                $param['window_scroll'] = '#modal_product_unit_conversion';
            }
            
            
            $js = get_instance()->load->view('product/product_unit_conversion/product_unit_conversion_basic_function_js',$param,TRUE);
            $app->js_set($js);
            
            $js = get_instance()->load->view('product/product_unit_conversion/product_unit_conversion_sales_moq_js',$param,TRUE);
            $app->js_set($js);
            
            $js = get_instance()->load->view('product/product_unit_conversion/product_unit_conversion_sales_real_weight_js',$param,TRUE);
            $app->js_set($js);

            $js = get_instance()->load->view('product/product_unit_conversion/product_unit_conversion_sales_expedition_weight_js',$param,TRUE);
            $app->js_set($js);

            
            return $components;
        }
    }
    
?>