--TEST--
Bash profile commands generator
--ARGS--
--cliff-bash-profile=awesometool
--FILE--
<?php

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
