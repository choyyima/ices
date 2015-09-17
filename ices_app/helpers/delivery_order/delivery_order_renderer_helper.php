<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Delivery_Order_Renderer {

    public static function modal_delivery_order_render($app,$modal){
        $modal->header_set(array('title'=>Lang::get('Delivery Order'),'icon'=>App_Icon::delivery_order()));
        $components = self::delivery_order_components_render($app, $modal,true);


    }

    public static function delivery_order_render($app,$form,$data,$path,$method){
        get_instance()->load->helper('delivery_order/delivery_order_engine');
        $path = Delivery_Order_Engine::path_get();
        $id = $data['id'];
        $components = self::delivery_order_components_render($app, $form,false);
        $back_href = $path->index;

        $form->button_add()->button_set('value','BACK')
            ->button_set('icon',App_Icon::btn_back())
            ->button_set('href',$back_href)
            ->button_set('class','btn btn-default')
            ;

        $js = '
            <script>
                $("#delivery_order_method").val("'.$method.'");
                $("#delivery_order_id").val("'.$id.'");
            </script>
        ';             
        $app->js_set($js);

        $js = '                
                delivery_order_init();
                delivery_order_bind_event();
                delivery_order_components_prepare(); 
        ';
        $app->js_set($js);

    }

    public static function delivery_order_components_render($app,$form,$is_modal){
        
        get_instance()->load->helper('delivery_order/delivery_order_engine');
        $path = Delivery_Order_Engine::path_get();            
        $components = array();
        $db = new DB();
        $id_prefix = 'delivery_order';
        $components['id'] = $form->input_add()->input_set('id','delivery_order_id')
                ->input_set('hide',true)
                ->input_set('value','')
                ;

        $form->input_add()->input_set('id','delivery_order_type')
                ->input_set('value','')
                ->input_set('hide_all',true)
                ;

        $disabled = array('disable'=>'');


        $reference_detail = array(
            //array('name'=>'type','label'=>Lang::get('Type'))
            //,array('name'=>'code','label'=>Lang::get('Code'))
        );

        

        $form->input_add()->input_set('id','delivery_order_method')
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
                ->input_select_set('id','delivery_order_store')
                ->input_select_set('data_add',$store_list)
                ->input_select_set('value',array())
                ->input_select_set('disable_all',true)

            ;

        $form->input_select_detail_add()
                ->input_select_set('label',Lang::get('Reference'))
                ->input_select_set('icon',App_Icon::info())
                ->input_select_set('min_length','0')
                ->input_select_set('id','delivery_order_reference')
                ->input_select_set('min_length','1')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('ajax_url',$path->ajax_search.'input_select_reference_search')
                ->input_select_set('disable_all',true)
                ->detail_set('id',"delivery_order_reference_detail")
                ->detail_set('ajax_url','')

            ;
        
        $form->input_add()->input_set('label',Lang::get('Code'))
                ->input_set('id','delivery_order_code')
                ->input_set('icon','fa fa-info')
                ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
            ;

        $form->datetimepicker_add()->datetimepicker_set('label',Lang::get(array('Delivery Order','Date')))
                ->datetimepicker_set('id','delivery_order_delivery_order_date')
                ->datetimepicker_set('value',Tools::_date('','F d, Y H:i')) 
                ->datetimepicker_set('disable_all',true)

            ;

        $warehouse_list_from = array();
        $q = '
            select t1.id id, t1.name data 
            from warehouse t1
                inner join warehouse_type t2 on t1.warehouse_type_id = t2.id
            where t1.status>0 and t2.code = "BOS"
            ';            
        $warehouse_list_from = $db->query_array($q);

        $form->input_select_add()
                ->input_select_set('label',Lang::get('From Warehouse'))
                ->input_select_set('icon',App_Icon::warehouse())
                ->input_select_set('min_length','0')
                ->input_select_set('id','delivery_order_warehouse_from')
                ->input_select_set('data_add',$warehouse_list_from)
                ->input_select_set('value',array())
                ->input_select_set('disable_all',true)
            ;

        $warehouse_to_detail = array(
            array('id'=>'delivery_order_warehouse_to_code','name'=>'code','label'=>Lang::get('Code'),'type'=>'text')
            ,array('id'=>'delivery_order_warehouse_to_name','name'=>'name','label'=>Lang::get('Name'),'type'=>'text')
            ,array('id'=>'delivery_order_warehouse_to_type','name'=>'warehouse_type','label'=>Lang::get('Type'),'type'=>'text')
            ,array('id'=>'delivery_order_warehouse_to_contact_name','name'=>'contact_name','label'=>Lang::get('Contact Name'),'type'=>'input')
            ,array('id'=>'delivery_order_warehouse_to_address','name'=>'address','label'=>Lang::get('Address'),'type'=>'input')
            ,array('id'=>'delivery_order_warehouse_to_phone','name'=>'phone','label'=>Lang::get('Phone'),'type'=>'input','attribute'=>'data-inputmask="\'mask\': \'(99) 99-999-99999\'" data-mask=""')

        );

        $form->input_select_detail_editable_add()
                ->input_select_set('label',Lang::get('To Warehouse'))
                ->input_select_set('icon',App_Icon::warehouse())
                ->input_select_set('min_length','0')
                ->input_select_set('id','delivery_order_warehouse_to')
                ->input_select_set('value',array())
                ->input_select_set('disable_all',true)
                ->detail_editable_set('rows',$warehouse_to_detail)
                ->detail_editable_set('id',"delivery_order_warehouse_to_detail")
                ->detail_editable_set('ajax_url','')

            ;

        $components['delivery_order_status'] = $form->input_select_add()
                ->input_select_set('label','Status')
                ->input_select_set('icon','fa fa-info')
                ->input_select_set('min_length','0')
                ->input_select_set('id','delivery_order_delivery_order_status')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('is_module_status',true)
                ->input_select_set('hide_all',true)
                ;


        $table = $form->form_group_add()->table_add();
        $table->table_set('id','delivery_order_product_table');
        $table->table_set('class','table fixed-table');
        $table->div_set('label','Product');
        $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
        $table->table_set('columns',array("name"=>"product_type","label"=>"",'col_attrib'=>array('class'=>'hidden')));
        $table->table_set('columns',array("name"=>"product_id","label"=>"",'col_attrib'=>array('class'=>'hidden')));
        $table->table_set('columns',array("name"=>"product_img","label"=>"",'header_class'=>'product-img','col_attrib'=>array('style'=>'')));
        $table->table_set('columns',array("name"=>"product_name","label"=>"Product",'col_attrib'=>array('style'=>'')));
        $table->table_set('columns',array("name"=>"unit_id","label"=>"",'col_attrib'=>array('style'=>'text-align:center;display:none')));            
        $table->table_set('columns',array("name"=>"unit_name","label"=>"Unit",'col_attrib'=>array('style'=>'width:150px')));
        $table->table_set('columns',array("name"=>"qty","label"=>"Qty",'col_attrib'=>array('style'=>'text-align:right;width:150px')));
        $table->table_set('columns',array("name"=>"action","label"=>"",'header_class'=>'table-action','col_attrib'=>array('style'=>'text-align:center;')));

        $table = $form->form_group_add()->table_add();
        $table->table_set('id','delivery_order_rma_add_table');
        $table->table_set('class','table fixed-table');
        $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
        $table->table_set('columns',array("name"=>"product_id","label"=>"",'col_attrib'=>array('class'=>'hidden')));
        $table->table_set('columns',array("name"=>"product_img","label"=>"",'col_attrib'=>array('style'=>'text-align:center;width:100px')));
        $table->table_set('columns',array("name"=>"product_name","label"=>"Product",'col_attrib'=>array('style'=>'text-align:center')));
        $table->table_set('columns',array("name"=>"rma_qty","label"=>"RMA Qty",'col_attrib'=>array('style'=>'text-align:right')));
        $table->table_set('columns',array("name"=>"max_qty","label"=>"Available Qty",'col_attrib'=>array('style'=>'text-align:right')));
        $table->table_set('columns',array("name"=>"unit_id","label"=>"",'col_attrib'=>array('style'=>'text-align:center;display:none')));            
        $table->table_set('columns',array("name"=>"qty","label"=>"Qty",'col_attrib'=>array('style'=>'text-align:right')));
        $table->table_set('columns',array("name"=>"unit_name","label"=>"Unit",'col_attrib'=>array('style'=>'text-align:center')));

        $table = $form->form_group_add()->table_add();
        $table->table_set('id','delivery_order_rma_view_table');
        $table->table_set('class','table fixed-table');
        $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px;text-align:center'),'attribute'=>'style="text-align:center"'));
        $table->table_set('columns',array("name"=>"product_img","label"=>"",'col_attrib'=>array('style'=>'text-align:left;width:100px;text-align:center')));
        $table->table_set('columns',array("name"=>"product_name","label"=>"Product",'col_attrib'=>array('style'=>'text-align:center')));
        $table->table_set('columns',array("name"=>"qty","label"=>"Qty",'col_attrib'=>array('style'=>'text-align:right;width:200px')));
        $table->table_set('columns',array("name"=>"unit_name","label"=>"Unit",'col_attrib'=>array('style'=>'text-align:center;width:200px;')));


        $form->textarea_add()->textarea_set('label','Notes')
                ->textarea_set('id','delivery_order_notes')
                ->textarea_set('value','')
                ->textarea_set('attrib',array())       
                ->textarea_set('disable_all',true)
                ->div_set('id','delivery_order_div_notes')

                ;


        $form->hr_add()->hr_set('class','');

        $form->button_add()->button_set('value','Submit')
                        ->button_set('id','delivery_order_submit')
                        ->button_set('icon',App_Icon::detail_btn_save())
                    ;

        $form->button_add()->button_set('value','Print')
                        ->button_set('id','delivery_order_print')
                        ->button_set('icon',App_Icon::printer())
                        ->button_set('class','btn btn-default pull-right')
                        ->button_set('disable_after_click',false)
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
            $param['detail_tab'] = '#modal_delivery_order .modal-body';
            $param['view_url'] = '';
            $param['window_scroll'] = '#modal_delivery_order';
        }

        $js = get_instance()->load->view('delivery_order/delivery_order_basic_function_js',$param,TRUE);
        $app->js_set($js);
        $js = get_instance()->load->view('delivery_order/delivery_order_rma_js',$param,TRUE);
        $app->js_set($js);
        $js = get_instance()->load->view('delivery_order/do_rswo_js',$param,TRUE);
        $app->js_set($js);
        return $components;

    }

    public static function delivery_order_status_log_render($app,$form,$data,$path){
         $config=array(
            'module_name'=>'delivery_order',
            'module_engine'=>'Delivery_Order_Engine',
            'id'=>$data['id']
        );
        SI::form_renderer()->status_log_tab_render($form, $config);
    }

}
    
?>