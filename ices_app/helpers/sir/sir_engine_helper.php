<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SIR_Engine {

    public static function path_get(){
        $path = array(
            'index'=>get_instance()->config->base_url().'sir/'
            ,'sir_engine'=>'sir/sir_engine'
            ,'sir_renderer' => 'sir/sir_renderer'
            ,'ajax_search'=>get_instance()->config->base_url().'sir/ajax_search/'
            ,'data_support'=>get_instance()->config->base_url().'sir/data_support/'

        );

        return json_decode(json_encode($path));
    }

    
    
    public static $module_list; 
    
    public static $status_list; 
    
    public static function helper_init(){
        //<editor-fold defaultstate="collapsed">
        self::$module_list = array(
            //<editor-fold defaultstate="collapsed">
            array(
                'name'=>array('val'=>'sales_invoice_pos','label'=>'Point of Sale'),
                'action'=>array(
                    array('val'=>'cancel','label'=>'Cancel','method'=>'sales_invoice_pos_cancel_add'),
                ),
            ),
            array(
                'name'=>array('val'=>'mf_work_process','label'=>Lang::get('Manufacturing - Work Process')),
                'action'=>array(
                    array('val'=>'free_rules','label'=>'Free Rules','method'=>'mf_work_process_free_rules_add'),
                ),
            ),
            array(
                'name'=>array('val'=>'refill_invoice','label'=>'Refill - Invoice'),
                'action'=>array(
                    array('val'=>'cancel','label'=>'Cancel','method'=>'refill_invoice_cancel_add'),
                ),
            ),
            //</editor-fold>
        );
        
        self::$status_list = array(
            //<editor-fold defaultstate="collapsed">
            array(
                'val'=>''
                ,'label'=>''
                ,'method'=>'product_stock_opname_add'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Add')
                        ,array('val'=>Lang::get(array('System Investigation Report'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            array(
                'val'=>''
                ,'label'=>''
                ,'method'=>'sales_invoice_pos_cancel_add'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Add')
                        ,array('val'=>Lang::get(array('System Investigation Report'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            array(
                'val'=>''
                ,'label'=>''
                ,'method'=>'refill_invoice_cancel_add'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Add')
                        ,array('val'=>Lang::get(array('System Investigation Report'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            array(
                'val'=>''
                ,'label'=>''
                ,'method'=>'mf_work_process_free_rules_add'
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Add')
                        ,array('val'=>Lang::get(array('System Investigation Report'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            ),
            array(//label name is used for method name
                'val'=>'done'
                ,'label'=>'DONE'
                ,'method'=>'sir_done'
                ,'default'=>true
                ,'next_allowed_status'=>array()
                ,'msg'=>array(
                    'success'=>array(
                        array('val'=>'Update')
                        ,array('val'=>Lang::get(array('System Investigation Report'),true,true,false,false,true))
                        ,array('val'=>'success')
                    )
                )
            )
            //</editor-fold>

        );
        //</editor-fold>
    }
    
    public static function sir_exists($id){
        $result = false;
        $db = new DB();
        $q = '
            select 1 
            from sir
            where status > 0 && id = '.$db->escape($id).'
        ';
        $rs = $db->query_array_obj($q);
        if(count($rs)>0){
            $result = true;
        }
        return $result;
    }
    
    public static function validate($method,$data=array()){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('sir/sir_data_support');
        get_instance()->load->helper('sir/sir_validator');
        
        $result = array(
            "success"=>1
            ,"msg"=>array()

        );
        $success = 1;
        $msg = array();
        $sir = isset($data['sir'])?$data['sir']:null;
        $sir_id = isset($sir['id'])?
            Tools::_str($sir['id']):'';

        $db = new DB();
        
        switch($method){            
            case 'sales_invoice_pos_cancel_add':
            case 'refill_invoice_cancel_add':
                //<editor-fold defaultstate="collapsed" desc="SIR MANDATORY VALIDATION">
                $store_id = isset($sir['store_id'])?Tools::_str($sir['store_id']):'';
                if(!count($db->fast_get('store',array('id'=>$store_id,'status'=>'1')))>0){
                    $success = 0;
                    $msg[] = Lang::get('Store').' '.Lang::get('empty',true,false);
                }
                
                $module_name = isset($sir['module_name'])?Tools::_str($sir['module_name']):'';
                $module_action = isset($sir['module_action'])?Tools::_str($sir['module_action']):'';
                if(!SIR_Data_Support::module_action_exists($module_name, $module_action)){
                    $success = 0;
                    $msg[] = Lang::get('Module').' '.Lang::get('empty',true,false);
                }
                
                if($method !== SIR_Data_Support::module_name_action_method_get($module_name,$module_action)){
                    $success = 0;
                    $msg[] = Lang::get('Method different to Module Name and Action');
                }
                
                $creator = isset($sir['creator'])?Tools::_str($sir['creator']):'';
                if(str_replace(' ','',$creator) === ''){
                    $success = 0;
                    $msg[] = Lang::get('Creator').' '.Lang::get('empty',true, false);
                }
                
                $description = isset($sir['description'])?Tools::_str($sir['description']):'';
                if(str_replace(' ','',$description) === ''){
                    $success = 0;
                    $msg[] = Lang::get('Description').' '.Lang::get('empty',true, false);
                }
                //</editor-fold>
                break;            
        }
        
        if($success === 1){
            switch($method){
                case 'sales_invoice_pos_cancel_add':
                    //<editor-fold defaultstate="collapsed">
                    $temp_result = eval('return SIR_Validator::'.$method.'_validate($data); ');

                    $success = $temp_result['success'];
                    $msg = array_merge($msg,$temp_result['msg']);
                    if($success !== 1) break;


                    //</editor-fold>
                    break;
                case 'refill_invoice_cancel_add':
                    //<editor-fold defaultstate="collapsed">

                    $temp_result = eval('return SIR_Validator::'.$method.'_validate($data); ');

                    $success = $temp_result['success'];
                    $msg = array_merge($msg,$temp_result['msg']);
                    if($success !== 1) break;


                    //</editor-fold>
                    break;
                case 'sir_done':
                    $success = 0;
                    $msg[] = 'Update System Investigation Report '.Lang::get('failed',true,false);

                    break;
                default:
                    $success = 0;
                    $msg[] = 'Unknown Validation Method';
                    break;

            }
        }
        
        
        $result['msg'] = $msg;
        $result['success'] = $success;
        return $result;
        //</editor-fold>
    }

    public static function adjust($action,$data=array()){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('sir/sir_adjuster');
        $db = new DB();
        $result = array();
        $sir_data = isset($data['sir'])?Tools::_arr($data['sir']):array();
        
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');
        
        $sir = array(
            'store_id'=>$sir_data['store_id'],
            'module_name'=>$sir_data['module_name'],
            'module_action'=>$sir_data['module_action'],
            'reference_id'=>$sir_data['reference_id'],
            'sir_date'=>Date('Y-m-d H:i:s'),
            'description'=>$sir_data['description'],
            'creator'=>$sir_data['creator'],
            'sir_status'=>SI::status_default_status_get('SIR_Engine')['val'],
            'status'=>'1',
            'modid'=>$modid,
            'moddate'=>$moddate
        );
        $result['sir'] = $sir;
                
        $module_name = isset($sir_data['module_name'])?Tools::_str($sir_data['module_name']):'';
        $module_action = isset($sir_data['module_action'])?Tools::_str($sir_data['module_action']):'';
        
        $result = eval('return SIR_Adjuster::'.$action.'_adjust($data,$result); ');
        
        return $result;
        //</editor-fold>
    }

    public function sir_add($db,$final_data,$action){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('sir/sir_saver');
        $result=array('success'=>1,'msg'=>array(),'trans_id'=>'');
        $success = 1;
        $msg = array();

        $fsir = $final_data['sir'];

        $store_id = $fsir['store_id'];
        $modid = User_Info::get()['user_id'];
        $moddate = Date('Y-m-d H:i:s');

        $module_name = $fsir['module_name'];
        $module_action = $fsir['module_action'];
        
        
        $sir_id = '';                            
        $fsir['code'] = SI::code_counter_store_get($db,$store_id, 'system_investigation_report');
        if(!$db->insert('sir',$fsir)){
            $msg[] = $db->_error_message();
            $db->trans_rollback();                                
            $success = 0;
        }

        $sir_code = $fsir['code'];

        if($success == 1){                                
            $sir_id = $db->fast_get('sir'
                    ,array('code'=>$sir_code))[0]['id'];
            $result['trans_id']=$sir_id; 

        }

        if($success == 1){
            $sir_status_log = array(
                'sir_id'=>$sir_id
                ,'sir_status'=>$fsir['sir_status']
                ,'modid'=>$modid
                ,'moddate'=>$moddate    
            );

            if(!$db->insert('sir_status_log',$sir_status_log)){
                $msg[] = $db->_error_message();
                $db->trans_rollback();                                
                $success = 0;
            }
        }
        
        if($success === 1){
            $temp_result = eval('return SIR_Saver::'.$action.'($db,$final_data,$sir_id);');
            $success = $temp_result['success'];
            $msg = array_merge($msg,$temp_result['msg']);
        }
        

        $result['success'] = $success;
        $result['msg'] = $msg;
        return $result;
        //</editor-fold>
    }

    public function sales_invoice_pos_cancel_add($db, $final_data, $id){
        //<editor-fold defaultstate="collapsed">
        return self::sir_add($db,$final_data,'sales_invoice_pos_cancel_add');
        //</editor-fold>
    }
    
    public function refill_invoice_cancel_add($db, $final_data, $id){
        //<editor-fold defaultstate="collapsed">
        return self::sir_add($db,$final_data,'refill_invoice_cancel_add');
        //</editor-fold>
    }
    
    public function mf_work_process_free_rules_add($db, $final_data, $id){
        //<editor-fold defaultstate="collapsed">
        return self::sir_add($db,$final_data,'mf_work_process_free_rules_add');
        //</editor-fold>
    }
    
}
?>
