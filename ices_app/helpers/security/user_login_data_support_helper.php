<?php
class User_Login_Data_Support {
    public static function store_list_get(){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '';
        if(User_Info::get()['role']!== 'ROOT'){
            $q = '
                select s.*
                from user_login_store uls
                    inner join store s on uls.store_id = s.id
                where uls.user_login_id = '.User_Info::get()['user_id'].'
            ';
        }
        else{
            $q = '
                select s.*
                from store s
                where s.status>0
            ';
        }
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs;
        }
        return $result;
        //</editor-fold>
    }
    
    public static function default_store_get(){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select s.*
            from store s
                inner join user_login ul on s.id = ul.default_store_id
            where ul.id = '.$db->escape(User_Info::get()['user_id']).'
        ';
        
        
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        return $result;
        //</editor-fold>
    }
    
    public static function user_login_store_exists($store_id){
        //<editor-fold defaultstate="collapsed">
        $result = false;
        $db = new DB();
        $q = '
            select 1
            from user_login_store uls
            inner join store s on uls.store_id = s.id
            where uls.user_login_id = '.$db->escape(User_Info::get()['user_id']).'
                and s.status > 0
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0) $result = true;
        return $result;
        //</editor-fold>
    }
}
?>
