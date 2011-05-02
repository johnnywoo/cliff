--TEST--
Tokenizer: stopping at an offset
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