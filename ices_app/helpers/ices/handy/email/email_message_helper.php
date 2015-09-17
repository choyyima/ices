<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Email_Message{
    
    private static function template_list_get(){
        return array(
            'attachment_find'=>'Dear Mr/Mrs,'."\r\n".'Please find our document as attached'."\r\n"."\r\n".'Thanks and regards,'."\r\n",
        );
    }
    
    public static function template_get($key){
        $msg = '';
        
        $template = self::template_list_get();
        foreach($template as $tmplt_key=>$tmplt_val){
            if($key === $tmplt_key){
                $msg = $tmplt_val;
            }
        }
        return $msg;
    }
}
?>