<script>
var rpt_refill_save_excel_get_param = function(){
    var lparent_pane = "<?php echo $detail_tab; ?>";
    var lprefix_id = "#<?php echo $component_prefix_id; ?>";
    var lresult = {};
    lresult.start_date = $(lparent_pane).find(lprefix_id+'_start_date').val();
    lresult.end_date = $(lparent_pane).find(lprefix_id+'_end_date').val();
    return lresult;        
}
</script>