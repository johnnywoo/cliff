--TEST--
Parser: common cases
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

echo "  empty\n";
draw_parser('');

echo "  one param\n";
draw_parser('a');

echo "  short options\n";
draw_parser('-a -bc -dVV -d VV blah', 'd');

echo "  long options\n";
draw_parser('--a --a-a-a --x=1 --x1 --x 1', 'd');

echo "  mixed options\n";
draw_parser('-a --b -cd --e=f -g h --x', 'g');

echo "  options after params\n";
draw_parser('-x --a P --a -x');

echo "  double dash\n";
draw_parser('-x --a -- --a -x');

?>
--EXPECT--
  empty
---
  one param
type: param value: 'a'
---
  short options
type: option name: '-a' value: NULL
type: option name: '-b' value: NULL
type: option name: '-c' value: NULL
type: option name: '-d' value: 'VV'
type: option name: '-d' value: 'VV'
type: param value: 'blah'
---
  long options
type: option name: '--a' value: NULL
type: option name: '--a-a-a' value: NULL
type: option name: '--x' value: '1'
type: option name: '--x1' value: NULL
type: option name: '--x' value: NULL
type: param value: '1'
---
  mixed options
type: option name: '-a' value: NULL
type: option name: '--b' value: NULL
type: option name: '-c' value: NULL
type: option name: '-d' value: NULL
type: option name: '--e' value: 'f'
type: option name: '-g' value: 'h'
type: option name: '--x' value: NULL
---
  options after params
type: option name: '-x' value: NULL
type: option name: '--a' value: NULL
type: param value: 'P'
type: param value: '--a'
type: param value: '-x'
---
  double dash
type: option name: '-x' value: NULL
type: option name: '--a' value: NULL
type: param value: '--a'
type: param value: '-x'
---
