<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Expedition_Renderer {
        
        public static function modal_expedition_render($app,$modal){
            $modal->header_set(array('title'=>'Expedition','icon'=>App_Icon::expedition()));
            $components = self::expedition_components_render($app, $modal,true);
            
            
        }
        
        public static function expedition_render($app,$form,$data,$path,$method){
            get_instance()->load->helper('expedition/expedition_engine');
            $path = Expedition_Engine::path_get();
            $id = $data['id'];
            $components = self::expedition_components_render($app, $form,false);
            $back_href = $path->index;
            
            $form->button_add()->button_set('value','BACK')
                ->button_set('icon',App_Icon::btn_back())
                ->button_set('href',$back_href)
                ->button_set('class','btn btn-default')
                ;

            $js = '
                <script>
                    $("#expedition_method").val("'.$method.'");
                    $("#expedition_id").val("'.$id.'");
                </script>
            ';             
            $app->js_set($js);
            
            $js = '                
                    expedition_init();
                    expedition_bind_event();
                    expedition_components_prepare(); 
            ';
            $app->js_set($js);
            
        }
        
        public static function expedition_components_render($app,$form,$is_modal){
            
            get_instance()->load->helper('expedition/expedition_engine');
            $path = Expedition_Engine::path_get();            
            $components = array();
            $db = new DB();
            $components['id'] = $form->input_add()->input_set('id','expedition_id')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;

            
            $form->input_add()->input_set('id','expedition_method')
                    ->input_set('hide',true)
                    ->input_set('value','')
                    ;            
            
            $form->input_add()->input_set('label',Lang::get('Code'))
                    ->input_set('id','expedition_code')
                    ->input_set('icon','fa fa-info')
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                    ->input_set('attrib',array('style'=>'font-weight:bold'))
                    
                ;
            
            $form->input_add()->input_set('label',Lang::get('Name'))
                    ->input_set('id','expedition_name')
                    ->input_set('icon','fa fa-info')
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            $components['phone'] = $form->input_add()->input_set('label','Phone')
                    ->input_set('id','expedition_phone')
                    ->input_set('icon','fa fa-phone')
                    ->input_set('input_mask_type','phone-mobile')
                    ->input_set('value','')
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            $components['phone2'] = $form->input_add()->input_set('label','Phone 2')
                    ->input_set('id','expedition_phone2')
                    ->input_set('icon','fa fa-phone')
                    ->input_set('input_mask_type','phone-mobile')
                    ->input_set('value','')
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            $components['phone3'] = $form->input_add()->input_set('label','Phone 3')
                    ->input_set('id','expedition_phone3')
                    ->input_set('icon','fa fa-phone')
                    ->input_set('input_mask_type','phone-mobile')
                    ->input_set('value','')
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            $components['bb_pin'] = $form->input_add()->input_set('label','BB Pin')
                    ->input_set('id','expedition_bb_pin')
                    ->input_set('icon','fa fa-envelope')
                    ->input_set('value','')
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            
            $components['email'] = $form->input_add()->input_set('label','Email')
                    ->input_set('id','expedition_email')
                    ->input_set('icon','fa fa-envelope')
                    ->input_set('value','')
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
             $components['expedition_status'] = $form->input_select_add()
                    ->input_select_set('label','Status')
                    ->input_select_set('icon','fa fa-user')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','expedition_expedition_status')
                    ->input_select_set('data_add',array())
                    ->input_select_set('value',array())
                     ->input_select_set('hide_all',true)
                    ;
            
           
             
            $components['address']=$form->input_add()->input_set('label',Lang::get('Address'))
                    ->input_set('id','expedition_address')
                    ->input_set('icon','fa fa-location-arrow')
                    ->input_set('value','')
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                    ;
            
            $components['city'] = $form->input_add()->input_set('label',Lang::get('City'))
                    ->input_set('id','expedition_city')
                    ->input_set('icon','fa fa-location-arrow')
                    ->input_set('value','')
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            $components['country'] = $form->input_add()->input_set('label',Lang::get('Country'))
                    ->input_set('id','expedition_country')
                    ->input_set('icon','fa fa-location-arrow')
                    ->input_set('value','')
                    ->input_set('hide_all',true)
                    ->input_set('disable_all',true)
                ;
            
            $unit_list = array();
            $q = 'select id id, code, name from unit where status> 0';
            $rs = $db->query_array($q);
            for($i=0;$i<count($rs);$i++){
                $unit_list[] = array(
                    'id'=>$rs[$i]['id']
                    ,'data'=>SI::html_tag('strong',$rs[$i]['code']).' '.$rs[$i]['name']
                );
            }
            
            $form->input_select_add()
                    ->input_select_set('label','Unit Measurement')
                    ->input_select_set('icon','fa fa-user')
                    ->input_select_set('min_length','0')
                    ->input_select_set('id','expedition_measurement_unit')
                    ->input_select_set('data_add',$unit_list)
                    ->input_select_set('value',array())
                     ->input_select_set('hide_all',true)
                    ; 
            

            $components['notes'] = $form->textarea_add()->textarea_set('label','Notes')
                    ->textarea_set('id','expedition_notes')
                    ->textarea_set('value','')
                    ->textarea_set('hide_all',true)
                    ->textarea_set('disable_all',true)
                ;
                    
                    
            $form->hr_add()->hr_set('class','');
            
            $form->button_add()->button_set('value','Submit')
                            ->button_set('id','expedition_submit')
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
                $param['detail_tab'] = '#modal_expedition .modal-body';
                $param['view_url'] = '';
                $param['window_scroll'] = '#modal_expedition';
            }
            
            $js = get_instance()->load->view('expedition/expedition_basic_function_js',$param,TRUE);
            $app->js_set($js);

            return $components;
            
        }
        
        public static function expedition_status_log_render($app,$form,$data,$path){
            get_instance()->load->helper('expedition/expedition_engine');
            $path = Expedition_Engine::path_get();
            get_instance()->load->helper($path->expedition_engine);
            
            $id = $data['id'];
            $db = new DB();
            $q = '
                select null row_num
                    ,t1.moddate
                    ,t1.expedition_status
                    ,t2.name user_name

                from expedition_status_log t1
                    inner join user_login t2 on t1.modid = t2.id
                where t1.expedition_id = '.$id.'
                    order by moddate asc
            ';
            $rs = $db->query_array($q);
            for($i = 0;$i<count($rs);$i++){
                $rs[$i]['row_num'] = $i+1;
                $expedition_status_name = '';
                $expedition_status_name = SI::get_status_attr(
                    Expedition_Engine::expedition_status_get(
                        $rs[$i]['expedition_status']
                    )['label']
                );
                $rs[$i]['expedition_status_name'] = $expedition_status_name;
                        
                
            }
            $expedition_status_log = $rs;
            
            $table = $form->form_group_add()->table_add();
            $table->table_set('id','expedition_status_log_table');
            $table->table_set('class','table fixed-table');
            $table->table_set('columns',array("name"=>"row_num","label"=>"#",'col_attrib'=>array('style'=>'width:30px')));
            $table->table_set('columns',array("name"=>"moddate","label"=>"Modified Date",'col_attrib'=>array('style'=>'')));
            $table->table_set('columns',array("name"=>"expedition_status_name","label"=>"Status",'col_attrib'=>array('style'=>'')));
            $table->table_set('columns',array("name"=>"user_name","label"=>"User",'col_attrib'=>array('style'=>'')));
            $table->table_set('data',$expedition_status_log);
        }
        
    }
    
?>