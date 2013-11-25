<?php

/**
 * This is a completion script for composer
 *
 * Only basic stuff, not full composer interface.
 * In theory it is possible to autogenerate the completion by using XML help from composer.
 *
 * Based on composer around 2013-11-04.
 */

require_once __DIR__ . '/../lib/Cliff/Cliff.php';
use \Cliff\Cliff;

function getCommandsList()
{
    return array(
        'about',
        'archive',
        'config',
        'create-project',
        'depends',
        'diagnose',
        'dump-autoload',
        'dumpautoload',
        'global',
        'help',
        'init',
        'install',
        'licenses',
        'list',
        'require',
        'run-script',
        'search',
        'self-update',
        'selfupdate',
        'show',
        'status',
        'update',
        'validate',
    );
}

Cliff::run(
    Cliff::config()
    ->desc('Bash completion script for composer')

    ->flag('--help')
    ->flag('--quiet')
    ->flag('--verbose -v')
    ->flag('--version -V')
    ->flag('--ansi')
    ->flag('--no-ansi')
    ->flag('--no-interaction -n')
    ->flag('--profile')
    ->option('--working-dir -d')

    ->param('command', array(
        'useForCommands' => true,
        'validator'      => '/./', // we need to supply this so Cliff won't require an actual defined command name
        'completion'     => 'getCommandsList',
    ))

    // making help complete subcommand names
    ->command('help', Cliff::config()
        ->param('cmd', array(
            'completion' => 'getCommandsList',
        ))
    )
);
