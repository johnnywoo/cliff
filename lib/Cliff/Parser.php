<?php

namespace Cliff;

require_once __DIR__ . '/Exception/ParseError.php';

class Parser
{
    private $args = array();
    private $optionsAllowed = true;

    const TYPE_OPTION = 1;
    const TYPE_PARAM  = 2;

    public function __construct(array $args = array())
    {
        if (!func_num_args()) {
            $args = $_SERVER['argv'];
            array_shift($args); // removing filename
        }
        $this->args = $args;
    }

    public function allowOptions($state = true)
    {
        $this->optionsAllowed = $state;
    }

    public function areOptionsAllowed()
    {
        return $this->optionsAllowed;
    }

    /**
     * Fetches next entity from the list of args
     *
     * A list of short options with values is needed because it affects parsing:
     * a short option with value behaves differently from one without it.
     *
     * @param string $shortOptionsWithValues
     * @throws Exception_ParseError
     * @return array|bool
     */
    public function read($shortOptionsWithValues = '')
    {
        if (empty($this->args)) {
            return false;
        }

        $arg = array_shift($this->args);
        if ($this->optionsAllowed) {
            // double dash is a separator between options and args
            if ($arg == '--') {
                $this->optionsAllowed = false;
                return $this->read();
            }

            // long option
            if (substr($arg, 0, 2) == '--') {
                $p = explode('=', $arg, 2);
                return array(
                    'type'  => self::TYPE_OPTION,
                    'name'  => $p[0],
                    'value' => isset($p[1]) ? $p[1] : null,
                );
            }

            // short option
            if (substr($arg, 0, 1) == '-') {
                $letter = substr($arg, 1, 1);
                $value  = null;

                if (strpos($shortOptionsWithValues, $letter) !== false) {
                    // short option with value
                    if (strlen($arg) > 2) { // -xVALUE
                        $value = substr($arg, 2);
                    } else { // -x VALUE
                        if (count($this->args) == 0) {
                            throw new Exception_ParseError('No value for -' . $letter, Exception_ParseError::E_NO_OPTION_VALUE);
                        }

                        $value = array_shift($this->args);
                    }
                } elseif (strlen($arg) > 2) {
                    // short option without value and there is another short option
                    // glued next to it, so we should put the arg back
                    array_unshift($this->args, '-' . substr($arg, 2));
                }

                return array(
                    'type'  => self::TYPE_OPTION,
                    'name'  => '-' . $letter,
                    'value' => $value,
                );
            }
        }

        // no option = param

        // options are only allowed before params
        $this->optionsAllowed = false;

        return array(
            'type'  => self::TYPE_PARAM,
            'value' => $arg,
        );
    }
}
