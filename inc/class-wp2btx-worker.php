<?php

class WP2BTX_Worker {

  function __construct() {
    add_action('wp2btx_submit_orders', array($this, 'submit_order_to_bitrix24') );
  }

	function submit_order_to_bitrix24($order_id){

		    $orderdata = $this->get_orderdata_s($order_id); 
		    $res = $this->send_data($orderdata); 
		    
		}

	function get_orderdata_s($oid){
		
        $order = new WC_Order($oid);
		
		$products_s = $this->get_order_data($oid); 

		$url_print_invoice = admin_url('admin-ajax.php?print-order='.$oid.'&print-order-type=invoice&action=print_order');

		$orderdata = array(
			'TITLE' => get_option('wp2btx_prefix') . $oid,
			'COMPANY_TITLE' => $order->get_billing_company($oid), 
			'NAME' => $order->get_billing_first_name($oid), 
			'LAST_NAME' => $order->get_billing_last_name($oid), 
			'ADDRESS' => $order->get_billing_state().' '.$order->get_billing_address_1(), 
			'OPPORTUNITY' => $order->get_total(), 
			'CURRENCY_ID' => $order->get_currency(), 
			'ASSIGNED_BY_ID' => get_option('wp2btx_user_id'),
			'COMMENTS' => strip_tags($products_s).' Печать счета: '.$url_print_invoice,
            'SOURCE_ID' => 'WEB',
            'EMAIL' => [
                '0' => [
                    'VALUE' => $order->get_billing_email(), 
                    'VALUE_TYPE' => 'WORK', 
                ], 
            ],
            'PHONE' => [
                '0' => [
                    'VALUE' => $order->get_billing_phone(), 
                    'VALUE_TYPE' => 'MOBILE', 
                ], 
            ],
		);
		//error_log('Order Data');
		//error_log(implode(', ',$orderdata));

		return $orderdata;
	
	}



	function get_order_data($order_id){
		$order = wc_get_order( $order_id );
		$order_item = 'Услуга: ';
		foreach ($order->get_items() as $key => $item) {
	      $order_item .= ''.$item['name'].' x '.$item['qty'].' цена: '.$order->get_formatted_line_subtotal( $item ).'';
	    }

	    foreach ( $order->get_order_item_totals() as $key => $total ) {
			 $order_item .= ''.$total['label'].' '.$total['value'].'';
		}

	    return $order_item;
	}


	/**
	* Send data to Bitrix24 via CRest
	*/
	function send_data($orderdata) {

		$result = CRest::call(
            'crm.lead.add',
            [
                'FIELDS' => $orderdata 
                ]
        );

        if(!empty($result['result'])){
            $msg = json_encode(['message' => 'Lead add']);
        }elseif(!empty($result['error_description'])){
            $msg = json_encode(['message' => 'Lead not added: '.$result['error_description']]);
        }else{
            $msg = json_encode(['message' => 'Lead not added']);
        }

		error_log("WP2BTX result:");
		error_log($msg);

	}

}

add_action('woocommerce_order_status_completed','submit_order_to_lead', 10,1);
function submit_order_to_lead($order_id){
	$w = new WP2BTX_Worker;
	$w->submit_order_to_bitrix24($order_id);
}