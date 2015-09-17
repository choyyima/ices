<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    
class Assigner_Engine {

    public static function value_set($app, $post){
        $js = '';
        $component = isset($post['component'])?
            (is_array($post['component'])?$post['component']:array()):array();
        for($i = 0;$i<count($component);$i++){

            $id = isset($component[$i]['id'])?Tools::_str($component[$i]['id']):'';
            $type = isset($component[$i]['type'])?Tools::_str($component[$i]['type']):'';
            $val = isset($component[$i]['val'])?$component[$i]['val']:'';

            switch($type){
                case 'input':
                    $js.=self::input_script_generate($id,$val);
                    break;
                case 'select2':
                    $js.=self::select2_script_generate($id,$val);
                    break;
            }
        }
        $app->js_set($js);
    }

    public static function input_script_generate($id, $val){
        $result = '';
        $val = Tools::_str($val);
        $result .= '$("#'.$id.'").val("'.$val.'");';
        return $result;
    }

    public static function select2_script_generate($id, $val){
        $result = '';
        $val_id = isset($val['id'])?Tools::_str($val['id']):'';
        $val_text = isset($val['text'])?Tools::_str($val['text']):'';
        $result .= '$("#'.$id.'").select2("data",{id:"'.$val_id.'",text:"'.$val_text.'"});';
        return $result;
    }

};

?>