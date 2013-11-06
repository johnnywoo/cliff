--TEST--
Tokenizer: common cases
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

// simple
echo "  empty\n";
drawTokenizer('');

echo "  one arg\n";
drawTokenizer('a');

echo "  many args\n";
drawTokenizer('-a -bc dVV');

// whitespace
echo "  leading space\n";
drawTokenizer(' leading');

echo "  trailing space\n";
drawTokenizer('trailing ');

echo "  many spaces\n";
drawTokenizer(' a lot  of   spaces    ');

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
