<?php

/**
 * html5lib tree-construction tests — PHP DOM (Dom\HTMLDocument, PHP 8.4)
 * 
 * Shitty program made by claude, fixed (mostly) by me.
 * 
 * Usage:
 *   php html5lib_tests.php path/to/html5lib-tests/tree-construction
 *   php html5lib_tests.php path/to/html5lib-tests/tree-construction -v
 *
 * Clone the test suite:
 *   git clone https://github.com/html5lib/html5lib-tests.git
 *
 * NOTE: Dom\HTMLDocument on this PHP version does not expose DOCTYPE or Comment
 * nodes — they surface as Text nodes or are dropped. Tests involving those node
 * types will fail by design. This is a known PHP DOM limitation, not a test bug.
 * Template content (.content DocumentFragment) is also absent from Dom\HTMLDocument.
 */

// ---------------------------------------------------------------------------
// .dat parser
// ---------------------------------------------------------------------------

function parse_dat(string $raw): array
{
    $tests = [];
    foreach (preg_split('/\n\n/', trim($raw)) as $block) {
        $block = rtrim($block);
        if ($block === '') continue;

        $t = ['data' => '', 'errors' => [], 'fragment' => null, 'script' => null, 'document' => ''];
        $lines = explode("\n", $block);
        $i = 0; $n = count($lines);

        while ($i < $n) {
            $l = $lines[$i];
            if ($l === '#data') {
                $i++; $acc = [];
                while ($i < $n && $lines[$i][0] !== '#') $acc[] = $lines[$i++];
                $t['data'] = implode("\n", $acc);
            } elseif ($l === '#errors' || $l === '#new-errors') {
                $i++;
                while ($i < $n && $lines[$i] !== '' && $lines[$i][0] !== '#') $t['errors'][] = $lines[$i++];
            } elseif ($l === '#document-fragment') {
                $t['fragment'] = trim($lines[++$i]); $i++;
            } elseif ($l === '#script-off' || $l === '#script-on') {
                $t['script'] = $l; $i++;
            } elseif ($l === '#document') {
                $i++; $acc = [];
                while ($i < $n && $lines[$i][0] !== '#') $acc[] = $lines[$i++];
                $t['document'] = implode("\n", $acc);
            } else {
                $i++;
            }
        }
        if ($t['data'] !== '') $tests[] = $t;
    }
    return $tests;
}

// ---------------------------------------------------------------------------
// Serializer — html5lib #document format
// ---------------------------------------------------------------------------

function serialize(\Dom\Node $node, int $depth = 0): string
{
    $pad = '| ' . str_repeat('  ', $depth);
    $out = '';

    // [PAI] BEGIN — instanceof sur les classes concrètes Dom\ au lieu de nodeType int
    if ($node instanceof \Dom\Element) {
        $ns = match ($node->namespaceURI) {
            'http://www.w3.org/2000/svg'         => 'svg ',
            'http://www.w3.org/1998/Math/MathML' => 'math ',
            default => '',
        };
        $out .= "{$pad}<{$ns}{$node->localName}>\n";

        $attrs = [];
        foreach ($node->attributes as $a) {
            $ans = match ($a->namespaceURI) {
                'http://www.w3.org/2000/svg'         => 'svg ',
                'http://www.w3.org/1998/Math/MathML' => 'math ',
                default => '',
            };
            $attrs[$ans . $a->localName] = $a->value ?? '';
        }
        ksort($attrs);
        foreach ($attrs as $k => $v) $out .= "{$pad}  {$k}=\"{$v}\"\n";

        foreach ($node->childNodes as $c) $out .= serialize($c, $depth + 1);

    } elseif ($node instanceof \Dom\Text) {
        $out .= "{$pad}\"{$node->data}\"\n";

    } elseif ($node instanceof \Dom\Comment) {
        $out .= "{$pad}<!-- {$node->data} -->\n";

    } elseif ($node instanceof \Dom\DocumentType) {
        $pub = $node->publicId ?? '';
        $sys = $node->systemId ?? '';
        $out .= $pub !== '' || $sys !== ''
            ? "{$pad}<!DOCTYPE {$node->name} \"{$pub}\" \"{$sys}\">\n"
            : "{$pad}<!DOCTYPE {$node->name}>\n";
    }
    // [PAI] END

    return $out;
}

