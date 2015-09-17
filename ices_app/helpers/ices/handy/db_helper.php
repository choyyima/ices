<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class DB{
    private $db = null;
    public $row_limit = 20;
    
    function result_format_get(){
        //<editor-fold defaultstate="collapsed">
        return array('success'=>1,'msg'=>array(),'trans_id'=>'');
        //</editor-fold>
    }
    
    public function __construct($param = array()){
        $db_name = isset($param['db_name'])?$param['db_name']:
            (isset(ICES_Engine::$app['app_db_conn_name'])?
                ICES_Engine::$app['app_db_conn_name']:'default');
        if($db_name === 'default'){
            $this->db = get_instance()->db;
        }
        else{
            $this->db = get_instance()->load->database($db_name, TRUE);        
        }
        $this->db->trans_strict(TRUE);
        
    }
    
    public function query_array($sql,$limit=1000){
        $result = array();
        $rs = $this->db->query($sql);
        
        if($rs){
            $result = $rs->result_array();
            if(count($result)>$limit){
                $result = array_splice($result,0,$limit);
            }
        }
        else{
            $result = null;
        }
        
        
        return $result;
    }
    
    public function query_array_obj($sql){
        $rs = $this->db->query($sql);
        if($rs){
            $result = $rs->result_array();
            $limit = 1000;
            if(count($result)>$limit){
                $result = array_splice($result,0,$limit);
            }
            return json_decode(json_encode($result));
        }
        else{
            $rs = false;
        }
        //return json_decode(json_encode($this->_error_message()));
    }
    
    
    public function query($sql){
        return $this->db->query($sql);

    }
    
    public function select($sql,$param=array(),$extra=""){
        $q = $sql;
        $start_idx = strrpos($q,')',-1) == -1?0:strrpos($q,')',-1);
        $q_where = '';
        if(strrpos($q,'where',$start_idx) != -1 && count($param)>0) $q.=' and ';
        if(isset($param))
        foreach($param as $key=>$val){
            if(strlen($q_where) == 0)
                $q_where = ' '.$key.' = '.$this->escape($val);
            else
                $q_where = ' and '.$key.' = '.$this->escape($val);
        }
        $q.=$q_where.$extra;

        return $this->query_array($q);
    }
    
    public function select_count($sql,$param=array(),$extra=""){
        $q = $sql;
        $start_idx = strrpos($q,')',-1) == -1?0:strrpos($q,')',-1);
        $q_where = '';
        if(strrpos($q,'where',$start_idx) != -1 && count($param)>0) $q.=' and ';
        if(isset($param))
        foreach($param as $key=>$val){
            if(strlen($q_where) == 0)
                $q_where = ' '.$key.' = '.$this->escape($val);
            else
                $q_where = ' and '.$key.' = '.$this->escape($val);
        }
        $q.=$q_where.$extra;

        return $this->query_array('select count(1) total_rows from ('.$q.') tf')[0]['total_rows'];
    }
    
    public function escape($str){
        return $this->db->escape($str);        
    }
    
    public function insert($sql,$data){
        return $this->db->insert($sql,$data);
    }
    
    public function update($sql,$data,$conditional){
        return $this->db->update($sql,$data,$conditional);
    }
    
    public function trans_begin(){
        $this->db->trans_begin();
    }
    
    public function trans_commit(){
        $this->db->trans_commit();
    }
    
    public function trans_rollback(){
        $this->db->trans_rollback();
    }
    
    public function trans_status(){
        return $this->db->trans_status();
    }
    
    public function _error_message(){
        if(ENVIRONMENT === 'development'){
            return $this->db->_error_message();
        }
    }
    
    public function close(){
        return $this->db->close();
    }
    
    public function fast_get($tbl,$filter = array()){
        $result = array();
        $q_where = '';
        foreach($filter as $key=>$val){
            $q_where .= ' and '.$key.' = '.$this->db->escape($val);
        }
        $rs = $this->db->query('select * from '.$tbl.' where 1 = 1 '.$q_where);
        if($rs){
            $result = $rs->result_array();
        }
        else{
            $result = null;
        }
        return $result;
    }
    
    public function insert_id(){
        return $this->db->insert_id();
    }
}




?>