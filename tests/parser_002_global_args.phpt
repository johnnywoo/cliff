--TEST--
Parser: global args instead of user-defined
--ARGS--
-a
--FILE--
<?php

require_once __DIR__ . '/_functions.inc';

drawParser(new Cliff\Parser());

?>
--EXPECT--
type: option name: '-a' value: NULL
---
