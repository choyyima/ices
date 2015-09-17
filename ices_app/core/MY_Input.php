<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MY_Input extends CI_Input{
    function post($index = NULL, $xss_clean = FALSE){
        // Check if a field has been provided
        if ($index === NULL AND ! empty($_POST))
        {
            $post = array();

            // Loop through the full _POST array and return it
            foreach (array_keys($_POST) as $key)
            {
                    $post[$key] = $this->_fetch_from_array($_POST, $key, $xss_clean);
            }
            return $post;
        }
        $post = file_get_contents('php://input');
        if (strlen($post)>0){
            $post = base64_decode($post);
            if(json_decode($post)!=null){                        
                $temp_post = json_decode($post,TRUE);
            }
            return $post;
        }

        return $this->_fetch_from_array($_POST, $index, $xss_clean);
    }
}
?>