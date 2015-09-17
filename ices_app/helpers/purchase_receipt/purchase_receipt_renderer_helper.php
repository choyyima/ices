<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Purchase_Receipt_Renderer {
        
        public static function modal_purchase_receipt_render($app,$modal){
            $modal->header_set(array('title'=>'Purchase Receipt','icon'=>App_Icon::purchase_receipt()));
            $components = self::purchase_receipt_components_render($app, $modal,true);
        }
        
        public static function purchase_receipt_render($app,$form,$data,$path,$method){
            get_instance()->load->helper('purchase_receipt/purchase_receipt_engine');
            $path = Purchase_Receipt_Engine::path_get();
            $id = $data['id'];
            $components = self::purchase_receipt_components_render($app, $form,false);
            $back_href = $path->index;
            
            $form->button_add()->button_set('value','BACK')
                ->button_set('icon',App_Icon::btn_back())
                ->button_set('href',$back_href)
                ->button_set('class','btn btn-default')
                ;

            $js = '
                <script>
                    $("#purchase_receipt_method").val("'.$method.'");
                    $("#purchase_receipt_id").val("'.$id.'");
                </script>
            ';             
            $app->js_set($js);
            
            $js = '                
                    purchase_receipt_init();
                    purchase_receipt_bind_event();
                    purchase_receipt_components_prepare(); 
            ';
            $app->js_set($js);
            
        }
        
        public static function purchase_receipt_components_render($app,$form,$is_modal){
            
            get_instance()->load->helper('purchase_receipt/purchase_receipt_engine');
            $path = Purchase_Receipt_Engine::path_get();            
            $components = array();
            $db = new DB();
            
            $id_prefix = 'purchase_receipt';
            
            $components['id'] = $form->input_add()->input_set('id',$id_prefix.'_id')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;
            
            $disabled = array('disable'=>'');
                                    
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
                ->input_select_set('label','Supplier')
                ->input_select_set('icon',APP_Icon::user())
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_supplier')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('hide_all',true)                    
                ->input_select_set('disable_all',true)  
                ->input_select_set('ajax_url',$path->ajax_search.'input_select_supplier_search/')
                ;
            
            $form->input_select_add()
                    ->input_select_set('label',Lang::get('Payment Type'))
                    ->input_select_set('icon',App_Icon::info())
                    ->input_select_set('min_length','0')
                    ->input_select_set('id',$id_prefix.'_payment_type')
                    ->input_select_set('value',array())
                    ->input_select_set('disable_all',true)
                    ->input_select_set('hide_all',true)    
                    ->input_select_set('ajax_url','')
                ;
            
            
            
            $form->datetimepicker_add()->datetimepicker_set('label',Lang::get('Purchase Receipt Date'))
                    ->datetimepicker_set('id',$id_prefix.'_purchase_receipt_date')
                    ->datetimepicker_set('value',Tools::_date('','F d, Y H:i')) 
                    ->datetimepicker_set('disable_all',true)
                    ->datetimepicker_set('hide_all',true)
                ;
            
            $form->input_add()->input_set('label',Lang::get('Supplier Bank Acc.'))
                    ->input_set('id',$id_prefix.'_supplier_bank_acc')
                    ->input_set('icon',App_Icon::money())
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            $form->input_add()->input_set('label',Lang::get('BOS Bank Name'))
                    ->input_set('id',$id_prefix.'_bos_bank_name')
                    ->input_set('icon',App_Icon::money())
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            $form->input_add()->input_set('label',Lang::get('BOS Bank Acc.'))
                    ->input_set('id',$id_prefix.'_bos_bank_acc')
                    ->input_set('icon',App_Icon::money())
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            $form->input_add()->input_set('label',Lang::get('Amount'))
                    ->input_set('id',$id_prefix.'_amount')
                    ->input_set('icon',App_Icon::money())
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            $form->input_add()->input_set('label',Lang::get('Outstanding Amount '))
                    ->input_set('id',$id_prefix.'_outstanding_amount')
                    ->input_set('icon',App_Icon::money())
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
                 
            $form->input_add()->input_set('label',Lang::get('Change Amount '))
                    ->input_set('id',$id_prefix.'_change_amount')
                    ->input_set('icon',App_Icon::money())
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            $components['purchase_receipt_status'] = $form->input_select_add()
                ->input_select_set('label','Status')
                ->input_select_set('icon','fa fa-info')
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_purchase_receipt_status')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('is_module_status',true)
                ->input_select_set('hide_all',true)                    
                ;
            
            $form->textarea_add()->textarea_set('label','Notes')
                    ->textarea_set('id','purchase_receipt_notes')
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
            
            $js = get_instance()->load->view('purchase_receipt/'.$id_prefix.'_basic_function_js',$param,TRUE);
            $app->js_set($js);
            return $components;
            
        }
        
        public static function purchase_receipt_status_log_render($app,$form,$data,$path){
            //<editor-fold defaultstate="collapsed">
            get_instance()->load->helper('purchase_receipt/purchase_receipt_engine');
            $path = Purchase_Receipt_Engine::path_get();
            
            $id = $data['id'];
            $db = new DB();
            $q = '
                select null row_num
                    ,t1.moddate
                    ,t1.purchase_receipt_status
                    ,t2.name user_name
                from purchase_receipt_status_log t1
                    inner join user_login t2 on t1.modid = t2.id
                    inner join purchase_receipt t3 
                        on t1.purchase_receipt_id = t3.id
                where t1.purchase_receipt_id = '.$id.'
                    order by moddate asc
            ';
            $rs = $db->query_array($q);
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                $rs[$i]['purchase_receipt_status_name']  = SI::get_status_attr(                    
                    SI::status_get('Purchase_Receipt_Engine',
                        $rs[$i]['purchase_receipt_status']
                    )['label']
                );
                $rs[$i]['moddate'] = Tools::_date($rs[$i]['moddate'],'F d, Y H:i:s');
                
            }
            $purchase_receipt_status_log = $rs;
            
            $table = $form->form_group_add()->table_add();
            $table->table_set('id','purchase_receipt_purchase_receipt_status_log_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'col_attrib'=>array()));
            $table->table_set('columns',array("name"=>"purchase_receipt_status_name","label"=>"Status",'col_attrib'=>array()));
            $table->table_set('columns',array("name"=>"user_name","label"=>"User",'col_attrib'=>array()));
            $table->table_set('data',$purchase_receipt_status_log);
            //</editor-fold>
        }
        
        public static function purchase_receipt_allocation_view_render($app,$form,$data,$path){
            //<editor-fold defaultstate="collapsed">
            
            get_instance()->load->helper('purchase_receipt_allocation/purchase_receipt_allocation_engine');
            get_instance()->load->helper('purchase_receipt_allocation/purchase_receipt_allocation_renderer');
            $id = $data['id'];
            $db = new DB();
            $rs = $db->fast_get('purchase_receipt',array('id'=>$id));
            if(count($rs)>0) {
                $purchase_receipt = $rs[0];            
                $form->form_group_add();
                if($purchase_receipt['purchase_receipt_status'] != 'X'){
                    if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'purchase_receipt_allocation','add')){
                    $form->button_add()->button_set('class','primary')
                            ->button_set('value','New Purchase Receipt Allocation')
                            ->button_set('icon','fa fa-plus')
                            ->button_set('attrib',array(
                                'data-toggle'=>"modal" 
                                ,'data-target'=>"#modal_purchase_receipt_allocation"
                            ))
                            ->button_set('disable_after_click',false)
                            ->button_set('id','purchase_receipt_allocation_new')
                        ;
                    }
                }
                $form->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
                $tbl = $form->table_add();
                $tbl->table_set('class','table');
                $tbl->table_set('id','purchase_receipt_allocation_table');
                $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
                $tbl->table_set('columns',array("name"=>"purchase_receipt_allocation_type_text","label"=>"Reference Type",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"reference_code","label"=>"Reference Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"code","label"=>"Allocation Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center'),"is_key"=>true));
                $tbl->table_set('columns',array("name"=>"allocated_amount","label"=>"Allocated Amount (Rp.)",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"purchase_receipt_allocation_status_text","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('data key','id');

                $q = '
                    select distinct NULL row_num
                        ,t1.*
                        ,t2.code purchase_invoice_code

                    from purchase_receipt_allocation t1
                        left outer join purchase_invoice t2 on t1.purchase_invoice_id = t2.id
                        inner join purchase_receipt t4 on t4.id = t1.purchase_receipt_id
                    where t4.id = '.$id.' order by t1.moddate desc

                ';
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){
                    $rs[$i]['reference_code'] = $rs[$i][$rs[$i]['purchase_receipt_allocation_type'].'_code'];
                    $rs[$i]['purchase_receipt_allocation_type_text'] = SI::type_get('Purchase_Receipt_Allocation_Engine',
                        $rs[$i]['purchase_receipt_allocation_type'])['label'];
                    $rs[$i]['row_num'] = $i+1;
                    $rs[$i]['allocated_amount'] = Tools::thousand_separator($rs[$i]['allocated_amount'],2,true);
                    $rs[$i]['purchase_receipt_allocation_status_text'] = SI::get_status_attr(
                        SI::status_get('Purchase_Receipt_Allocation_Engine', $rs[$i]['purchase_receipt_allocation_status'])['label']
                    );
                }
                $tbl->table_set('data',$rs);
                

                $modal_purchase_receipt_allocation = $app->engine->modal_add()->id_set('modal_purchase_receipt_allocation')->width_set('75%');

                $purchase_receipt_allocation_data = array(
                    'purchase_receipt'=>array(
                        'id'=>$purchase_receipt['id']
                    )                
                );
                $purchase_receipt_allocation_data = json_decode(json_encode($purchase_receipt_allocation_data));

                Purchase_Receipt_Allocation_Renderer::modal_purchase_receipt_allocation_render(
                        $app
                        ,$modal_purchase_receipt_allocation
                    );


                $param = array(
                    'index_url'=>$path->index
                    ,'ajax_search'=>$path->ajax_search
                    ,'purchase_receipt_id'=>$purchase_receipt['id']
                    ,'purchase_receipt_text'=>$purchase_receipt['code']
                    ,'supplier_id'=>$purchase_receipt['supplier_id']
                );

                $js = get_instance()->load->view('purchase_receipt/purchase_receipt_allocation_js',$param,TRUE);
                $app->js_set($js);
                
            }
            //</editor-fold>
        }
    }
    
?>