<?php

namespace Opencart\Admin\Controller\Extension\CesOcmod\Module;

if (defined('DIR_EXTENSION')) {
	require_once DIR_EXTENSION . 'ces_ocmod/system/engine/controller.php';
} else {
	require_once DIR_SYSTEM . 'extension/ces_ocmod/system/engine/controller.php';
}

require_once(DIR_EXTENSION . 'ces_ocmod/system/engine/config.php');

/**
 * Class CesOcmod
 *
 * @package Opencart\Admin\Controller\Extension\CesOcmod\Module
 */
class CesOcmod extends \Opencart\System\Engine\Extension\CesOcmod\Controller
{
	private $error = array();

	private function writeEmptyFolderFile($path)
	{
		if (!is_dir($path)) {
			mkdir($path, 0777);
		}

		if (!is_file($path . 'index.html')) {
			$myfile = @fopen($path . 'index.html', 'a') or exit('Unable to open file!');
			@fwrite($myfile, '');
			@fclose($myfile);
		}
	}

	public function install()
	{
		$this->writeEmptyFolderFile(DIR_OCMOD);
		$this->writeEmptyFolderFile(DIR_MODIFICATION);
		$this->writeEmptyFolderFile(DIR_STORAGE_MARKETPLACE);
		$this->writeEmptyFolderFile(DIR_OCMOD_BACKUP);

		$this->backupAllFiles();

		$this->load->model('setting/setting');
		$this->load->model('localisation/language');

		$value                            = array();
		$value[$this->eName . '_status']   	   = false;
		$value[$this->eName . '_refresh_modification_on_install']   	   = false;
		$value[$this->eName . '_refresh_modification_on_uninstall']   	   = false;

		$this->model_setting_setting->editSetting($this->eName, $value);

		$this->uninstallEvents();
		$this->installEvents();

		// Alter user rights
		$this->load->model('user/user_group');

		foreach ([$this->ePathInstaller, $this->ePathModification, $this->ePathInstall] as $key) {
			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', $key);

			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', $key);
		}

		$this->load->model($this->ePath);
		$this->model_extension_ces_ocmod_module_ces_ocmod->install();
	}

	public function uninstall()
	{
		$this->deleteDirectory(DIR_OCMOD);
		$this->uninstallEvents();

		$this->load->model($this->ePath);
		$this->model_extension_ces_ocmod_module_ces_ocmod->uninstall();

		$this->restoreAllFiles();
	}

