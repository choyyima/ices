<script>
<?php 
    $my_file_path = APPPATH.'views/ices/dashboard/dashboard_js.php';
    $my_content = file_get_contents($my_file_path); 
    $my_content = str_replace('<script>','',$my_content);
    $my_content = str_replace('</script>','',$my_content);
?>    
</script>