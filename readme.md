### PHP QR Code Class
It is an easy-to-access and easy-to-use qr code generator class that makes it easy to create a qr code image with php.
A lightweight, easy-to-use PHP library for generating QR codes. This library supports various QR code versions, error correction levels, and encoding modes.

## Features

- Multiple encoding modes (numeric, alphanumeric, byte, kanji)
- Error correction levels (L, M, Q, H)
- Customizable output formats (PNG image, HTML table, raw data)
- Reed-Solomon error correction
- Automatic version selection
- Mask pattern optimization
- No external dependencies

## Support Me

This software is developed during my free time and I will be glad if somebody will support me.

Everyone's time should be valuable, so please consider donating.

[https://buymeacoffee.com/oxcakmak](https://buymeacoffee.com/oxcakmak)

## Table of Contents
- **[Features](#features)**
- **[Installation](#installation)**
- **[Usage](#usage)**
  - **[Basic Usage](#basic-usage)**
  - **[Advanced Usage](#advanced-usage)**
  - **[Output Formats](#output-formats)**
- **[Parameters](#parameters)**
  - **[Error Correction Levels](#error-correction-levels)**
  - **[Encoding Modes](#encoding-modes)**
- **[Examples](#examples)**
  - **[Generate QR Code for URL](#generate-qr-code-for-url)**
  - **[Generate QR Code for Maps](#generate-qr-code-for-maps)**
  - **[Generate QR Code for Phone Number](#generate-qr-code-for-phone-number)**
  - **[Generate QR Code with High Error Correction](#generate-qr-code-with-high-error-correction)**
- **[Debug Mode](#debug-mode)**
- **[Requirements](#requirements)**
- **[License](#license)**
- **[Contributing](#contributing)**
- **[Support](#support)**

## Installation

1. Download the `PhpQrCode.php` file
2. Include it in your PHP project

```php
require_once('PhpQrCode.php');
```

## Usage

### Basic Usage

```php
// Create a new QR code
$qr = new PhpQrCode("Hello World");

// Output as image
echo '<img src="'.$qr->return_image().'" />';
```

### Advanced Usage

```php
// Create QR code with custom parameters
$qr = new PhpQrCode(
    "https://www.example.com",    // Content
    'H',                         // Error Correction Level (L, M, Q, H)
    'byte'                       // Mode (num, alpha, byte, kanji)
);

// Customize image output
$pixel_size = 8;    // Size of each QR code pixel (1-10)
$padding = 4;       // Padding around QR code (1-5)
echo '<img src="'.$qr->return_image($pixel_size, $padding).'" />';
```

### Output Formats

```php
// 1. As PNG Image
echo '<img src="'.$qr->return_image().'" />';

// 2. As HTML Table
echo '<style>
    table { border-collapse: collapse; }
    td { width: 5px; height: 5px; }
    td.m1 { background: black; }
    td.m0 { background: white; }
</style>';
echo $qr->return_html();

// 3. As Raw Data Array
$data = $qr->return_data();
```

## Parameters

### Error Correction Levels

- L (Low) - 7% of data can be restored
- M (Medium) - 15% of data can be restored
- Q (Quartile) - 25% of data can be restored
- H (High) - 30% of data can be restored

### Encoding Modes

- `num` - Numeric (0-9)
- `alpha` - Alphanumeric (0-9, A-Z, space, $, %, *, +, -, ., /, :)
- `byte` - Byte mode (ISO-8859-1)
- `kanji` - Kanji mode (Shift JIS)

## Examples

### Generate QR Code for URL

```php
$qr = new PhpQrCode(
    "https://www.example.com",
    'M',    // Medium error correction
    'byte'  // Binary encoding
);
echo '<img src="'.$qr->return_image(4, 3).'" />';
```

### Generate QR Code for Phone Number

```php
$qr = new PhpQrCode(
    "tel:+1234567890",
    'L',    // Low error correction
    'num'   // Numeric encoding
);
echo '<img src="'.$qr->return_image().'" />';
```

### Generate QR Code with High Error Correction

```php
$qr = new PhpQrCode(
    "Hello World!",
    'H',      // High error correction
    'alpha'   // Alphanumeric encoding
);
echo '<img src="'.$qr->return_image(6, 4).'" />';
```

## Debug Mode

```php
$qr = new PhpQrCode("Test");
$qr->set_debug_mode(true);  // Enable debug output
```

## Requirements

- PHP 5.3 or higher
- GD Library enabled

## License

This project is licensed under the GNU Public License - see the LICENSE file for details.

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## Support

For issues and feature requests, please create an issue in the GitHub repository.
