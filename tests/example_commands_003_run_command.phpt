--TEST--
Branches example: executing a subcommand
--ARGS--
add 1 2 3
--FILE--
<?php

include __DIR__ . '/../examples/commands.php';

?>
--EXPECT--
6
