<?php

namespace Cliff;

// let's not affect autoload, we don't have too much files here
require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/Tokenizer.php';
require_once __DIR__ . '/Parser.php';
require_once __DIR__ . '/Request.php';
require_once __DIR__ . '/Config/Item.php';

class Completion
{
    /** @var Config */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Calculates the completion options
     *
     * For meaning of COMP_WORDBREAKS refer to bash manual.
     *
     * @param string $command full command to be completed
     * @param int $cursorOffset position of the cursor
     * @param string $compWordbreaks
     * @return string[]
     */
    public function complete($command, $cursorOffset, $compWordbreaks = " \t\n\"'><=;|&(:")
    {
        $tokenizer = new Tokenizer($command);

        $lastArg = false;
        $words   = array();
        while ($next = $tokenizer->read($cursorOffset)) {
            $lastArg = $next;
            $words[] = $next->word;
        }
        array_shift($words); // first one is the executable name

        if (empty($words)) {
            $lastArg = false;
        }

        try {
            $options = $this->completeConfig($words, $lastArg ? $lastArg->word : '');
            return $this->reduceOptions($options, $lastArg, $compWordbreaks);
        } catch (\Exception $e) {
            return array();
        }
    }

    protected function completeConfig($args, $currentArg)
    {
        array_pop($args); // last one is being completed
        $parser = new Parser($args);

        $request = new Request();
        $request->incompleteMode   = true;
        $request->disableCallbacks = true;
        $request->load($this->config, $parser, $_REQUEST);

        $completions = array();
        if ($parser->areOptionsAllowed()) {
            $options = $request->getInnermostBranchConfig()->getOptions();

            if (substr($currentArg, 0, 1) == '-') {
                $this->completeOptions($options, $completions);
            }

            if (preg_match('/^((--[^\s=]+)=)(.*)$/', $currentArg, $m)) {
                $op =& $options[$m[2]]; // 2 --x
                if (isset($op)) {
                    $this->completeItemValue($completions, $m[3], $op, $m[1]);
                } // 1 --x=  3 value
            }
        }

        foreach ($request->getAllowedParams() as $param) {
            if (!($param->visibility & Config::V_COMPLETION)) {
                continue;
            }

            $this->completeItemValue($completions, $currentArg, $param);
        }

        return $completions;
    }


    /**
     * @param Config_Option[] $options
     * @param array $completions
     */
    protected function completeOptions($options, &$completions)
    {
        foreach ($options as $alias => $option) {
            // ignore one-letter aliases
            if (strlen($alias) == 2) {
                continue;
            }

            if (!($option->visibility & Config::V_COMPLETION)) {
                continue;
            }

            // if there is a value, we shouldn't add a space, so the value can be completed right away
            $alias .= $option->needsValue() ? '=' : ' ';

            $completions[] = $alias;
        }
    }

    protected function completeItemValue(&$completions, $enteredValue, Config_Item $item, $prefix = '')
    {
        foreach ($item->complete($enteredValue) as $line) {
            $completions[] = $prefix . $line . ' ';
        }
    }


    /**
     * Tailors list of options to the prefix which is being completed
     *
     * @param string[] $options
     * @param Token $arg
     * @param string $compWordbreaks
     * @return string[]
     */
    protected function reduceOptions($options, $arg, $compWordbreaks)
    {
        if (!$arg) {
            return $options;
        }

        $cmpWord = strtolower($arg->word);
        $cmpArg  = strtolower($arg->arg);
        $length  = strlen($cmpArg);

        // Readline treats our completion options as variants of last comp-word.
        // Those words are separated not by IFS, like shell-words, but by
        // COMP_WORDBREAKS characters like '=' and ':'.
        //
        // Our tokenizer splits its words in a shell-word manner, therefore
        // the completion options can contain many comp-words. For correct completion
        // to work, we need to find the last wordbreak and remove everything before it
        // from our options, leaving only the last comp-word.
        $prefix      = $arg->getArgTail($compWordbreaks);
        $forcePrefix = ($prefix != $arg->arg);

        foreach ($options as $k => $variant) {
            // need to convert the casing (ac<tab> -> aCC)
            $variantPrefix = strtolower(substr($variant, 0, $length));
            if ($variantPrefix != $cmpWord || strlen($variant) == $length) {
                // does not match or is equal to what is being completed; skip this option
                unset($options[$k]);
                continue;
            }

            // If the arg matches the word (that is, there is no special syntax in the arg)
            // and we don't have to force the prefix because of a wordbreak, then it's better
            // to use the whole variant string instead of a prefixed one (this way we can
            // get correct case of chars)
            if ($forcePrefix || $variantPrefix != $cmpArg) {
                $options[$k] = $prefix . substr($variant, $length);
            }
        }

        return $options;
    }