// ---------------------------------------------------------------------------
// Runner
// ---------------------------------------------------------------------------

function run_file(string $path, bool $verbose): array
{
    $tests = parse_dat(file_get_contents($path));
    $p = $f = $s = 0;

    foreach ($tests as $i => $t) {
        if ($t['script'] === '#script-on') { $s++; continue; }

        $html = $t['data'];
        $expected = rtrim($t['document']);

        try {
            if ($t['fragment'] !== null) {
                // Fragment parsing with context element
                $parts = explode(' ', $t['fragment'], 2);
                [$ns_hint, $local] = count($parts) === 2 ? $parts : ['html', $parts[0]];
                $ns = match ($ns_hint) {
                    'svg'  => 'http://www.w3.org/2000/svg',
                    'math' => 'http://www.w3.org/1998/Math/MathML',
                    default => 'http://www.w3.org/1999/xhtml',
                };
                $doc = Dom\HTMLDocument::createEmpty();
                $ctx = $doc->createElementNS($ns, $local);
                $frag = $doc->parseFragment($html, $ctx);
                $actual = '';
                foreach ($frag->childNodes as $c) $actual .= serialize($c, 0);
                $actual = rtrim($actual);
            } else {
                $doc = Dom\HTMLDocument::createFromString($html, LIBXML_NOERROR);
                $actual = '';
                foreach ($doc->childNodes as $c) $actual .= serialize($c, 0);
                $actual = rtrim($actual);
            }
        } catch (\Throwable $e) {
            $actual = 'EXCEPTION: ' . $e->getMessage();
        }

        if ($actual === $expected) {
            $p++;
            if ($verbose) echo '  pass #' . ($i + 1) . "\n";
        } else {
            $f++;
            $label = str_replace(__DIR__ . '/', '', $path) . ' #' . ($i + 1);
            echo "\nFAIL [{$label}] html=" . str_replace("\n", '\n', mb_strimwidth($html, 0, 80, '…')) . "\n";
            echo "  expected:\n";
            foreach (explode("\n", $expected) as $l) echo "    $l\n";
            echo "  got:\n";
            foreach (explode("\n", $actual) as $l) echo "    $l\n";
        }
    }

    return [$p, $f, $s];
}

// ---------------------------------------------------------------------------
// Main
// ---------------------------------------------------------------------------

$dir     = $argv[1] ?? __DIR__ . '/html5lib-tests/tree-construction';
$verbose = in_array('-v', $argv);

if (!is_dir($dir)) {
    echo "Not found: {$dir}\n  git clone https://github.com/html5lib/html5lib-tests.git\n";
    exit(1);
}

$files = [
    'adoption01.dat',
    'adoption02.dat',
    'foreign-fragment.dat',
    'inbody.dat',
    'tables01.dat',
    'tests1.dat',
    'tests2.dat',
    'tests3.dat',
];

$tp = $tf = $ts = 0;
echo "PHP " . PHP_VERSION . " — Dom\\HTMLDocument\n" . str_repeat('-', 60) . "\n";

foreach ($files as $f) {
    $path = $dir . '/' . $f;
    if (!file_exists($path)) { echo "skip (not found): $f\n"; continue; }
    echo "\n[$f]\n";
    [$p, $fa, $s] = run_file($path, $verbose);
    echo "  $p passed, $fa failed, $s skipped\n";
    $tp += $p; $tf += $fa; $ts += $s;
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "TOTAL: $tp passed, $tf failed, $ts skipped\n";
exit($tf > 0 ? 1 : 0);
