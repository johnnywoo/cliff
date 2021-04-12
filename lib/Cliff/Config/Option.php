<?php

namespace Cliff;

require_once __DIR__ . '/Item.php';
require_once __DIR__ . '/../Exception.php';

class Config_Option extends Config_Item
{
    public $flagValue = null;

    public $forceFlagValue = false;

    public $aliases = array();

    public function __construct(array $props = array())
    {
        if (isset($props['aliases']) && !is_array($props['aliases'])) {
            $props['aliases'] = static::makeAliasesList($props['aliases']);
        }

        if (!isset($props['name'])) {
            $props['name'] = static::makeDefaultName($props);
        }

        parent::__construct($props);
    }

    public function needsValue()
    {
        return $this->flagValue === null;
    }

    public function getShortAliasLetters()
    {
        $list = array();
        foreach ($this->aliases as $alias) {
            if (strlen($alias) == 2) {
                $list[] = substr($alias, 1);
            }
        }
        return $list;
    }



    public static function makeDefaultName($props)
    {
        if (empty($props['aliases'])) {
            throw new Exception('Flag/option needs at least one alias');
        }

        return ltrim(reset($props['aliases']), '-');
    }

    public static function makeAliasesList($str)
    {
        $list = array();
        foreach (preg_split('/\s+/', $str, -1, PREG_SPLIT_NO_EMPTY) as $alias) {
            $len = strlen($alias);

            $c1 = substr($alias, 0, 1);
            $c2 = substr($alias, 1, 1);

            // --long-option => --long-option
            if ($len > 2 && $c1 == '-' && $c2 == '-') {
                $list[] = $alias;
                continue;
            }

            // -short => -s -h -o -r -t
            if ($len > 1 && $c1 == '-' && $c2 != '-') {
                for ($i = 1; $i < $len; $i++) {
                    $list[] = '-' . substr($alias, $i, 1);
                }
                continue;
            }

            throw new Exception('Config error: bad option alias ' . $alias);
        }
        return $list;
    }
}
