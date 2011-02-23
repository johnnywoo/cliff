--TEST--
Parser: global args instead of user-defined
--ARGS--
-a
--FILE--
<?php

require_once __DIR__ . '/_functions.inc';
use cliff\Parser;

draw_parser(new Parser());

?>
--EXPECT--
type: option name: '-a' value: NULL
---
