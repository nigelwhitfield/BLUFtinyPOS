<?php
// BLUF tiny POS system - generate page of products from Stripe price info, to display for POS

require_once('config.php') ;
?>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>BLUF tiny POS</title>
		<link rel="stylesheet" href="pos.css">
		<script src="/lib-js/jquery/jquery1.12.4.min.js"></script>
	</head>
	<body>
		<div id="tpmain">
		<h1>Welcome to BLUF's tiny POS</h1>
		<p>Tap on a product picture to add it to your basket. Use the + and - buttons to change quantities. When you're done, tap the Finish and Pay button</p>
		<div class="container">
<?php foreach ( $skus as $sku ) { ?>
			<div>
				<img class="product" src="<?php echo $sku['image']; ?>" data-prod="<?php echo $sku['item']; ?>" data-cost="<?php echo $sku['cost']; ?>">
				<p><?php echo $sku['name']; ?></p>
				<h3>€<?php echo $sku['cost']/100; ?></h3>
				<p><span class="plusminus prodminus" data-prod="<?php echo $sku['item']; ?>">–</span><span class="qty" data-prod="<?php echo $sku['item']; ?>">&nbsp;</span><span class="plusminus prodplus" data-prod="<?php echo $sku['item']; ?>">+</span></p>
			</div>	
<?php }	?>	
		</div>
		<div id="finish">
			Finish & Pay
		</div>
		<div id="total">
		</div>
		<div class="reset" id="empty" style="display:none">
			Empty basket
		</div>
		</div>
		<div id="tpqr" style="display:none">
			<h1>Now, please scan the QR code</h1>
			<p>Open the camera app on your phone, and scan the QR code to complete your purchase. On some Android devices, you may need to tap the Google Lens icon for the camera to recognise the code.</p>
			<p>Follow the link in the QR code to pay using your mobile phone on Stripe.com</p>
			<p class="qrcode"><img src="" id="qrcode" width="60%"></p>
			<p align="center">When you have completed the transaction on your phone, please collect your items. Your order reference is</p>
			<p id="orderref"></p>
			<div class="reset">
				Done
			</div>
		</div>
		
	<script>
	// here's where the magic happens
		$('.product').click( function() {
			var pid = $(this).data('prod') ;
			
			var qty = $('.qty[data-prod="' + pid + '"]').text() ;
			
			
			if ( isNaN(parseInt(qty)) ) { qty = 0 } ; 
			qty++ ;
		
			$('.qty[data-prod="' + pid + '"]').text(qty) ;
			calc_total() ;
		}) ;
		
		$('.prodplus').click( function() {
			var pid = $(this).data('prod') ;
			
			var qty = $('.qty[data-prod="' + pid + '"]').text() ;
			if ( isNaN(parseInt(qty)) ) { qty = 0 } ; 
			qty++ ;
			$('.qty[data-prod="' + pid + '"]').text(qty) ;
			calc_total() ;
		});
		
		$('.prodminus').click( function() {
			var pid = $(this).data('prod') ;
			
			var qty = $('.qty[data-prod="' + pid + '"]').text() ;
			if ( ! isNaN(parseInt(qty)) ) { 
				qty-- ;
			} ; 
			
			if ( qty == 0 ) { qty = ' ' ; }
			$('.qty[data-prod="' + pid + '"]').text(qty) ;
			calc_total() ;
		});
		
	
		$('.reset').click( function() {
			window.location.reload() ;
		}) ;
		
		$('#finish').click( function() {
			var items = new Array() ;
			
			$('.qty').each( function() {
				var pid = $(this).data('prod') ;
				var qty = parseInt($(this).text()) ;
				
				if ( qty > 0 ) {
					items.push( { pid: pid, qty : qty } ) ;
				}
			}) ;
			
			if (items.length > 0 ) {
				// yay - let's make a checkout session
				$.post('tpcheckout.php', { items : items }, function(result) {
					if (result.status == 'ok') {
						var qurl = 'qrcode.php?uri=' + result.url ;
						$('#qrcode').attr('src',qurl) ;
						$('#orderref').text(result.ref) ;
						$('#tpmain').hide() ;
						$('#tpqr').show() ;					
					} else {
						alert('Sorry, there was an error') ;
					}
				}, 'json') ;
			}
		}) ;
		
		function calc_total() {
			var total = 0 ;
			$('.qty').each( function() {
				var pid = $(this).data('prod') ;
				var qty = $(this).text() ;
				
				if ( ! isNaN(parseInt(qty)) ) {
					var unit = $('.product[data-prod="' + pid + '"').data('cost') ;
					var line = unit*qty ;
					total = total + line ;
				}
			}) ;
			
			if ( total > 0 ) {
				$('#total').text('€' + total/100) ;
				$('#empty').show() ;
			} else {
				$('#total').text('') ;
				$('#empty').hide() ;
			}
		}
	</script>	
	</body>
</html>	