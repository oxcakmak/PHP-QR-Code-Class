### PHP QR Code Class
It is an easy-to-access and easy-to-use qr code generator class that makes it easy to create a qr code image with php.

### Usage:
```php
/* include qr class */
include("qr.php");
/* Enter qr text (username, number, link etc.)*/
$qrText = "test";
/* Enter size (in pixel - 250 = 250x250, 300 = 300x300 etc.) */
$qrSize = "250";
/* QR Code Quality [L, M, Q, H] - recommended: M */
$qrQuality = "M";

$qr = new QR($qrText, $qrQuality);
$qr->return_image($qrSize);
echo $qr;
```
### Suggestion:
Please use the output as an image, as the output will be a file. For example:
```
RewriteRule ^qr.png$ qr-generator.php [QSA, L]
```

but you may not need it optionally, my advice is to use it by creating a function in a file.
