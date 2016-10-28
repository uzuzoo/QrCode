QR Code
=======

*By [endroid](http://endroid.nl/)*
*Update [uzuzoo](https://github.com/uzuzoo)*

This library based on QRcode Perl CGI & PHP scripts by Y. Swetake helps you generate images containing a QR code.

## Installation

Use [Composer](https://getcomposer.org/) to install the library.

``` bash
$ composer require uzuzoo/qrcode
```

## Usage

```php
<?php

use Uzuzoo\QrCode\QrCode;

$qrCode = new QrCode();
$qrCode
    ->setText('Life is too short to be generating QR codes')
    ->setSize(300)
    ->setPadding(10)
    ->setErrorCorrection('high')
    ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
    ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
    ->setLabel('Scan the code')
    ->setLabelFontSize(16)
    ->setImageType(QrCode::IMAGE_TYPE_PNG)
;

// now we can directly output the qrcode
header('Content-Type: '.$qrCode->getContentType());
$qrCode->render();

// or create a response object
$response = new Response($qrCode->get(), 200, array('Content-Type' => $qrCode->getContentType()));

```

## License

This bundle is under the MIT license. For the full copyright and license
information please view the LICENSE file that was distributed with this source code.
