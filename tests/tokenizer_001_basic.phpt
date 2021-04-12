--TEST--
Tokenizer: common cases
--ARGS--
--FILE--
<?php

require_once __DIR__ . '/_functions.inc';

// simple
echo "  empty\n";
drawTokenizer('');

echo "  one arg\n";
drawTokenizer('a');

echo "  many args\n";
drawTokenizer('-a -bc dVV');

// whitespace
echo "  leading space\n";
drawTokenizer(' leading');

echo "  trailing space\n";
drawTokenizer('trailing ');

echo "  many spaces\n";
drawTokenizer(' a lot  of   spaces    ');

?>
--EXPECT--
  empty
> ||
---
  one arg
> |a|
arg |a|  word |a|
---
  many args
> |-a -bc dVV|
arg |-a|  word |-a|
arg |-bc|  word |-bc|
arg |dVV|  word |dVV|
---
  leading space
> | leading|
arg |leading|  word |leading|
---
  trailing space
> |trailing |
arg |trailing|  word |trailing|
arg ||  word ||
---
  many spaces
> | a lot  of   spaces    |
arg |a|  word |a|
arg |lot|  word |lot|
arg |of|  word |of|
arg |spaces|  word |spaces|
arg ||  word ||
---
