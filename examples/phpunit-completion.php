<?php

/**
 * This is a completion script for phpunit
 *
 * You can use this to enable basic bash completion for phpunit.
 * To do it, add to your bash profile:
 *
 * eval "$(/usr/bin/php /path/to/your/phpunit-completion.php --cliff-bash-profile=phpunit | sed 1d)"
 *
 * Based on phpunit 3.5.13.
 */

require_once __DIR__.'/../lib/Cliff.php';
use \cliff\Cliff;

Cliff::run(
	Cliff::config()
	->desc('Bash completion script for phpunit')

	->option('--log-junit')
	->option('--log-tap')
	->flag('--log-dbus')
	->option('--log-json')

	->option('--coverage-html')
	->option('--coverage-clover')

	->option('--testdox-html')
	->option('--testdox-text')

	->option('--filter')
	->option('--group')
	->option('--exclude-group')
	->flag('--list-groups')

	->option('--loader')
	->option('--repeat')

	->flag('--tap')
	->flag('--testdox')
	->flag('--colors')
	->flag('--stderr')
	->flag('--stop-on-error')
	->flag('--stop-on-failure')
	->flag('--stop-on-skipped')
	->flag('--stop-on-incomplete')
	->flag('--strict')
	->flag('--verbose')
	->flag('--wait')

	->flag('--skeleton-class')
	->flag('--skeleton-test')

	->flag('--process-isolation')
	->flag('--no-globals-backup')
	->flag('--static-backup')
	->flag('--syntax-check')

	->option('--bootstrap')
	->option('--configuration -c')
	->flag('--no-configuration')
	->option('--include-path')

	->flag('--help')
	->flag('--version')

	->flag('--debug')
);
