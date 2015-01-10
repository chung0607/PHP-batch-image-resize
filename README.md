# PHP-batch-image-resize

## Introduction
Batch resize image. Support nested folders. The file structure is preserved.

## How to use
1. Set the soruce and destination value at the head of `resizer.php`.
2. Run it in command line `php resizer.php`.

## Notes
- I tested on Mac only
- Make sure you installed `imagick`
- Currently only support `JPG` files. Add it yourself around line 95.
