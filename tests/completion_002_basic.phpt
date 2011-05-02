--TEST--
Bash completion generator
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

include __DIR__ . '/_functions.inc';
use cliff\Cliff;
use cliff\Config;
use cliff\Completion;

$config = Cliff::config()
	->flag('--aaa')
	->flag('--bbb')
	->flag('--ccc')

	->flag('--invisible', array(
		'visibility' => Config::V_ALL - Config::V_COMPLETION,
	))

	->option('--whatever', array(
		'completion' => array('one', 'two', 'three'),
	))
	->option('--url', array(
		'completion' => array('http://example.com/a', 'http://example.com/b', 'http://example.com/c'),
	))
	->option('--word', array(
		'completion' => function() {
			return array('el', 'pueblo', 'unido');
		},
	));

echo "  args\n";
draw_completon($config, 'x -');

echo "  one arg\n";
draw_completon($config, 'x --a');

echo "  empty\n";
draw_completon($config, 'x');

echo "  option value\n";
draw_completon($config, 'x --whatever=');

echo "  url value\n";
draw_completon($config, 'x --url=http://ex');

echo "  callback\n";
draw_completon($config, 'x --word=p');

?>
--EXPECT--
  args
--aaa |
--bbb |
--ccc |
--whatever=|
--url=|
--word=|
---
  one arg
--aaa |
---
  empty
---
  option value
one |
two |
three |
---
  url value
//example.com/a |
//example.com/b |
//example.com/c |
---
  callback
pueblo |
---