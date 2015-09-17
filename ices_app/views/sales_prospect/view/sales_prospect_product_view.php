        
        <div class="form-group">
            <label><input class='disable_all' type="checkbox" id="sales_prospect_delivery_checkbox"  disable_all_type="iCheck"> Delivery</label>
            <div class='table-responsive' style="overflow-x:auto">
            <table id="sales_prospect_product_table" class="table pos-table table-fixed">
                <thead>
                    <tr>
                    <th class='pos-table-row-num'>#</th>
                    <th class='pos-table-product-img'></th>
                    <th class='pos-product-table-product'>Product</th>
                    <th class='pos-product-table-total-stock' col_name="total_stock"><?php echo preg_replace('/ /','<br/>','Total Stock',1) ?></th>
                    <th class='pos-product-table-mult-qty'>Mult.<br/>Qty</th>
                    <th class='pos-product-table-qty'>Qty</th>
                    <th class='pos-product-table-unit'>Unit</th>
                    <th class='pos-product-table-expedition-weight' ><?php echo preg_replace('/ /','<br/>',Lang::get('Expedition Weight'),1); ?></th>
                    <th class='pos-product-table-amount'>Amount (<?php echo Tools::currency_get(); ?>)</th>
                    <th class='pos-product-table-subtotal'>Sub Total (<?php echo Tools::currency_get(); ?>)</th>
                    <th class='pos-table-action'></th>
                    
                    </tr>
                </thhead>
                <tbody>                    
                </tbody>
                <tfoot>
                    <tr><td colspan="7" >
                        </td>
                        <td style="text-align:right;" col_name="expedition_weight">
                            <span>
                            <strong id="sales_prospect_expedition_weight_total"> </strong>
                            </span>
                        </td>
                        <td style="text-align:right">
                        <strong >Total <?php echo '('.Tools::currency_get().')'?></strong></td>
                        <td style="text-align:right"><span><strong id="sales_prospect_product_total" ></strong></span></td><td></td></tr>
                    <tr><td colspan="9" style="text-align:right;vertical-align:middle">
                            <span><input id="sales_prospect_product_discount_percent" style="text-align:right;width:75px;height:34px;padding:6px 12px;"></span>
                            <strong> % Discount <?php echo '('.Tools::currency_get().')'?>&nbsp</strong>
                        </td>
                        <td><input id="sales_prospect_product_discount" style="text-align:right" class="form-control" value='0.00'></td>
                        <td></td>
                    </tr>
                    <tr><td colspan="9" style="text-align:right">
                            <button id="sales_prospect_btn_extra_charge" class="btn btn-default btn-xs" ><i class="fa fa-clipboard"></i></button>
                            <strong>Extra Charge <?php echo '('.Tools::currency_get().')'?></strong></td>
                        <td style="text-align:right"><span><strong id = "sales_prospect_product_extra_charge"></strong></span></td><td></td></tr>
                    <tr><td colspan="7" style="text-align:right">
                                
                        </td>
                        
                        <td colspan="2" style="text-align:right;vertical-align:middle">                        
                            <strong><?php echo Lang::get('Estimated Delivery Cost');?> <?php echo '('.Tools::currency_get().')'?></strong>
                        </td>
                        <td style="text-align:right"  >
                            
                            <input style="text-align:right" id = "sales_prospect_delivery_cost_estimation"
                                   class="hide_all form-control disable_all"  disable_all_type="common">
                                <strong class = 'hide_all' style="padding:10px;" id = "sales_prospect_delivery_cost_estimation_text"> </strong>
                            
                        </td>
                        <td></td>
                    </tr>
                
                    <tr><td colspan="9" style="text-align:right;border-top:1px solid #ddd;">
                            <strong>GRAND TOTAL <?php echo '('.Tools::currency_get().')'?></strong>
                        </td>
                        <td style="text-align:right;border-top:1px solid #ddd;">
                            <span><strong id = "sales_prospect_product_grand_total"></strong></span>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            </div>
        </div>



