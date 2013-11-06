<?php

/*

DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.

Cliff: a CLI framework for PHP.
Copyright 2011 Aleksandr Galkin.

This file is part of Cliff.

Cliff is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License
as published by the Free Software Foundation, either version 3
of the License, or (at your option) any later version.

Cliff is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with Cliff. If not, see <http://www.gnu.org/licenses/>.

*/

namespace Cliff;

// let's not affect autoload, we don't have too much files here
require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/Request.php';
require_once __DIR__ . '/Parser.php';
require_once __DIR__ . '/Exception/ParseError.php';
require_once __DIR__ . '/Usage.php';
require_once __DIR__ . '/Completion.php';

class Cliff
{
    /**
     * This exit code will be used if an uncaught exception occurs
     */
    public static $errorExitCode = 1;

    /**
     * A shortcut for `new Config` for easy chaining
     *
     * @return Config
     */
    public static function config()
    {
        return new Config();
    }

    public static function run(Config $config = null)
    {
        if ($config === null) {
            $config = static::getDefaultConfig();
        }

        if ($config->scriptName == '') {
            $config->scriptName = basename($_SERVER['argv'][0]);
        }

        $request = new Request();

        static::setExceptionHandler($request);
        static::addDefaultOptions($config, true);

        // run
        $request->load($config, new Parser(), $_REQUEST);
        $request->validate();
    }

    protected static function getDefaultConfig()
    {
        return static::config()
            ->allowUnknownOptions()
            ->manyParams('args', array(
                'isRequired' => false,
            ))
        ;
    }

    protected static function setExceptionHandler(Request $request)
    {
        set_exception_handler(
            function (\Exception $e) use ($request) {

                /** @var $request Request */

                // do not show error about required param when there is no args (there will be usage)
                $skipErrorMessage = false;
                if ($e instanceof Exception_ParseError) {
                    if ($e->getCode() == Exception_ParseError::E_NO_PARAM && count($_SERVER['argv']) == 1) {
                        $skipErrorMessage = true;
                    }
                }
                if (!$skipErrorMessage) {
                    fwrite(STDERR, $e->getMessage() . "\n");
                }

                if ($e instanceof Exception_ParseError) {
                    // show usage
                    $usage = new Usage($request->getInnermostBranchConfig());

                    if (!$skipErrorMessage) {
                        echo "\n";
                    }
                    fwrite(STDERR, $usage->make());
                }

                exit(Cliff::$errorExitCode);
            }
        );
    }

    public static function addDefaultOptions(Config $config, $isTopConfig = false)
    {
        if ($isTopConfig) {
            // completion handler for bash
            $config->flag(
                '--cliff-complete--', array(
                    'visibility' => Config::V_NONE,
                    'callback' => function () use ($config) {
                        Completion::actionComplete($config);
                        exit;
                    },
                )
            );

            // completion handler for bash
            $config->option(
                '--cliff-bash-profile', array('
                    Generate alias and completion commands for bash profile

                    To enable bash completion, you should place the following in your ' . Completion::guessBashProfileName() . ' (all on one line):
                    ' . Completion::makeProfileCommand($config->isCustomScriptName ? $config->scriptName : 'your_alias'),

                    'visibility' => Config::V_HELP,
                    'callback' => function ($alias) {
                        Completion::actionBashProfile($alias);
                        exit;
                    },
                )
            );
        }

        $config->flag(
            '--help', array(
                'Show descriptions of options and params',
                'visibility' => Config::V_ALL & ~Config::V_REQUEST, // everywhere except in $_REQUEST
                'callback' => function () use ($config) {
                    $usage = new Usage($config);
                    $usage->isHelpMode = true;
                    echo $usage->make();
                    exit;
                },
            )
        );
    }
}
