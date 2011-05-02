--TEST--
Options example: help
--ARGS--
--help
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

include __DIR__ . '/../examples/options.php';

?>
--EXPECT--
Usage: example_options_002_help.php [-dtn] [options] <number>

Takes a number and multiplies it.

OPTIONS

  -d, --double
    Multiply the number by 2

  -t, --triple
    Multiply the number by 3

  -n, --negate
    Change sign of the number

  -m ..., --multiply=...
    Multiply by an arbitrary number

  --cliff-bash-profile=...
    Generate alias and completion commands for bash profile

  --help
    Show descriptions of options and params

PARAMETERS

  number
    A number to operate on