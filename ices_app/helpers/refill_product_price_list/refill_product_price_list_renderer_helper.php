<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Refill_Product_Price_List_renderer {
        
        public static function modal_refill_product_price_list_render($app,$modal){
            $modal->header_set(array('title'=>'Sales Receipt','icon'=>App_Icon::refill_product_price_list()));
            $components = self::refill_product_price_list_components_render($app, $modal,true);
        }
        
        public static function refill_product_price_list_render($app,$form,$data,$path,$method){
            get_instance()->load->helper('refill_product_price_list/refill_product_price_list_Engine');
            $path = refill_product_price_list_Engine::path_get();
            $id = $data['id'];
            $components = self::refill_product_price_list_components_render($app, $form,false);
            $back_href = $path->index;
            
            $form->button_add()->button_set('value','BACK')
                ->button_set('icon',App_Icon::btn_back())
                ->button_set('href',$back_href)
                ->button_set('class','btn btn-default')
                ;
            
            $js = '
                <script>
                    $("#rppl_method").val("'.$method.'");
                    $("#rppl_id").val("'.$id.'");
                </script>
            ';             
            $app->js_set($js);
            
            $js = '                
                    rppl_init();
                    rppl_bind_event();
                    rppl_components_prepare(); 
            ';
            $app->js_set($js);
            
        }
        
        public static function refill_product_price_list_components_render($app,$form,$is_modal){
            
            get_instance()->load->helper('refill_product_price_list/refill_product_price_list_Engine');
            $path = refill_product_price_list_Engine::path_get();            
            $components = array();
            $db = new DB();
            
            $id_prefix = 'rppl';
            
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
            
            $form->input_add()->input_set('label',Lang::get('Code'))
                    ->input_set('id',$id_prefix.'_code')
                    ->input_set('icon','fa fa-info')
                    ->input_set('attrib',array('style'=>'font-weight:bold'))
                    ->input_set('hide_all',true)
                ;
            
            $form->input_add()->input_set('label',Lang::get('Name'))
                    ->input_set('id',$id_prefix.'_name')
                    ->input_set('icon','fa fa-info')
                    ->input_set('attrib',array('style'=>'font-weight:bold'))
                    ->input_set('hide_all',true)
                ;
            
            $form->input_select_add()
                ->input_select_set('label','Status')
                ->input_select_set('icon','fa fa-info')
                ->input_select_set('min_length','0')
                ->input_select_set('id',$id_prefix.'_refill_product_price_list_status')
                ->input_select_set('data_add',array())
                ->input_select_set('value',array())
                ->input_select_set('is_module_status',true)
                ->input_select_set('hide_all',true)                    
                ;
             
            $table = $form->form_group_add()->table_add();
            $table->table_set('id',$id_prefix.'_product_medium_unit_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
                        $table->table_set('columns',array("name"=>"product_category","label"=>"Category",'col_attrib'=>array('style'=>'text-align:left')));
            $table->table_set('columns',array("name"=>"product_medium","label"=>"Medium",'col_attrib'=>array('style'=>'text-align:left')));
            $table->table_set('columns',array("name"=>"capacity_unit","label"=>"Capacity Unit",'col_attrib'=>array('style'=>'text-align:left;')));
            $table->table_set('columns',array("name"=>"data","label"=>"",'col_attrib'=>array('style'=>'text-align:left;')));
            $table->table_set('columns',array("name"=>"","label"=>"",'col_attrib'=>array('style'=>'text-align:left;width:30px')));
            
            $form->textarea_add()->textarea_set('label','Notes')
                    ->textarea_set('id','refill_product_price_list_notes')
                    ->textarea_set('value','')
                    ->textarea_set('attrib',array())       
                    ;
                        
            $form->custom_component_add()->src_set('refill_product_price_list/view/modal_price_list_data');
            
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
            
            $js = get_instance()->load->view('refill_product_price_list/'.$id_prefix.'_basic_function_js',$param,TRUE);
            $app->js_set($js);
            return $components;
            
        }
        
        public static function refill_product_price_list_status_log_render($app,$form,$data,$path){
            //<editor-fold defaultstate="collapsed">
            $config=array(
                'module_name'=>'refill_product_price_list',
                'module_engine'=>'refill_product_price_list_Engine',
                'id'=>$data['id']
            );
            SI::form_renderer()->status_log_tab_render($form, $config);
            //</editor-fold>
        }
        
        public static function refill_product_price_list_allocation_view_render($app,$form,$data,$path){
            //<editor-fold defaultstate="collapsed">
            get_instance()->load->helper('refill_product_price_list_allocation/refill_product_price_list_allocation_engine');
            get_instance()->load->helper('refill_product_price_list_allocation/refill_product_price_list_allocation_renderer');
            $id = $data['id'];
            $db = new DB();
            $rs = $db->fast_get('refill_product_price_list',array('id'=>$id));
            if(count($rs)>0) {
                $refill_product_price_list = $rs[0];            
                $form->form_group_add();
                if($refill_product_price_list['refill_product_price_list_status'] != 'X'){
                    if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'refill_product_price_list_allocation','add')){
                    $form->button_add()->button_set('class','primary')
                            ->button_set('value','New Sales Receipt Allocation')
                            ->button_set('icon','fa fa-plus')
                            ->button_set('attrib',array(
                                'data-toggle'=>"modal" 
                                ,'data-target'=>"#modal_refill_product_price_list_allocation"
                            ))
                            ->button_set('disable_after_click',false)
                            ->button_set('id','refill_product_price_list_allocation_new')
                        ;
                    }
                }
                $form->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
                $tbl = $form->table_add();
                $tbl->table_set('class','table');
                $tbl->table_set('id','refill_product_price_list_allocation_table');
                $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
                $tbl->table_set('columns',array("name"=>"refill_product_price_list_allocation_type_text","label"=>"Reference Type",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"reference_code","label"=>"Reference Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"code","label"=>"Allocation Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center'),"is_key"=>true));
                $tbl->table_set('columns',array("name"=>"allocated_amount","label"=>"Allocated Amount (Rp.)",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"refill_product_price_list_allocation_status_text","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
                $tbl->table_set('data key','id');

                $q = '
                    select distinct NULL row_num
                        ,t1.*
                        ,t2.code sales_invoice_code
                        ,t3.code customer_bill_code

                    from refill_product_price_list_allocation t1
                        left outer join sales_invoice t2 on t1.sales_invoice_id = t2.id
                        left outer join customer_bill t3 on t1.customer_bill_id = t3.id
                        inner join refill_product_price_list t4 on t4.id = t1.refill_product_price_list_id
                    where t4.id = '.$id.' order by t1.moddate desc

                ';
                $rs = $db->query_array($q);
                for($i = 0;$i<count($rs);$i++){
                    $rs[$i]['reference_code'] = $rs[$i][$rs[$i]['refill_product_price_list_allocation_type'].'_code'];
                    $rs[$i]['refill_product_price_list_allocation_type_text'] = SI::type_get('refill_product_price_list_Allocation_Engine',
                        $rs[$i]['refill_product_price_list_allocation_type'])['label'];
                    $rs[$i]['row_num'] = $i+1;
                    $rs[$i]['allocated_amount'] = Tools::thousand_separator($rs[$i]['allocated_amount'],2,true);
                    $rs[$i]['refill_product_price_list_allocation_status_text'] = SI::get_status_attr(
                        SI::status_get('refill_product_price_list_Allocation_Engine', $rs[$i]['refill_product_price_list_allocation_status'])['label']
                    );
                }
                $tbl->table_set('data',$rs);
                

                $modal_refill_product_price_list_allocation = $app->engine->modal_add()->id_set('modal_refill_product_price_list_allocation')->width_set('75%');

                $refill_product_price_list_allocation_data = array(
                    'refill_product_price_list'=>array(
                        'id'=>$refill_product_price_list['id']
                    )                
                );
                $refill_product_price_list_allocation_data = json_decode(json_encode($refill_product_price_list_allocation_data));

                refill_product_price_list_Allocation_Renderer::modal_refill_product_price_list_allocation_render(
                        $app
                        ,$modal_refill_product_price_list_allocation
                    );


                $param = array(
                    'index_url'=>$path->index
                    ,'ajax_search'=>$path->ajax_search
                    ,'refill_product_price_list_id'=>$refill_product_price_list['id']
                    ,'refill_product_price_list_text'=>$refill_product_price_list['code']
                    ,'customer_id'=>$refill_product_price_list['customer_id']
                );

                $js = get_instance()->load->view('refill_product_price_list/refill_product_price_list_allocation_js',$param,TRUE);
                $app->js_set($js);
                
            }
            //</editor-fold>
        }
    }
    
?>