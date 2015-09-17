<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Refill_Receipt_Renderer {

    public static function modal_refill_receipt_render($app,$modal){
        $modal->header_set(array('title'=>'Refill Receipt','icon'=>App_Icon::refill_receipt()));
        $components = self::refill_receipt_components_render($app, $modal,true);
    }

    public static function refill_receipt_render($app,$form,$data,$path,$method){
        get_instance()->load->helper('refill_receipt/refill_receipt_engine');
        $path = Refill_Receipt_Engine::path_get();
        $id = $data['id'];
        $components = self::refill_receipt_components_render($app, $form,false);
        $back_href = $path->index;

        $form->button_add()->button_set('value','BACK')
            ->button_set('icon',App_Icon::btn_back())
            ->button_set('href',$back_href)
            ->button_set('class','btn btn-default')
            ;

        $js = '
            <script>
                $("#refill_receipt_method").val("'.$method.'");
                $("#refill_receipt_id").val("'.$id.'");
            </script>
        ';             
        $app->js_set($js);

        $js = '                
                refill_receipt_init();
                refill_receipt_bind_event();
                refill_receipt_components_prepare(); 
        ';
        $app->js_set($js);

    }

    public static function refill_receipt_components_render($app,$form,$is_modal){

        get_instance()->load->helper('refill_receipt/refill_receipt_engine');
        get_instance()->load->helper('bos_bank_account/bos_bank_account_data_support');
        $path = Refill_Receipt_Engine::path_get();            
        $components = array();
        $db = new DB();

        $id_prefix = Refill_Receipt_Engine::$prefix_id;

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
            ->input_select_set('label','Customer')
            ->input_select_set('icon',APP_Icon::user())
            ->input_select_set('min_length','0')
            ->input_select_set('id',$id_prefix.'_customer')
            ->input_select_set('data_add',array())
            ->input_select_set('value',array())
            ->input_select_set('hide_all',true)                    
            ->input_select_set('disable_all',true)  
            ->input_select_set('ajax_url',$path->ajax_search.'input_select_customer_search/')
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



        $form->datetimepicker_add()->datetimepicker_set('label',Lang::get('Refill Receipt Date'))
                ->datetimepicker_set('id',$id_prefix.'_refill_receipt_date')
                ->datetimepicker_set('value',Tools::_date('','F d, Y H:i')) 
                ->datetimepicker_set('disable_all',true)
                ->datetimepicker_set('hide_all',true)
            ;

        $form->input_add()->input_set('label',Lang::get('Customer Bank Acc.'))
                ->input_set('id',$id_prefix.'_customer_bank_acc')
                ->input_set('icon',App_Icon::money())
                ->input_set('hide_all',true)
                ->input_set('disable_all',true)
            ;

        $bba_list = array();
        foreach(Bos_Bank_Account_Data_Support::bos_bank_account_list_get(array('bos_bank_account_status'=>'active')) 
            as $idx=>$row){
            $bba_list[] = array(
                'id'=>$row['id'],
                'text'=>$row['code'],
            );
        }

        $form->input_select_add()
            ->input_select_set('label',Lang::get('BOS Bank Account'))
            ->input_select_set('icon',App_Icon::info())
            ->input_select_set('min_length','0')
            ->input_select_set('id',$id_prefix.'_bos_bank_account')
            ->input_select_set('value',array())
            ->input_select_set('disable_all',true)
            ->input_select_set('hide_all',true)    
            ->input_select_set('data_add',$bba_list)
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

        $form->datetimepicker_add()->datetimepicker_set('label',Lang::get('Deposit Date'))
            ->datetimepicker_set('id',$id_prefix.'_deposit_date')
            ->datetimepicker_set('value','') 
            ->datetimepicker_set('disable_all',true)
            ->datetimepicker_set('hide_all',true)
        ;

        $components['refill_receipt_status'] = $form->input_select_add()
            ->input_select_set('label','Status')
            ->input_select_set('icon','fa fa-info')
            ->input_select_set('min_length','0')
            ->input_select_set('id',$id_prefix.'_refill_receipt_status')
            ->input_select_set('data_add',array())
            ->input_select_set('value',array())
            ->input_select_set('is_module_status',true)
            ->input_select_set('hide_all',true)                    
            ;

        $form->textarea_add()->textarea_set('label','Notes')
                ->textarea_set('id','refill_receipt_notes')
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

        $js = get_instance()->load->view('refill_receipt/'.$id_prefix.'_basic_function_js',$param,TRUE);
        $app->js_set($js);
        return $components;

    }

    public static function refill_receipt_status_log_render($app,$form,$data,$path){
        //<editor-fold defaultstate="collapsed">
        $config=array(
            'module_name'=>'refill_receipt',
            'module_engine'=>'Refill_Receipt_Engine',
            'id'=>$data['id']
        );
        SI::form_renderer()->status_log_tab_render($form, $config);
        //</editor-fold>
    }

    public static function refill_receipt_allocation_view_render($app,$form,$data,$path){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('refill_receipt_allocation/refill_receipt_allocation_engine');
        get_instance()->load->helper('refill_receipt_allocation/refill_receipt_allocation_renderer');
        $id = $data['id'];
        $db = new DB();
        $rs = $db->fast_get('refill_receipt',array('id'=>$id));
        if(count($rs)>0) {
            $refill_receipt = $rs[0];            
            $form->form_group_add();
            if($refill_receipt['refill_receipt_status'] != 'X'){
                if(Security_Engine::get_controller_permission(User_Info::get()['user_id'],'refill_receipt_allocation','add')){
                $form->button_add()->button_set('class','primary')
                        ->button_set('value',Lang::get(array('New','Refill Receipt Allocation')))
                        ->button_set('icon','fa fa-plus')
                        ->button_set('attrib',array(
                            'data-toggle'=>"modal" 
                            ,'data-target'=>"#modal_refill_receipt_allocation"
                        ))
                        ->button_set('disable_after_click',false)
                        ->button_set('id','refill_receipt_allocation_new')
                    ;
                }
            }
            $form->form_group_add()->attrib_set(array('style'=>'margin-bottom:20px'));
            $tbl = $form->table_add();
            $tbl->table_set('class','table');
            $tbl->table_set('id','refill_receipt_allocation_table');
            $tbl->table_set('columns',array("name"=>"row_num","label"=>"#",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'width:30px;text-align:center')));            
            $tbl->table_set('columns',array("name"=>"refill_receipt_allocation_type_text","label"=>"Reference Type",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"reference_code","label"=>"Reference Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"code","label"=>"Allocation Code",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center'),"is_key"=>true));
            $tbl->table_set('columns',array("name"=>"allocated_amount","label"=>"Allocated Amount (Rp.)",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"refill_receipt_allocation_status_text","label"=>"Status",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'attribute'=>'style="text-align:center"','col_attrib'=>array('style'=>'text-align:center')));
            $tbl->table_set('data key','id');

            $q = '
                select distinct NULL row_num
                    ,t1.*
                    ,t2.code refill_invoice_code

                from refill_receipt_allocation t1
                    left outer join refill_invoice t2 on t1.refill_invoice_id = t2.id
                    inner join refill_receipt t4 on t4.id = t1.refill_receipt_id
                where t4.id = '.$id.' order by t1.id desc

            ';
            $rs = $db->query_array($q);
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['reference_code'] = $rs[$i][$rs[$i]['refill_receipt_allocation_type'].'_code'];
                $rs[$i]['refill_receipt_allocation_type_text'] = SI::type_get('Refill_Receipt_Allocation_Engine',
                    $rs[$i]['refill_receipt_allocation_type'])['label'];
                $rs[$i]['row_num'] = $i+1;
                $rs[$i]['allocated_amount'] = Tools::thousand_separator($rs[$i]['allocated_amount'],2,true);
                $rs[$i]['refill_receipt_allocation_status_text'] = SI::get_status_attr(
                    SI::status_get('Refill_Receipt_Allocation_Engine', $rs[$i]['refill_receipt_allocation_status'])['label']
                );
            }
            $tbl->table_set('data',$rs);


            $modal_refill_receipt_allocation = $app->engine->modal_add()->id_set('modal_refill_receipt_allocation')->width_set('75%');

            $refill_receipt_allocation_data = array(
                'refill_receipt'=>array(
                    'id'=>$refill_receipt['id']
                )                
            );
            $refill_receipt_allocation_data = json_decode(json_encode($refill_receipt_allocation_data));
            
            Refill_Receipt_Allocation_Renderer::modal_refill_receipt_allocation_render(
                    $app
                    ,$modal_refill_receipt_allocation
                );


            $param = array(
                'index_url'=>$path->index
                ,'ajax_search'=>$path->ajax_search
                ,'refill_receipt_id'=>$refill_receipt['id']
                ,'refill_receipt_text'=>$refill_receipt['code']
                ,'customer_id'=>$refill_receipt['customer_id']
            );

            $js = get_instance()->load->view('refill_receipt/refill_receipt_allocation_js',$param,TRUE);
            $app->js_set($js);

        }
        //</editor-fold>
    }
}
    
?>