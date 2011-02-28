--TEST--
Options example: short options
--ARGS--
-d -nm 7 -t -- -1
--FILE--
<?php
include __DIR__ . '/../examples/options.php';
?>
--EXPECT--
42