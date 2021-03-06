--TEST--
Parser: common cases
--ARGS--
--FILE--
<?php

require_once __DIR__ . '/_functions.inc';

echo "  empty\n";
drawParser('');

echo "  one param\n";
drawParser('a');

echo "  short options\n";
drawParser('-a -bc -dVV -d VV blah', 'd');

echo "  long options\n";
drawParser('--a --a-a-a --x=1 --x1 --x 1', 'd');

echo "  mixed options\n";
drawParser('-a --b -cd --e=f -g h --x', 'g');

echo "  options after params\n";
drawParser('-x --a P --a -x');

echo "  double dash\n";
drawParser('-x --a -- --a -x');

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
