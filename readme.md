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

If you want to revert it as base64:
### qr.php (function):
```php
/* include qr class */
include("classes/qr-image/qr.php");

function qrImage($text, $size = NULL, $quality = NULL){
    $size = 250;
    $quality = "M";
    $qr = new QR($text, $quality);
    return $qr->return_image($size);
}
```

### qr-image/qr.php:
find `return_image` function and replace end:
```php
ob_start();
imagepng($im);
$stringdata = ob_get_contents();
imagedestroy($im);
ob_end_clean();
return base64_encode($stringdata);
```

usage:
```php
echo '<img class="mb-3" src="data:image/png;base64,'.qrImage(gaCode($user['uac']."@".$config['url'])).'" alt="'.$user['uac'].'" style="width:100%;" />';
```
