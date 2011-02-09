<?php

namespace cliff;

class Exception extends \Exception
{
	const E_NO_OPTION_VALUE = 1;
	const E_WRONG_OPTION    = 7;

	const E_NO_PARAM        = 2;
	const E_WRONG_PARAM     = 4;
	const E_TOO_MANY_PARAMS = 8;

	const E_CONFIG_ERROR    = 5;
}