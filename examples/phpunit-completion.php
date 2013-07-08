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

function getGroups() {
	exec("phpunit --list-groups | grep '-' | grep -v '__nogroup__' | cut -d ' ' -f3 2>/dev/null", $out, $err);
	if(!$err) {
		return array_map('trim', $out);
	}
}

Cliff::run(
	Cliff::config()
	->desc('Bash completion script for phpunit 3.7.19')

	->option('--log-junit')
	->option('--log-tap')
	->option('--log-json')

	->option('--coverage-html')
	->option('--coverage-clover')
	->option('--coverage-php')
	->option('--coverage-text')

	->option('--testdox-html')
	->option('--testdox-text')

	->option('--filter')
	->option('--testsuite')
	->option('--group', array('completion' => 'getGroups'))
	->option('--exclude-group', array('completion' => 'getGroups'))
	->flag('--list-groups')
	->option('--test-suffix')

	->option('--loader')
	->option('--printer')
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
	->flag('--verbose -v')
	->flag('--debug')

	->flag('--skeleton-class')
	->flag('--skeleton-test')

	->flag('--process-isolation')
	->flag('--no-globals-backup')
	->flag('--static-backup')

	->option('--bootstrap')
	->option('--configuration -c')
	->flag('--no-configuration')
	->option('--include-path')

	->flag('--help')
	->flag('--version')
);
