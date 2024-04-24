<?php 
$allowList = [
   'http://192.168.1.9:53726',
   'http://localhost:53726'
];
if(!empty($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowList)) {
   header("Access-Control-Allow-Origin: ".$_SERVER['HTTP_ORIGIN']);
}

header("Access-Control-Allow-Methods: *"); //GET, POST, PUT, PATCH, DELETE, OPTIONS
header("Access-Control-Allow-Headers: *"); //Content-Type, Authorization
