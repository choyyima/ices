<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product_Renderer {
    public static function modal_product_render($app,$modal){
        $modal->header_set(array('title'=>'Product','icon'=>App_Icon::product()));
        $components = self::product_components_render($app, $modal,true);
    }

    public static function product_render($app,$form,$data,$path,$method){
        get_instance()->load->helper('product/product_engine');
        $path = Product_Engine::path_get();
        $id = $data['id'];
        $components = self::product_components_render($app, $form,false);
        $back_href = $path->index;

        $form->button_add()->button_set('value','BACK')
            ->button_set('icon',App_Icon::btn_back())
            ->button_set('href',$back_href)
            ->button_set('class','btn btn-default')
            ;

        $js = '
            <script>
                $("#product_method").val("'.$method.'");
                $("#product_id").val("'.$id.'");
            </script>
        ';             
        $app->js_set($js);

        $js = '                
                product_init();
                product_bind_event();
                product_components_prepare(); 
        ';
        $app->js_set($js);

    }

    public static function product_components_render($app,$form,$is_modal){

        get_instance()->load->helper('product/product_engine');
        $path = Product_Engine::path_get();            
        $components = array();
        $db = new DB();

        $id_prefix = 'product';

        $components['id'] = $form->input_add()->input_set('id',$id_prefix.'_id')
                ->input_set('hide',true)
                ->input_set('value','')
                ;

        $disabled = array('disable'=>'');

        $form->input_add()->input_set('id',$id_prefix.'_method')
                ->input_set('hide',true)
                ->input_set('value','')
                ;            

        $form->div_add()->div_set('class','form-group')
                ->img_add()
                ->img_set('id','product_img_view')
                ->img_set('class','product-img')
                ->img_set('src','');
        $form->input_add()->input_set('label','Image')->input_set('name','name')
                ->input_set('icon','fa fa-info')
                ->input_set('id','product_img')
                ->input_set('value','')
                ->input_set('type','file')
                ->input_set('attrib',array('accept'=>'.jpg'))
                ;


        $form->input_add()->input_set('label',Lang::get('Code'))->input_set('name','code')
                ->input_set('icon','fa fa-info')
                ->input_set('value','')
                ->input_set('id','product_code');

        $form->input_add()->input_set('label',Lang::get('Name'))->input_set('name','name')
                ->input_set('icon','fa fa-gift')
                ->input_set('id','product_name')
                ->input_set('value','')
                ->input_set('maxlength',500);

        $form->input_select_add()
            ->input_select_set('label','Status')
            ->input_select_set('icon','fa fa-info')
            ->input_select_set('min_length','0')
            ->input_select_set('id',$id_prefix.'_product_status')
            ->input_select_set('data_add',array())
            ->input_select_set('value',array())
            ->input_select_set('is_module_status',true)
            ->input_select_set('hide_all',true)                    
            ;

        $product_category = $form->input_select_add()
            ->input_select_set('name','product_subcategory_id')
            ->input_select_set('label','Product Sub Category')
            ->input_select_set('icon','fa fa-tag')
            ->input_select_set('min_length','1')
            ->input_select_set('ajax_url',$path->ajax_search.'product_subcategory')
            ->input_select_set('value',array())
            ->input_select_set('id','product_subcategory_id')
            ;

        $unit_columns = array(
            array(
                'name'=>'product_sales_multiplication_qty'
                ,'label'=>'Sales Multiplication Qty'
                ,'type'=>'input'
                ,'filter'=>'numeric'
            )
            ,array(
                'name'=>'buffer_stock_qty'
                ,'label'=>'Buffer Stock Qty'
                ,'type'=>'input'
                ,'filter'=>'numeric'
            )
            ,array(
                "name"=>"code"
                ,"label"=>"Code"
            )
            ,array(
                "name"=>"name"
                ,"label"=>"Name"
            )
            ,array(
                'name'=>'product_unit_parent_child'
                ,'label'=>'Child Product'
                ,'value'=>'<a data="'.htmlspecialchars('{"parent_qty":"1","product_unit_child":[]}').'" href="#">Child Product</a>'
            )
        );

        $unit = $form->input_select_table_add();
        $unit->input_select_set('name','unit_id')
                ->input_select_set('id',$id_prefix.'_unit')
                ->input_select_set('label','Unit')
                ->input_select_set('icon','fa fa-tag')
                ->input_select_set('min_length','1')
                ->input_select_set('ajax_url',$path->ajax_search.'unit')
                ->input_select_set('value',array("id"=>"","data"=>""))
                ->table_set('columns',$unit_columns)
                ->table_set('id',$id_prefix."_unit_table")
                ->table_set('ajax_url',$path->ajax_search.'unit_id')
                ->table_set('column_key','id')
                ->table_set('allow_duplicate_id',false)
                ->table_set('selected_value',array())

                ;

        //<editor-fold defaultstate="collapsed" desc="Child Product">
        $modal_child_product = $form->modal_add()->id_set($id_prefix.'_modal_child_product')->width_set('75%');
        $modal_child_product->header_set(array('title'=>'Child Product','icon'=>'fa fa-gift'));
        $modal_child_product->modal_button_footer_add($id_prefix.'_modal_btn_submit','button','btn btn-primary pull-left',  'fa fa-save','Submit');
        $modal_child_product->modal_button_footer_add($id_prefix.'_modal_btn_cancel','button','btn btn-default pull-left',  'fa fa-times','Cancel');

        $modal_child_product->input_add()->input_set('label','Product')
                ->input_set('icon','fa fa-gift')
                ->input_set('id',$id_prefix.'_parent_product')
                ->input_set('value','')
                ->input_set('disable_all',true)
                ;
        $modal_child_product->input_add()->input_set('label','Product')
                ->input_set('icon','fa fa-gift')
                ->input_set('id',$id_prefix.'_parent_product_id')
                ->input_set('value','')
                ->input_set('hide_all',true)
                ;


        $modal_child_product->input_add()->input_set('label','Unit')
                ->input_set('icon','fa fa-tag')
                ->input_set('id',$id_prefix.'_parent_unit')
                ->input_set('value','')
                ->input_set('disable_all',true)
                ;

        $modal_child_product->input_add()->input_set('label','')
                ->input_set('icon','fa fa-tag')
                ->input_set('id',$id_prefix.'_parent_unit_id')
                ->input_set('value','')
                ->input_set('hide_all',true)
                ;


        $modal_child_product->input_add()->input_set('label','Qty')
                ->input_set('icon','fa fa-info')
                ->input_set('id',$id_prefix.'_parent_qty')
                ->input_set('is_numeric',true)
                ->input_set('value','1');

        $child_product = $modal_child_product->table_input_add();
        $child_product->table_input_set('id',$id_prefix.'_child_product')
            ->label_set('value','Child Product')
            ->table_input_set('columns',array(
               'col_name'=>'child_product_id'
               ,'th'=>array('val'=>'','visible'=>false)
               ,'td'=>array('val'=>'','tag'=>'span','class'=>'','visible'=>false
               )
           ))
            ->table_input_set('columns',array(
                'col_name'=>'child_product'
                ,'th'=>array('val'=>'Product')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'','attr'=>array('original'=>'')
                )
            ))                    
            ->table_input_set('columns',array(
               'col_name'=>'child_unit_id'
               ,'th'=>array('val'=>'','visible'=>false)
               ,'td'=>array('val'=>'','tag'=>'span','class'=>'','visible'=>false
               )
            ))
            ->table_input_set('columns',array(
                'col_name'=>'child_unit'
                ,'th'=>array('val'=>'Unit','col_style'=>'width:200px')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'','attr'=>array('original'=>''))
            ))
            ->table_input_set('columns',array(
                'col_name'=>'child_qty'
                ,'th'=>array('val'=>'Qty','col_style'=>'width:200px;text-align:right')
                ,'td'=>array('val'=>'','tag'=>'input','class'=>'form-control','attr'=>array(),'col_style'=>'text-align:right','style'=>'text-align:right')
            ))
            ->table_input_set('new_row',true)
            ;
        //</editor-fold>


        $rswo_reference_product_req_list = array();
        foreach(Product_Engine::$rswo_product_reference_req_list as $idx=>$row){
            $rswo_reference_product_req_list[] = array('id'=>$row['val'],'text'=>$row['label']);
        }

        $form->input_select_add()->input_select_set('name','rswo_product_reference_req')
                ->input_select_set('id',$id_prefix.'_rswo_product_reference_req')
                ->input_select_set('label',Lang::get('Refill').' '.Lang::get('Subcon Work Order').' - '.Lang::get('Product Reference Required'))
                ->input_select_set('icon','fa fa-info')
                ->input_select_set('min_length','0')
                ->input_select_set('allow_empty',false)
                ->input_select_set('value',$rswo_reference_product_req_list[0])
                ->input_select_set('data_add',$rswo_reference_product_req_list)

                ;

        $form->textarea_add()->textarea_set('label','Additional Information')
                ->textarea_set('id',$id_prefix.'_additional_info')
                ->textarea_set('value','')
                ->textarea_set('attrib',array())       

                ;

        $form->textarea_add()->textarea_set('label','Notes')
                ->textarea_set('id','product_notes')
                ->textarea_set('value','')
                ->textarea_set('attrib',array())       

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

        $js = get_instance()->load->view('product/'.$id_prefix.'_basic_function_js',$param,TRUE);
        $app->js_set($js);
        $js = get_instance()->load->view('product/'.'child_product_js',array(),TRUE);
        $app->js_set($js);
        return $components;

    }

    public static function product_status_log_render($app,$form,$data,$path){
        //<editor-fold defaultstate="collapsed">
        $config=array(
            'module_name'=>'product',
            'module_engine'=>'Sales_Receipt_Engine',
            'id'=>$data['id']
        );
        SI::form_renderer()->status_log_tab_render($form, $config);
        //</editor-fold>
    }
        
}
?>