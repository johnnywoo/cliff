--TEST--
Bash profile commands generator
--ARGS--
--cliff-bash-profile=awesometool
--FILE--
<?php
include __DIR__ . '/../lib/Cliff.php';
use cliff\Cliff;

// empty config mode: no params/options are accepted except Cliff standard ones
Cliff::run(Cliff::config());

?>
--EXPECTF--
alias 'awesometool'='php '\''%s/tests/usage_007_bash_profile_generator.php'\'''
function _cliff_complete_awesometool() {
    saveIFS=$IFS
    IFS=$'\n'
    COMPREPLY=($(php '%s/tests/usage_007_bash_profile_generator.php' --cliff-complete-- "$COMP_LINE" "$COMP_POINT"))
    IFS=$saveIFS
}
complete -o bashdefault -o default -o nospace -F _cliff_complete_awesometool 'awesometool'
