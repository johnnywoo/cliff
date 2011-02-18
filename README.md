# Cliff — a CLI framework for PHP

Cliff allows you to write CLI scripts in PHP with comfort.
The main features are:

  * Arguments/options parser with validation
  * Usage and help generation
  * Terse chained-call configuration syntax
  * Exception handling

The basic idea of Cliff is to allow you to work with CLI arguments exactly the same way
you work with http request variables: by using `$_REQUEST`. All you need is to describe
options and parameters of your script, and then everything just works.

## HOWTO

You may want to look at scripts in `examples` directory, or even simply grab one of those
and modify to your needs.

A short example:

    <?php
    require_once 'cliff/lib/Cliff.php';
    use \cliff\Cliff;

    Cliff::run(Cliff::config()->many_params('lines'));

    foreach($_REQUEST['lines'] as $arg)
    {
        echo "$arg\n";
    }
    ?>

This little script will take arbitrary number of arguments and output each of them
on a separate line. Now, if you want to add an option to uppercase the arguments
before outputting them, it can be done quite easily:

    <?php
    require_once 'cliff/lib/Cliff.php';
    use \cliff\Cliff;

    Cliff::run(
        Cliff::config()
        ->option('--uppercase -u', true) // adds --uppercase option and -u as its alias
        ->many_params('lines')
    );

    foreach($_REQUEST['lines'] as $arg)
    {
        if($_REQUEST['uppercase'])    // this will be null if the option is not present
            $arg = strtoupper($arg);  // in args, so you don't have to worry about notices

        echo "$arg\n";
    }
    ?>

Now our script may be called as `php script.php -u abc def`. If called without arguments
or with incorrect ones, the script will show a short description of available options and
parameters. 'Parameter' in Cliff means any non-option argument.

Read phpdoc comments in `lib/Config.php` for all configuration possibilities.

## REQUIREMENTS

The only thing Cliff requires is PHP 5.3.

## TODO

If you don't mind, I'll leave this todo list here.

  * Modifying option values and params via callbacks
  * Ability to design complex subcommand structures via branches and subcommands
    (to distinguish branches we can just forbid two consequent branches with no params)
  * Bash completion for options and params
  * Special class for argument-related exceptions, so we can show usage only for those
  * Helper for colors
  * Helper for reading (char, string, password, stdin)
  * Helper for writing (out, err, interface = skipped if not tty, table)
  * Adopting external commands as subcommands
  * Adopting external Cliff scripts as subcommands with usage/completion import
  * Specifying default error exit code somewhere (for grep-like exit codes)