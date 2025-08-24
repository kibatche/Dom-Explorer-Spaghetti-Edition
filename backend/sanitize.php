<?php
include_once 'vendor/autoload.php';
use \Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use \Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use \Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

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
    $blocked_elements = [
        'html',
        'body',

        // form elements
        'form',
        'button',
        'input',
        'select',
        'datalist',
        'option',
        'optgroup',
        'textarea',
    ];
    foreach ($blocked_elements as $blocked_element) {
        $config = $config->blockElement($blocked_element);
    }

    // Drop some elements (tag and contents are removed)
    $dropped_elements = [
        'head',
        'script',

        // header elements used to link external resources
        'link',
        'meta',

        // elements used to embed potential malicious external application
        'applet',
        'canvas',
        'embed',
        'object',
    ];
    foreach ($dropped_elements as $dropped_element) {
        $config = $config->dropElement($dropped_element);
    }

    // Allow class and style attribute
    $config = $config->allowAttribute('class', '*');
    $config = $config->allowAttribute('style', '*');

    $config = $config->allowElement('iframe')->dropAttribute('srcdoc', '*');

    // Keep attributes specific to rich text auto completion
    $rich_text_completion_attributes = [
        // required for proper display of autocompleted tags
        'contenteditable',

        // required for user mentions and form tags
        'data-user-mention',
        'data-user-id',
        'data-form-tag',
        'data-form-tag-value',
        'data-form-tag-provider',
    ];
    foreach ($rich_text_completion_attributes as $attribute) {
        $config = $config->allowAttribute($attribute, 'span');
    }
    $sanitizer = new HtmlSanitizer($config);
    $sanitizedHtml =  $sanitizer->sanitize($html);
    echo json_encode(["html" => $sanitizedHtml]);
}