    public static function actionComplete(Config $config, $args = null)
    {
        if ($args === null) {
            $args = $_SERVER['argv'];
        }

        $compWordbreaks = end($args);
        $compPoint      = prev($args);
        $compLine       = prev($args);

        /** @var $cmp Completion */
        $cmp = new static($config);
        foreach ($cmp->complete($compLine, $compPoint, $compWordbreaks) as $opt) {
            echo "$opt\n";
        }
    }

    public static function actionBashProfile($alias)
    {
        $fname = static::getScriptFilename();

        // if the file has a shebang, we assume it can execute itself
        if (is_readable($fname) && file_get_contents($fname, 0, null, 0, 2) == '#!') {
            $aliasCmd    = $fname;
            $completeCmd = escapeshellarg($fname);
        } else {
            $aliasCmd    = static::getPhpCommand(true) . ' ' . escapeshellarg($fname);
            $completeCmd = $aliasCmd;
        }

        $funcname = '_cliff_complete_' . $alias;

        echo 'alias ' . escapeshellarg($alias) . '=' . escapeshellarg($aliasCmd) . "\n";
        echo 'function ' . $funcname . '() {' . "\n";
        echo '    saveIFS=$IFS' . "\n";
        echo "    IFS=$'\\n'\n";
        echo '    COMPREPLY=($(' . $completeCmd . ' --cliff-complete-- "$COMP_LINE" "$COMP_POINT" "$COMP_WORDBREAKS"))' . "\n";
        echo '    IFS=$saveIFS' . "\n";
        echo "}\n";
        echo 'complete -o bashdefault -o default -o nospace -F ' . $funcname . ' ' . escapeshellarg($alias) . "\n";
    }

    public static function makeProfileCommand($scriptName)
    {
        return 'eval "$(' . static::getPhpCommand()
            . ' ' . escapeshellarg(static::getScriptFilename())
            . ' --cliff-bash-profile=' . escapeshellarg($scriptName) . ')"'
        ;
    }

    public static function guessBashProfileName()
    {
        return static::guessFile(
            array(
                '~/.profile',
                '~/.bash_profile',
            ),
            'bash profile'
        );
    }

    public static function getScriptFilename()
    {
        return realpath($_SERVER['PHP_SELF']);
    }

    /**
     * Returns a command to run the php cli
     *
     * 'Command' means it is already escaped, while 'filename' is not.
     *
     * @param bool $assumeNoShebang
     * @return string
     */
    public static function getPhpCommand($assumeNoShebang = false)
    {
        // weird magic, but well, there's no way to do this right (right?)

        // if we're a nice shell script, let's use that
        if (!$assumeNoShebang) {
            $fname = static::getScriptFilename();
            if (is_readable($fname)) {
                list($line) = explode("\n", file_get_contents($fname, 0, null, 0, 1024), 2);
                if (substr($line, 0, 2) == '#!') {
                    return substr($line, 2);
                }
            }
        }

        // a convenient constant in PHP 5.4
        if (defined('PHP_BINARY')) {
            return escapeshellarg(PHP_BINARY);
        }

        // a less convenient constant that's been there forever (at least from PHP 4.4)
        if (defined('PHP_BINDIR') && file_exists(PHP_BINDIR . '/php')) {
            return escapeshellarg(PHP_BINDIR . '/php');
        }

        // well, whatever
        return 'php';
    }

    protected static function guessFile($locations, $default)
    {
        foreach ($locations as $loc) {
            if (@file_exists(str_replace('~', isset($_ENV['HOME']) ? $_ENV['HOME'] : '~', $loc))) {
                return $loc;
            }
        }
        return $default;
    }
}
