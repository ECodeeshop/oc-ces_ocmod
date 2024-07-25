<?php
namespace Opencart\Admin\Controller\Extension\CesOcmod\Event;

if (defined('DIR_EXTENSION')) {
	require_once DIR_EXTENSION . 'ces_ocmod/system/engine/controller.php';
} else {
	require_once DIR_SYSTEM . 'extension/ces_ocmod/system/engine/controller.php';
}

/**
 * Class CesOcmod
 *
 * @package Opencart\Admin\Controller\Extension\CesOcmod\Event
 */
class CesOcmod extends \Opencart\System\Engine\Extension\CesOcmod\Controller
{
	// refresh modification on module install
	public function controller_module_install_after(string &$route, mixed &$args, $output): void {
		if ($this->config->get($this->eName . '_refresh_modification_on_install') && is_file(DIR_EXTENSION . $this->request->get['code'] . '/system/install.ocmod.xml')) {
			$this->load->controller($this->ePathMarketplace . '/modification.refresh', ['no_redirect' => 1]);
		}
	}

	// refresh modification on module uninstall
	public function controller_module_uninstall_after(string &$route, mixed &$args, $output): void {
		if ($this->config->get($this->eName . '_refresh_modification_on_uninstall') && is_file(DIR_EXTENSION . $this->request->get['code'] . '/system/install.ocmod.xml')) {
			$this->load->controller($this->ePathMarketplace . '/modification.refresh', ['no_redirect' => 1]);
		}
	}

	// Column left menu
	public function view_common_column_left_before(string &$route, mixed &$args): void {
		if ($this->config->get($this->eName . '_status') && $this->user->hasPermission('access', $this->ePathModification)) {

			$this->load->language($this->ePathInstaller);
			$this->load->language($this->ePathModification);

			$children = [];

			$children[] = [
				'id'       => 'menu-ocmod-installer',
				'icon'	   => 'fas fa-home',
				'name'	   => $this->language->get('text_installer'),
				'href'     => $this->url->link($this->ePathInstaller, 'user_token=' . $this->session->data['user_token']),
				'children' => []
			];

			$children[] = [
				'id'       => 'menu-ocmod-modifications',
				'icon'	   => 'fas fa-home',
				'name'	   => $this->language->get('text_modifications'),
				'href'     => $this->url->link($this->ePathModification, 'user_token=' . $this->session->data['user_token']),
				'children' => []
			];

			$children[] = [
				'id'       => 'menu-ocmod-settings',
				'icon'	   => 'fas fa-cog',
				'name'	   => $this->language->get('text_settings'),
				'href'     => $this->url->link($this->ePath, 'user_token=' . $this->session->data['user_token']),
				'children' => []
			];

			$menu = [
				'id'       => 'menu-ocmod',
				'icon'	   => 'fas fa-home',
				'name'	   => $this->language->get('text_ocmod'),
				'children' => $children
			];

			array_splice($args['menus'], 1, 0, [
				$menu
			]);
		}
	}
}
