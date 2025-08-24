<?php
require_once 'vendor/autoload.php';
require_once 'Richtext.php';

$richText = new RichText();



echo $richText->getSafeHtml('<!--&gt;<img src=x onerror=alert()&gt;>');