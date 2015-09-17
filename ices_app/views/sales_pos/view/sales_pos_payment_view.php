
        <div class="form-group">
            <label>Customer Deposit</label>
            <table id="sales_pos_customer_deposit_table" class="table fixed-table pos-table">
                <thead>
                    <tr>
                    <th style="width:30px">#</th>
                    <th style="">Code </th>
                    <th style="">Deposit Date</th>
                    <th style='text-align:right'>Amount (<?php echo Tools::currency_get(); ?>)</th>
                    <th style='text-align:right;width:150px'>Allocated <br/>Amount (<?php echo Tools::currency_get(); ?>)</th>
                    <th style='width:50px'></th>
                    </tr>
                </thhead>
                <tbody>                    
                </tbody>
                <tfoot>
                    <tr><td colspan="4" style="text-align:right">
                            <span><strong >Total <?php echo '('.Tools::currency_get().')'?></strong></span>
                        </td>
                        <td style="text-align:right">
                            <span><strong id="sales_pos_customer_deposit_allocated_amount_total" ></strong></span>
                        </td>
                        <td/>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="form-group" >
            <label>Receipt</label>
            <div style="overflow-x:auto">
            <table id="sales_pos_payment_table" class="table pos-table fixed-table">
                <thead>
                    <tr>
                    <th style="width:30px">#</th>
                    <th style="width:120px">Code </th>
                    <th style="width:120px">Payment Type</th>
                    <th style="width:120px">Date</th>
                    <th style="width:130px">Customer Bank Acc</th>
                    <th style="width:120px">BOS Bank Account</th>
                    <th style='text-align:right;width:140px'>Amount (<?php echo Tools::currency_get(); ?>)</th>
                    <th style='text-align:right;width:130px'>Allocated Amount (<?php echo Tools::currency_get(); ?>)</th>
                    <th style='width:50px'></th>
                    </tr>
                </thhead>
                <tbody>                    
                </tbody>
                <tfoot>
                    <tr><td colspan="6" style="text-align:right">
                        <strong >Total <?php echo '('.Tools::currency_get().')'?></strong></td>
                        <td style="text-align:right"><span><strong id="sales_pos_payment_total" ></strong></span></td>
                        <td style="text-align:right;"><span><strong id="sales_pos_payment_allocated_amount_total" ></strong></span></td>
                        <td/>
                    </tr>
                </tfoot>
            </table>
            </div>
        </div>



