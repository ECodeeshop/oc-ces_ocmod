<?php

namespace Opencart\System\Engine\Extension\CesOcmod;

use Opencart\System\Library\Extension\CesOcmod\Ces\cesOcmodLib;

if (defined('DIR_EXTENSION')) {
	require_once DIR_EXTENSION . 'ces_ocmod/system/library/ces/cesOcmodLib.php';
} else {
	require_once DIR_SYSTEM . 'library/ces/cesOcmodLib.php';
}

/**
 * Class Controller
 *
 * @package Opencart\System\Engine\Extension\CesOcmod
 */
class Controller extends \Opencart\System\Engine\Controller
{
	use cesOcmodLib;
}
