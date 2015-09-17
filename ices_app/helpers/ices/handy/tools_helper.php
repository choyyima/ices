<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tools{
    
    function __construct(){
        
    }
    
    function _alpha_numeric($data,$opt=array()){
        $result = null;
        try{
            $data = Tools::_str($data);
            $data = preg_replace('/[^0-9a-z-A-Z ]/','',$data);
            $result = $data;
        }
        catch(Exception $e){
            $result = null;
        }
        return $result;
    }
    
    static function _date($data='',$format='Y-m-d H:i:s',$interval='PT0S',$opt = null){
        $result = null;
        $lc_time = isset($opt['LC_TIME'])?$opt['LC_TIME']:'';
        $ori_lc_time = setlocale(LC_TIME,0);
        if(in_array($interval,array('',null))) $interval = 'PT0S';
        try{
            setlocale(LC_TIME,$lc_time);
            
            $result = new DateTime($data);
            
            if(substr($interval,0,1) === '-'){
                $t_interval = substr($interval,1,strlen($interval)-1);
                $t_datetime = new DateInterval($t_interval);
                $t_datetime->invert = 1;
                $result->add($t_datetime);
                
            }
            else{
                $result->add(new DateInterval($interval));
            }
            
            $format = preg_replace('/[^ -:]/','%${0}',$format);
            $format = str_replace('i','M',$format);
            $format = str_replace('s','S',$format);
            $format = str_replace('F','B',$format);
            $format = str_replace('l','A',$format);
            $result = strftime($format,$result->getTimestamp());
            
            //$result = $result->format($format);
        }
        catch(Exception $e){
            $result = (new DateTime())->format('Y-m-d H:i:s');
        }
        setlocale(LC_TIME,$ori_lc_time);
        return $result;
    }
    
    static function _bool($data){
        $result = false;
        if(self::_float(self::_str($data)) === self::_float('1')){
             $result = true;
        }
        else if (is_string($data)){
            if(strtolower(self::_str($data))==='true' ) $result = true;
        }
        else if (is_bool($data)){
            if($data) $result = true;
        }
        return $result;
    }
    
    static function _arr($data){
        $result = array();
        if(is_array($data)) $result = $data;
        
        return $result;
    }
    
    static function _str($data){
        $result = '';
        if(is_string($data)) $result = $data;
        else if (is_numeric($data)) $result = (string)$data;
        else if (is_bool($data)) $result = (string)$data;
        return $result;
    }
    
    static function _float($data){
        $result = floatval('0');        
        if(is_string($data)) $result = floatval($data);
        else if(is_numeric($data)) $result = floatval(self::_str($data));        
        return $result;
    }
    
    static function _int($data){
        $result = intval('0');        
        if(is_string($data)) $result = intval($data);
        else if(is_numeric($data)) $result = intval(self::_str($data));        
        return $result;
    }
    
    function array_sort (&$array, $key) {
        $sorter=array();
        $ret=array();
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii]=$va[$key];
        }
        asort($sorter);
        foreach ($sorter as $ii => $va) {
            $ret[$ii]=$array[$ii];
        }
        $array=$ret;
    }
    
    function array_obj_sort (&$array, $key,$order="asc") {
        $sorter=array();
        $ret=array();
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii]=$va->$key;
        }
        if($order == "desc")
            ksort($sorter);
        else
            asort($sorter);
        foreach ($sorter as $ii => $va) {
            $ret[$ii]=$array[$ii];
        }
        $array=$ret;
    }
    
    static function array_extract($src=array(),$res_format=array(),$cond_filter = array('data'=>array(),'cfg'=>array())){
        //<editor-fold defaultstate="collapsed">
        // src must be 2 dimensional array;
        
        $result = array();
        
        for($i = 0;$i<count($src);$i++){
            $trow = array();
            $row_valid = true;
            
            $cond_filter_data_arr = isset($cond_filter['data'])?Tools::_arr($cond_filter['data']):array();
            $cond_filter_cfg = isset($cond_filter['cfg'])?$cond_filter['cfg']:array();
            $cf_key_must_exists = isset($cond_filter_cfg['key_must_exists'])?
                Tools::_bool($cond_filter_cfg['key_must_exists']):true;
            $cf_val_must_match = isset($cond_filter_cfg['val_must_match'])?
                Tools::_bool($cond_filter_cfg['val_must_match']):true;
            $cf_compare_sign = isset($cond_filter_cfg['compare_sign'])?$cond_filter_cfg['compare_sign']:'===';
            $cfg_data_conversion = isset($cond_filter_cfg['data_conversion'])?$cond_filter_cfg['data_conversion']:null;
            $any_match = false;
            foreach($cond_filter_data_arr as $cond_filter_data_idx=>$cond_filter_data){
                foreach($cond_filter_data as $cond_key=>$cond_val){
                    if($cf_key_must_exists){
                        if(!isset($src[$i][$cond_key])){$row_valid = false;break;}                        
                    }
                    $src_val = isset($src[$i][$cond_key])?$src[$i][$cond_key]:null;
                    
                    switch($cfg_data_conversion){
                        case 'str':
                            $src_val = Tools::_str($src_val);
                            $cond_val = Tools::_str($cond_val);
                            break;
                        default:
                            break;
                    }
                    
                    $compare_string = 'return $src_val '.$cf_compare_sign.' $cond_val;';
                    
                    if(eval($compare_string)) $any_match = true;
                    
                    if($cf_val_must_match){                        
                        if(!eval($compare_string)){
                            $row_valid = false;
                        }
                    }    
                    else{
                        if($any_match) $row_valid =true;
                    }
                    
                    
                }
            }
            
            
            
            if($row_valid){
                if(is_array($res_format)){
                    $key = $res_format;
                    if(count($key) === 0){
                        $trow= $src[$i];
                    }
                    else{
                        for($j = 0;$j<count($key);$j++){
                            if(isset($src[$i][$key[$j]])){
                                $trow[$key[$j]] = $src[$i][$key[$j]];
                            }
                        }
                    }
                }
                else if ($res_format === 'index'){
                    $trow = $i;
                }
            }
            if($row_valid)
            $result[] = $trow;
        }
        return $result;
        //</editor-fold>
    }
    
    static function thousand_separator($data,$max_dec=5,$force_max_dec = false){
        $result = '';
        
        
        if($force_max_dec){
            if(strpos((string)$data,'.') !== false){
                $max_dec = strlen($data)-strpos($data,'.')-1;
            }
        
        }
        
        if($max_dec>5) $max_dec = 5;
        
        $result = number_format(floatVal($data),$max_dec,'.',',');
        if(strpos($result,'.')!== false){
            while($result[strlen($result)-1] === '0' && ((strlen($result)-(strpos($result,'.')+1)) >2)){
                $result = substr($result,0,strlen($result)-1);
            }
        }
        return $result;
    }
    
    function object_to_array($data)
    {
        if (is_array($data) || is_object($data))
        {
            $result = array();
            foreach ($data as $key => $value)
            {
                $result[$key] = self::object_to_array($value);
            }
            return $result;
        }
        return $data;
    }
    
    function round($data,$digit=2){
        $result = $data;
        try{
            $result = round($result,$digit);    
        }
        catch(Exception $e){
            
        }
        return $result;
    }
    
    static function currency_get(){
        return 'Rp.';
    }
    
    static function img_load($filename,$encode=true){
        $result = '';
        if(file_exists($filename)){ 
            if($encode){
                $result = 'data:image/jpeg;base64,'.base64_encode(file_get_contents(get_instance()->config->base_url().$filename));
            }
            else{
                $result = get_instance()->config->base_url().$filename.'?lastmod=12345678';
            }
        }
        else{
            $result = get_instance()->config->base_url().'img/blank.gif?lastmod=12345678';
        }
        
        return $result;
    }
    
    function data_array_exists($data = array(), $filter = array()){
        $result = false;
        for($i = 0;$i<count($data);$i++){
            $all_match = false;
            foreach($filter as $fkey=>$fval){
                if(!isset($data[$i][$fkey])){
                    $all_match = false;
                    break;
                }
                else{
                    if($data[$i][$fkey] == $fval){
                        $all_match = true;
                    }
                    else{
                        $all_match = false;
                        break;
                    }
                }
            }
            if($all_match){
                $result = true;
                break;
            }
        }
        return $result;
    }
    
    public function curr_date(){
        return Date('Y-m-d H:i:s');
    }
    
    public function id_generate($prefix=''){
        $result = '';
        $charset='abcdefghijklmnopqrstuvwxyz0123456789';
        $length = 10;
        for($i=0;$i<$length;$i++){
            $result .= $charset[mt_rand(0, strlen($charset)-1)];
        }
        $result = $prefix.$result;
        return $result;
        
    }
    
    public static function empty_to_null($data,$remove_space=true){
        $result = Tools::_str($data);
        $temp_result = $result;
        if($remove_space) $temp_result = preg_replace ('/[ ]/','',$temp_result);
        if($temp_result ===  '') $result = null;
        
        return $result;
    }
    
    public function class_name_get($string){
        $string = strtolower($string);
        $string = ucfirst($string);
        return preg_replace_callback('/(?<=(_))./',
                      function ($m) { return strtoupper($m[0]); },
                      $string);
    }
    
    public function between($min,$max,$val){
        $result = false;
        if($val >= $min && $val <= $max) $result = true;
        return $result;
    }
    
    function html_tag($tag,$data,$attrib=array()){
        $tag_attr = '';
        foreach($attrib as $attr=>$val){
            $tag_attr.=$attr.'="'.$val.'" ';
        }
        return '<'.$tag.' '.$tag_attr.'>'.$data.'</'.$tag.'>';
    }
    
    function html_untag($element,$cfg = array()){
        $result = '';
        $remove_all_tag = isset($cfg['remove_all_tag'])?$cfg['remove_all_tag']:true;
        
        if($remove_all_tag){
            $result = strip_tags($element);
        }
        
        return $result;
    }
    
    
}




?>