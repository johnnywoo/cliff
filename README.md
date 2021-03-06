# Cliff — a CLI framework for PHP

Cliff allows you to write CLI scripts in PHP with comfort.
The main features are:

  * Arguments/options parser
  * Terse chained-call configuration syntax
  * No-config mode
  * Usage and help generation
  * Automatic bash completion

The basic idea of Cliff is to allow you to work with CLI arguments exactly the same way
you work with http request variables: by using `$_REQUEST`. All you need is to describe
options and parameters of your script, and then everything just works. In no-config mode,
you don't even have to describe anything.

## VERSION

Current Cliff version is 1.0. It is incompatible with the previous version (0.2) because
of the new code style, which moved everything to camelCase, breaking the API.

## DISCLAIMER

What Cliff is not: it is not a getopt library. It may be overkill to use Cliff for
simplistic cron jobs. What Cliff is for is large tools which you are using a lot, like
CLI versions of extensive projects, data mining tools, etc. If you crave for smart bash
completion, Cliff is the right tool for you.

## HOWTO

You may want to look at scripts in `examples` directory, or even simply grab one of those
and modify to your needs.

A short example:

    <?php
    require_once 'cliff/lib/Cliff/Cliff.php';
    use \Cliff\Cliff;

    Cliff::run(Cliff::config()->manyParams('lines'));

    foreach ($_REQUEST['lines'] as $arg) {
        echo "$arg\n";
    }

This little script will take arbitrary number of arguments and output each of them
on a separate line. Now, if you want to add an option to uppercase the arguments
before outputting them, it can be done quite easily:

    <?php
    require_once 'cliff/lib/Cliff/Cliff.php';
    use \Cliff\Cliff;

    Cliff::run(
        Cliff::config()
        ->flag('--uppercase -u') // adds --uppercase and -u as its alias
        ->manyParams('lines')
    );

    foreach ($_REQUEST['lines'] as $arg) {
        if ($_REQUEST['uppercase']) {    // this will be FALSE if the flag is not set,
            $arg = strtoupper($arg);  // so you don't have to worry about notices
        }

        echo "$arg\n";
    }

Now our script may be called as `php script.php -u abc def`. If called without arguments
or with incorrect ones, the script will show a short description of available options and
parameters. 'Parameter' in Cliff means any non-option argument.

Read phpdoc comments in `lib/Cliff/Config.php` for all configuration possibilities.

