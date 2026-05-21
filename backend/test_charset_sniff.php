<?php
/**
 * Test charset sniffing via <meta charset> dans sanitizeFor('head', ...)
 *
 * NativeParser wraps : <!DOCTYPE html><head>{input}</head>
 * => <meta charset> en head = déclaration légitime, sniffée par Lexbor.
 * Dom\HTMLDocument::createFromString() sans encoding => charset sniff actif.
 *
 * Note : les éléments non valides en head (<img>, etc.) sont déplacés en body
 * par le parseur => silencieusement perdus par sanitizeFor('head', ...) qui ne
 * lit que le head element. Les tests utilisent donc <title> et text nodes,
 * valides en head.
 */

include_once 'vendor/autoload.php';

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

// [PAI] BEGIN — configuration sanitizer (GLPI config)
$config = (new HtmlSanitizerConfig())
    ->allowSafeElements()
    ->allowLinkSchemes(['http', 'https', 'mailto'])
    ->withMaxInputLength(-1);

$blocked = ['html', 'body', 'head', 'meta', 'link', 'base', 'script', 'noscript',
            'frame', 'frameset', 'object', 'embed', 'applet', 'param',
            'animate', 'animatetransform', 'set', 'foreignobject'];
foreach ($blocked as $el) {
    $config = $config->blockElement($el);
}
$config = $config->allowAttribute('class', '*');

$sanitizer = new HtmlSanitizer($config);
// [PAI] END

// [PAI] BEGIN — helpers

function lexborParse(string $html, string $context = 'head'): array {
    $doc = @\Dom\HTMLDocument::createFromString(
        sprintf('<!DOCTYPE html><%s>%s</%1$s>', $context, $html)
    );
    $headEl = $doc->getElementsByTagName('head')->item(0);
    $bodyEl = $doc->getElementsByTagName('body')->item(0);
    return [
        'charset' => $doc->characterSet,
        'head'    => $headEl?->innerHTML ?? '',
        'body'    => $bodyEl?->innerHTML ?? '',
    ];
}

function reparse(string $sanitized): string {
    $doc = @\Dom\HTMLDocument::createFromString(
        '<!DOCTYPE html><body>' . $sanitized . '</body>',
        0,
        'UTF-8'
    );
    return $doc->getElementsByTagName('body')->item(0)?->innerHTML ?? '';
}

function section(string $title): void {
    echo "\n" . str_repeat('═', 70) . "\n  $title\n" . str_repeat('═', 70) . "\n";
}

function show(string $label, string $value): void {
    echo sprintf("  %-22s %s\n", $label . ':', $value ?: '(vide)');
}
// [PAI] END

// ─── CAS 1 : head context — <title> avec ESC $@ ──────────────────────────────
// En mode JIS, les bytes de "</title>" peuvent être consommés comme paires JIS
// => le titre ne se ferme jamais => script après titre capturé comme text node
section('CAS 1 — head context : <title> + ESC $@ (JIS swallow </title> ?)');

// \x1b$@ = switch JIS ; </title> = 2F+74+69+74+6C+65 => paires JIS : /t, it, le
// \x1b(B = retour ASCII
$input1 = '<meta charset="iso-2022-jp"><title>' . "\x1b\x24\x40" . 'test</title>' . "\x1b\x28\x42" . '<script>alert(1)</script>';
show('input', $input1);

$l1 = lexborParse($input1);
show('charset', $l1['charset']);
show('head innerHTML', $l1['head']);
show('body innerHTML', $l1['body']);
show('sanitizeFor(head)', $sanitizer->sanitizeFor('head', $input1));

// ─── CAS 2 : head context — text node brut avec ESC $@ ──────────────────────
// Text node direct en head avec séquences ESC
section('CAS 2 — head context : text node ESC $@ <> paires JIS');

$input2 = '<meta charset="iso-2022-jp">' . "\x1b\x24\x40" . '<><>' . "\x1b\x28\x42" . 'texte' . "\x1b\x24\x40" . '<><>';
show('input (hex)', bin2hex($input2));

$l2 = lexborParse($input2);
show('charset', $l2['charset']);
show('head innerHTML', $l2['head']);
show('body innerHTML', $l2['body']);
show('sanitizeFor(head)', $sanitizer->sanitizeFor('head', $input2));

