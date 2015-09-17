<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Intake_Data_Support {
    
    static function intake_get($id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select t1.* 
            from intake t1
            where t1.status>0 
                and t1.id = '.$db->escape($id).'

        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        
        return $result;
        //</editor-fold>
    }
    
    static function intake_product_get($intake_id){
        get_instance()->load->helper('product/product_engine');
        get_instance()->load->helper('product/product_data_support');
        
        $result = array();
        $db = new DB();
        //<editor-fold defaultstate="collapsed" desc="Registered Product">

        $q = '
            select t1.reference_type,
                t1.reference_id,
                t1.qty,
                t2.id product_id,
                t2.code product_code,
                t2.name product_name,
                t3.id unit_id,
                t3.code unit_code,
                t3.name unit_name,
                t1.product_type
                
            from intake_product t1
                inner join product t2 on t1.product_id = t2.id
                inner join unit t3 on t1.unit_id = t3.id
            where t1.intake_id = '.$db->escape($intake_id).'
                and t1.product_type = "registered_product"
        ';
        $rs_product = $db->query_array($q);
        if(count($rs_product)>0){
            for($i = 0;$i<count($rs_product);$i++){
                $product_id = $rs_product[$i]['product_id'];
                $product_type = $rs_product[$i]['product_type'];
                
                switch($product_type){
                    case 'registered_product':
                        $rs_product[$i]['product_img'] = Product_Engine::img_get($product_id);
                        $rs_product[$i]['product_text'] = SI::html_tag('strong',$rs_product[$i]['product_code'])
                            .' '.Product_Data_Support::product_type_get($rs_product[$i]['product_type'])['label']
                            .' - '.$rs_product[$i]['product_name']
                        ;
                        break;
                }
            }
            $result = array_merge($result,$rs_product);
        }
        //</editor-fold>

        return $result;
    }
    
    static function reference_detail_get($reference_type,$reference_id,$intake_id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('intake_final/intake_final_engine');
        $path = Intake_Final_Engine::path_get();
        $result = array();
        $db = new DB();
        switch($reference_type){
            case 'sales_invoice':
                $intake_final = Intake_Data_Support::intake_final_get($intake_id);
                                   
                $result = array(
                    array('id'=>'type','label'=>'Type: ','val'=>SI::type_get('intake_engine', $reference_type)['label']),
                    array('id'=>'dof_code','label'=>Lang::get('Product Intake Final').': ',
                        'val'=>SI::html_tag('a',$intake_final['code'],array('target'=>'_blank','href'=>$path->index.'view/'.$intake_final['id']))
                    ),
                );
                break;
            
        }
        return $result;
        //</editor-fold>
    }
    
    static function warehouse_from_get($id){
        //<editor-fold defaultstate="collapsed">
        $result = array();
        $db = new DB();
        $q = '
            select dowf.*
            from intake_warehouse_from dowf
            where dowf.intake_id = '.$db->escape($id).'

        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $result = $rs[0];
        }
        
        return $result;
        //</editor-fold>
    }
   
    public static function notification_intake_not_done_get(){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('rpt_simple/rpt_simple_data_support');
        $result = array('response'=>null);
        $response = null;
        $temp_result = Rpt_Simple_Data_Support::report_table_intake_not_done();        
        if($temp_result['info']['data_count']>0){
            $response = array(
                'icon'=>App_Icon::html_get('fa fa-truck')
                ,'href'=>get_instance()->config->base_url().'rpt_simple/index/intake/not_done'
                ,'msg'=>' '.($temp_result['info']['data_count']).' '.Lang::get(array('product intake'),true,false,false,true).' - '.Lang::get(array('not done yet'),true,false,false,true).''
            );
        }
        $result['response'] = $response;
        return $result;
        //</editor-fold>
    }
        
    static function intake_final_get($intake_id){
        //<editor-fold defaultstate="collapsed">
        get_instance()->load->helper('intake_final/intake_final_data_support');
        $result = array();
        $db = new DB();
        $q = '
            select dofdo.intake_final_id
            from intake_final_intake dofdo
            where dofdo.intake_id = '.$db->escape($intake_id).'
        ';
        $rs = $db->query_array($q);
        if(count($rs)>0){
            $dof_id = $rs[0]['intake_final_id'];
            $result = Intake_Final_Data_Support::intake_final_get($dof_id);
            
        }
        return $result;
        //</editor-fold>
    }
}
?>