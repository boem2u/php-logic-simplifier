<?php

require_once '../vendor/autoload.php';

$str = Logic\Simplifier\SimplificationTable::simplify('~(a|~b|~c|~d) & (x|y)');
print($str).PHP_EOL;


$str = Logic\Simplifier\SimplificationTable::simplify('a|(a&b)|c');
print($str).PHP_EOL;