// ─── CAS 3 : head context — GB18030 FE 5C A6 DB dans <title> ───────────────
section('CAS 3 — GB18030 : FE 5C A6 DB dans <title> en head');

$input3 = '<meta charset="gb18030"><title>' . "\xfe\x5c\xa6\xdb" . '</title>';
show('input (hex)', bin2hex($input3));

$l3 = lexborParse($input3);
show('charset', $l3['charset']);
show('head innerHTML', $l3['head']);
show('sanitizeFor(head)', $sanitizer->sanitizeFor('head', $input3));
show('re-parse', reparse($sanitizer->sanitizeFor('head', $input3)));

// ─── CAS 4 : head context — JIS Roman ESC (J : 0x5C = ¥ dans <title> ────────
section('CAS 4 — ISO-2022-JP JIS Roman : 0x5C = ¥ dans <title>');

// \x1b(J = JIS Roman ; 0x5C = ¥ (au lieu de \)
$input4 = '<meta charset="iso-2022-jp"><title>' . "\x1b\x28\x4a\x5c\x27" . '</title>';
show('input (hex)', bin2hex($input4));

$l4 = lexborParse($input4);
show('charset', $l4['charset']);
show('head innerHTML', $l4['head']);
show('sanitizeFor(head)', $sanitizer->sanitizeFor('head', $input4));

// ─── CAS 5 : body context (comparaison) — structural mutation img ────────────
// Utilisé comme référence : montre que les <img> vont en body hors head context
section('CAS 5 — body context (ref) : mutation structurelle img + ESC $@');

$input5 = '<meta charset="iso-2022-jp"><img src="src' . "\x1b\x24\x40" . '">text' . "\x1b\x28\x42" . '<img src="javascript:alert()//">';
show('input', $input5);

$l5 = lexborParse($input5, 'body');
show('charset', $l5['charset']);
show('body innerHTML', $l5['body']);
show('sanitizeFor(body)', $sanitizer->sanitizeFor('body', $input5));
show('re-parse', reparse($sanitizer->sanitizeFor('body', $input5)));

// [PAI] BEGIN — CAS 6 + 7 : onerror=alert(origin) structural mutation
// [PAI] BEGIN — CAS 8+ : <style> autorisé en head

// ─── CAS 6 : body context — onerror au lieu de javascript: ──────────────────
// Question : si onerror=alert(origin) se retrouve DANS la valeur de src
// (vu comme texte par Lexbor), Symphony le détecte-t-il comme event handler ?
section('CAS 6 — body context : onerror=alert(origin) dans src via ESC $@');

$input6 = '<meta charset="iso-2022-jp"><img src="src' . "\x1b\x24\x40" . '">text' . "\x1b\x28\x42" . '<img src="onerror=alert(origin)//">';
show('input', $input6);

$l6 = lexborParse($input6, 'body');
show('charset', $l6['charset']);
show('body innerHTML', $l6['body']);
show('sanitizeFor(body)', $sanitizer->sanitizeFor('body', $input6));
show('re-parse', reparse($sanitizer->sanitizeFor('body', $input6)));

// ─── CAS 7 : head context — onerror dans un élément autorisé en head ─────────
// <title> est valide en head. On tente de swallow la fermeture </title>
// via ESC $@ et d'injecter onerror dans un second élément.
// Variante : src sans " interne pour garder onerror dans la valeur d'attribut.
section('CAS 7 — head context : onerror dans <title> via ESC $@ swallow');

// Ici on tente : <title src="x\x1b$@">onerror=alert(origin)\x1b(B</title>
// Si ESC $@ swallow "> alors le contenu "onerror=..." est dans le title
$input7 = '<meta charset="iso-2022-jp"><title>' . "\x1b\x24\x40" . 'test' . "\x1b\x28\x42" . 'onerror=alert(origin)</title>';
show('input', $input7);

$l7 = lexborParse($input7, 'head');
show('charset', $l7['charset']);
show('head innerHTML', $l7['head']);
show('body innerHTML', $l7['body']);
show('sanitizeFor(head)', $sanitizer->sanitizeFor('head', $input7));
show('re-parse', reparse($sanitizer->sanitizeFor('head', $input7)));

// Variante : element inconnu autorisé en head (template, ou custom)
// On teste si un élément template peut porter onerror en head context
section('CAS 7b — head context : <template> avec onerror via ESC $@');

