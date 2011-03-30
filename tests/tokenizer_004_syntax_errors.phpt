--TEST--
Tokenizer: syntax errors
--ARGS--
--FILE--
<?php

require_once __DIR__ . '/_functions.inc';

echo "  unclosed double quote\n";
draw_tokenizer('a "q');

echo "  unclosed single quote\n";
draw_tokenizer('a \'b c');

echo "  unclosed escaping\n";
draw_tokenizer('a\\');

?>
--EXPECT--
  unclosed double quote
> |a "q|
arg |a|  word |a|
arg |"q|  word |q|
---
  unclosed single quote
> |a 'b c|
arg |a|  word |a|
arg |'b c|  word |b c|
---
  unclosed escaping
> |a\|
arg |a\|  word |a|
---