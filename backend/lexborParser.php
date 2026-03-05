<?php
require_once('DomNodeSerializableClass.php');
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

$method = $_SERVER['REQUEST_METHOD'];
$ipAddress = $_SERVER['REMOTE_ADDR'];
$port      = $_SERVER['REMOTE_PORT'];
if ($method != "POST")
{
    header("Allow: POST");
    header("HTTP/1.0 405 Method Not Allowed");;
}
else
{
    header("Content-Type: application/json");
    $json = json_decode(file_get_contents('php://input'));
    $html = $json->html;
    $DocPHPDomLexbor = Dom\HTMLDocument::createFromString($html);
    echo json_encode(new LexborHtmlDocument($DocPHPDomLexbor));
}
