<?php

namespace Cliff;

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/Config/Option.php';

class Usage
{
    /** @var Config */
    private $config;

    public $width = 100;

    public $maxTermLength    = 20;
    public $termPaddingLeft  = 2;
    public $termPaddingRight = 2;

    public $maxOptionsListed = 5;

    public $isHelpMode = false;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function make()
    {
        $desc = '';
        if ($this->isHelpMode && $this->config->description != '') {
            $desc = $this->formatDescription($this->config->description);
            $desc = "\n" . $this->wrap($desc) . "\n";
        }

        return 'Usage: ' . $this->makeUsage() . "\n" . $desc
            . $this->makeOptionsBlock()
            . $this->makeParamsBlock()
            . $this->makeCommandsBlock()
        ;
    }

    public function makeUsage($scriptName = null)
    {
        if ($scriptName === null) {
            $scriptName = $this->config->scriptName;
        }

        $usage = $scriptName;

        /** @var $options Config_Option[] */
        $options = $this->getItemsByVisibility(__NAMESPACE__ . '\Config_Option');
        if (count($options)) {
            $aliases      = array();
            $shortOptions = '';
            foreach ($options as $option) {
                foreach ($this->makeupOptionAliases($option) as $alias) {
                    if (substr($alias, 0, 2) != '--' && !$option->needsValue()) {
                        $shortOptions .= substr($alias, 1);
                    } else {
                        $aliases[] = $alias;
                    }
                }
            }
            if ($shortOptions) {
                $usage .= ' [-' . $shortOptions . ']';
            }
            $usage .= count($aliases) > $this->maxOptionsListed ? ' [options]' : ' [' . join('] [', $aliases) . ']';
        }

        /** @var $param Config_Param */
        foreach ($this->getItemsByVisibility(__NAMESPACE__ . '\Config_Param') as $param) {
            $usage .= ' <' . $param->name . '>';
        }

        return $usage;
    }

    private function makeOptionsBlock()
    {
        $lines = array();
        /** @var $options Config_Option[] */
        $options = $this->getItemsByVisibility(__NAMESPACE__ . '\Config_Option');
        foreach ($options as $option) {
            $term  = join(', ', $this->makeupOptionAliases($option));
            $title = '';
            if ($option->description != '') {
                $title = $this->formatDescription($option->description);
            }

            $lines[] = array($term, $title);
        }
        return $this->makeDefinitionList($lines, $this->isHelpMode ? 'OPTIONS' : '');
    }

    private function makeParamsBlock()
    {
        $lines = array();
        /** @var $param Config_Param */
        foreach ($this->getItemsByVisibility(__NAMESPACE__ . '\Config_Param') as $param) {
            $title   = $this->formatDescription($param->description);
            $lines[] = array('<' . $param->name . '>', $title);
        }
        if (count($lines) == 1 && $param->useForCommands) {
            $lines = array();
        }
        return $this->makeDefinitionList($lines, 'PARAMETERS');
    }

    private function makeCommandsBlock()
    {
        $lines = array();
        foreach ($this->config->getBranches() as $config) {
            $usage   = new Usage($config);
            $title   = $this->formatDescription($config->description);
            $lines[] = array($usage->makeUsage(), $title);
        }
        return $this->makeDefinitionList($lines, 'COMMANDS');
    }

    private function makeDefinitionList($lines, $title)
    {
        if (empty($lines)) {
            return '';
        }

        // finding out max term length
        $maxLength = 0;
        foreach ($lines as $row) {
            $l = strlen($row[0]);
            if ($l > $this->maxTermLength) {
                $l = 0;
            }
            $maxLength = max($maxLength, $l);
        }
        $maxLength = min($maxLength, $this->maxTermLength);

        $columnOffset = $this->termPaddingLeft + $this->termPaddingRight;
        if (!$this->isHelpMode) {
            $columnOffset += $maxLength;
        }

        $text = "\n";
        if ($title != '') {
            $text .= "$title\n";
        }
        foreach ($lines as $row) {
            $line = $this->wrap($row[0], $this->termPaddingLeft, true);
            if (($row[1] != '' && strlen($row[0]) > $maxLength) || $this->isHelpMode) {
                // long desc/long param summary: make an indent on the next line
                $line .= "\n" . str_repeat(' ', $columnOffset);
            } elseif ($row[1] != '') {
                // do not make trailing spaces
                // short desc: add spaces to form descriptions into a column
                $line = str_pad($line, $columnOffset, ' ');
            }

            $line .= $this->wrap($row[1], $columnOffset);

            if ($this->isHelpMode) {
                $text .= "\n";
            }

            $text .= $line . "\n";
        }
        return $text;
    }

    private function wrap($text, $paddingLeft = 0, $padFirstLine = false)
    {
        $width   = $this->width - $paddingLeft;
        $out     = '';
        $padding = str_repeat(' ', $paddingLeft);
        foreach (explode("\n", wordwrap($text, $width, "\n", true)) as $i => $line) {
            if ($i || $padFirstLine) {
                $out .= $padding;
            }
            $out .= $line . "\n";
        }
        $out = substr($out, 0, -1);
        // removing trailing spaces
        $out = preg_replace('/[ \t]+(?=\n|$)/', '', $out);
        return $out;
    }

    private function makeupOptionAliases(Config_Option $option)
    {
        $aliases = $option->aliases;
        if ($option->needsValue()) {
            foreach ($aliases as &$v) {
                $v .= (substr($v, 0, 2) == '--') ? '=...' : ' ...';
            }
        }

        // moving short names up
        usort($aliases, array('static', 'optionAliasCmp'));

        return $aliases;
    }

    private static function optionAliasCmp($a, $b)
    {
        $as = (substr($a, 0, 2) == '--');
        $bs = (substr($b, 0, 2) == '--');
        if ($as != $bs) {
            return $as ? 1 : -1;
        }
        return strcmp($a, $b);
    }

    private function formatDescription($text)
    {
        // removing meaningless indent
        $text = static::unindent($text);

        if (!$this->isHelpMode) {
            list($text) = explode("\n", $text, 2);
        }

        return $text;
    }

    private function getItemsByVisibility($class)
    {
        $visibility = $this->isHelpMode ? Config::V_HELP : Config::V_USAGE;

        $list = array();

        foreach ($this->config->getItems() as $option) {
            if ($option instanceof $class && $option->visibility & $visibility) {
                $list[] = $option;
            }
        }

        return $list;
    }

    /**
     * Strips extra indentation from a string
     *
     * unindent() finds a common indent in your string and strips it away,
     * so you can write descriptions for config items without having to
     * break indentation. It also trims the string.
     *
     * @param string $desc
     * @return string
     */
    public static function unindent($desc)
    {
        $indent       = '';
        $indentLength = 0;
        $lines        = explode("\n", $desc);
        foreach ($lines as $i => $line) {
            if ($line == '') {
                continue;
            }

            // we grab the first non-empty string prefix, except for the first line
            if ($indent == '' && trim($line) != '' && preg_match('/^[\t ]+/', $line, $m)) {
                // if first line does not have indent, we ignore it and look for the next line
                if (!strlen($m[0]) && $i == 0) {
                    continue;
                }

                $indent       = $m[0];
                $indentLength = strlen($indent);
            }

            if (substr($line, 0, $indentLength) == $indent) {
                $lines[$i] = substr($line, $indentLength);
            }
        }

        return trim(join("\n", $lines));
    }
}