### Examples

 * [params.php](https://github.com/johnnywoo/cliff/blob/master/examples/params.php): the simplest script that shows how to accept multiple params
 * [options.php](https://github.com/johnnywoo/cliff/blob/master/examples/options.php): how to use flags and options
 * [callbacks.php](https://github.com/johnnywoo/cliff/blob/master/examples/callbacks.php): how to use callbacks and validators
 * [commands.php](https://github.com/johnnywoo/cliff/blob/master/examples/commands.php): how to define subcommands a-la `git checkout`
 * [pear-completion.php](https://github.com/johnnywoo/cliff/blob/master/examples/pear-completion.php): a sample config (+ bash completion) for pear executable
 * [phpunit-completion.php](https://github.com/johnnywoo/cliff/blob/master/examples/phpunit-completion.php): a sample config (+ bash completion) for phpunit executable

## NO-CONFIG MODE

No-config mode allows you to have your cake and eat it too: you get `$_REQUEST` filled,
but don't have to configure anything.

    <?php
    require_once 'cliff/lib/Cliff/Cliff.php';
    \Cliff\Cliff::run();

    // work with $_REQUEST as you like:
    // -x will become $_REQUEST['x'] == true
    // --option will become $_REQUEST['option'] == true
    // --option=a will become $_REQUEST['option'] == 'a'
    // non-options will be accumulated in $_REQUEST['args'] array

You can always add configuration later, to enable proper help, validation and bash completion.

## BASH COMPLETION

The completion is available for configured options (the options/flags themselves
and long option values) and param values. It may work incorrectly with weird characters,
but simple cases should behave properly.

To enable the completion, put the following into your ~/.profile
(or ~/.bash_profile, whatever the name is on your system).

    eval "$(/usr/bin/php /path/to/your/awesometool.php --cliff-bash-profile=atool)"

In this example:

 * `/usr/bin/php` is your php 5.3 cli binary
 * `/path/to/your/awesometool.php` is your cliff script
 * `atool` is the alias you will use to execute the awesome tool

After editing the profile, `source` it (or simply log off and on), and `atool <tab>` should
start working.

Of course, instead of eval, you can execute the command by hand, alter its output, etc.
Eval is just a convenient way to get things working.

### Bash completion for third-party programs

You can use Cliff to add bash completion to any other program. Simply create a cliff script
with nothing but config and completion callbacks, and set the actual program name
when installing the completion into bash profile.

To do that:

 1. Write a script with desired config, like the one in `examples/phpunit-completion.php`
 2. Put this in your profile:

        eval "$(/usr/bin/php /path/to/your/phpunit-completion.php --cliff-bash-profile=phpunit | sed 1d)"

The `sed` part will remove alias command, so when you type "phpunit", it will still mean the phpunit
you can work with (not the helper script); but completion will use the helper script.

## NOTES

To signal an error in your script, simply throw an exception. It will be caught by Cliff,
its message will be displayed into stderr, and the script will exit with non-zero status
(useful for shell scripting).

To change error exit code, you need to change `Cliff::$errorExitCode`. Default error exit code is 1.

## REQUIREMENTS

The only thing Cliff requires is PHP 5.3.

## TODO

If you don't mind, I'll leave this todo list here.

  * [+] Modifying option values and params via validator callbacks
  * [+] Special class for argument-related exceptions, so we can show usage only for those
  * [+] Specifying default error exit code somewhere (for grep-like exit codes)
  * [+] Separate options and flags
  * [+] A way to specify required options and options that require a value but provide default one
  * [+] Ability to allow unconfigured options
  * [+] Non-config mode: aggregate all provided options and store all params into $_REQUEST['args'],
    no validation or anything; could be useful for tmp scripts (hack a tool together with no design
    planning and then, when it matures, configure it for usage and completion)
  * [+] A way to specify optional parameters (e.g. vmig db [table])
  * [+] Non-validation parse time callbacks (to prevent --help from breaking completion)
  * Bash completion for options and params
    * [+] Completion for options
    * [+] A nice way to install completion handlers into the profile
    * [+] Completion for option values
    * [+] Completion for params
    * Completion of params and validation
    * Completion standard modes (filenames, etc) mixed with custom options
    * Smart completion for mentioned options (not array = do not complete it twice)
    * Completion for single-letter option values
    * Completion of weird chars and escaping
    * Aliases when installing completion into bash profile (like g='script' x='script -a -b')
  * Look into trollop for potential insight
  * Ability to specify default values for single-letter options
  * [+] Allow a string instead of $props array
  * Helper for colors
  * Helper for reading (char, string, password, stdin)
  * Helper for writing (out, err, interface = skipped if not tty, table)
  * Ability to store STDIN in a variable (is it possible to iterate over it instead?)
  * [+] Ability to design complex subcommand structures via branches and subcommands
    * [+] Aliases for command names
      * Usage/help for command name aliases
    * Defining branches without params (like `x --list whatever`)
      (to distinguish branches we can just forbid two consequent branches with no params)
    * Adopting external commands as subcommands
    * Adopting external Cliff scripts as subcommands with usage/completion import
      * Load config from Cliff XML (not sure if PEAR::Console_CommandLine format is enough)
      * Generate config in Cliff XML
  * Load config from PEAR::Console_CommandLine XML
  * Need some way to include code samples in descriptions (formatting breaks those)
  * Refactoring and benchmarking
    * An interface/base class for lazy configs (scripts with lots of commands)

## KNOWN BUGS

### Completion makes incorrect/extra chars after sudo

When you try to use sudo with your script, completion results may look like
`sudo pear list-upgrades\  ` (with incorrect escaped space at the end).
This is actually a bug in bash-completion suite (or other script you're using
to enable sudo completion), which does not work correctly with `-o nospace`.
Try completing `sudo git checkout`, it will have weird double space at the end too.
If it doesn't and Cliff completion still glitches, please write me a letter.

## CONTACTS

The Cliff framework was created by Aleksandr "Johnny Woo" Galkin in 2011.

E-mail: agalkin@agalkin.ru

Github page: https://github.com/johnnywoo/cliff

Cliff is released under MIT license. This means you can do pretty much whatever you want with it.

Copyright 2011 Aleksandr Galkin.
