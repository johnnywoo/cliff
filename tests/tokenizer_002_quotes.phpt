--TEST--
Tokenizer: quotes
--ARGS--
--FILE--
<?php

require_once __DIR__ . '/_functions.inc';

echo "  enclosing\n";
draw_tokenizer('"double" \'single\'');

echo "  inline\n";
draw_tokenizer('--opt="double" --opt=\'single\'');

echo "  nested in double\n";
draw_tokenizer('--opt="\'a\'"');

echo "  nested in single\n";
draw_tokenizer('--opt=\'"a"\'');

echo "  empty strings\n";
draw_tokenizer('"" \'\'');

echo "  quotes and spaces\n";
draw_tokenizer('a b   " c c "   \' d d \'   " "   \' \'');

?>
--EXPECT--
  enclosing
> |"double" 'single'|
arg |"double"|  word |double|
arg |'single'|  word |single|
---
  inline
> |--opt="double" --opt='single'|
arg |--opt="double"|  word |--opt=double|
arg |--opt='single'|  word |--opt=single|
---
  nested in double
> |--opt="'a'"|
arg |--opt="'a'"|  word |--opt='a'|
---
  nested in single
> |--opt='"a"'|
arg |--opt='"a"'|  word |--opt="a"|
---
  empty strings
> |"" ''|
arg |""|  word ||
arg |''|  word ||
---
  quotes and spaces
> |a b   " c c "   ' d d '   " "   ' '|
arg |a|  word |a|
arg |b|  word |b|
arg |" c c "|  word | c c |
arg |' d d '|  word | d d |
arg |" "|  word | |
arg |' '|  word | |
---