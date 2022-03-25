<?php

// BLUF tiny POS products and other config

// This is a list of the price ids from Stripe that you want to appear in your POS

$items = array( 
	'price_1Kgp7eF85rMQI4KMa6gX1dSi', // 25th anniversary tie clip
	'price_1Kgp5PF85rMQI4KMjbUc0wGr', // nickel tie clip
	'price_1Kgp6OF85rMQI4KMKOafq4vE', // gilt tie clip
	'price_1Kgp76F85rMQI4KMmtqSO8N3' // embroidered badge
) ;

$storeemail = 'someone@somewhere' ; // where to send order confirmations

$StripeKey = 'sk_test_blahblahblah' ; // your Stripe secret key

$inifile = '/var/bluf/private/tinypos/products.ini' ; // should be writable by the web server

require_once('lib-php/stripe-7.97/init.php') ; // path to the Stripe client library 

// No changes needed below this line

if ( isset($_REQUEST['reload']) || ! is_readable($inifile) ) {
	// generate a new product ini file
	
	date_default_timezone_set('UTC') ;
	$inidata = sprintf("; POS ini file created %s\n\n", strftime('%F %H:%M:%S')) ;
	
	$stripe = new \Stripe\StripeClient($StripeKey) ;
	
	$skus = array() ;
	
	// Get products from Stripe	
	foreach ( $items as $item ) {
		$pricedetails = $stripe->prices->retrieve($item) ;
		
		$productinfo = $stripe->products->retrieve($pricedetails->product) ;
		
		if ( $productinfo->active == true ) {
			$sku = array( 
					'item' => $item,
					'cost' => $pricedetails->unit_amount,
					'currency' => $pricedetails->currency,
					'name' => $productinfo->name,
					'description' => $productinfo->description,
					'image' => $productinfo->images[0]
			) ;
			$inidata .= sprintf("[%s]\nitem = %s\ncost = %s\ncurrency = %s\nname = %s\ndescription = %s\nimage = %s\n\n",$item,$item,$pricedetails->unit_amount,$pricedetails->currency,$productinfo->name,$productinfo->description,$productinfo->images[0]) ;
			$skus[] = $sku ; 
		}
	}
	
	// write the config file
	file_put_contents($inifile, $inidata) ;
} else {
	$skus = parse_ini_file($inifile,true) ;
}
?>