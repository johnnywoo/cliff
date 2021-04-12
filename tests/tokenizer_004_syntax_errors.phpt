--TEST--
Tokenizer: syntax errors
--ARGS--
--FILE--
<?php

require_once __DIR__ . '/_functions.inc';

echo "  unclosed double quote\n";
drawTokenizer('a "q');

echo "  unclosed single quote\n";
drawTokenizer('a \'b c');

echo "  unclosed escaping\n";
drawTokenizer('a\\');

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
