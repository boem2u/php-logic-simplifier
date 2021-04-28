# php-logic-simplifier

php-logic-simplifier 逻辑表达式

From [kjarosz256/python-logic-simplifier](https://github.com/kjarosz256/python-logic-simplifier)


#### install:

`composer require dddxxx/php-logic-simplifier`


#### example:

```
<?php

require_once './vendor/autoload.php';

$str = Logic\Simplifier\SimplificationTable::simplify('~(a|~b|~c|~d) & (x|y)');
print($str).PHP_EOL;

```

output:
```
~a & b & c & d & x | ~a & b & c & d & y
```
