--TEST--
Default values for options
--ARGS--
--val=123 --def --req-val=456 --req-def
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

include __DIR__ . '/../lib/Cliff.php';
use cliff\Cliff;

Cliff::run(
	Cliff::config()
	->option('--val', array('flag_value' => 'ZZZ'))
	->option('--def', array('flag_value' => 'ZZZ'))
	->option('--absent', array('flag_value' => 'NO WAI', 'default' => 'YA RLY'))
	->option('--req-val', array('flag_value' => 'ZZZ', 'is_required' => true))
	->option('--req-def', array('flag_value' => 'ZZZ', 'is_required' => true))
	// absence of a required option will cause an error
);

var_dump($_REQUEST);

?>
--EXPECT--
array(5) {
  ["val"]=>
  string(3) "123"
  ["def"]=>
  string(3) "ZZZ"
  ["absent"]=>
  string(6) "YA RLY"
  ["req-val"]=>
  string(3) "456"
  ["req-def"]=>
  string(3) "ZZZ"
}
