--TEST--
Tokenizer: stopping at an offset
--ARGS--
--FILE--
<?php

require_once __DIR__ . '/_functions.inc';

echo "  basic\n";
draw_tokenizer('a b c', 1);

echo "  escaping\n";
draw_tokenizer('a\ b', 2);

echo "  past escaping\n";
draw_tokenizer('a\ b', 3);

?>
--EXPECT--
  basic
> |a b c|
arg |a|  word |a|
---
  escaping
> |a\ b|
arg |a\|  word |a\|
---
  past escaping
> |a\ b|
arg |a\ |  word |a |
---