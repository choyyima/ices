<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Purchase_Return_Renderer {
        
        public static function modal_purchase_return_render($app,$modal){
            $modal->header_set(array('title'=>'Receive Product','icon'=>App_Icon::info()));
            $components = self::purchase_return_components_render($app, $modal, null,true);
            
            
        }
        
        public static function purchase_return_render($app,$form,$data,$path,$method){
            get_instance()->load->helper($path->purchase_return_engine);
            $path = Purchase_Return_Engine::path_get();
            $id = $data['id'];
            $components = self::purchase_return_components_render($app, $form,false);
            $back_href = $path->index;
            
            $form->button_add()->button_set('value','BACK')
                ->button_set('icon',App_Icon::btn_back())
                ->button_set('href',$back_href)
                ->button_set('class','btn btn-default')
                ;

            $js = '
                <script>
                    $("#purchase_return_method").val("'.$method.'");
                    $("#purchase_return_id").val("'.$id.'");
                </script>
            ';             
            $app->js_set($js);
            
            $js = '                
                    purchase_return_init();
                    purchase_return_bind_event();
                    purchase_return_components_prepare(); 
            ';
            $app->js_set($js);
            
        }
        
        public static function purchase_return_components_render($app,$form,$is_modal){
            
            get_instance()->load->helper('transaction/purchase/return/purchase_return_engine');
            $path = Purchase_Return_Engine::path_get();            
            $components = array();
            $db = new DB();
            $components['id'] = $form->input_add()->input_set('id','purchase_return_id')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;
            
            $form->input_add()->input_set('id','purchase_return_method')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;            
            
            //$disabled = array('disable'=>'');
            
            $asd = $form->input_add()->input_set('label','Code')
                    ->input_set('id','purchase_return_code')
                    ->input_set('icon','fa fa-info')
                    ->input_set('attrib',array('disabled'=>'','style'=>'font-weight:bold'))
                    ->div_set('id','purchase_return_div_code')
                    
                ;
            
            $purchase_invoice_detail = array(
                array('name'=>'code','label'=>'Code')
                ,array('name'=>'supplier_name','label'=>'Supplier')
                ,array('name'=>'grand_total','label'=>'Grand Total ('.Tools::currency_get().')')
                ,array('name'=>'outstanding_qty','label'=>'Outstanding Qty (units)')
            );
            
            $components['purchase_invoice'] = $form->input_select_detail_add()
                    ->div_set('id','purchase_return_div_purchase_invoice')
                    ->input_select_set('label','Purchase Invoice')
                    ->input_select_set('icon',App_Icon::info())
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','purchase_return_purchase_invoice')
                    ->input_select_set('min_length','1')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->input_select_set('ajax_url',$path->ajax_search.'purchase_return_transaction/input_select_purchase_invoice_search')
                    ->detail_set('rows',$purchase_invoice_detail)
                    ->detail_set('id',"purchase_return_purchase_invoice_detail")
                    ->detail_set('ajax_url',$path->ajax_search.'purchase_return_transaction/input_select_purchase_invoice_get')
                ;
            
            $form->datetimepicker_add()->datetimepicker_set('label','Purchase Return Date')
                    ->datetimepicker_set('id','purchase_return_purchase_return_date')
                    ->datetimepicker_set('value',(string)date('Y-m-d H:i')) 
                    ->div_set('id','purchase_return_div_purchase_return_date')
                ;
            
            
            
            $components['purchase_return_status'] = $form->input_select_add()
                    ->input_select_set('label','Status')
                    ->input_select_set('icon','fa fa-info')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','purchase_return_purchase_return_status')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                    ->div_set('id','purchase_return_div_purchase_return_status')
                    ;
            
            $components['cancellation_reason']=$form->textarea_add()->textarea_set('label','Cencellation Reason')
                    ->textarea_set('id','purchase_return_cancellation_reason')
                    ->textarea_set('value','')
                    ->div_set('id','purchase_return_div_cancellation_reason')  
                    ;
            
            $form->custom_component_add()
                        ->src_set('transaction/purchase/return/purchase_return_view')
                        ;

            $form->textarea_add()->textarea_set('label','Notes')
                    ->textarea_set('id','purchase_return_notes')
                    ->textarea_set('value','')
                    ->textarea_set('attrib',array())       
                    ->div_set('id','purchase_return_div_notes')
                    ;
            
            $form->hr_add()->hr_set('class','');
            
            $form->button_add()->button_set('value','Submit')
                            ->button_set('id','purchase_return_submit')
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
                $param['detail_tab'] = '#modal_purchase_return';
                $param['view_url'] = '';
                $param['window_scroll'] = '#modal_purchase_return';
            }
            
            $js = get_instance()->load->view('transaction/purchase/return/purchase_return_basic_function_js',$param,TRUE);
            $app->js_set($js);
            
            return $components;
            
        }
        
        public static function purchase_return_status_log_render($app,$form,$data,$path){
            get_instance()->load->helper('purchase_return/purchase_return_engine');
            $id = $data['id'];
            $db = new DB();
            $q = '
                select null row_num
                    ,t1.moddate
                    ,purchase_return_status
                    ,t2.name user_name
                from purchase_return_status_log t1
                    inner join user_login t2 on t1.modid = t2.id
                where t1.purchase_return_id = '.$id.'
                    order by moddate asc
            ';
            $rs = $db->query_array($q);
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                $rs[$i]['purchase_return_status_name'] = 
                        SI::get_status_attr(Purchase_Return_Engine::purchase_return_status_get($rs[$i]['purchase_return_status'])['label']);
                
            }
            $purchase_return_status_log = $rs;
            
            $table = $form->form_group_add()->table_add();
            $table->table_set('id','purchase_return_purchase_return_add_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'col_attrib'=>array('style'=>'text-align:left')));
            $table->table_set('columns',array("name"=>"purchase_return_status_name","label"=>"Status",'col_attrib'=>array('style'=>'text-align:left')));
            $table->table_set('columns',array("name"=>"user_name","label"=>"User",'col_attrib'=>array('style'=>'text-align:left')));
            $table->table_set('data',$purchase_return_status_log);
        }
        
    }
    
?>