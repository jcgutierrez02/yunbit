<?php

$json_pretty = json_encode($_REQUEST, JSON_PRETTY_PRINT);
echo "<pre>".$json_pretty."<pre/>";

if (isset($_FILES)) {
    $json_pretty = json_encode($_FILES, JSON_PRETTY_PRINT);
    echo "<pre>".$json_pretty."<pre/>";
    
}