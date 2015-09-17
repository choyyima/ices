<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Intake_Renderer {
        
        public static function modal_intake_render($app,$modal){
            $modal->header_set(array('title'=>Lang::get('Product Intake'),'icon'=>App_Icon::info()));
            $components = self::intake_components_render($app, $modal,true);
            
            
        }
        
        public static function intake_render($app,$form,$data,$path,$method){
            get_instance()->load->helper('intake/intake_engine');
            $path = Intake_Engine::path_get();
            $id = $data['id'];
            $components = self::intake_components_render($app, $form,false);
            $back_href = $path->index;
            
            $form->button_add()->button_set('value','BACK')
                ->button_set('icon',App_Icon::btn_back())
                ->button_set('href',$back_href)
                ->button_set('class','btn btn-default')
                ;

            $js = '
                <script>
                    $("#intake_method").val("'.$method.'");
                    $("#intake_id").val("'.$id.'");
                </script>
            ';             
            $app->js_set($js);
            
            $js = '                
                    intake_init();
                    intake_bind_event();
                    intake_components_prepare(); 
            ';
            $app->js_set($js);
            
        }
        
        public static function intake_components_render($app,$form,$is_modal){
            
            get_instance()->load->helper('intake/intake_engine');
            $path = Intake_Engine::path_get();            
            $components = array();
            $db = new DB();
            $components['id'] = $form->input_add()->input_set('id','intake_id')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;
            
            $form->input_add()->input_set('id','intake_type')
                    ->input_set('value','')
                    ->input_set('hide_all',true)
                    ;
            
            $disabled = array('disable'=>'');
            
            
            $form->input_add()->input_set('id','intake_method')
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
                    ->input_select_set('id','intake_store')
                    ->input_select_set('data_add',$store_list)
                    ->input_select_set('value',array())
                    ->input_select_set('disable_all',true)
                                        
                ;
            
            $form->input_add()->input_set('label',Lang::get('Code'))
                    ->input_set('id','intake_code')
                    ->input_set('icon','fa fa-info')
                    ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
                ;
            
            $reference_detail = array(
                //array('name'=>'type','label'=>Lang::get('Type'))
                //,array('name'=>'code','label'=>Lang::get('Code'))
            );
            
            $form->input_select_detail_add()
                    ->input_select_set('label',Lang::get('Reference'))
                    ->input_select_set('icon',App_Icon::info())
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','intake_reference')
                    ->input_select_set('min_length','1')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->input_select_set('ajax_url',$path->ajax_search.'input_select_reference_search')
                    ->input_select_set('disable_all',true)
                    ->detail_set('rows',$reference_detail)
                    ->detail_set('id',"intake_reference_detail")
                    ->detail_set('ajax_url','')
                    
                ;
            
            $form->datetimepicker_add()->datetimepicker_set('label',Lang::get(array('Product Intake','Date')))
                    ->datetimepicker_set('id','intake_intake_date')
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
                    ->input_select_set('id','intake_warehouse_from')
                    ->input_select_set('data_add',$warehouse_list_from)
                    ->input_select_set('value',array())
                    ->input_select_set('disable_all',true)
                ;
            
            $components['intake_status'] = $form->input_select_add()
                    ->input_select_set('label','Status')
                    ->input_select_set('icon','fa fa-info')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','intake_intake_status')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->div_set('id','intake_div_intake_status')
                    ->input_select_set('is_module_status',true)
                    ;

            
            $table = $form->form_group_add()->table_add();
            $table->table_set('id','intake_product_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"product_type","label"=>"",'col_attrib'=>array('class'=>'hidden')));
            $table->table_set('columns',array("name"=>"product_id","label"=>"",'col_attrib'=>array('class'=>'hidden')));
            $table->table_set('columns',array("name"=>"product_img","label"=>"",'header_class'=>'product-img','col_attrib'=>array('style'=>'')));
            $table->table_set('columns',array("name"=>"product_name","label"=>"Product",'col_attrib'=>array('style'=>'')));
            $table->table_set('columns',array("name"=>"unit_id","label"=>"",'col_attrib'=>array('style'=>'text-align:center;display:none')));            
            $table->table_set('columns',array("name"=>"unit_name","label"=>"Unit",'col_attrib'=>array('style'=>'width:150px')));
            $table->table_set('columns',array("name"=>"qty","label"=>"Qty",'col_attrib'=>array('style'=>'text-align:right;width:150px')));
            $table->table_set('columns',array("name"=>"action","label"=>"",'header_class'=>'table-action','col_attrib'=>array('style'=>'text-align:center;')));
        
            $form->textarea_add()->textarea_set('label','Notes')
                    ->textarea_set('id','intake_notes')
                    ->textarea_set('value','')
                    ->textarea_set('attrib',array())       
                    ->textarea_set('disable_all',true)
                    ->div_set('id','intake_div_notes')
                    
                    ;
            
            
            $form->hr_add()->hr_set('class','');
            
            $form->button_add()->button_set('value','Submit')
                            ->button_set('id','intake_submit')
                            ->button_set('icon',App_Icon::detail_btn_save())
                        ;
            
            $form->button_add()->button_set('value','Print')
                            ->button_set('id','intake_print')
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
                $param['detail_tab'] = '#modal_intake .modal-body';
                $param['view_url'] = '';
                $param['window_scroll'] = '#modal_intake';
            }
            
            $js = get_instance()->load->view('intake/intake_basic_function_js',$param,TRUE);
            $app->js_set($js);
            //$js = get_instance()->load->view('intake/intake_rma_js',$param,TRUE);
            //$app->js_set($js);
            return $components;
            
        }
        
        public static function intake_status_log_render($app,$form,$data,$path){
            get_instance()->load->helper('intake/intake_engine');
            $path = Intake_Engine::path_get();
            
            $id = $data['id'];
            $db = new DB();
            $q = '
                select null row_num
                    ,t1.moddate
                    ,t1.intake_status
                    ,t2.name user_name
                    ,t3.intake_type
                from intake_status_log t1
                    inner join user_login t2 on t1.modid = t2.id
                    inner join intake t3 on t1.intake_id = t3.id
                where t1.intake_id = '.$id.'
                    order by moddate asc
            ';
            $rs = $db->query_array($q);
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                $rs[$i]['intake_status_name']  = SI::get_status_attr(                    
                    SI::status_get('Intake_Engine',
                        $rs[$i]['intake_status']
                    )['label']
                );
                $rs[$i]['moddate'] = Tools::_date($rs[$i]['moddate'],'F d, Y H:i:s');
                
            }
            $intake_status_log = $rs;
            
            $table = $form->form_group_add()->table_add();
            $table->table_set('id','intake_intake_status_log_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'col_attrib'=>array()));
            $table->table_set('columns',array("name"=>"intake_status_name","label"=>"Status",'col_attrib'=>array()));
            $table->table_set('columns',array("name"=>"user_name","label"=>"User",'col_attrib'=>array()));
            $table->table_set('data',$intake_status_log);
        }
        
    }
    
?>