<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Intake_Final_Renderer {
        
        public static function modal_intake_final_render($app,$modal){
            $modal->header_set(array('title'=>'Intake Final','icon'=>App_Icon::info()));
            $components = self::intake_final_components_render($app, $modal,true);
        }
        
        public static function intake_final_render($app,$form,$data,$path,$method){
            get_instance()->load->helper('intake_final/intake_final_engine');
            $path = Intake_Final_Engine::path_get();
            $id = $data['id'];
            $components = self::intake_final_components_render($app, $form,false);
            $back_href = $path->index;
            
            $form->button_add()->button_set('value','BACK')
                ->button_set('icon',App_Icon::btn_back())
                ->button_set('href',$back_href)
                ->button_set('class','btn btn-default')
                ;

            $js = '
                <script>
                    $("#intake_final_method").val("'.$method.'");
                    $("#intake_final_id").val("'.$id.'");
                </script>
            ';             
            $app->js_set($js);
            
            $js = '                
                    intake_final_init();
                    intake_final_bind_event();
                    intake_final_components_prepare(); 
            ';
            $app->js_set($js);
            
        }
        
        public static function intake_final_components_render($app,$form,$is_modal){
            
            get_instance()->load->helper('intake_final/intake_final_engine');
            $path = Intake_Final_Engine::path_get();            
            $components = array();
            $db = new DB();
            
            $id_prefix = 'intake_final';
            
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
                    //->input_select_set('hide_all',true)
                                        
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
                    //->input_select_set('hide_all',true)
                    ->detail_set('rows',$reference_detail)
                    ->detail_set('id',$id_prefix."_reference_detail")
                    ->detail_set('ajax_url','')
                    
                ;
            
            $form->datetimepicker_add()->datetimepicker_set('label',Lang::get('Intake Final Date'))
                    ->datetimepicker_set('id',$id_prefix.'_intake_final_date')
                    ->datetimepicker_set('value',Tools::_date('','F d, Y H:i')) 
                    ->datetimepicker_set('disable_all',true)
                    //->datetimepicker_set('hide_all',true)
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
                array('id'=>'intake_warehouse_to_code','name'=>'code','label'=>Lang::get('Code'),'type'=>'text')
                ,array('id'=>'intake_warehouse_to_name','name'=>'name','label'=>Lang::get('Name'),'type'=>'text')
                ,array('id'=>'intake_warehouse_to_type','name'=>'warehouse_type','label'=>Lang::get('Type'),'type'=>'text')
                ,array('id'=>'intake_warehouse_to_contact_name','name'=>'contact_name','label'=>Lang::get('Contact Name'),'type'=>'input')
                ,array('id'=>'intake_warehouse_to_address','name'=>'address','label'=>Lang::get('Address'),'type'=>'input')
                ,array('id'=>'intake_warehouse_to_phone','name'=>'phone','label'=>Lang::get('Phone'),'type'=>'input','attribute'=>'data-inputmask="\'mask\': \'(99) 99-999-99999\'" data-mask=""')
                
            );
            
            $components['intake_final_status'] = $form->input_select_add()
                    ->input_select_set('label','Status')
                    ->input_select_set('icon','fa fa-info')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id',$id_prefix.'_intake_final_status')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->input_select_set('is_module_status',true)
                    //->input_select_set('hide_all',true)
                    
                    ;
            
            $form->custom_component_add()->src_set('intake_final/view/intake_product_table_view');
            
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
            
            $js = get_instance()->load->view('intake_final/'.$id_prefix.'_basic_function_js',$param,TRUE);
            $app->js_set($js);
            return $components;
            
        }
        
        public static function intake_final_status_log_render($app,$form,$data,$path){
            get_instance()->load->helper('intake_final/intake_final_engine');
            $path = Intake_Final_Engine::path_get();
            
            $id = $data['id'];
            $db = new DB();
            $q = '
                select null row_num
                    ,t1.moddate
                    ,t1.intake_final_status
                    ,t2.name user_name
                    ,t3.intake_final_type
                from intake_final_status_log t1
                    inner join user_login t2 on t1.modid = t2.id
                    inner join intake_final t3 on t1.intake_final_id = t3.id
                where t1.intake_final_id = '.$id.'
                    order by moddate asc
            ';
            $rs = $db->query_array($q);
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                $rs[$i]['intake_final_status_name']  = SI::get_status_attr(                    
                    SI::status_get('Intake_Final_Engine',
                        $rs[$i]['intake_final_status']
                    )['label']
                );
                $rs[$i]['moddate'] = Tools::_date($rs[$i]['moddate'],'F d, Y H:i:s');
                
            }
            $intake_final_status_log = $rs;
            
            $table = $form->form_group_add()->table_add();
            $table->table_set('id','intake_final_intake_final_status_log_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'col_attrib'=>array()));
            $table->table_set('columns',array("name"=>"intake_final_status_name","label"=>"Status",'col_attrib'=>array()));
            $table->table_set('columns',array("name"=>"user_name","label"=>"User",'col_attrib'=>array()));
            $table->table_set('data',$intake_final_status_log);
        }
        
    }
    
?>