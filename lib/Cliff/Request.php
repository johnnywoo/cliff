<?php

namespace Cliff;

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/Parser.php';
require_once __DIR__ . '/Config/Item.php';
require_once __DIR__ . '/Config/Option.php';
require_once __DIR__ . '/Config/Param.php';
require_once __DIR__ . '/Exception/ParseError.php';

class Request
{
    /** @var Config */
    private $config;

    /** @var Parser */
    private $parser;

    /** @var Request */
    private $detectedBranchRequest;

    public $incompleteMode   = false;
    public $disableCallbacks = false;

    /** @var Config_Option[] */
    public $options = array();
    /** @var Config_Param[] */
    public $paramsStack  = array();

    protected $registeredItems = array();
    protected $defaultsLookup  = array();

    /** @var array */
    private $target;

    public function load(Config $config, Parser $parser, &$target)
    {
        $this->config = $config;
        $this->parser = $parser;
        $this->target =& $target;

        $shortOptionsWithValues = $this->config->getShortOptionsWithValues();

        $this->registeredItems = array();
        $this->defaultsLookup  = array();
        $this->options         = $this->config->getOptions();
        $this->paramsStack     = $this->config->getParams();

        foreach ($this->config->getItems() as $item) {
            $this->setRequestVal($item);
        }

        while ($item = $this->parser->read($shortOptionsWithValues)) {
            if ($item['type'] == Parser::TYPE_OPTION) {
                $this->registerOption($this->options, $item);
            }

            if ($item['type'] == Parser::TYPE_PARAM) {
                $this->registerParam($this->paramsStack, $item);
            }
        }

        // removing the reference from $_REQUEST
        unset($this->target);
    }

    protected function setRequestVal(Config_Item $item, $value = null)
    {
        if (!($item->visibility & Config::V_REQUEST)) {
            return;
        }

        $name    = $item->name;
        $isArray = $item->isArray;

        if (func_num_args() == 1) {
            // initialization mode
            $value = $item->default;
            // absent value should not be wrapped in array
            $isArray = false;

            $this->defaultsLookup[$name] = true;
        } else {
            // actual value was registered
            $this->registeredItems[] = $item;
        }

        if ($isArray) {
            if (!isset($this->target[$name]) || !is_array($this->target[$name])) {
                $this->target[$name] = array();
            }

            // if default value is an array, we need to clean it up before inserting actual first value
            if (!empty($this->defaultsLookup[$name])) {
                $this->target[$name] = array();
                unset($this->defaultsLookup[$name]);
            }

            $this->target[$name][] = $value;
        } else {
            $this->target[$name] = $value;
        }
    }

    protected function isRegistered(Config_Item $item)
    {
        return in_array($item, $this->registeredItems);
    }

    protected function registerOption($options, $item)
    {
        if (isset($options[$item['name']])) {
            $option = $options[$item['name']]; // item.name is actually an alias (--smth)
        } else {
            if (!$this->config->allowUnknownOptions) {
                throw new Exception_ParseError('Unknown option ' . $item['name'], Exception_ParseError::E_WRONG_OPTION);
            }

            $option = new Config_Option(array(
                'aliases'   => $item['name'],
                'flagValue' => ($item['value'] === null) ? true : null, // unknown flags get TRUE
            ));
        }

        if ($option->needsValue() && $item['value'] === null) {
            throw new Exception_ParseError('No value for ' . $item['name'], Exception_ParseError::E_NO_OPTION_VALUE);
        }

        $value = ($item['value'] === null || $option->forceFlagValue) ? $option->flagValue : $item['value'];

        if (!$option->validate($value)) {
            throw new Exception_ParseError('Incorrect value for ' . $item['name'], Exception_ParseError::E_NO_OPTION_VALUE);
        }

        $this->setRequestVal($option, $value);

        if (!$this->disableCallbacks) {
            $option->runCallback($value);
        }

        return $option;
    }

    protected function registerParam(&$params, $item)
    {
        if (empty($params)) {
            throw new Exception_ParseError('Too many arguments', Exception_ParseError::E_TOO_MANY_PARAMS);
        }

        /** @var $param Config_Param */
        $param = reset($params);

        $value = $item['value'];

        $isValid = $param->validate($value);

        if (!$isValid && !$param->isRequired) {
            // okay, it is not valid, but the param is not required,
            // so we just continue to the next param
            array_shift($params);
            return $this->registerParam($params, $item);
        }

        if ($param->isArray) {
            if (!$isValid) {
                // array needs one or more values
                if (!$this->isRegistered($param)) {
                    throw new Exception_ParseError('Unexpected "' . $value . '" in place of ' . $param->name, Exception_ParseError::E_WRONG_PARAM);
                }

                // okay, now it is not valid, but there is something in the array
                // so we just continue to the next param
                array_shift($params);
                return $this->registerParam($params, $item);
            }

            $this->setRequestVal($param, $value);
        } else {
            if (!$isValid) {
                throw new Exception_ParseError('Unexpected "' . $value . '" in place of ' . $param->name, Exception_ParseError::E_WRONG_PARAM);
            }

            $this->setRequestVal($param, $value);
            array_shift($params);
        }

        if (!$this->disableCallbacks) {
            $param->runCallback($value);
        }

        if ($param->useForCommands) {
            $branch = $this->config->getBranch($value);
            if (!$branch) {
                throw new Exception_ParseError('Unknown command "' . $value . '"');
            }

            // the rest of arguments are treated as a separate request to the subcommand
            /** @var Request $req */
            $req = new static();
            $this->detectedBranchRequest = $req;

            // options are allowed again
            $this->parser->allowOptions();

            $req->load($branch, $this->parser, $this->target[$value]);
        }

        return $param;
    }

    public function getInnermostBranchConfig()
    {
        if ($this->detectedBranchRequest) {
            return $this->detectedBranchRequest->getInnermostBranchConfig();
        }

        return $this->config;
    }


    /**
     * Validates the request state after it's been loaded
     */
    public function validate()
    {
        if ($this->detectedBranchRequest) {
            $this->detectedBranchRequest->validate();
        }

        // allowed leftower params:
        // non-required params
        // required array params with non-empty arrays
        foreach ($this->paramsStack as $param) {
            if (!$param->isRequired) {
                continue;
            }

            if ($param->isArray && $this->isRegistered($param)) {
                continue;
            }

            throw new Exception_ParseError('Need more arguments', Exception_ParseError::E_NO_PARAM);
        }

        $list = array();
        foreach ($this->options as $option) {
            if (!$option->isRequired || $this->isRegistered($option)) {
                continue;
            }

            $list[] = reset($option->aliases);
        }
        if (!empty($list)) {
            throw new Exception_ParseError('Need value' . (count($list) > 1 ? 's' : '') . ' for ' . join(', ', $list), Exception_ParseError::E_NO_OPTION);
        }
    }

    /**
     * @return Config_Param[]
     */
    public function getAllowedParams()
    {
        if ($this->detectedBranchRequest) {
            return $this->detectedBranchRequest->getAllowedParams();
        }

        if (empty($this->paramsStack)) {
            return array();
        }

        $params = array();
        reset($this->paramsStack);
        do {
            // we need all non-required params and the first required
            /** @var $p Config_Param */
            $p = current($this->paramsStack);
            $params[] = $p;
        } while (next($this->paramsStack) && !$p->isRequired);

        return $params;
    }
}
