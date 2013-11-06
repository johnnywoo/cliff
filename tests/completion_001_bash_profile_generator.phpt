--TEST--
Bash profile commands generator
--ARGS--
--cliff-bash-profile=awesometool
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

include __DIR__ . '/../lib/Cliff/Cliff.php';
use Cliff\Cliff;

// empty config mode: no params/options are accepted except Cliff standard ones
Cliff::run(Cliff::config());

?>
--EXPECTF--
alias 'awesometool'=''\''%s/php'\'' '\''%s/tests/completion_001_bash_profile_generator.php'\'''
function _cliff_complete_awesometool() {
    saveIFS=$IFS
    IFS=$'\n'
    COMPREPLY=($('%s/php' '%s/tests/completion_001_bash_profile_generator.php' --cliff-complete-- "$COMP_LINE" "$COMP_POINT" "$COMP_WORDBREAKS"))
    IFS=$saveIFS
}
complete -o bashdefault -o default -o nospace -F _cliff_complete_awesometool 'awesometool'
