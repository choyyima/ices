<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class SIR_Renderer {
        
        public static function modal_sir_render($app,$modal){
            $modal->header_set(array('title'=>'System Investigation Report','icon'=>App_Icon::sir()));
            $components = self::sir_components_render($app, $modal,true);
        }
        
        public static function sir_render($app,$form,$data,$path,$method){
            get_instance()->load->helper('sir/sir_engine');
            $path = SIR_Engine::path_get();
            $id = $data['id'];
            $components = self::sir_components_render($app, $form,false);
            $back_href = $path->index;
            
            $form->button_add()->button_set('value','BACK')
                ->button_set('icon',App_Icon::btn_back())
                ->button_set('href',$back_href)
                ->button_set('class','btn btn-default')
                ;

            $js = '
                <script>
                    $("#sir_method").val("'.$method.'");
                    $("#sir_id").val("'.$id.'");
                </script>
            ';             
            $app->js_set($js);
            
            $js = '                
                    sir_init();
                    sir_bind_event();
                    sir_components_prepare(); 
            ';
            $app->js_set($js);
            
        }
        
        public static function sir_components_render($app,$form,$is_modal){
            
            get_instance()->load->helper('sir/sir_engine');
            $path = SIR_Engine::path_get();            
            $components = array();
            $db = new DB();
            
            $id_prefix = 'sir';
            
            $components['id'] = $form->input_add()->input_set('id',$id_prefix.'_id')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;
            
            $reference_detail = array(
                
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
            
            $form->input_select_add()
                ->input_select_set('label',Lang::get('Module Name'))
                ->input_select_set('icon',App_Icon::info())
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_module_name')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('ajax_url',$path->data_support.'input_select_module_name_get/')
                ->input_select_set('disable_all',true)
                 ->input_select_set('hide_all',true)
                
            ;
            
            $form->input_select_add()
                ->input_select_set('label',Lang::get('Module Action'))
                ->input_select_set('icon',App_Icon::info())
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_module_action')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('ajax_url',$path->data_support.'input_select_module_action_get/')
                ->input_select_set('disable_all',true)
                 ->input_select_set('hide_all',true)
                 
            ;
            
            $form->input_select_detail_add()
                ->input_select_set('label',Lang::get('Reference'))
                ->input_select_set('icon',App_Icon::info())
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_reference')
                ->input_select_set('min_length','1')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('ajax_url',$path->ajax_search.'input_select_reference_search/')
                ->input_select_set('disable_all',true)
                 ->input_select_set('hide_all',true)
                ->detail_set('rows',$reference_detail)
                ->detail_set('id',$id_prefix."_reference_detail")
                ->detail_set('ajax_url','')                    
            ;
                                                
            $form->datetimepicker_add()->datetimepicker_set('label',Lang::get(array('System Investigation Report','Date')))
                    ->datetimepicker_set('id',$id_prefix.'_sir_date')
                    ->datetimepicker_set('value',Tools::_date('','F d, Y H:i')) 
                    ->datetimepicker_set('disable_all',true)
                    ->datetimepicker_set('hide_all',true)
                ;
            
            $form->input_add()->input_set('label',Lang::get('Creator '))
                    ->input_set('id',$id_prefix.'_creator')
                    ->input_set('icon',App_Icon::user())
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            $components['sir_status'] = $form->input_select_add()
                ->input_select_set('label','Status')
                ->input_select_set('icon','fa fa-info')
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_sir_status')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('is_module_status',true)
                ->input_select_set('hide_all',true)      
                ->input_select_set('disable_all',true)
                ;
            
            $form->textarea_add()->textarea_set('label','Description')
                    ->textarea_set('id',$id_prefix.'_description')
                    ->textarea_set('value','')
                    ->textarea_set('attrib',array())       
                    ->textarea_set('disable_all',true)
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
            
            $js = get_instance()->load->view('sir/'.$id_prefix.'_basic_function_js',$param,TRUE);
            $app->js_set($js);
            
           
            return $components;
            
        }
        
        public static function sir_status_log_render($app,$form,$data,$path){
            get_instance()->load->helper('sir/sir_engine');
            $path = SIR_Engine::path_get();
            
            $id = $data['id'];
            $db = new DB();
            $q = '
                select null row_num
                    ,t1.moddate
                    ,t1.sir_status
                    ,t2.name user_name
                from sir_status_log t1
                    inner join user_login t2 on t1.modid = t2.id
                    inner join sir t3 
                        on t1.sir_id = t3.id
                where t1.sir_id = '.$id.'
                    order by moddate asc
            ';
            $rs = $db->query_array($q);
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                $rs[$i]['sir_status_name']  = SI::get_status_attr(                    
                    SI::status_get('SIR_Engine',
                        $rs[$i]['sir_status']
                    )['label']
                );
                $rs[$i]['moddate'] = Tools::_date($rs[$i]['moddate'],'F d, Y H:i:s');
                
            }
            $sir_status_log = $rs;
            
            $table = $form->form_group_add()->table_add();
            $table->table_set('id','sir_sir_status_log_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'col_attrib'=>array()));
            $table->table_set('columns',array("name"=>"sir_status_name","label"=>"Status",'col_attrib'=>array()));
            $table->table_set('columns',array("name"=>"user_name","label"=>"User",'col_attrib'=>array()));
            $table->table_set('data',$sir_status_log);
        }
        
        public static function sir_allocation_view_render($app,$form,$data,$path){
            get_instance()->load->helper('sir_allocation/sir_allocation_engine');
            get_instance()->load->helper('sir_allocation/sir_allocation_renderer');
            $id = $data['id'];
            $db = new DB();
            $rs = $db->fast_get('sir',array('id'=>$id));
            if(count($rs)>0) {
                $sir = $rs[0];            
                $form->form_group_add();
                if($sir['sir_status'] != 'X'){
                    if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'sir_allocation','add')){
                    $form->button_add()->button_set('class','primary')
                            ->button_set('value','New System Investigation Report Allocation')
                            ->button_set('icon','fa fa-plus')
                            ->button_set('attrib',array(
                                'data-toggle'=>"modal" 
                                ,'data-target'=>"#modal_sir_allocation"
                            ))
                            ->button_set('disable_after_click',false)
                            ->button_set('id','sir_allocation_new')
                        ;
                    }
                }
                $form->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
                $tbl = $form->table_add();
                $tbl->table_set('class','table');
                $tbl->table_set('id','sir_allocation_table');
                $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center'),"is_key"=>true));            
                $tbl->table_set('columns',array("name"=>"sir_allocation_type_text","label"=>"Reference Type",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"reference_code","label"=>"Reference Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"code","label"=>"Allocation Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"allocated_amount","label"=>"Allocated Amount (Rp.)",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"sir_allocation_status_text","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('data key','id');

                $q = '
                    select distinct NULL row_num
                        ,t1.*
                        ,t2.code sales_invoice_code
                        ,t3.code sir_code

                    from sir_allocation t1
                        left outer join sales_invoice t2 on t1.sales_invoice_id = t2.id
                        left outer join sir t3 on t1.sir_id = t3.id
                        inner join sir t4 on t4.id = t1.sir_id
                    where t4.id = '.$id.' order by t1.moddate desc

                ';
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){
                    $rs[$i]['reference_code'] = $rs[$i][$rs[$i]['sir_allocation_type'].'_code'];
                    $rs[$i]['sir_allocation_type_text'] = SI::type_get('SIR_Allocation_Engine',
                        $rs[$i]['sir_allocation_type'])['label'];
                    $rs[$i]['row_num'] = $i+1;
                    $rs[$i]['allocated_amount'] = Tools::thousand_separator($rs[$i]['allocated_amount'],2,true);
                    $rs[$i]['sir_allocation_status_text'] = SI::get_status_attr(
                        SI::status_get('SIR_Allocation_Engine', $rs[$i]['sir_allocation_status'])['label']
                    );
                }
                $tbl->table_set('data',$rs);
                

                $modal_sir_allocation = $app->engine->modal_add()->id_set('modal_sir_allocation')->width_set('75%');

                $sir_allocation_data = array(
                    'sir'=>array(
                        'id'=>$sir['id']
                    )                
                );
                $sir_allocation_data = json_decode(json_encode($sir_allocation_data));

                SIR_Allocation_Renderer::modal_sir_allocation_render(
                        $app
                        ,$modal_sir_allocation
                        ,$sir_allocation_data
                    );


                $param = array(
                    'index_url'=>$path->index
                    ,'ajax_search'=>$path->ajax_search
                    ,'sir_id'=>$sir['id']
                    ,'sir_text'=>$sir['code']
                );

                $js = get_instance()->load->view('sir/sir_allocation_js',$param,TRUE);
                $app->js_set($js);
                
            }
        }
        
        public static function customer_deposit_allocation_view_render($app,$form,$data,$path){
            get_instance()->load->helper('customer_deposit_allocation/customer_deposit_allocation_engine');
            get_instance()->load->helper('customer_deposit_allocation/customer_deposit_allocation_renderer');
            $id = $data['id'];
            $db = new DB();
            $q = '
                select t1.*
                from sir t1
                where t1.id = '.$db->escape($id).'
            ';
            $rs = $db->query_array($q);
            if(count($rs)>0) {
                $sir = $rs[0];            
                $form->form_group_add();
                
                $form->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
                $tbl = $form->table_add();
                $tbl->table_set('class','table');
                $tbl->table_set('id','customer_deposit_allocation_table');
                $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center'),"is_key"=>true));            
                $tbl->table_set('columns',array("name"=>"customer_deposit_code","label"=>"Customer Deposit Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"code","label"=>"Allocation Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"allocated_amount","label"=>"Allocated Amount (Rp.)",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"customer_deposit_allocation_status_text","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('data key','id');

                $q = '
                    select distinct NULL row_num
                        ,t1.*
                        ,t3.code customer_deposit_code
                        ,t3.id customer_deposit_id
                    from customer_deposit_allocation t1
                        inner join sir t2 on t1.sir_id = t2.id
                        inner join customer_deposit t3 on t3.id = t1.customer_deposit_id
                    where t2.id = '.$db->escape($id).' order by t1.moddate desc

                ';
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){
                    $rs[$i]['customer_deposit_allocation_type_text'] = SI::type_get('Customer_Deposit_Allocation_Engine',
                        $rs[$i]['customer_deposit_allocation_type'])['label'];
                    $rs[$i]['row_num'] = $i+1;
                    $rs[$i]['allocated_amount'] = Tools::thousand_separator($rs[$i]['allocated_amount'],2,true);
                    $rs[$i]['customer_deposit_allocation_status_text'] = SI::get_status_attr(
                        SI::status_get('Customer_Deposit_Allocation_Engine', $rs[$i]['customer_deposit_allocation_status'])['label']
                    );
                }
                $tbl->table_set('data',$rs);
                
                
                $modal_customer_deposit_allocation = $app->engine->modal_add()->id_set('modal_customer_deposit_allocation')->width_set('75%');

                $customer_deposit_allocation_data = array(
                    'customer_deposit'=>array(
                        'id'=>''
                    )                
                );
                $customer_deposit_allocation_data = json_decode(json_encode($customer_deposit_allocation_data));

                Customer_Deposit_Allocation_Renderer::modal_customer_deposit_allocation_render(
                        $app
                        ,$modal_customer_deposit_allocation
                        ,$customer_deposit_allocation_data
                    );

                
                $param = array(
                    'index_url'=>$path->index
                    ,'ajax_search'=>$path->ajax_search
                    ,'customer_deposit_id'=>''
                    ,'customer_deposit_text'=>''
                );

                $js = get_instance()->load->view('sir/sir_allocation_js',$param,TRUE);
                $app->js_set($js);
                
            }
        }
    }
    
?>