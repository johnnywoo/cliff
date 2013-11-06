--TEST--
Tokenizer: escaping
--ARGS--
--FILE--
<?php

/*

DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.

Cliff: a CLI framework for PHP.
Copyright 2011 Aleksandr Galkin.

This file is part of Cliff.

Cliff is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License
as published by the Free Software Foundation, either version 3
of the License, or (at your option) any later version.

Cliff is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with Cliff. If not, see <http://www.gnu.org/licenses/>.

*/

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
