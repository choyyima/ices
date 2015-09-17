<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Dashboard_Data_Support {

    public static function coba_get() {
        $result = array('prfix_id' => '#coba', 'target_data' => 'coba body', 'data' => '');

        $db = new DB();
        $q = 'select id,app_name,name,coba_status from coba';
        $rs = $db->query_array($q);
        if (count($rs) > 0) {
            $data = '';
            foreach ($rs as $i => $row) {
                $data .='<tr>';
                $data .='<td>' . ($i + 1) . '</td>';
                $data .='<td><a target="_blank" href="' . get_instance()->config->base_url() . 'coba/view/' . $row['id'] . '">' . ($row['app_name']) . '</a></td>';
                $data .='<td>' . ($rs[$i]['name']) . '</td>';
                $data .='<td>' . ($rs[$i]['coba_status']) . '</td>';
                $data .='</tr>';
            }
            $result['data'] = $data;
        }
        return $result;
    }

}

?>
