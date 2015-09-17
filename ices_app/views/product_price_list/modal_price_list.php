
    <div class="modal fade in" id="product_price_list_modal_price_list" tabindex="-1" role="dialog" aria-hidden="true" 
         active_child="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="<?php echo APP_Icon::product_price_list() ?>"></i> <strong></strong></h4>
            </div>
            
            <div class="modal-body">
                <table id = 'product_price_list_modal_price_list_table' class='table' style = "font-size:14px;table-layout:fixed">
                    <thead>
                        <th style="width:30px">#</th>
                        <th style='text-align:right'>  Min Qty </th>
                        <th style='text-align:right'>Amount </th>
                        <th style="width:30px"></th>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer clearfix">
                <button id = "product_price_list_modal_price_list_btn_ok" type="button" class="btn btn-primary pull-left"><i class="fa fa-check"></i> OK</button>
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><i class="fa fa-minus-circle"></i> Cancel</button>
            </div>

        </div>
    </div>
    </div>