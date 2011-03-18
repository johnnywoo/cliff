--TEST--
Tokenizer: common cases
--ARGS--
--FILE--
<?php

require_once __DIR__ . '/_functions.inc';

// simple
echo "  empty\n";
draw_tokenizer('');

echo "  one arg\n";
draw_tokenizer('a');

echo "  many args\n";
draw_tokenizer('-a -bc dVV');

// whitespace
echo "  leading space\n";
draw_tokenizer(' leading');

echo "  trailing space\n";
draw_tokenizer('trailing ');

echo "  many spaces\n";
draw_tokenizer(' a lot  of   spaces    ');

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