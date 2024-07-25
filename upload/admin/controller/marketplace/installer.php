<?php
namespace Opencart\Admin\Controller\Extension\CesOcmod\Marketplace;

if (defined('DIR_EXTENSION')) {
	require_once DIR_EXTENSION . 'ces_ocmod/system/engine/controller.php';
} else {
	require_once DIR_SYSTEM . 'extension/ces_ocmod/system/engine/controller.php';
}

/**
 * Class Installer
 *
 * @package Opencart\Admin\Controller\Extension\CesOcmod\Marketplace
 */
class Installer extends \Opencart\System\Engine\Extension\CesOcmod\Controller
{
	/**
	 * @return void
	 */
	public function index(): void {
		$this->load->language($this->ePathInstaller);

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->ePathInstaller, 'user_token=' . $this->session->data['user_token'])
		];

		// Use the  for the max file size
		$data['error_upload_size'] = sprintf($this->language->get('error_file_size'), ini_get('upload_max_filesize'));

		$data['config_file_max_size'] = ((int)preg_filter('/[^0-9]/', '', ini_get('upload_max_filesize')) * 1024 * 1024);

		$data['upload'] = $this->url->link('tool/installer.upload', 'user_token=' . $this->session->data['user_token']);
		$data['upload_ocmod'] = html_entity_decode($this->url->link($this->ePathInstaller . $this->separator . 'upload', 'user_token=' . $this->session->data['user_token']));
		$data['installer_list'] = html_entity_decode($this->url->link($this->ePathInstaller . $this->separator . 'list', 'user_token=' . $this->session->data['user_token']));

		if (isset($this->request->get['filter_extension_id'])) {
			$data['filter_extension_download_id'] = (int)$this->request->get['filter_extension_download_id'];
		} else {
			$data['filter_extension_download_id'] = '';
		}

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->ePathInstaller, $data));
	}

	/**
	 * @return void
	 */
	public function upload(): void {
		$this->load->language($this->ePathInstaller);

		$json = [];

		// 1. Validate the file uploaded.
		if (isset($this->request->files['file']['name'])) {
			$filename = basename($this->request->files['file']['name']);

			// 2. Validate the filename.
			if ((oc_strlen($filename) < 1) || (oc_strlen($filename) > 128)) {
				$json['error'] = $this->language->get('error_filename');
			}

			// 3. Validate is ocmod file.
			if (substr($filename, -10) != '.ocmod.zip') {
				$json['error'] = $this->language->get('error_file_type');
			}

			// 4. check if there is already a file
			$file = DIR_STORAGE_MARKETPLACE . $filename;

			if (is_file($file)) {
				$json['error'] = $this->language->get('error_file_exists');

				unlink($this->request->files['file']['tmp_name']);
			}

			if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
				$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
			}

			// if ($this->model_setting_extension->getInstallByCode(basename($filename, '.ocmod.zip'))) {
			// 	$json['error'] = $this->language->get('error_installed');
			// }
		} else {
			$json['error'] = $this->language->get('error_upload');
		}

		if (empty($json['error'])) {

			// Check user has permission
			if (!$this->user->hasPermission('modify', $this->ePathInstaller)) {
				$json['error'] = $this->language->get('error_permission');
			}

			// Check if there is a install zip already there
			$files = glob(DIR_STORAGE_MARKETPLACE . '*.tmp');

			foreach ($files as $file) {
				if (is_file($file) && (filectime($file) < (time() - 5))) {
					unlink($file);
				}

				if (is_file($file)) {
					$json['error'] = $this->language->get('error_install');

					break;
				}
			}

			// Check for any install directories
			$directories = glob(DIR_STORAGE_MARKETPLACE . 'tmp-*');

			foreach ($directories as $directory) {
				if (is_dir($directory) && (filectime($directory) < (time() - 5))) {
					// Get a list of files ready to upload
					$files = array();

					$path = array($directory);

					while (count($path) != 0) {
						$next = array_shift($path);

						// We have to use scandir function because glob will not pick up dot files.
						foreach (array_diff(scandir($next), array('.', '..')) as $file) {
							$file = $next . '/' . $file;

							if (is_dir($file)) {
								$path[] = $file;
							}

							$files[] = $file;
						}
					}

					rsort($files);

					foreach ($files as $file) {
						if (is_file($file)) {
							unlink($file);
						} elseif (is_dir($file)) {
							rmdir($file);
						}
					}

					rmdir($directory);
				}

				if (is_dir($directory)) {
					$json['error'] = $this->language->get('error_install');

					break;
				}
			}

			if (isset($this->request->files['file']['name'])) {
				if (substr($this->request->files['file']['name'], -10) != '.ocmod.zip') {
					$json['error'] = $this->language->get('error_filetype');
				}

				if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
					$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
				}
			} else {
				$json['error'] = $this->language->get('error_upload');
			}

			if (!$json) {
				$this->session->data['install'] = uniqid();

				$file = DIR_STORAGE_MARKETPLACE . $this->session->data['install'] . '.tmp';

				move_uploaded_file($this->request->files['file']['tmp_name'], $file);

				if (is_file($file)) {

					$extension_install_id = 0;
					// $extension_install_id = $this->model_setting_extension->addExtensionInstall($this->request->files['file']['name']);

					$json['text'] = $this->language->get('text_install');

					$json['next'] = str_replace('&amp;', '&', $this->url->link($this->ePathInstall . $this->separator . 'install', 'user_token=' . $this->session->data['user_token'] . '&extension_install_id=' . $extension_install_id, true));
				} else {
					$json['error'] = $this->language->get('error_file');
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

}
