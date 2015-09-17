<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Refill_Receipt_Allocation_Renderer {
        
        public static function modal_refill_receipt_allocation_render($app,$modal){
            $modal->footer_attr_set(array('style'=>'display:none'));
            $modal->header_set(array('title'=>'Refill Receipt Allocation','icon'=>App_Icon::refill_receipt()));
            $components = self::refill_receipt_allocation_components_render($app, $modal,true);
            
        }
        
        public static function refill_receipt_allocation_render($app,$form,$data,$path,$method){
            get_instance()->load->helper('refill_receipt_allocation/refill_receipt_allocation_engine');
            $path = Refill_Receipt_Allocation_Engine::path_get();
            $id = $data['id'];
            $components = self::refill_receipt_allocation_components_render($app, $form,false);
            $back_href = $path->index;
            
            $form->button_add()->button_set('value','BACK')
                ->button_set('icon',App_Icon::btn_back())
                ->button_set('href',$back_href)
                ->button_set('class','btn btn-default')
                ;

            $js = '
                <script>
                    $("#refill_receipt_allocation_method").val("'.$method.'");
                    $("#refill_receipt_allocation_id").val("'.$id.'");
                </script>
            ';             
            $app->js_set($js);
            
            $js = '                
                    refill_receipt_allocation_init();
                    refill_receipt_allocation_bind_event();
                    refill_receipt_allocation_components_prepare(); 
            ';
            $app->js_set($js);
            
        }
        
        public static function refill_receipt_allocation_components_render($app,$form,$is_modal){
            
            get_instance()->load->helper('refill_receipt_allocation/refill_receipt_allocation_engine');
            $path = Refill_Receipt_Allocation_Engine::path_get();            
            $components = array();
            $db = new DB();
            
            $id_prefix = Refill_Receipt_Allocation_Engine::$prefix_id;
            
            $components['id'] = $form->input_add()->input_set('id',$id_prefix.'_id')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;
            
            $form->input_add()->input_set('id',$id_prefix.'_customer_id')
                    ->input_set('value','')
                    ->input_set('hide_all',true)
                    ;
            
            $disabled = array('disable'=>'');
            
            $form->input_add()->input_set('id',$id_prefix.'_type')
                    ->input_set('value','')
                    ->input_set('hide_all',true)
                    ;
            
            $reference_detail = array(
                array('name'=>'type','label'=>Lang::get('Type'))
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
            
            $cust_dep_detail = array(
                array('name'=>'amount','label'=>Lang::get('Amount')),
                array('name'=>'outstanding_amount','label'=>Lang::get('Outstanding Amount')),
                array('name'=>'refill_receipt_date','label'=>Lang::get('Refill Receipt Date'))
            );
            
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
                    ->input_select_set('label',Lang::get('Refill Receipt'))
                    ->input_select_set('icon','fa fa-money')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id',$id_prefix.'_refill_receipt')
                    ->input_select_set('min_length','1')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->input_select_set('ajax_url',$path->ajax_search.'input_select_refill_receipt_search/')
                    ->input_select_set('disable_all',true)
                    ->input_select_set('hide_all',true)
                    ->detail_set('rows',$cust_dep_detail)
                    ->detail_set('id',$id_prefix."_refill_receipt_detail")
                    ->detail_set('ajax_url',$path->data_support.'input_select_refill_receipt_detail_get/')                    
                ;
            
            $reference_detail = array();
            
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
                        
            $form->input_add()->input_set('label',Lang::get('Allocated Amount '))
                    ->input_set('id',$id_prefix.'_allocated_amount')
                    ->input_set('icon',App_Icon::money())
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            
            $components['refill_receipt_allocation_status'] = $form->input_select_add()
                ->input_select_set('label','Status')
                ->input_select_set('icon','fa fa-info')
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_refill_receipt_allocation_status')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('is_module_status',true)
                ->input_select_set('hide_all',true)                    
                
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
            
            $js = get_instance()->load->view('refill_receipt_allocation/'.$id_prefix.'_basic_function_js',$param,TRUE);
            $app->js_set($js);
            return $components;
            
        }
        
        public static function refill_receipt_allocation_status_log_render($app,$form,$data,$path){
            get_instance()->load->helper('refill_receipt_allocation/refill_receipt_allocation_engine');
            $path = Refill_Receipt_Allocation_Engine::path_get();
            
            $id = $data['id'];
            $db = new DB();
            $q = '
                select null row_num
                    ,t1.moddate
                    ,t1.refill_receipt_allocation_status
                    ,t2.name user_name
                from refill_receipt_allocation_status_log t1
                    inner join user_login t2 on t1.modid = t2.id
                    inner join refill_receipt_allocation t3 
                        on t1.refill_receipt_allocation_id = t3.id
                where t1.refill_receipt_allocation_id = '.$id.'
                    order by moddate asc
            ';
            $rs = $db->query_array($q);
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                $rs[$i]['refill_receipt_allocation_status_name']  = SI::get_status_attr(                    
                    SI::status_get('Refill_Receipt_Allocation_Engine',
                        $rs[$i]['refill_receipt_allocation_status']
                    )['label']
                );
                $rs[$i]['moddate'] = Tools::_date($rs[$i]['moddate'],'F d, Y H:i:s');
                
            }
            $refill_receipt_allocation_status_log = $rs;
            
            $table = $form->form_group_add()->table_add();
            $table->table_set('id','refill_receipt_allocation_refill_receipt_allocation_status_log_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'col_attrib'=>array()));
            $table->table_set('columns',array("name"=>"refill_receipt_allocation_status_name","label"=>"Status",'col_attrib'=>array()));
            $table->table_set('columns',array("name"=>"user_name","label"=>"User",'col_attrib'=>array()));
            $table->table_set('data',$refill_receipt_allocation_status_log);
        }
        
        
    }
    
?>