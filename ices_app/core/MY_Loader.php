<?php
class MY_Loader extends CI_Loader{
    
    public function helper($helpers = array()){
        //<editor-fold defaultstate="collapsed">
        parent::helper($helpers);

        foreach ($this->_ci_prep_filename($helpers, '_helper') as $helper){
            if ($this->_ci_helpers[$helper]){
                foreach ($this->_ci_helper_paths as $path){
                    if (file_exists($path.'helpers/'.$helper.'.php')){
                        $class_arr = get_php_classes(file_get_contents($path.'helpers/'.$helper.'.php'));
                        foreach($class_arr as $class){
                            if(method_exists($class,'helper_init')) {
                                eval($class.'::helper_init();');
                            }
                        }
                    }
                }
            
            }
        }
        //</editor-fold>
    }
}
?>
