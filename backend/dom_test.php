<?php

use Dom\HTMLDocument;

$r = Dom\HTMLDocument::createFromString('<math><mi><svg></p>');
$desc = $r->getElementsByTagName('p')->item(0);
var_dump($desc->parentNode);