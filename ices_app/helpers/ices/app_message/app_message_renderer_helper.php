<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    get_instance()->load->helper('request_form/request_form_engine');
    class App_Message_Renderer {
        
        
        public static function app_message_render($app,$form){
            get_instance()->load->helper('app_message/app_message_engine');
            $path = APP_Message_Engine::path_get();

            $components = self::app_message_components_render($app, $form,false);
            $js = '
                app_message_bind_event();
                app_message_init_data();
            ';
            $app->js_set($js);

            
        }
        
        public static function app_message_components_render($app,$form,$is_modal){
            
            
            $path = APP_Message_Engine::path_get();            
            $components = array();
            $db = new DB();
            
            $form->custom_component_add()->src_set(ICES_Engine::$app['app_base_dir'].'app_message/app_message');
            $js = get_instance()->load->view(ICES_Engine::$app['app_base_dir'].'app_message/app_message_js',array(),true);
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