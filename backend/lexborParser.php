<?php
require_once('DomNodeSerializableClass.php');
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

error_reporting(0);

$method = $_SERVER['REQUEST_METHOD'];
$ipAddress = $_SERVER['REMOTE_ADDR'];
$port      = $_SERVER['REMOTE_PORT'];
if ($method != "POST")
{
    header("Allow: POST");
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode("{}");
}
else
{
    header("Content-Type: application/json");
    $json = json_decode(file_get_contents('php://input'));
    $lexborVersion = "2.7.0";
    $phpDomVersion = "php-8.4.18-dom";
    $html = $json->html;
    $version = $json->lexborVersion;
    // [PAI] (IA) — versions standalone disponibles via binaire compilé (plug)
    $standaloneBinaries = [
        '2.7.0' => './bin/lexbor_tree-2.7.0',
        '3.0.0' => './bin/lexbor_tree-3.0.0',
    ];
    if (isset($standaloneBinaries[$version]))
    {
        $path = $standaloneBinaries[$version];
        $proc = proc_open($path, [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w'],  // stderr
        ], $pipes);

        fwrite($pipes[0], $html);
        fclose($pipes[0]);

        $json = stream_get_contents($pipes[1]);
        proc_close($proc);
        $jsonENcode = json_decode($json);
        echo $json;
    }
    else
    {
        // [PAI] BEGIN — parseMode: createFromString (default) | innerHTML (fragment mode)
        $parseMode = isset($json->parseMode) ? $json->parseMode : 'createFromString';
        $contextTag = isset($json->contextTag) ? $json->contextTag : 'body';

        if ($parseMode === 'innerHTML') {
            if ($contextTag === 'body') {
                $baseDoc = Dom\HTMLDocument::createFromString('<!DOCTYPE html><html><body></body></html>');
            } else {
                $baseDoc = Dom\HTMLDocument::createFromString("<!DOCTYPE html><html><body><" . $contextTag . "></" . $contextTag . "></body></html>");
            }
            $ctx = $baseDoc->getElementsByTagName($contextTag)->item(0);
            if ($ctx !== null) {
                $ctx->innerHTML = $html;
            }
            $ParsedDoc = new LexborHtmlDocument($baseDoc);
            echo json_encode($ParsedDoc, 0, 4096);
        } else {
            $DocPHPDomLexbor = Dom\HTMLDocument::createFromString($html);
            $ParsedDoc = new LexborHtmlDocument($DocPHPDomLexbor);
            echo json_encode($ParsedDoc, 0, 4096);
        }
        // [PAI] END
    }
}
