-- BOS BANK ACCOUNT --
delete from bos_bank_account;
delete from bos_bank_account_status_log;


-- PRODUCT --
delete from product;
delete from product_category;
delete from product_subcategory;
delete from product_buffer_stock;
delete from product_unit;
delete from product_unit_conversion;
delete from product_unit_parent;
delete from product_unit_child;
delete from product_sales_multiplication_qty;
delete from product_cfg;

-- Bill Of Material --
delete from bom;
delete from bom_component_product;
delete from bom_result_product;
delete from bom_status_log;

-- PRODUCT STOCK --
delete from product_stock;
delete from product_stock_history;
delete from product_stock_sales_available;
delete from product_stock_sales_available_history;
delete from product_stock_bad;
delete from product_stock_bad_history;

-- PRODUCT PRICE LIST --
delete from product_price_list;
delete from product_price_list_product;
delete from product_price_list_status_log;
delete from product_price_list_delivery_extra_charge;
delete from product_price_list_delivery_moq;
delete from product_price_list_delivery_moq_mixed;
delete from product_price_list_delivery_moq_mixed_product;
delete from product_price_list_delivery_moq_separated;
delete from product_price_list_delivery_mop;
delete from product_price_list_delivery_mop_mixed;
delete from product_price_list_delivery_mop_mixed_product;
delete from product_price_list_delivery_mop_separated;

-- UNIT --
delete from unit;

-- CUSTOMER --
delete from customer;
delete from customer_customer_type;
delete from customer_customer_type_log;
delete from customer_status_log;

-- CUSTOMER TYPE --
delete from customer_type;
delete from customer_type_product_price_list;
delete from customer_type_refill_product_price_list;


-- PURCHASE INVOICE--
delete from purchase_invoice;
delete from purchase_invoice_product;
delete from purchase_invoice_status_log;

-- PURCHASE RECEIPT --
delete from purchase_receipt_allocation;
delete from purchase_receipt;
delete from purchase_receipt_status_log;

-- PURCHASE RETURN --
delete from purchase_invoice_purchase_return;
delete from purchase_return;
delete from purchase_return_product;

-- STOCK TRANSFER --
delete from stock_transfer;
delete from stock_transfer_status_log;
delete from stock_transfer_product;

-- RECEIVE PRODUCT --
delete from rma_receive_product;
delete from purchase_invoice_receive_product;
delete from receive_product;
delete from receive_product_product;
delete from receive_product_warehouse_from;
delete from receive_product_warehouse_to;
delete from receive_product_status_log;
delete from rswo_rp;

-- DELIVERY ORDER --
delete from rma_delivery_order;
delete from delivery_order;
delete from delivery_order_final;
delete from delivery_order_final_status_log;
delete from delivery_order_final_delivery_order;
delete from delivery_order_product;
delete from delivery_order_warehouse_from;
delete from delivery_order_warehouse_to;
delete from delivery_order_status_log;
delete from sales_invoice_delivery_order_final;
delete from dof_dofc;
delete from delivery_order_final_confirmation;
delete from delivery_order_final_confirmation_status_log;
delete from delivery_order_final_confirmation_info;
delete from delivery_order_final_confirmation_additional_cost;
delete from rswo_do;

-- INTAKE --
delete  from intake_final;
delete  from intake_final_status_log;
delete  from intake;
delete  from intake_final_intake;
delete  from intake_warehouse_from;
delete  from intake_status_log;
delete  from intake_product;
delete from sales_invoice_intake_final;

-- RMA --
delete from rma;
delete from rma_product;
delete from rma_status_log;
delete from purchase_invoice_rma;

-- REQUEST FORM --
delete from request_form;
delete from request_form_status_log;

-- SALES PROSPECT --
delete from sales_prospect;
delete from sales_prospect_status_log;
delete from sales_prospect_product;
delete from sales_prospect_additional_cost;
delete from sales_prospect_additional_cost;
delete from sales_prospect_info;


-- SALES INVOICE --
delete from sales_invoice;
delete from sales_invoice_product;
delete from sales_invoice_additional_cost;
delete from sales_invoice_info;
delete from sales_invoice_status_log;

-- SALES RECEIPT --
delete from sales_receipt;
delete from sales_receipt_allocation;
delete from sales_receipt_status_log;

-- CUSTOMER DEPOSIT --
delete from dofc_cd;
delete from customer_deposit;
delete from customer_deposit_allocation;
delete from customer_deposit_customer_refund;
delete from customer_deposit_status_log;
delete from dofc_cd;
delete from rwo_cd;

-- CUSTOMER REFUND --
delete from customer_refund;
delete from customer_refund_status_log;

-- CUSTOMER BILL --
delete from dofc_cb;
delete from customer_bill;
delete from customer_bill_status_log;

-- REFILL WORK ORDER --
delete from refill_work_order;
delete from refill_work_order_product;
delete from refill_work_order_info;
delete from refill_work_order_status_log;

-- REFILL SUBCON WORK ORDER --
delete from refill_subcon_work_order;
delete from rswo_product;
delete from rswo_expected_product_result;
delete from refill_subcon_work_order_status_log;

-- REFILL CHECKING RESULT FORM --
delete from refill_checking_result_form;
delete from refill_checking_result_form_status_log;
delete from rcrf_product;
delete from rcrf_product_recondition_cost;
delete from rcrf_product_sparepart_cost;

-- REFILL INVOICE --
delete from refill_invoice;
delete from refill_invoice_status_log;
delete from ri_product;
delete from ri_product_recondition_cost;
delete from ri_product_sparepart_cost;

-- REFILL RECEIPT --
delete from refill_receipt;
delete from refill_receipt_status_log;
delete from refill_receipt_allocation;

-- Manufacturing Work Order --
delete from mf_work_order;
delete from mf_work_order_status_log;
delete from mfwo_info;
delete from mfwo_ordered_product;

-- Manufacturing Work Process --
delete from mf_work_process;
delete from mf_work_process_status_log;
delete from mfwp_checker;
delete from mfwp_component_product;
delete from mfwp_expected_result_product;
delete from mfwp_info;
delete from mfwp_result_product;
delete from mfwp_scrap_product;
delete from mfwp_worker;

-- System Investigation Report --
delete from sir;
delete from sir_status_log;

delete from product_stock_opname;
delete from pso_product;
delete from product_stock_opname_status_log;

delete from user_login_inbox;