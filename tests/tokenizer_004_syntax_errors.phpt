--TEST--
Tokenizer: syntax errors
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
