--TEST--
Bash completion generator: support for subcommands
--FILE--
<?php

include __DIR__ . '/_functions.inc';
use cliff\Cliff;
use cliff\Config;
use cliff\Completion;

$config = Cliff::config()
    ->flag('--aaa')
    ->flag('--bbb')
    ->flag('--ccc')

    ->command('list', Cliff::config()
        ->flag('--aaa')
        ->flag('--bbb')
    )

    ->command('delete', Cliff::config()
        ->flag('--aaa')
        ->flag('--bbb')
        ->flag('--kkk')

        ->option('--kraks', array('completion' => array('foo')))
    )
;

echo "  args\n";
drawCompleton($config, 'x -');

echo "  one arg\n";
drawCompleton($config, 'x --a');

echo "  empty\n";
drawCompleton($config, 'x');

echo "  subcommand options\n";
drawCompleton($config, 'x list -');
// We have --help here and not in the top because the --help can only be attached
// after we're done with the config, i.e. by Cliff::run() which we don't use in this test.
// Normally --help will be present in both places.

echo "  subcommand option value\n";
drawCompleton($config, 'x delete --kraks=');

?>
--EXPECT--
  args
--aaa |
--bbb |
--ccc |
---
  one arg
--aaa |
---
  empty
list |
delete |
---
  subcommand options
--aaa |
--bbb |
--help |
---
  subcommand option value
foo |
---
