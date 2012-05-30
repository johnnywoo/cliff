--TEST--
Branches example: usage
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

include __DIR__ . '/../examples/commands.php';

?>
--EXPECT--
Usage: example_commands_001_usage.php [-r] [--roman] [--precision=...] [--help] <command>

  -r, --roman      Output results in Roman numerals
  --precision=...  Output results with given precision
  --help           Show descriptions of options and params

COMMANDS
  add [--help] <summands>
    Add any numbers together
  subtract [--help] <minuend> <subtrahend>
    Subtract a number from another number