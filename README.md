# BLUF tiny POS
This is a very small point of sale system designed chiefly to deal with a very specific problem.
It doesn't attempt to do everything - or even very many things - so it's absolutely not for
everyone.

Try it out at [https://dev.bluf.com/tinypos](https://dev.bluf.com/tinypos)

## Why I made a POS
From time to time, BLUF has a stand at community events, and at those we like to sell merchandise
such as badges, tie clips, T shirts and so on, to help fund the club. We usually sell online via
a couple of dedicated web stores, and use Stripe for processing card payments.

Although card readers are available for Stripe in some countries, they're not yet available everywhere,
including Estonia, which is where our EU company is based. I looked into alternative card processors
but I'd have had to go to Estonia to pick up a reader and make a first transaction, which is not
practical right now.

We can use an app called Charge for Stripe, which runs on an Android phone and allows us to take
payments at events by entering card details manually. It can also read a card number using the camera
or using NFC. In the past, we have found that some people are quite nervous when they see us using
the camera.

And while NFC works, it doesn't yet work with Apple or Google Pay, because although a card number is
transmitted to the Android app, the CVV number isn't, and there's no way to view it on the phones.

So, we're now having point of sale material that has a QR code for each product we sell. The QR code
directs people to a Stripe Payment Link for that product. So, if someone just wants one item, they 
can scan the code, show us the payment confirmation screen, and we'll hand it over.

What if they want more than one thing? That's where Payment Links don't fit the bill; it would be
tedious to have to scan and pay for each thing separately.

So, step forward BLUF tiny POS.

## What the BLUF tiny POS does
The POS is a simple grid based display of products from our Stripe account. Tapping or clicking
on an item adds it to the basket. Quantities can be updated, or the whole basket emptied. To pay,
the user taps on the Finish & Pay button, and a Stripe Checkout Session is created, with the
selected products. The POS then displays a QR code with the link to the checkout.

A customer opens their phone camera, points it at the QR code, taps to follow the link and then
completes the purchase on their own device. If they have a Google or Apple wallet set up, they can
pay with just one more click.

After payment, a confirmation email is sent, and the customer sees an order reference. The order
details are also visible in the Stripe dashboard. The customer can show the order reference to a
staff member, who'll hand over the items they've paid for.

The POS screen has been designed with touch devices in mind. It could be used by a staff member, 
who then shows the customer the QR code, or potentially on a device like an iPad in kiosk mode.

## What's not included
There is no stock control element in this. I have made it as simple as possible. In part that's
because it's entirely possible for someone to scan the QR code, and then do nothing with it for
a while. And because with only a handful of items, it seemed like overkill - it would need a
handler for Stripe callbacks, and probably some sort of database. All in all, just a lot of extra
work.

And, in the interests of simplicity and not storing more than is necessary, no information
about individual orders is stored on the server. When a QR code is generated, an email with the
pending order details is sent to the store email address. When the order payment is triggered, a
confirmation email is sent, with just the order reference.

Essentially, as explained above, this is really for a very specific use case. It's not intended to
be a full-fledged POS, but rather a simple way to allow customers to complete a transaction on their
own device, if they're not comfortable about a mobile payment app being used, rather than a traditional
card reader.

There is no support for handling different currencies.

Further development is considered unlikely.

## Requirements
The tiny POS runs on PHP. The original version on the dev server was 7.2. It should run on most other
versions, though the strftime function used in the config file has been deprecated in PHP 8.1

The front end web page uses a CSS grid, so requires a reasonably modern web browser.

You also need:
+ A Stripe account
+ The Stripe PHP client library (version 7.97 was used in development)
+ The PHP QRcode library from http://phpqrcode.sourceforge.net/
+ JQuery (version 1.12.4 was used in development)

## Configuration
I recommend you start with a Stripe test key, and set up products in test mode.

### On Stripe.com
For each product, upload a photo - ideally they should all have the same proportions - and add a name and
a description. The former is what appears in the POS grid display, and the latter will be included in the
notification of the sale sent to the store owner, and shown in the Stripe dashboard.

Create a price for each item; this will usually be a standard non-recurring payment. You need to copy the
price IDs that you want to use in the POS, and add them to the $items array in config.php

### On your web server
Create a folder to hold the files for the project. You should also create a folder elsewhere that can be
written to by the web server, which will hold a cached version of the product information from Stipe. On
our system, this is /var/bluf/private/tinypos

### config.php
This file contains most of the setup for the tiny POS. The $items array should include all the price ids
of all the items in your Stripe account that you want to sell. You can temporarily remove an item from sale
by disabling it in the Stripe dashboard.

The $storeemail parameter is an email address where the store owner will receive notifications of pending and
completed transactions.

$StripeKey is the Stripe secret key. Set this to a test key initially.

$inifile is the full path to a file where data from Stripe is cached. In our test system this is 
/var/bluf/private/tinypos/products.ini. The file is a standard PHP ini file format, and stores the product names,
prices and descriptions retrieved from Stripe.

It is generated automatically if it can't be read, or if the script is called with the ?reload parameter.
So, to remove a product - for example if you sell out - you just disable it in your Stripe dashboard, then
load index.php?reload to rebuild the cache.

The next item in config.php is the path to the init.php file for the Stripe PHP client library. You will 
need to make sure this is in your PHP include path.

### qrcode.php
You may need to alter the include path to the merged phpqrcode.php file downloaded from Sourceforge

### index.php
On line 11, update to point to your preferred location for loading JQuery, whether locally or via a CDN

If using a currency other than Euros, change the symbol used in lines 22 and 133

### pos.css
Adjust to taste

## Testing
Open the folder containing the tiny POS in your web browser. The first load may take a few second, depending
on how many products you have created, as each one needs details retrieving from Stripe.

When the page appears, check that the ini file has been created, and contains the appropriate cached data.

Try adding some products, then tap Finish & Pay. You should receive a pending order email.

A test transaction can be completed by using the QR code to open the checkout page on Stripe, and using one
of the Stripe test numbers or your phone's wallet.

After testing, copy products to Live mode in Stripe, and update the Stripe key in config.php. Force a rebuild
of the cache file by loading index.php?reload, and you're ready to go.

Nigel Whitfield
March 2022.
