
<div class="modal fade" id="rppl_modal_price_list" tabindex="-1" role="dialog" aria-hidden="true" 
     active_child="-1">
<div class="modal-dialog" style="width:75%">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"><i class="<?php ?>"></i><span></span></h4>
        </div>

        <div class="modal-body" style="font-size:12px">
            <input style="display:none" id="product_category_row">
            <table id = 'price_list_table' class='table' style = "font-size:12px;table-layout:fixed;">
                <thead>
                    <th class='table-row-num' col_name="row_num"><br/>#</th>
                    <th style="width:200px;text-align:center" col_name="min_cap">Min Cap. </th>
                    <th style="width:200px;text-align:center" col_name="max_cap">Max Cap.</th>
                    <th style='min-width:200px;text-align:center' col_name="price">Price</th>                    
                    <th class='table-action' col_name="action"></th>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div class="modal-footer clearfix">
            <button id = "rpl_modal_price_list_btn_ok" type="button" class="btn btn-primary pull-left"><i class="fa fa-check"></i> OK</button>
            <button id = "rpl_modal_price_list_btn_cancel" type="button" class="btn btn-default pull-left" data-dismiss="modal"><i class="fa fa-minus-circle"></i> Cancel</button>
        </div>
    </div>
</div>
</div>