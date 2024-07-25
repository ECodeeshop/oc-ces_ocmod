<?php
namespace Opencart\Admin\Controller\Extension\CesOcmod\Marketplace;

if (defined('DIR_EXTENSION')) {
	require_once DIR_EXTENSION . 'ces_ocmod/system/engine/controller.php';
} else {
	require_once DIR_SYSTEM . 'extension/ces_ocmod/system/engine/controller.php';
}

/**
 * Class Install
 *
 * @package Opencart\Admin\Controller\Extension\CesOcmod\Marketplace
 */
class Install extends \Opencart\System\Engine\Extension\CesOcmod\Controller
{
	public function install() {
		$this->load->language($this->ePathInstall);

		$json = array();

		$extension_install_id = $this->getExtensionId();

		if (!$this->user->hasPermission('modify', $this->ePathInstall)) {
			$json['error'] = $this->language->get('error_permission');
		}

		// Make sure the file name is stored in the session.
		if (!isset($this->session->data['install'])) {
			$json['error'] = $this->language->get('error_file');
		} elseif (!is_file(DIR_STORAGE_MARKETPLACE . $this->session->data['install'] . '.tmp')) {
			$json['error'] = $this->language->get('error_file');
		}

		if (!$json) {
			$json['text'] = $this->language->get('text_unzip');

			$json['next'] = str_replace('&amp;', '&', $this->url->link($this->ePathInstall . $this->separator . 'unzip', 'user_token=' . $this->session->data['user_token'] . '&extension_install_id=' . $extension_install_id, true));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function unzip() {
		$this->load->language($this->ePathInstall);

		$json = array();

		$extension_install_id = $this->getExtensionId();

		if (!$this->user->hasPermission('modify', $this->ePathInstall)) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!isset($this->session->data['install'])) {
			$json['error'] = $this->language->get('error_file');
		} elseif (!is_file(DIR_STORAGE_MARKETPLACE . $this->session->data['install'] . '.tmp')) {
			$json['error'] = $this->language->get('error_file');
		}

		// Sanitize the filename
		if (!$json) {
			$file = DIR_STORAGE_MARKETPLACE . $this->session->data['install'] . '.tmp';

			// Unzip the files
			$zip = new \ZipArchive();

			if ($zip->open($file)) {
				$zip->extractTo(DIR_STORAGE_MARKETPLACE . 'tmp-' . $this->session->data['install']);
				$zip->close();
			} else {
				$json['error'] = $this->language->get('error_unzip');
			}

			// Remove Zip
			unlink($file);

			$json['text'] = $this->language->get('text_move');

			$json['next'] = str_replace('&amp;', '&', $this->url->link($this->ePathInstall . $this->separator . 'move', 'user_token=' . $this->session->data['user_token'] . '&extension_install_id=' . $extension_install_id, true));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function move() {
		$this->load->language($this->ePathInstall);

		$json = array();

		$extension_install_id = $this->getExtensionId();

		if (!$this->user->hasPermission('modify', $this->ePathInstall)) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!isset($this->session->data['install'])) {
			$json['error'] = $this->language->get('error_directory');
		} elseif (!is_dir(DIR_STORAGE_MARKETPLACE . 'tmp-' . $this->session->data['install'] . '/')) {
			$json['error'] = $this->language->get('error_directory');
		}

		if (!$json) {
			$directory = DIR_STORAGE_MARKETPLACE . 'tmp-' . $this->session->data['install'] . '/';

			$path = array($directory . '*');
			$files = [];
			while (count($path) != 0) {
				$next = array_shift($path);

				foreach ((array)glob($next) as $file) {
					if (is_dir($file)) {
						$path[] = $file . '/*';
					}

					if (is_file($file)) {
						$files[] = $file;
					}
				}

			}

 			if (!empty($files)) {

 				foreach ($files as $file) {
	 				$response = $this->isValidXML(file_get_contents($file));
					if ($response['error']) {
						$json['error'] = $response['error'];
					}
 				}
 			}
		}

		if (!$json) {
			$json['text'] = $this->language->get('text_xml');

			$json['next'] = str_replace('&amp;', '&', $this->url->link($this->ePathInstall . $this->separator . 'xml', 'user_token=' . $this->session->data['user_token'] . '&extension_install_id=' . $extension_install_id, true));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function xml() {
		$this->load->language($this->ePathInstall);

		$json = array();

		$extension_install_id = $this->getExtensionId();

		if (!$this->user->hasPermission('modify', $this->ePathInstall)) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!isset($this->session->data['install'])) {
			$json['error'] = $this->language->get('error_directory');
		} elseif (!is_dir(DIR_STORAGE_MARKETPLACE . 'tmp-' . $this->session->data['install'] . '/')) {
			$json['error'] = $this->language->get('error_directory');
		}

		if (!$json) {
			$file = DIR_STORAGE_MARKETPLACE . 'tmp-' . $this->session->data['install'] . '/install.xml';

			if (is_file($file)) {
				$this->load->model($this->ePathModificationModel);

				// If xml file just put it straight into the DB
				$xml = file_get_contents($file);

				$response = $this->isValidXML($xml);
				$data = $response['data'];

				if ($response['error']) {
					$json['error'] = $response['error'];
				} else {
					try {
						$name = $data['name'];
						$code = $data['code'];
						$author = $data['author'];
						$version = $data['version'];
						$link = $data['link'];


		                if ($code) {
		                    // Check to see if the modification is already installed or not.
		                    $modification_info = $this->model_extension_ces_ocmod_setting_modification->getModificationByCode($code);

		                    if ($modification_info) {
		                        $this->model_extension_ces_ocmod_setting_modification->deleteModification($modification_info['modification_id']);
		                    }
		                }

						if (!$json) {

							$modification_data = array(
								'extension_install_id' => $extension_install_id,
								'name'                 => $name,
								'code'                 => $code,
								'author'               => $author,
								'version'              => $version,
								'link'                 => $link,
								'xml'                  => $xml,
								'status'               => 1
							);

							$this->model_extension_ces_ocmod_setting_modification->addModification($modification_data);
						}
					} catch (Exception $exception) {
						$json['error'] = sprintf($this->language->get('error_exception'), $exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine());
					}
				}
			}
		}

		if (!$json) {
			$json['text'] = $this->language->get('text_remove');

			$json['next'] = str_replace('&amp;', '&', $this->url->link($this->ePathInstall . $this->separator . 'remove', 'user_token=' . $this->session->data['user_token'], true));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function remove() {
		$this->load->language($this->ePathInstall);

		$json = array();

		if (!$this->user->hasPermission('modify', $this->ePathInstall)) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!isset($this->session->data['install'])) {
			$json['error'] = $this->language->get('error_directory');
		}

		if (!$json) {
			$directory = DIR_STORAGE_MARKETPLACE . 'tmp-' . $this->session->data['install'] . '/';

			if (is_dir($directory)) {
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

				if (is_dir($directory)) {
					rmdir($directory);
				}
			}

			$file = DIR_STORAGE_MARKETPLACE . $this->session->data['install'] . '.tmp';

			if (is_file($file)) {
				unlink($file);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function uninstall() {
		$this->load->language($this->ePathInstall);

		$json = array();

		$extension_install_id = $this->getExtensionId();

		if (!$this->user->hasPermission('modify', $this->ePathInstall)) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/extension');

			$results = $this->model_setting_extension->getExtensionPathsByExtensionInstallId($extension_install_id);

			rsort($results);

			foreach ($results as $result) {
				$source = '';

				// Check if the copy location exists or not
				if (substr($result['path'], 0, 5) == 'admin') {
					$source = DIR_APPLICATION . substr($result['path'], 6);
				}

				if (substr($result['path'], 0, 7) == 'catalog') {
					$source = DIR_CATALOG . substr($result['path'], 8);
				}

				if (substr($result['path'], 0, 5) == 'image') {
					$source = DIR_IMAGE . substr($result['path'], 6);
				}

				if (substr($result['path'], 0, 14) == 'system/library') {
					$source = DIR_SYSTEM . 'library/' . substr($result['path'], 15);
				}

				if (is_file($source)) {
					unlink($source);
				}

				if (is_dir($source)) {
					// Get a list of files ready to upload
					$files = array();

					$path = array($source);

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
						if (is_dir($file)) {
							if ($this->isDirEmpty($file)) {
								rmdir($file);
							}
						}
					}

					if (is_file($source)) {
						unlink($source);
					}

					if (is_dir($source)) {
						if ($this->isDirEmpty($source)) {
							rmdir($source);
						}
					}
				}

				$this->model_setting_extension->deleteExtensionPath($result['extension_path_id']);
			}

			// Remove the install
			$this->model_setting_extension->deleteExtensionInstall($extension_install_id);

			// Remove any xml modifications
			$this->load->model($this->ePathModificationModel);

			$this->model_extension_ces_ocmod_setting_modification->deleteModificationsByExtensionInstallId($extension_install_id);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	private function isDirEmpty ($dir_name) {
		if (!is_dir($dir_name)) {
			return false;
		}
		foreach (scandir($dir_name) as $dir_file)
		{
			if (!in_array($dir_file, array('.','..'))) {
				return false;
			}
		}
		return true;
	}

	private function getExtensionId()
	{
		if (isset($this->request->get['extension_install_id'])) {
			$extension_install_id = $this->request->get['extension_install_id'];
		} else {
			$extension_install_id = 0;
		}

		return $extension_install_id;
	}
}
