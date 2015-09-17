<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function my_load_and_rename_class($param){
    //<editor-fold defaultstate="collapsed">
    $result = '';
    $file_path = $param['file_path'];
    $src_class = $param['src_class'];
    $src_extends_class = isset($param['src_extends_class'])?
        ($param['src_extends_class']!==''?' extends '.$param['src_extends_class']:''):'';
    $dst_class = $param['dst_class'];
    $dst_extends_class = isset($param['dst_extends_class'])?
        ($param['dst_extends_class']!==''?' extends '.$param['dst_extends_class']:''):'';
    
    $my_content = file_get_contents($file_path);
    $my_content = str_replace('<?php','',$my_content);
    $my_content = str_replace('?>','',$my_content);
    $my_content = str_replace('class '.$src_class.$src_extends_class,
        'class '.$dst_class.$dst_extends_class,
    $my_content);
    eval($my_content);
    //</editor-fold>
}

function &load_class($class, $directory = 'libraries', $prefix = 'CI_')
{
    //<editor-fold defaultstate="collapsed">
    static $_classes = array();

    // Does the class exist?  If so, we're done...
    if (isset($_classes[$class]))
    {
            return $_classes[$class];
    }

    $name = FALSE;

    // Look for the class first in the local application/libraries folder
    // then in the native system/libraries folder
    foreach (array(APPPATH, BASEPATH) as $path)
    {
        if (file_exists($path.$directory.'/'.$class.'.php'))
        {
            $name = $prefix.$class;

            if (class_exists($name) === FALSE)
            {
                    require($path.$directory.'/'.$class.'.php');
            }

            break;
        }
    }

    // Is the request a class extension?  If so we load it too
    foreach (array(APPPATH) as $path)
    {
        if (file_exists($path.$directory.'/'.config_item('subclass_prefix').$class.'.php'))
        {
            $name = config_item('subclass_prefix').$class;

            if (class_exists($name) === FALSE)
            {
                    require($path.$directory.'/'.config_item('subclass_prefix').$class.'.php');
            }
        }
    }


    // Did we find the class?
    if ($name === FALSE)
    {
        // Note: We use exit() rather then show_error() in order to avoid a
        // self-referencing loop with the Excptions class
        exit('Unable to locate the specified class: '.$class.'.php');
    }

    // Keep track of what we just loaded
    is_loaded($class);

    $_classes[$class] = new $name();
    return $_classes[$class];
    //</editor-fold>
}
        
function _exception_handler($severity, $message, $filepath, $line)
{
    //<editor-fold defaultstate="collapsed">
    // We don't bother with "strict" notices since they tend to fill up
    // the log file with excess information that isn't normally very helpful.
    // For example, if you are running PHP 5 and you use version 4 style
    // class functions (without prefixes like "public", "private", etc.)
    // you'll get notices telling you that these have been deprecated.
    if ($severity == E_STRICT)
    {
           return;
    }

    $_error =& load_class('Exceptions','core','MY_');

    // Should we display the error? We'll get the current error_reporting
    // level and add its bits with the severity bits to find out.
    if (($severity & error_reporting()) == $severity)
    {
        $_error->show_php_error($severity, $message, $filepath, $line);
        
    }

    // Should we log the error?
    if (config_item('log_threshold') == 1)
    {
        $_error->log_exception($severity, $message, $filepath, $line);
    }
    
    if(get_instance()->config->config['MY_']['exit_on_exception_handler']){        
        die();
    }
    //</editor-fold>
}

function _on_shutdown(){
    //<editor-fold defaultstate="collapsed">
    $error = error_get_last();
    
    if($error!==null){
        
        $post = json_decode(get_instance()->input->post(), true);
        $ajax_post = false;
        if(isset($post['ajax_post'])) $ajax_post = ($post['ajax_post'] === true?true:false);
        $file = isset($error['file'])?$error['file']:'';
        $line = isset($error['line'])?$error['line']:'';
        $message = isset($error['message'])?$error['message']:'';
        
        $levels = array(
                E_ERROR				=>	'Error',
                E_WARNING			=>	'Warning',
                E_PARSE				=>	'Parsing Error',
                E_NOTICE			=>	'Notice',
                E_CORE_ERROR		=>	'Core Error',
                E_CORE_WARNING		=>	'Core Warning',
                E_COMPILE_ERROR		=>	'Compile Error',
                E_COMPILE_WARNING	=>	'Compile Warning',
                E_USER_ERROR		=>	'User Error',
                E_USER_WARNING		=>	'User Warning',
                E_USER_NOTICE		=>	'User Notice',
                E_STRICT			=>	'Runtime Notice'
        );
        
        if($ajax_post){
            ob_clean();
            $result = array('success'=>0,
                'msg'=>array(
                    'Severity: '.$levels[$error['type']],
                    'Path: '.$file,
                    'Line: '.$line,$message
                ),
                'response'=>''
            );
            echo json_encode($result);
            
        }
    }
    //</editor-fold>
}

function get_php_classes($php_code) {
    $classes = array();
    $tokens = token_get_all($php_code);
    $count = count($tokens);
    for ($i = 2; $i < $count; $i++) {
      if (   $tokens[$i - 2][0] == T_CLASS
          && $tokens[$i - 1][0] == T_WHITESPACE
          && $tokens[$i][0] == T_STRING) {

          $class_name = $tokens[$i][1];
          $classes[] = $class_name;
      }
    }
    return $classes;
}

?>