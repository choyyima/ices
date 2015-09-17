<?php
class Message{
    private static $type="";
    private static $msg="";
    public static $message_session = true;
    public static function set($type="",$msg=array()){
        $temp_post = get_instance()->input->post();
        if(isset($temp_post['message_session'])){
            Message::$message_session = $temp_post['message_session'];
        }

        if(self::$message_session){
            $data = array(
                "app_msg_type"=>$type
                ,"app_msg_msg"=>$msg
            );
            get_instance()->session->set_userdata($data);            
        }
    }

    public static function get(){
        $type = "";
        $msg = array();
        $data = array(
            "type"=>$type
            ,"msg"=>$msg
        );
        if(strlen(get_instance()->session->userdata("app_msg_type"))>0) $data['type'] = get_instance()->session->userdata("app_msg_type");
        if(count(get_instance()->session->userdata("app_msg_msg"))>0) $data['msg'] = get_instance()->session->userdata("app_msg_msg");

        get_instance()->session->unset_userdata(
            array(
                "app_msg_type"=>'',
                "app_msg_msg"=>''
            )
        );
        return $data;
    }
}
?>
