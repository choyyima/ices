<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class App_Message extends MY_ICES_Controller {
    private $title='Message';
    private $title_icon = '';
    private $path = array();
    
    function __construct(){
        parent::__construct();
        get_instance()->load->helper(ICES_Engine::$app['app_base_dir'].'app_message/app_message_engine');
        $this->path = App_Message_Engine::path_get();
        $this->title_icon = App_Icon::message();
        
    }
    
    
    function index(){
        $action = "";

        $app = new App();            
        $db = $this->db;

        $app->set_title($this->title);
        $app->set_breadcrumb($this->title,strtolower($this->title));
        $app->set_content_header($this->title,$this->title_icon,$action);
        
        get_instance()->load->helper($this->path->app_message_renderer);
        $form = $app->engine->div_add()->div_set('class','mailbox row')
                    ->div_add()->div_set('class','col-xs-12')
                    ->div_add()->div_set('class','box box-solid')
                    ->div_add()->div_set('class','box box-body box-primary')
                    ->div_add()->div_set('class','row')
                ;
        App_Message_Renderer::app_message_render($app, $form);
        
        $app->render();
    }
    
    function message_nav_get(){
        $result = SI::result_format_get();
        $success = 1;
        $msg = array();
        $response = array();
        
        $db = new DB();
        $cont = true;
        $q = '
            select t1.id
                ,t1.msg_header
                ,DATE_FORMAT(t1.date,"%M %d %a, %H:%i") moddate
                ,concat(t2.first_name, " ", t2.last_name) sender_name
            from user_login_inbox t1
                left outer join user_login  t2 on t1.sender_id = t2.id
            where t1.status>0 and t1.user_login_id = '.$db->escape(User_Info::get()['user_id']).'
                and is_read = 0 
            order by t1.date desc
            limit 0,100
        ';
        $rs = $db->query_array_obj($q);
        if(count($rs) == 0) {$cont = false;}

        if($cont){
            for($i=0;$i<count($rs);$i++){
                $response[] = array(
                    'id'=>$rs[$i]->id
                    ,'sender_name'=>$rs[$i]->sender_name
                    ,'msg_header'=>$rs[$i]->msg_header
                    ,'moddate'=>$rs[$i]->moddate
                );
            }
        }
        $result['success'] = $success;
        $result['msg'] = $msg;
        $result['response'] = $response;
        echo json_encode($result);
    }
    
    function app_message_inbox_get(){
        get_instance()->load->helper($this->path->app_message_engine);
        $db = new DB();
        $cont = true;
        $result = array();
        $user_login_id = User_Info::get()['user_id'];
        $post = json_decode($this->input->post(),true);
        $lookup_str = $db->escape(isset($post['lookup_str'])?"%".$post['lookup_str']."%":'');
        $q = '
            select t1.id
                ,concat(t2.first_name," ",t2.last_name) sender_name
                ,t1.date
                ,t1.msg_header
                ,t1.is_read
            from user_login_inbox t1
                left outer join user_login t2 on t1.sender_id = t2.id
            where t1.status>0 
                and t1.user_login_id = '.$db->escape($user_login_id).'                
                and t1.msg_header like '.$lookup_str.'
            order by t1.date desc
        ';
        $info = APP_Message_Engine::app_message_info_get($q,$post);
        
        if((int)$info['num_of_rows'] === 0) $cont = false;
        $msg = [];
        if($cont){           
            
            $q .=' limit '.($info['row_start']-1).', '.$info['rows_per_page'];
            $rs = $db->query_array($q);
            $msg = $rs;
        }
        
        $result['info'] = $info;
        $result['msg'] = $msg;
        
        echo json_encode($result);
    }
        
    function message_get($id){
        $db = new DB();
        $result = array();
        $cont = true;
            $q = '
                select distinct t1.*
                    , concat(t2.first_name, " ", t2.last_name) sender_name
                from user_login_inbox t1
                    left outer join user_login  t2 on t1.sender_id = t2.id
                where t1.status>0 and t1.id = '.$db->escape($id).'
                order by t1.moddate desc
            ';
            $rs = $db->query_array($q);
            if(count($rs) == 0) {$cont = false;}
            
            if($cont){
                $result = $rs[0];
            }
            echo json_encode($result);
    }
    
    function message_inbox_mark_unread(){
        $data = json_decode($this->input->post(), true);
        $db = new DB();
        foreach($data as $id){
            $db->update('user_login_inbox', array('is_read'=>0), array('id'=>$id,'user_login_id'=>User_Info::get()['user_id']));
        }
    }
    
    function message_inbox_mark_read(){
        $data = json_decode($this->input->post(), true);
        $db = new DB();
        foreach($data as $id){
            $db->update('user_login_inbox', array('is_read'=>1), array('id'=>$id,'user_login_id'=>User_Info::get()['user_id']));
        }
    }
    
    function message_inbox_delete(){
        $data = json_decode($this->input->post(), true);
        $db = new DB();
        foreach($data as $id){
            $db->update('user_login_inbox', array('status'=>0), array('id'=>$id,'user_login_id'=>User_Info::get()['user_id']));
        }
    }
}

