<?php

class App_Icon{
    function __construct(){
        
    }
    
    
    public static function submit(){return 'fa fa-save';}
    public static function cancel(){return 'fa fa-times';}
    public static function dashboard(){return 'fa fa-dashboard';}
    public static function smart_search() { return array('fa fa-graduation-cap','fa fa-search');}
    public static function search() { return 'fa fa-search';}
    public static function store(){return 'fa fa-building';}
    public static function money() {return 'fa fa-money';}
    public static function mail(){return 'fa fa-envelope';}
    public static function bos_bank_account() {return 'fa fa-bank';}
    public static function customer() {return 'fa fa-user';}
    public static function user(){return 'fa fa-user';}
    public static function u_group(){return 'fa fa-group';}
    public static function menu(){return 'fa fa-align-left';}
    public static function customer_type(){ return 'fa fa-users';}
    
    public static function detail_btn_delete() {return 'fa fa-times';}
    public static function detail_btn_edit() {return 'fa fa-pencil-square-o';}
    public static function detail_btn_save() {return 'fa fa-save';}
    public static function detail_btn_download() {return 'fa fa-download';}
    public static function detail_btn_cancel() {return 'fa fa-times';}
    public static function detail_btn_back() {return 'fa fa-arrow-left';}
    public static function printer() {return 'fa fa-print';}
    public static function btn_add(){return 'fa fa-plus';}
    public static function btn_back() {return 'fa fa-arrow-left';}
    public static function btn_save() {return 'fa fa-save';}
    public static function btn_cancel() {return 'fa fa-times';}
    public static function btn_refresh() {return 'fa fa-refresh';}
    public static function sir() {return 'fa fa-pencil-square-o';}
    
    public static function request_form(){return 'fa fa-calendar-o';}
    public static function report() {return 'fa fa-bar-chart-o';}
    public static function message() {return 'fa fa-envelope-o';}
    
    public static function info() {return 'fa fa-info';}
    
    public static function contact_category() {return 'fa fa-info';}
    
    public static function html_get($app_icon){
        $result='';
        if(is_array($app_icon)){
            $app_icon_arr = $app_icon;
            foreach($app_icon_arr as $icon_idx=>$icon){
                $result.='<i class="'.$icon.'"></i> ';
            }
        }
        else{
            $result.='<i class="'.$app_icon.'"></i>';
        }
        return $result;
    }
    
}
?>