	public function index()
	{
		$data = [];

		$this->load->language($this->ePath);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting($this->eName, $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true),
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->ePath, 'user_token=' . $this->session->data['user_token'], true),
		);

		$data['save'] = $this->url->link('extension/ces_ocmod/module/ces_ocmod.save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

		foreach ([$this->eName . '_status', $this->eName . '_refresh_modification_on_install', $this->eName . '_refresh_modification_on_uninstall'] as $key) {
			if (isset($this->request->post[$key])) {
				$data[$key] = $this->request->post[$key];
			} else {
				$data[$key] = $this->config->get($key);
			}
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$data['full_instructions'] = sprintf($this->language->get('full_instructions'), DIR_MODIFICATION, DIR_EXTENSION . '____MODULE_NAME____/system/install.ocmod.xml');

		$this->response->setOutput($this->load->view($this->ePath, $data));
	}

	/**
	 * @return void
	 */
	public function save(): void
	{
		$this->load->language($this->ePath);

		$json = [];

		if (!$this->user->hasPermission('modify', $this->ePath)) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting($this->eName, $this->request->post);

			if (empty($this->request->post[$this->eName . '_status'])) {
				$this->deleteDirectory(DIR_MODIFICATION);
			} else {
				$this->writeEmptyFolderFile(DIR_MODIFICATION);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function installEvents()
	{

		$this->load->model('setting/event');

		$events = $this->getEvents();

		if (substr(VERSION, 0, 7) < '4.0.1.0') {
			foreach ($events as $event) {
				$this->model_setting_event->addEvent($event['code'], $event['description'], $event['trigger'], $event['action'], $event['status'], $event['sort_order']);
			}
		} else {
			foreach ($events as $event) {
				$this->model_setting_event->addEvent($event);
			}
		}
	}

	public function uninstallEvents()
	{
		$this->load->model('setting/event');

		$events = $this->getEvents();
		foreach ($events as $event) {
			$this->model_setting_event->deleteEventByCode($event['code']);
		}
	}

	public function getEvents()
	{
		if (substr(VERSION, 0, 7) < '4.0.2.0') {
			$separator = '/';
		} else {
			$separator = '.';
		}

		return array(
			[
				'code' => $this->eName . '_admin_view_common_column_left_before',
				'description' => 'Column Left Menu',
				'trigger' => 'admin/view/common/column_left/before',
				'action' => $this->ePathEvent . '/ces_ocmod' .  $separator . 'view_common_column_left_before',
				'status' => 1,
				'sort_order' => 0,
			],
			[
				'code' => $this->eName . '_admin_controller_module_install_after',
				'description' => 'Modification Refresh on Module Install',
				'trigger' => 'admin/controller/extension/module.install/after',
				'action' => $this->ePathEvent . '/ces_ocmod' .  $separator . 'controller_module_install_after',
				'status' => 1,
				'sort_order' => 0,
			],
			[
				'code' => $this->eName . '_admin_controller_module_uninstall_after',
				'description' => 'Modification Refresh on Module Uninstall',
				'trigger' => 'admin/controller/extension/module.uninstall/after',
				'action' => $this->ePathEvent . '/ces_ocmod' .  $separator . 'controller_module_uninstall_after',
				'status' => 1,
				'sort_order' => 0,
			],
		);
	}

	protected function validate()
	{
		if (!$this->user->hasPermission('modify', $this->ePath)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}


	private function backupAllFiles()
	{
		$this->backupFile(
			'autoloader',
			DIR_SYSTEM . 'engine/',
			['if (isset($file) && is_file($file)) {', 'namespace Opencart\System\Engine;'],
			['		$file = modification($file);', "require_once(DIR_EXTENSION . 'ces_ocmod/system/engine/config.php');"],
		);
	}

	public function backupFile($backupFileName, $path, $find, $replace, $extension = 'php')
	{
		$backupDir = DIR_OCMOD_BACKUP;
		$backupFileNameFull = $backupFileName . '.' . $extension;
		$backupFile = $path . $backupFileNameFull;
		$backupFileNameFullNew = $backupFileName . '.txt';
		$backupFileNew = $path . $backupFileNameFullNew;

		if (file_exists($backupFile)) {
			if (file_exists($backupFileNew)) {
				$backupFileF = file_get_contents($backupFileNew);
			} else {
				$backupFileF = file_get_contents($backupFile);
				// rename originalfile file
				rename($backupFile, $backupFileNew);
				file_put_contents($backupDir . '/' . $backupFileNameFullNew, $backupFileF);
			}

			$overideFileText = $backupFileF;
			foreach (array_keys($find) as $index) {
				// Find the position of $find[$index]
				$position = strpos($overideFileText, $find[$index]);

				// Check if the string exists
				if ($position !== false) {
					// Insert " current" on a new line after the found position
					$overideFileText = substr($overideFileText, 0, $position + strlen($find[$index])) . PHP_EOL . $replace[$index] . substr($overideFileText, $position + strlen($find[$index]));
				}
			}

			file_put_contents($backupFile, $overideFileText);
		}

		return true;
	}

	public function restoreFile($backupFileName, $path, $find, $replace, $extension = 'php')
	{
		$backupDir = DIR_OCMOD_BACKUP;
		$backupFileNameFull = $backupFileName . '.' . $extension;
		$originalFilePath = $path . $backupFileNameFull;

		$backupFileNameFullNew = $backupFileName . '.txt';
		$restoreFilePath = $backupDir . '/' . $backupFileNameFullNew;
		$restoreOriginalMainFilePath = $path . $backupFileNameFullNew;
		$originalFilePathToCopy = '';

		if (file_exists($restoreFilePath)) {
			$originalFilePathToCopy = $restoreFilePath;
		} elseif (file_exists($restoreOriginalMainFilePath)) {
			$originalFilePathToCopy = $restoreOriginalMainFilePath;
		}

		if ($originalFilePathToCopy) {
			$restoreFile = file_get_contents($originalFilePathToCopy);
			$overideFileText = str_replace($find, $replace, $restoreFile);
			file_put_contents($originalFilePath, $overideFileText);
		}

		return true;
	}

	private function restoreAllFiles()
	{
		$this->restoreFile(
			'autoloader',
			DIR_SYSTEM . 'engine/',
			['$file = modification($file);', "require_once(DIR_EXTENSION . 'ces_ocmod/system/engine/config.php');"],
			['', ''],
		);

		return true;
	}

	private function deleteDirectory($dir) {
	    if (!file_exists($dir)) {
	        return false;
	    }

	    if (!is_dir($dir)) {
	        return unlink($dir);
	    }

	    $items = scandir($dir);
	    foreach ($items as $item) {
	        if ($item == '.' || $item == '..') {
	            continue;
	        }

	        $path = $dir . DIRECTORY_SEPARATOR . $item;
	        if (is_dir($path)) {
	            $this->deleteDirectory($path);
	        } else {
	            unlink($path);
	        }
	    }

	    return rmdir($dir);
	}
}

