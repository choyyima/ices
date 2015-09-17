<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Security_Menu_Engine {
    static $menu_list;
    
    public static function helper_init(){
        //<editor-fold defaultstate="collapsed">
        
        self::$menu_list = array();
        $ices_app_base_url = SI::type_get('ICES_Engine','ices','$app_list')['app_base_url']; 
        self::$menu_list['ices'] = array(
            //<editor-fold defaultstate="collapsed" desc="ices menu">
            Lang::get("Dashboard")=>array(
                'id'=>'d'
                ,"properties"=>array(
                    "class"=>"fa fa-dashboard"
                )
                ,"ref"=>$ices_app_base_url."dashboard"                
            )
            ,Lang::get("Security")=>array(
                'id'=>'s'
                ,"properties"=>array(
                    "class"=>"fa fa-cog"
                )
                ,"ref"=>"#"
                ,"child"=>array(
                    Lang::get("User Group")=>array(
                        'id'=>'s_ug'
                        ,"properties"=>array("class"=>"fa fa-th")
                        ,"ref"=>$ices_app_base_url."u_group"
                    )
                    ,Lang::get("Employee")=>array(
                        'id'=>'s_ul',
                        "properties"=>array("class"=>"fa fa-th")
                        ,"ref"=>$ices_app_base_url."employee"
                    )
                    ,Lang::get("System")=>array(
                        'id'=>'s_s',
                        "properties"=>array("class"=>"fa fa-th")
                        ,"ref"=>"#"
                        ,"child"=>array(
                            Lang::get("Controller")=>array(
                                'id'=>'s_s_cnt',
                                "properties"=>array("class"=>"fa fa-th")
                                ,"ref"=>$ices_app_base_url."security_controller"
                            ),
                            Lang::get("Component")=>array(
                                'id'=>'s_s_cmp',
                                "properties"=>array("class"=>"fa fa-th")
                                ,"ref"=>$ices_app_base_url."security_component"
                            )
                        )
                    )
                    ,Lang::get("Menu")=>array(
                        'id'=>'s_m',
                        "properties"=>array("class"=>"fa fa-th")
                        ,"ref"=>$ices_app_base_url."security_menu"
                    )                    
                )
            )
            //</editor-fold>
        );
        
        $phone_book_app_base_url = SI::type_get('ICES_Engine','phone_book','$app_list')['app_base_url']; 
        self::$menu_list['phone_book'] = array(
            //<editor-fold defaultstate="collapsed" desc="ices menu">
            Lang::get("Dashboard")=>array(
                'id'=>'d'
                ,"properties"=>array(
                    "class"=>"fa fa-dashboard"
                )
                ,"ref"=>$ices_app_base_url."dashboard"                
            ),
            Lang::get("Master")=>array(
                'id'=>'m'
                ,"properties"=>array(
                    "class"=>APP_ICON::info()
                )
                ,"ref"=>"#"
                ,"child"=>array(
                    Lang::get("Contact Cagetory")=>array(
                        'id'=>'m_cc'
                        ,"properties"=>array("class"=>"fa fa-th")
                        ,"ref"=>$ices_app_base_url."contact_category"
                    )
                )
            )
            
            //</editor-fold>
        );
        
        //</editor-fold>
    }
    
    public static function path_get(){
        $app = SI::type_get('ICES_Engine','ices','$app_list');
        $path = array(
            'index'=>$app['app_base_url'].'security_menu/'
            ,'security_menu_engine'=>  $app['app_base_dir'].'security_menu/security_menu_engine'
            ,'security_menu_data_support'=>$app['app_base_dir'].'security_menu/security_menu_data_support'
            ,'security_menu_renderer' => $app['app_base_dir'].'security_menu/security_menu_renderer'
            ,'ajax_search'=>$app['app_base_url'].'security_menu/ajax_search/'
            ,'data_support'=>$app['app_base_url'].'security_menu/data_support/'

        );

        return json_decode(json_encode($path));
    }
    
    public static function current_user_menu_get(){
        $result = array();
        $path = self::path_get();
        get_instance()->load->helper($path->security_menu_data_support);
        $app_name = ICES_Engine::$app['val'];
        $ices = SI::type_get('ICES_Engine','ices','$app_list');
        $db = new DB(array('db_name'=>$ices['app_db_conn_name']));
        
        $result = self::$menu_list[$app_name];
        
        if(User_Info::get()['role']!='ROOT'){
            
            $u_group_security_menu = Security_Menu_Data_Support::
                    u_group_security_menu_by_employee_get(User_Info::get()['user_id'],$app_name);
            
            foreach($result as $key1=>$lvl1){
                if(isset($lvl1['child'])){
                    foreach($lvl1['child'] as $key2=>$lvl2){
                        if(isset($lvl2['child'])){
                            foreach($lvl2['child'] as $key3=>$lvl3){
                                $found = false;
                                for($i = 0;$i<count($u_group_security_menu);$i++){
                                    if($u_group_security_menu[$i]->menu_id == $lvl3['id']){
                                        $found = true;
                                    }
                                }
                                if(!$found){
                                    unset($result[$key1]['child'][$key2]['child'][$key3]);
                                }
                            }
                        }
                        
                    }
                }
                $found = false;
                for($i = 0;$i<count($u_group_security_menu);$i++){
                    if($u_group_security_menu[$i]->menu_id == $lvl1['id']){
                        $found = true;
                    }
                }
                if(!$found){
                    unset($result[$key1]);
                }
            }
        }
        
        
        return $result;
    }
}
?>