$config_template = (new HtmlSanitizerConfig())
    ->allowSafeElements()
    ->withMaxInputLength(-1)
    ->allowElement('template')
    ->allowAttribute('onerror', 'template');
$san_template = new HtmlSanitizer($config_template);

$input7b = '<meta charset="iso-2022-jp"><template src="x' . "\x1b\x24\x40" . '">text' . "\x1b\x28\x42" . '<template onerror="alert(origin)">';
show('input', $input7b);

$l7b = lexborParse($input7b, 'head');
show('charset', $l7b['charset']);
show('head innerHTML', $l7b['head']);
show('body innerHTML', $l7b['body']);
show('sanitizeFor(head)', $san_template->sanitizeFor('head', $input7b));
// [PAI] END

// ─── CAS 8 : head + <style> autorisé — ESC $@ swallow </style> ──────────────
// </style> bytes : 2F 73 74 79 6C 65
// En JIS mode : /s (2F73), ty (7479), le (6C65) = 3 paires JIS valides
// => </style> swallowé => contenu après capturé dans le style
section('CAS 8 — head : <style> + ESC $@ swallow </style>');

$config_style = (new HtmlSanitizerConfig())
    ->allowSafeElements()
    ->withMaxInputLength(-1)
    ->allowElement('style');
foreach (['html','body','head','meta','link','base','script','noscript','frame','frameset',
          'object','embed','applet','param','animate','animatetransform','set','foreignobject'] as $el) {
    $config_style = $config_style->blockElement($el);
}
$san_style = new HtmlSanitizer($config_style);

// \x1b$@ = switch JIS => </style> consommé comme JIS chars => pas de fermeture
// \x1b(B = retour ASCII => <script>alert(1)</script> parsé comme texte dans style
$input8 = '<meta charset="iso-2022-jp"><style>' . "\x1b\x24\x40" . 'body{color:red}</style>' . "\x1b\x28\x42" . '<script>alert(1)</script>';
show('input', $input8);

$l8 = lexborParse($input8, 'head');
show('charset', $l8['charset']);
show('head innerHTML', $l8['head']);
show('body innerHTML', $l8['body']);
show('sanitizeFor(head)', $san_style->sanitizeFor('head', $input8));
show('re-parse', reparse($san_style->sanitizeFor('head', $input8)));

// ─── CAS 9 : <style> en head — injection via </style> swallowé, payload CSS ──
// Si le style ne se ferme pas, le contenu CSS + markup qui suit est capturé
// Test avec un payload qui serait dangereux si sorti du style
section('CAS 9 — head : <style> contenu capturé après swallow, re-parse');

$input9 = '<meta charset="iso-2022-jp"><style>' . "\x1b\x24\x40" . 'a{color:red}</style>' . "\x1b\x28\x42" . '<img src=x onerror=alert(1)></style>';
show('input', $input9);

$l9 = lexborParse($input9, 'head');
show('charset', $l9['charset']);
show('head innerHTML', $l9['head']);
show('body innerHTML', $l9['body']);
$san9 = $san_style->sanitizeFor('head', $input9);
show('sanitizeFor(head)', $san9);
show('re-parse', reparse($san9));

// ─── CAS 10 : <style> SVG en body — entités décodées dans style SVG ──────────
// En SVG, <style> est un élément générique => entités décodées dans le contenu
// &lt;script&gt; dans <svg><style> => <script> dans text content
section('CAS 10 — body : <style> SVG, entités décodées dans contenu');

$config_svg_style = (new HtmlSanitizerConfig())
    ->allowSafeElements()
    ->withMaxInputLength(-1)
    ->allowElement('svg')
    ->allowElement('style');
$san_svg = new HtmlSanitizer($config_svg_style);

$input10 = '<svg><style>&lt;script&gt;alert(1)&lt;/script&gt;</style></svg>';
show('input', $input10);

$l10 = lexborParse($input10, 'body');
show('body innerHTML', $l10['body']);
$san10 = $san_svg->sanitizeFor('body', $input10);
show('sanitizeFor(body)', $san10);
show('re-parse', reparse($san10));
// [PAI] END

echo "\n" . str_repeat('─', 70) . "\n  PHP " . PHP_VERSION . "\n" . str_repeat('─', 70) . "\n\n";
