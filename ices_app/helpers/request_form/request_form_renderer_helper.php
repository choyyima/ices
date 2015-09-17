<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    get_instance()->load->helper('request_form/request_form_engine');
    class Request_Form_Renderer {
        
        public static function modal_request_form_render($app,$modal){
            $modal->header_set(array('title'=>'Receive Product','icon'=>App_Icon::info()));
            $components = self::request_form_components_render($app, $modal, null,true);
            
            
        }
        
        public static function request_form_render($app,$form,$data,$path,$method){
            //get_instance()->load->helper('request_form/request_form_engine');
            $path = Request_Form_Engine::path_get();
            $id = $data['id'];
            $components = self::request_form_components_render($app, $form, $data,false);
            $back_href = $path->index;
            
            $form->button_add()->button_set('value','BACK')
                ->button_set('icon',App_Icon::btn_back())
                ->button_set('href',$back_href)
                ->button_set('class','btn btn-default')
                ;

            $js = '
                <script>
                    $("#request_form_method").val("'.$method.'");
                    $("#request_form_id").val("'.$id.'");
                </script>
            ';             
            $app->js_set($js);
            
            $js = '                
                    request_form_init();
                    request_form_bind_event();
                    request_form_components_prepare(); 
                    //$("#request_form_request_form_type").select2("data",{id:"1",text:"DUMMY DATA"}).change();
            ';
            $app->js_set($js);
            
        }
        
        public static function request_form_components_render($app,$form,$data,$is_modal){
            
            
            $path = Request_Form_Engine::path_get();            
            $components = array();
            $db = new DB();
            $components['id'] = $form->input_add()->input_set('id','request_form_id')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;
            
            $form->input_add()->input_set('id','request_form_method')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;            
            
            $disabled = array('disable'=>'');
            
            
            $request_form_type_list = array();
            $q = 'select id id, name data from request_form_type where status >0';            
            $request_form_type_list = $db->query_array($q);
            
            
            $form->input_select_add()
                    ->input_select_set('label','Type')
                    ->input_select_set('icon',App_Icon::info())
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','request_form_request_form_type')
                    ->input_select_set('data_add',$request_form_type_list)
                    ->input_select_set('value',array())
                    ->div_set('hide',true)
                    //->div_set('id','request_form_div_type')                                        
                ;
            
            $form->input_add()->input_set('label','Code')
                    ->input_set('id','request_form_code')
                    ->input_set('icon','fa fa-info')
                    ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
                    ->div_set('hide',true)
                ;
            
            $form->input_add()->input_set('label','Requester')
                    ->input_set('id','request_form_requester')
                    ->input_set('icon','fa fa-info')
                    ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
                    ->div_set('hide',true)
                ;
            
            $warehouse_list = array();
            $q = 'select id id, name data from warehouse where status>0';            
            $warehouse_list = $db->query_array($q);
            
            $form->input_select_add()
                    ->input_select_set('label','From Warehouse')
                    ->input_select_set('icon',App_Icon::warehouse())
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','request_form_mutation_warehouse_from')
                    ->input_select_set('data_add',$warehouse_list)
                    ->input_select_set('value',array())
                    ->div_set('hide',true)
                    //->div_set('id','request_form_div_warehouse_from')
                                        
                ;
            
            $form->input_select_add()
                    ->input_select_set('label','To Warehouse')
                    ->input_select_set('icon',App_Icon::warehouse())
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','request_form_mutation_warehouse_to')
                    ->input_select_set('data_add',$warehouse_list)
                    ->input_select_set('value',array())
                    ->div_set('hide',true)
                    //->div_set('id','request_form_div_warehouse_to')                                        
                ;
            
            $form->datetimepicker_add()->datetimepicker_set('label','Request Form Date')
                    ->datetimepicker_set('id','request_form_request_form_date')
                    ->datetimepicker_set('value','') 
                    ->div_set('hide',true)
                    //->div_set('id','request_form_div_request_form_date')
                ;
            
            $components['request_form_status'] = $form->input_select_add()
                    ->input_select_set('label','Status')
                    ->input_select_set('icon','fa fa-info')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','request_form_request_form_status')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->div_set('hide',true)
                    //->div_set('id','request_form_div_request_form_status')
                    ;
            
            $components['cancellation_reason']=$form->textarea_add()->textarea_set('label','Cencellation Reason')
                    ->textarea_set('id','request_form_cancellation_reason')
                    ->textarea_set('value','')
                    ->div_set('hide',true)
                    //->div_set('id','request_form_div_cancellation_reason')  
                    ;
            
            $form->textarea_add()->textarea_set('label','Notes')
                    ->textarea_set('id','request_form_notes')
                    ->textarea_set('value','')
                    ->textarea_set('attrib',array())  
                    ->div_set('hide',true)
                    //->div_set('id','request_form_div_notes')
                    ;
            
            $form->input_select_add()
                ->input_select_set('label','Product')
                ->input_select_set('icon',App_Icon::product())
                ->input_select_set('min_length','1')
                ->input_select_set('value',array())
                ->input_select_set('id','request_form_request_form_mutation_product')
                ->input_select_set('ajax_url',$path->ajax_search.'request_form_mutation_product')
                ->div_set('hide',true)
                ;
            
            $table = $form->form_group_add()->div_set('hide',true)->table_add();
            $table->table_set('id','request_form_request_form_mutation_add_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"product_id","label"=>"",'col_attrib'=>array('class'=>'hidden')));
            $table->table_set('columns',array("name"=>"product_img","label"=>"",'col_attrib'=>array('style'=>'text-align:left;width:100px')));
            $table->table_set('columns',array("name"=>"product_name","label"=>"Product",'col_attrib'=>array('style'=>'text-align:left')));
            $table->table_set('columns',array("name"=>"qty","label"=>"Qty",'col_attrib'=>array('style'=>'text-align:left;width:200px')));
            $table->table_set('columns',array("name"=>"unit_id","label"=>"",'col_attrib'=>array('style'=>'text-align:left;display:none')));
            $table->table_set('columns',array("name"=>"unit_name","label"=>"Unit",'col_attrib'=>array('style'=>'text-align:left;width:100px')));
            $table->table_set('columns',array("name"=>"action","label"=>"",'col_attrib'=>array('style'=>'text-align:left;width:30px')));
            
            
            $table = $form->form_group_add()->div_set('hide',true)->table_add();
            $table->table_set('id','request_form_request_form_mutation_view_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"product_img","label"=>"",'col_attrib'=>array('style'=>'text-align:left;width:100px')));
            $table->table_set('columns',array("name"=>"product_name","label"=>"Product",'col_attrib'=>array('style'=>'text-align:left')));
            $table->table_set('columns',array("name"=>"qty","label"=>"Qty",'col_attrib'=>array('style'=>'text-align:left;width:200px')));
            $table->table_set('columns',array("name"=>"unit_name","label"=>"Unit",'col_attrib'=>array('style'=>'text-align:left;width:100px')));
            $table->table_set('columns',array("name"=>"action","label"=>"",'col_attrib'=>array('style'=>'text-align:left;width:30px')));
            
            
            
            $form->hr_add()->hr_set('class','');
            
            $form->button_add()->button_set('value','Submit')
                            ->button_set('id','request_form_submit')
                            ->button_set('icon',App_Icon::detail_btn_save())
                            ->button_set('style','display:none')
                        ;
            /*
            $form->button_add()->button_set('value','Print')
                            ->button_set('id','request_form_print')
                            ->button_set('icon',App_Icon::printer())
                            ->button_set('class','btn btn-default pull-right')
                            ->button_set('disable_after_click',false)
                    ;
            */
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
                $param['detail_tab'] = '#modal_request_form';
                $param['view_url'] = '';
                $param['window_scroll'] = '#modal_request_form';
            }
            
            $param['mutation_id'] = $db->query_array_obj('select id from request_form_type where lower(code) = "mutation"')[0]->id;
            
            $js = get_instance()->load->view('request_form/request_form_basic_function_js',$param,TRUE);
            $app->js_set($js);
            $js = get_instance()->load->view('request_form/request_form_mutation_js',array(),TRUE);
            $app->js_set($js);
            
            return $components;
            
        }
        
        public static function request_form_status_log_render($app,$form,$data,$path){
            //get_instance()->load->helper('request_form/request_form_engine');
            $id = $data['id'];
            $db = new DB();
            $q = '
                select null row_num
                    ,t1.moddate
                    ,request_form_status
                    ,t2.name user_name
                from request_form_status_log t1
                    inner join user_login t2 on t1.modid = t2.id
                where t1.request_form_id = '.$id.'
                    order by moddate asc
            ';
            $rs = $db->query_array($q);
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                $rs[$i]['request_form_status_name'] = 
                        SI::get_status_attr(Request_Form_Engine::request_form_mutation_status_get($rs[$i]['request_form_status'])['label']);
                
            }
            $request_form_status_log = $rs;
            
            $table = $form->form_group_add()->table_add();
            $table->table_set('id','request_form_request_form_add_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'col_attrib'=>array('style'=>'text-align:left')));
            $table->table_set('columns',array("name"=>"request_form_status_name","label"=>"Status",'col_attrib'=>array('style'=>'text-align:left')));
            $table->table_set('columns',array("name"=>"user_name","label"=>"User",'col_attrib'=>array('style'=>'text-align:left')));
            $table->table_set('data',$request_form_status_log);
        }
        
    }
    
?>