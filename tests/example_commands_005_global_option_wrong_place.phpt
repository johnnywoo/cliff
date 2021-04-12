--TEST--
Branches example: global option should not work for subcommand
--ARGS--
add -r 1 2 3 || echo "exit $?"
--FILE--
<?php

include __DIR__ . '/../examples/commands.php';

?>
--EXPECT--
exit 1
