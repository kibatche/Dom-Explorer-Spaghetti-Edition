<?php
include_once 'vendor/autoload.php';
use \Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use \Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

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
//glpi config
    $config = (new HtmlSanitizerConfig())
    ->allowSafeElements()
    ->allowLinkSchemes([
        'aim',
        'app',
        'feed',
        'file',
        'ftp',
        'gopher',
        'http',
        'https',
        'irc',
        'mailto',
        'news',
        'nntp',
        'sftp',
        'ssh',
        'tel',
        'telnet',
        'notes',
    ])
    ->allowRelativeLinks()
    ->allowRelativeMedias()
    ->withMaxInputLength(-1)
;
    // Block some elements (tag is removed but contents is preserved)
    // DOMPurify style
    $blocked_elements = [
    'html',
    'body',
    'head',
    'meta',
    'link',
    'base',
    'script',
    'noscript',
    'frame',
    'frameset',
    'object',
    'embed',
    'applet',
    'param',
    'layer',
    'ilayer',
    'animate',
    'animatemotion',
    'animatetransform',
    'set',
    'foreignobject',
    ];
    foreach ($blocked_elements as $blocked_element) {
        $config = $config->blockElement($blocked_element);
    }

    // Allow class and style attribute
    $config = $config->allowAttribute('class', '*');
    $config = $config->allowElement('iframe')->dropAttribute('srcdoc', '*');
    $config = $config->allowAttribute('shadowrootmode', 'template');
    $config = $config->allowElement('form');
    $config = $config->allowElement('input');
    $config = $config->allowElement('select');
    $config = $config->allowElement('textarea');
    $config = $config->allowElement('math');
    $config = $config->allowElement('svg');
    $config = $config->allowElement('mi');
    $config = $config->allowElement('mo');
    $config = $config->allowElement('mn');
    $config = $config->allowElement('ms');
    $config = $config->allowElement('mtext');
    $config = $config->allowElement('desc');
    $config = $config->allowElement('title');
    // $config = $config->withMaxInputLength(52);
    $sanitizer = new HtmlSanitizer($config);
    $sanitizedHtml =  $sanitizer->sanitize($html);
    echo json_encode(["html" => $sanitizedHtml]);
}