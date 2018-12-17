<?php

require_once '../vendor/autoload.php';

$str = Logic\Simplifier\SimplificationTable::simplify('~(中国|~美国|~日本|~韩国) & (手机|电脑)');
print($str).PHP_EOL;