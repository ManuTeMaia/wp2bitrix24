<?php


/**
 *
 */
class WooBC_Main_Worker {

  function __construct() {
    add_action('woobc_submit_orders', array($this, 'submit_order_to_bitrix24') );
  }

	/*
	* Функция отправки заказов в Битрикс24
	*/
	function submit_order_to_bitrix24($order_id){

		    $orderdata = $this->get_orderdata_s($order_id); //получаем данные для запроса
		    $res = $this->send_data($orderdata); //отправляем запрос и обрабатываем ответ
		    
		}

	function get_orderdata_s($oid){
		
        $order = new WC_Order($oid);
		
		$products_s = $this->get_order_data($oid); //детали заказа

		$url_print_invoice = admin_url('admin-ajax.php?print-order='.$oid.'&print-order-type=invoice&action=print_order');

		$orderdata = array(
			'TITLE' => get_option('woobc_prefix') . $oid,
			'COMPANY_TITLE' => $order->get_billing_company($oid), //имя компании
			'NAME' => $order->get_billing_first_name($oid), //имя заказчика
			'LAST_NAME' => $order->get_billing_last_name($oid), //фамилия заказчика
			'ADDRESS' => $order->get_billing_state().' '.$order->get_billing_address_1(), //адрес заказчика
			'OPPORTUNITY' => $order->get_total(), //сумма заказа
			'CURRENCY_ID' => $order->get_currency(), //валюта заказа
			'ASSIGNED_BY_ID' => get_option('woobc_user_id'),
			'PHONE_MOBILE' => $order->get_billing_phone(), //телефон заказчика
			'EMAIL_WORK' => $order->get_billing_email(), //email заказчика 
			'COMMENTS' => strip_tags($products_s).' Печать счета: '.$url_print_invoice,
		);
		error_log('Order Data');
		error_log(implode(', ',$orderdata));

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
	* Send data to Bitrix24
	*/
	function send_data($orderdata) {

		$result = CRest::call(
            'crm.lead.add',
            ['FIELDS' => 
                [
                    'TITLE' => $orderdata['TITLE'], 
                    'NAME' => $orderdata['NAME'],
                    'LAST_NAME' => $orderdata['LAST_NAME'],
                    'EMAIL' => [
                        '0' => [
                            'VALUE' => $orderdata['EMAIL_WORK'], 
                            'VALUE_TYPE' => 'WORK', 
                        ], 
                    ],
                    'PHONE' => [
                        '0' => [
                            'VALUE' => $orderdata['PHONE_MOBILE'], 
                            'VALUE_TYPE' => 'MOBILE', 
                        ], 
                    ],
                    'ADDRESS' => $orderdata['ADDRESS'],
                    'OPPORTUNITY' => $orderdata['OPPORTUNITY'],
                    'CURRENCY_ID' => $orderdata['CURRENCY_ID'],
                    'CREATED_BY_ID' => 1,
                    'SOURCE_ID' => 'WEB',
                    'COMMENTS' => $orderdata['COMMENTS']
                ], 
            ]
        );

		error_log("Bitrix result:");
		error_log($result);
		return $result;
	}

}

add_action('woocommerce_order_status_completed','submit_order_to_lead', 10,1);
function submit_order_to_lead($order_id){
	$w = new WooBC_Main_Worker;
	$w->submit_order_to_bitrix24($order_id);
	error_log("Order ID: ");
	error_log($order_id);
}