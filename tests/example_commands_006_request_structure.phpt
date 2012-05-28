--TEST--
Branches example: request structure with subcommands
--ARGS--
subtract 10 1
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

include __DIR__ . '/../examples/commands.php';

var_dump($_REQUEST);

?>
--EXPECT--
9
array(4) {
  ["roman"]=>
  bool(false)
  ["precision"]=>
  string(1) "2"
  ["command"]=>
  string(8) "subtract"
  ["subtract"]=>
  array(2) {
    ["minuend"]=>
    string(2) "10"
    ["subtrahend"]=>
    string(1) "1"
  }
}
