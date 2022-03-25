<?php

// Checkout code for BLUF Tiny POS
// This creates the checkout session and then returns info to the front end

if ( $_SERVER['REQUEST_METHOD'] != 'POST' ) {
	die("There's nothing here for you") ;
}

require('config.php') ;

\Stripe\Stripe::setApiKey($StripeKey);

$line_items = array() ;
$status = array() ;

$success = 'https://dev.bluf.com/tinypos/success.php?ref=' ;
$cancel = 'https://dev.bluf.com/tinypos/cancelled.html' ;

// make a random order reference
$ref = '';
for ( $i = 0 ; $i < 5 ; $i++ ) {
	$ref .= chr(rand(65,90)) ;
}

$receipt =  sprintf("Order ref %s\r\n\r\n",$ref)  ;

// Sanity check, make sure these are products we have defined
foreach ( $_POST['items'] as $line ) {
	if ( in_array($line['pid'],$items) && ($line['qty'] > 0 )) {
		$line_items[] = array('price' => $line['pid'],'quantity' => $line['qty']) ;
		$receipt .= sprintf("%d\t%s\r\n",$line['qty'],$skus[$line['pid']]['description']) ;
	}
}

if ( count($line_items) > 0 ) {
	
	$checkout = \Stripe\Checkout\Session::create([
	  'line_items' => $line_items,
	  'mode' => 'payment',
	  'success_url' => $success . $ref,
	  'cancel_url' => $cancel,
	  'client_reference_id' => $ref,
	  'payment_intent_data' => array( 'description' => $receipt )
	]);
	
	$status['status'] = 'ok' ;
	$status['ref'] = $ref ;
	$status['url'] = urlencode($checkout->url) ;
	
	// email a receipt
	mail($storeemail,'BLUF tiny POS pending order ref '. $ref,$receipt) ;
	
} else {
	$status['status'] = 'error' ;
}

print json_encode($status) ;
?>