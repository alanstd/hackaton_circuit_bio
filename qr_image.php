<?php
http_response_code(410);
header("Content-Type: text/plain; charset=UTF-8");
echo "Cette version n'utilise plus qr_image.php.";
