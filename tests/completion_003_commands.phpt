--TEST--
Bash completion generator: support for subcommands
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

	->command('list', Cliff::config()
		->flag('--aaa')
		->flag('--bbb')
	)

	->command('delete', Cliff::config()
		->flag('--aaa')
		->flag('--bbb')
		->flag('--kkk')

		->option('--kraks', array('completion' => array('foo')))
	)
;

echo "  args\n";
draw_completon($config, 'x -');

echo "  one arg\n";
draw_completon($config, 'x --a');

echo "  empty\n";
draw_completon($config, 'x');

echo "  subcommand options\n";
draw_completon($config, 'x list -');
// We have --help here and not in the top because the --help can only be attached
// after we're done with the config, i.e. by Cliff::run() which we don't use in this test.
// Normally --help will be present in both places.

echo "  subcommand option value\n";
draw_completon($config, 'x delete --kraks=');

?>
--EXPECT--
  args
--aaa |
--bbb |
--ccc |
---
  one arg
--aaa |
---
  empty
list |
delete |
---
  subcommand options
--aaa |
--bbb |
--help |
---
  subcommand option value
foo |
---
