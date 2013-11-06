--TEST--
Tokenizer: quotes
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

echo "  enclosing\n";
drawTokenizer('"double" \'single\'');

echo "  inline\n";
drawTokenizer('--opt="double" --opt=\'single\'');

echo "  nested in double\n";
drawTokenizer('--opt="\'a\'"');

echo "  nested in single\n";
drawTokenizer('--opt=\'"a"\'');

echo "  empty strings\n";
drawTokenizer('"" \'\'');

echo "  quotes and spaces\n";
drawTokenizer('a b   " c c "   \' d d \'   " "   \' \'');

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
