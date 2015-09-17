<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Delivery_Order_Final_Renderer {
        
        public static function modal_delivery_order_final_render($app,$modal){
            $modal->footer_attr_set(array('style'=>'display:none'));
            $modal->header_set(array('title'=>'Delivery Order Final','icon'=>App_Icon::info()));
            $components = self::delivery_order_final_components_render($app, $modal,true);
        }
        
        public static function delivery_order_final_render($app,$form,$data,$path,$method){
            get_instance()->load->helper('delivery_order_final/delivery_order_final_engine');
            $path = Delivery_Order_Final_Engine::path_get();
            $id = $data['id'];
            $components = self::delivery_order_final_components_render($app, $form,false);
            $back_href = $path->index;
            
            $form->button_add()->button_set('value','BACK')
                ->button_set('icon',App_Icon::btn_back())
                ->button_set('href',$back_href)
                ->button_set('class','btn btn-default')
                ;

            $js = '
                <script>
                    $("#dof_method").val("'.$method.'");
                    $("#dof_id").val("'.$id.'");
                </script>
            ';             
            $app->js_set($js);
            
            $js = '                
                    dof_init();
                    dof_bind_event();
                    dof_components_prepare(); 
            ';
            $app->js_set($js);
            
        }
        
        public static function delivery_order_final_components_render($app,$form,$is_modal){
            
            get_instance()->load->helper('delivery_order_final/delivery_order_final_engine');
            $path = Delivery_Order_Final_Engine::path_get();            
            $components = array();
            $db = new DB();
            
            $id_prefix = 'dof';
            
            $components['id'] = $form->input_add()->input_set('id',$id_prefix.'_id')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;
            
            $form->input_add()->input_set('id',$id_prefix.'_type')
                    ->input_set('value','')
                    ->input_set('hide_all',true)
                    ;
            
            $disabled = array('disable'=>'');
            
            
            $reference_detail = array(
                //array('name'=>'type','label'=>Lang::get('Type'))
                //,array('name'=>'code','label'=>Lang::get('Code'))
            );
            
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
            
            $form->input_select_detail_add()
                    ->input_select_set('label',Lang::get('Reference'))
                    ->input_select_set('icon',App_Icon::info())
                    ->input_select_set('min_length','0')
                    ->input_select_set('id',$id_prefix.'_reference')
                    ->input_select_set('min_length','1')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->input_select_set('ajax_url',$path->ajax_search.'input_select_reference_search')
                    ->input_select_set('disable_all',true)
                    ->input_select_set('hide_all',true)
                    ->detail_set('rows',$reference_detail)
                    ->detail_set('id',$id_prefix."_reference_detail")
                    ->detail_set('ajax_url','')
                    
                ;
            
            $form->datetimepicker_add()->datetimepicker_set('label',Lang::get(array('Delivery Order Final','Date')))
                    ->datetimepicker_set('id',$id_prefix.'_delivery_order_final_date')
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
                        
            $warehouse_to_detail = array(
                array('id'=>'dof_warehouse_to_code','name'=>'code','label'=>Lang::get('Code'),'type'=>'text')
                ,array('id'=>'dof_warehouse_to_name','name'=>'name','label'=>Lang::get('Name'),'type'=>'text')
                ,array('id'=>'dof_warehouse_to_type','name'=>'warehouse_type','label'=>Lang::get('Type'),'type'=>'text')
                ,array('id'=>'dof_warehouse_to_contact_name','name'=>'contact_name','label'=>Lang::get('Contact Name'),'type'=>'input')
                ,array('id'=>'dof_warehouse_to_address','name'=>'address','label'=>Lang::get('Address'),'type'=>'input')
                ,array('id'=>'dof_warehouse_to_phone','name'=>'phone','label'=>Lang::get('Phone'),'type'=>'input','attribute'=>'data-inputmask="\'mask\': \'(99) 99-999-99999\'" data-mask=""')
                
            );
            
            $form->input_select_detail_editable_add()
                    ->input_select_set('label',Lang::get('To Warehouse'))
                    ->input_select_set('icon',App_Icon::warehouse())
                    ->input_select_set('min_length','0')
                    ->input_select_set('id',$id_prefix.'_warehouse_to')
                    ->input_select_set('value',array())
                    ->input_select_set('disable_all',true)
                    ->detail_editable_set('rows',$warehouse_to_detail)
                    ->detail_editable_set('id',$id_prefix."_warehouse_to_detail")
                    ->detail_editable_set('ajax_url','')

                ;
            
            $components['delivery_order_final_status'] = $form->input_select_add()
                    ->input_select_set('label','Status')
                    ->input_select_set('icon','fa fa-info')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id',$id_prefix.'_delivery_order_final_status')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->input_select_set('is_module_status',true)
                    ->input_select_set('module_prefix_id','dof')
                    ->input_select_set('module_primary_data_key','delivery_order_final')
                    ;
            
            $form->custom_component_add()->src_set('delivery_order_final/view/dof_product_table_view');
            
            $form->hr_add()->hr_set('class','');
            
            $form->button_add()->button_set('value','Submit')
                            ->button_set('id',$id_prefix.'_submit')
                            ->button_set('icon',App_Icon::detail_btn_save())
                        ;
            
            $form->button_add()->button_set('value','Print')
                            ->button_set('id',$id_prefix.'_print')
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
            );
            
            
            
            if($is_modal){
                $param['detail_tab'] = '#modal_'.$id_prefix.' .modal-body';
                $param['view_url'] = '';
                $param['window_scroll'] = '#modal_'.$id_prefix;
            }
            
            $js = get_instance()->load->view('delivery_order_final/'.$id_prefix.'_basic_function_js',$param,TRUE);
            $app->js_set($js);
            return $components;
            
        }
        
        public static function delivery_order_final_status_log_render($app,$form,$data,$path){
            $config=array(
                'module_name'=>'delivery_order_final',
                'module_engine'=>'Delivery_Order_Final_Engine',
                'id'=>$data['id']
            );
            SI::form_renderer()->status_log_tab_render($form, $config);
        }
        
        public static function dofc_view_render($app,$form,$data,$path){
            //<editor-fold defaultstate="collapsed">
            
            get_instance()->load->helper('dofc/dofc_engine');
            get_instance()->load->helper('dofc/dofc_renderer');
            $id = $data['id'];
            $db = new DB();
            $rs = $db->fast_get('delivery_order_final',array('id'=>$id));
            if(count($rs)>0) {
                $dof = $rs[0];            
                $form->form_group_add();
                if($dof['delivery_order_final_status'] === 'done'){
                    if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'dofc','add')){
                    $form->button_add()->button_set('class','primary')
                            ->button_set('value',Lang::get(array(array('val'=>'New','grammar'=>'adj'),array('val'=>'Delivery Order Final Confirmation'))))
                            ->button_set('icon','fa fa-plus')
                            ->button_set('attrib',array(
                                'data-toggle'=>"modal" 
                                ,'data-target'=>"#modal_dofc"
                            ))
                            ->button_set('disable_after_click',false)
                            ->button_set('id','dofc_btn_new')
                        ;
                    }
                }
                $form->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
                $tbl = $form->table_add();
                $tbl->table_set('class','table');
                $tbl->table_set('id','dofc_view_table');
                $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
                $tbl->table_set('columns',array("name"=>"code","label"=>"Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center'),"is_key"=>true));
                $tbl->table_set('columns',array("name"=>"dofc_date","label"=>Lang::get(array('Delivery Order Final Confirmation','Date')),'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"dofc_status_text","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('data key','id');

                $q = '
                    select distinct NULL row_num
                        ,dofc.*                        
                    from dof_dofc
                    inner join delivery_order_final_confirmation dofc 
                            on dof_dofc.delivery_order_final_confirmation_id = dofc.id
                    where dof_dofc.delivery_order_final_id = '.$db->escape($id).'
                    order by dofc.id desc
                ';
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){
                    $rs[$i]['row_num'] = $i+1;
                    
                    $rs[$i]['dofc_status_text'] = SI::get_status_attr(
                        SI::type_get('dofc_engine', $rs[$i]['delivery_order_final_status'])['label']
                    );
                }
                $tbl->table_set('data',$rs);                
                
                $modal_dofc = $app->engine->modal_add()->id_set('modal_dofc')
                        ->width_set('90%')
                        ->footer_attr_set(array('style'=>'display:none'));

                $dofc_data = array(
                    'dof'=>array(
                        'id'=>$dof['id']
                    )                
                );
                $dofc_data = json_decode(json_encode($dofc_data));

                DOFC_Renderer::modal_dofc_render(
                        $app
                        ,$modal_dofc
                    );


                $param = array(
                    'index_url'=>$path->index
                    ,'ajax_search'=>$path->ajax_search
                    ,'reference_id'=>$dof['id']
                    ,'reference_text'=>$dof['code']
                    ,'reference_type'=>$dof['delivery_order_final_type']
                );
                
                $js = get_instance()->load->view('delivery_order_final/dofc_js',$param,TRUE);
                $app->js_set($js);
                
            }
            //</editor-fold>
        }
    }
    
?>