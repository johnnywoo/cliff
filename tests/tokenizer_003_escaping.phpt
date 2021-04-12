--TEST--
Tokenizer: escaping
--ARGS--
--FILE--
<?php

require_once __DIR__ . '/_functions.inc';

echo "  non-quoted\n";
drawTokenizer("a\\a b\\\\b c\\\nc");

echo "  single-quoted\n";
drawTokenizer("'a\\a' 'b\\\\b' 'c\\\nc'");

echo "  double-quoted\n";
drawTokenizer("\"a\\a\" \"b\\\\b\" \"c\\\nc\"");

echo "  double-quoted double quote\n";
drawTokenizer('"a\""');

echo "  space escaping\n";
drawTokenizer('a\\ a "b\\ b" \'c\\ c\'');

echo "  outside quotes\n";
drawTokenizer('a\\\'a b\\"b c\nc');

?>
--EXPECT--
  non-quoted
> |a\a b\\b c\
c|
arg |a\a|  word |aa|
arg |b\\b|  word |b\b|
arg |c\
c|  word |cc|
---
  single-quoted
> |'a\a' 'b\\b' 'c\
c'|
arg |'a\a'|  word |a\a|
arg |'b\\b'|  word |b\\b|
arg |'c\
c'|  word |c\
c|
---
  double-quoted
> |"a\a" "b\\b" "c\
c"|
arg |"a\a"|  word |a\a|
arg |"b\\b"|  word |b\b|
arg |"c\
c"|  word |cc|
---
  double-quoted double quote
> |"a\""|
arg |"a\""|  word |a"|
---
  space escaping
> |a\ a "b\ b" 'c\ c'|
arg |a\ a|  word |a a|
arg |"b\ b"|  word |b\ b|
arg |'c\ c'|  word |c\ c|
---
  outside quotes
> |a\'a b\"b c\nc|
arg |a\'a|  word |a'a|
arg |b\"b|  word |b"b|
arg |c\nc|  word |cnc|
---
