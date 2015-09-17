<div class="form-group" style="">
<div class=""><label>Product</label></div>
<div class="table-responsive" id="">
    <table id="purchase_invoice_product_table" class="table" style="font-size:12px !important;">
        <thead>
            <tr>
                <th col_name="row_num" class="table-row-num">#</th>
                <th col_name="product_img" class="product-img"></th>
                <th col_name="product" class="table-product-search">Product</th>
                <th col_name="qty" class="table-qty" style="text-align:right">Qty</th>
                <th col_name="movement_outstanding_qty" class="table-qty" style="text-align:right"><?php echo Lang::get('Movement Outstanding Qty',true,false,true)?></th>
                <th col_name="unit" class="table-unit">Unit</th>                                
                <th col_name="amount" style="text-align:right" class="">Amount</th>
                <th col_name="subtotal" style="text-align:right;width:200px" class="">Subtotal</th>
                <th col_name="action" class="table-action"></th>
            </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
            <?php $f_colspan=5; ?>
            <tr><td colspan="<?php echo $f_colspan; ?>" style="text-align:right;border-top:2px solid #ddd">
                </td>
                <td col_name="movement_outstanding_qty" style="text-align:right;border-top:2px solid #ddd"></td>
                <td style="text-align:right;border-top:2px solid #ddd">
                    <strong >Total <?php echo '('.Tools::currency_get().')'?></strong></td>
                <td style="text-align:right;border-top:2px solid #ddd"><span><strong id="purchase_invoice_product_total" ></strong></span>
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
</div>