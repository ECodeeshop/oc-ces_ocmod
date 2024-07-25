<?php

// constants
define('DIR_OCMOD', DIR_STORAGE . 'ocmod/');
define('DIR_STORAGE_MARKETPLACE', DIR_OCMOD . 'marketplace/');
define('DIR_OCMOD_BACKUP', DIR_OCMOD . 'changed_backup/');
define('DIR_MODIFICATION', DIR_OCMOD . 'modification/');
// Core Installation of ocmod module
define('DIR_MODIFICATION_DEFAULT_FILE', DIR_EXTENSION . 'ces_ocmod/system/modification.xml');

// fixed for twig as core remove root path
if (!function_exists('removePath')) {
	function removePath($file, $remove_path)
	{
		if (!$remove_path) {
			return $file;
		}
		return substr($file, strlen($remove_path) + 1);
	}
}

// Modification Override
if (!function_exists('modification')) {
	function modification($filename, $remove_path = '')
	{
		if (strpos($filename, 'vendor') === false) {
			$file = $filename;

			if (defined('DIR_MODIFICATION') && substr($filename, strlen(DIR_SYSTEM))) {
				// modification path
				$file = str_replace(DIR_OPENCART, DIR_MODIFICATION, $filename);
				if (is_file($file)) {
					return removePath($file, $remove_path);
				}
				if (is_file(DIR_MODIFICATION . $file)) {
					$file = removePath(DIR_MODIFICATION . $file);
					return $file;
				}
			}
		}

		return $filename;
	}
}
