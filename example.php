<?php
/* include qr class */
include("qr.php");
/* Enter qr text (username, number, link etc.)*/
$qrText = "test";
/* Enter size (in pixel - 250 = 250x250, 300 = 300x300 etc.) */
$qrSize = "250";
/* QR Code Quality [L, M, Q, H] - recommended: M */
$qrQuality = "M";

$qr = new QR($qrText, "M");
$qr->return_image($qrSize);
echo $qr;
?>
