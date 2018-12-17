# php-logic-simplifier
php-logic-simplifier 逻辑表达式

source [kjarosz256/python-logic-simplifier](https://github.com/kjarosz256/python-logic-simplifier)

```
<?php

require_once './vendor/autoload.php';

$str = Logic\Simplifier\SimplificationTable::simplify('~(中国|~美国|~日本|~韩国) & (手机|电脑)');
print($str).PHP_EOL;

```

output:
```
~中国 & 手机 & 日本 & 美国 & 韩国 | ~中国 & 日本 & 电脑 & 美国 & 韩国
```
