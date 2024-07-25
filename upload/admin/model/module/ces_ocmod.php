<?php
namespace Opencart\Admin\Model\Extension\CesOcmod\Module;
use \Opencart\System\Helper AS Helper;

class CesOcmod extends \Opencart\System\Engine\Model {

	public function install() {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "modification` (
		  `modification_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		  `extension_install_id` int(11) NOT NULL,
		  `name` varchar(64) NOT NULL,
		  `code` varchar(64) NOT NULL,
		  `author` varchar(64) NOT NULL,
		  `version` varchar(32) NOT NULL,
		  `link` varchar(255) NOT NULL,
		  `xml` mediumtext NOT NULL,
		  `status` tinyint(1) NOT NULL,
		  `date_added` datetime NOT NULL,
		  `date_modified` datetime NOT NULL
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");

		$this->db->query("ALTER TABLE `" . DB_PREFIX . "modification` MODIFY `modification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;");
  	}

	public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "modification`");
  	}
}