<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class APP_Message_Engine {



    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'app_message/'
            ,'app_message_engine'=>'app_message/app_message_engine'
            ,'app_message_renderer' => 'app_message/app_message_renderer'
            ,'ajax_search'=>get_instance()->config->base_url().'app_message/ajax_search/'

        );

        return json_decode(json_encode($path));
    }

    private static function send_message_validate($method,$data=array()){
        $result = array(
            "success"=>1
            ,"msg"=>array()

        );
        $db = new DB();
        $data['user_login_inbox'];
        $user_id = isset($data['user_login_id'])?$data['user_login_id']:'';
        if(strlen($user_id) === 0){
            $result['success'] = 0;
            $result['msg'][] = 'Empty User ID';
        }

        if($result['success'] === 1){
            $rs = $db->query_array_obj('select 1 from user_login where status>0 and id = '.$db->escape($data['user_login_id']));
            if(count($rs) === 0){
                $result['success'] = 0;
                $result['msg'][] = 'User ID doesn\'t exist';
            }
        }

        $sender_id = isset($data['sender_id'])?$data['sender_id']:'';
        if(strlen($sender_id) === 0){
            $result['success'] = 0;
            $result['msg'][] = 'Empty Sender ID';
        }

        if($result['success'] === 1){
            $rs = $db->query_array_obj('select 1 from user_login where status>0 and id = '.$db->escape($data['sender_id']));
            if(count($rs) === 0){
                $result['success'] = 0;
                $result['msg'][] = 'Sender ID doesn\'t exist';
            }
        }

        $msg_header = isset($data['msg_header'])?$data['msg_header']:'';
        if(strlen($msg_header) === 0){
            $result['success'] = 0;
            $result['msg'][] = 'Message Header Empty';
        }

        $msg_body = isset($data['msg_body'])?$data['msg_body']:'';
        if(strlen($msg_body) === 0){
            $result['success'] = 0;
            $result['msg'][] = 'Message Body Empty';
        }
        return $result;
    }
    public static function send_message_adjust($data){
        $result = array();
        $modid = User_Info::get()['user_id'];
        $moddate = date("Y-m-d H:i:s");
        $result['user_login_inbox']['user_login_id'] = $data['user_login_id'];
        $result['user_login_inbox']['sender_id'] = $data['sender_id'];
        $result['user_login_inbox']['date'] = $moddate;
        $result['user_login_inbox']['msg_header'] = $data['msg_header'];
        $result['user_login_inbox']['msg_body'] = $data['msg_body'];
        $result['user_login_inbox']['modid'] = $modid;
        $result['user_login_inbox']['moddate'] = $moddate;
        $result['user_login_inbox']['is_read'] = 0;



        return $result;            
    }
    public static function send_message($data){
        $db = new DB();
        $result = array('success'=>1,'msg'=>'');          
        $success = 1;
        $msg = array();


        if($success === 1){
            $final_data = self::send_message_adjust($data);
            try{                            
                $db->trans_begin();
                $fuser_login_inbox = isset($final_data['user_login_inbox'])?$final_data['user_login_inbox']:null;
                if(!$db->insert('user_login_inbox',$fuser_login_inbox)){
                    $msg[] = $db->_error_message();
                    $db->trans_rollback();                                
                    $success = 0;
                }

                if($success == 1){
                    $db->trans_commit();
                    $rs = $db->query_array_obj('select concat(first_name," ",last_name) name from user_login where id ='.$db->escape($fuser_login_inbox['user_login_id']));
                    $username = $rs[0]->name;
                    $msg[] = 'Send Message to '.$username.' Success';
                }
            }
            catch(Exception $e){
                $db->trans_rollback();
                $msg[] = $e->getMessage();
                $success = 0;
            }

        }

        $result['success'] = $success;
        $result['msg'] = $msg;

        return $result;
    }

    public static function app_message_info_get($q,$post){
        $db = new DB();

        $info = array(
            'num_of_rows'=>0,
            'row_start'=>1,
            'row_end'=> 0,
            'curr_page'=>isset($post['page'])?$post['page']:1,
            'rows_per_page'=>10
        );

        $q_num_of_rows = 'select count(1) num_of_rows from ('.$q.') tf';
        $info['num_of_rows'] = $db->query_array_obj($q_num_of_rows)[0]->num_of_rows;

        $max_page = 0;
        if((int)$info['num_of_rows'] === 0){
            $info['curr_page'] = 0;
            $max_page = 0;
            $info['row_start'] = 0;
        }
        else{
            $max_page = ($info['num_of_rows'] % $info['rows_per_page'])>0?
                (int)($info['num_of_rows'] / $info['rows_per_page'])+1:
                ($info['num_of_rows'] / $info['rows_per_page']);

            if($info['curr_page'] > $max_page){
                //it's page is over
                $info['curr_page'] = $max_page;
                $info['row_start'] = (($info['curr_page'] -1)* $info['rows_per_page'])+1;                
                $info['row_end'] = $info['num_of_rows'];

            }
            else if ($info['curr_page']<1){
                $info['row_start'] = $info['num_of_rows']>0?1:0;
                $info['row_end'] = $info['rows_per_page'];
                $info['curr_page'] = 1;
            }
            else{
                $info['row_start'] = (($info['curr_page'] -1)* $info['rows_per_page'])+1;
                $info['row_end'] = ($info['curr_page'])* $info['rows_per_page'];
                $info['row_end'] = $info['row_end']>$info['num_of_rows']? $info['num_of_rows']:$info['row_end'];
            }

        }



        return $info;
    }

}
?>
