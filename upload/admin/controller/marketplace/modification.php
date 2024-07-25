<?php
namespace Opencart\Admin\Controller\Extension\CesOcmod\Marketplace;

if (defined('DIR_EXTENSION')) {
	require_once DIR_EXTENSION . 'ces_ocmod/system/engine/controller.php';
} else {
	require_once DIR_SYSTEM . 'extension/ces_ocmod/system/engine/controller.php';
}

/**
 * Class Modification
 *
 * Modifcation XML Documentation can be found here:
 * https://github.com/opencart/opencart/wiki/Modification-System
 *
 * @package Opencart\Admin\Controller\Extension\CesOcmod\Marketplace
 */
class Modification extends \Opencart\System\Engine\Extension\CesOcmod\Controller
{
	private $error = array();

	/**
	 * @return void
	 */
	public function commonMethod(): void {
		$this->load->language($this->ePathModification);

		$this->load->model($this->ePathModificationModel);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->ePathModification, 'user_token=' . $this->session->data['user_token'] . $url)
		];

		$data['add'] = $this->url->link($this->ePathModification . $this->separator . 'form', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['action'] = $this->url->link($this->ePathModification . $this->separator . 'list', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['refresh'] = $this->url->link($this->ePathModification . $this->separator . 'refresh', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['clear'] = $this->url->link($this->ePathModification . $this->separator . 'clear', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link($this->ePathModification . $this->separator . 'delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['list'] = $this->getList();

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->ePathModification, $data));
	}

	/**
	 * @return void
	 */
	public function index(): void {
		$this->load->language($this->ePathModification);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->commonMethod();
	}

	/**
	 * @return void
	 */
	public function list(): void {
		$this->load->language($this->ePathModification);

		$this->response->setOutput($this->getList());
	}

	public function delete(): void {
		$this->load->language($this->ePathModification);

		$json = [];

		if (isset($this->request->post['selected'])) {
			$selected = $this->request->post['selected'];
		} else {
			$selected = [];
		}

		if (!$this->user->hasPermission('modify', $this->ePathModification)) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model($this->ePathModificationModel);

			foreach ($selected as $modification_id) {
				$this->model_extension_ces_ocmod_setting_modification->deleteModification($modification_id);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function refresh($data = array()) {
		$this->load->language($this->ePathModification);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model($this->ePathModificationModel);

		if ($this->validate()) {

			$this->saveData($data);

			// fixing 302 issue for refresh modification on module install or uninstall no redirect event
			if (!empty($data['no_redirect'])) {
				return;
			}

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link(!empty($data['redirect']) ? $data['redirect'] : $this->ePathModification, 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->commonMethod();
	}

	public function clear() {
		$this->load->language($this->ePathModification);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model($this->ePathModificationModel);

		if ($this->validate()) {
			$files = array();

			// Make path into an array
			$path = array(DIR_MODIFICATION . '*');

			// While the path array is still populated keep looping through
			while (count($path) != 0) {
				$next = array_shift($path);

				foreach (glob($next) as $file) {
					// If directory add to path array
					if (is_dir($file)) {
						$path[] = $file . '/*';
					}

					// Add the file to the files to be deleted array
					$files[] = $file;
				}
			}

			// Reverse sort the file array
			rsort($files);

			// Clear all modification files
			foreach ($files as $file) {
				if ($file != DIR_MODIFICATION . 'index.html') {
					// If file just delete
					if (is_file($file)) {
						unlink($file);

					// If directory use the remove directory function
					} elseif (is_dir($file)) {
						rmdir($file);
					}
				}
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->ePathModification, 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->commonMethod();
	}

	public function enable() {
		$this->load->language($this->ePathModification);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model($this->ePathModificationModel);

		if (isset($this->request->get['modification_id']) && $this->validate()) {
			$this->model_extension_ces_ocmod_setting_modification->enableModification($this->request->get['modification_id']);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->ePathModification, 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->commonMethod();
	}

	public function disable() {
		$this->load->language($this->ePathModification);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model($this->ePathModificationModel);

		if (isset($this->request->get['modification_id']) && $this->validate()) {
			$this->model_extension_ces_ocmod_setting_modification->disableModification($this->request->get['modification_id']);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->ePathModification, 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->commonMethod();
	}

	public function clearlog() {
		$this->load->language($this->ePathModification);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model($this->ePathModificationModel);

		if ($this->validate()) {
			$handle = fopen(DIR_LOGS . 'ocmod.log', 'w+');

			fclose($handle);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->ePathModification, 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->commonMethod();
	}

	/**
	 * @return string
	 */
	protected function getList(): string {
		if (isset($this->request->get['sort'])) {
			$sort = (string)$this->request->get['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($this->request->get['order'])) {
			$order = (string)$this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['modifications'] = array();

		$filter_data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_pagination_admin'),
			'limit' => $this->config->get('config_pagination_admin')
		];

		$this->load->model($this->ePathModificationModel);

		$modification_total = $this->model_extension_ces_ocmod_setting_modification->getTotalModifications();

		$results = $this->model_extension_ces_ocmod_setting_modification->getModifications($filter_data);

		foreach ($results as $result) {
			$data['modifications'][] = array(
				'modification_id' => $result['modification_id'],
				'name'            => $result['name'],
				'author'          => $result['author'],
				'version'         => $result['version'],
				'status'          => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'date_added'      => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'link'            => $result['link'],
				'edit'          => $this->url->link($this->ePathModification . $this->separator . 'form', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $result['modification_id'], true),
				'enable'          => $this->url->link($this->ePathModification . $this->separator . 'enable', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $result['modification_id'], true),
				'disable'         => $this->url->link($this->ePathModification . $this->separator . 'disable', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $result['modification_id'], true),
				'enabled'         => $result['status']
			);
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		$data['action'] = $this->url->link($this->ePathModification . $this->separator . 'list', 'user_token=' . $this->session->data['user_token'] . $url);

		$data['sort_name'] = $this->url->link($this->ePathModification . $this->separator . 'list', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_author'] = $this->url->link($this->ePathModification . $this->separator . 'list', 'user_token=' . $this->session->data['user_token'] . '&sort=author' . $url, true);
		$data['sort_version'] = $this->url->link($this->ePathModification . $this->separator . 'list', 'user_token=' . $this->session->data['user_token'] . '&sort=version' . $url, true);
		$data['sort_status'] = $this->url->link($this->ePathModification . $this->separator . 'list', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);
		$data['sort_date_added'] = $this->url->link($this->ePathModification . $this->separator . 'list', 'user_token=' . $this->session->data['user_token'] . '&sort=date_added' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $modification_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link($this->ePathModification . $this->separator . 'list', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($modification_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($modification_total - $this->config->get('config_pagination_admin'))) ? $modification_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $modification_total, ceil($modification_total / $this->config->get('config_pagination_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		// Log
		$file = DIR_LOGS . 'ocmod.log';

		if (file_exists($file)) {
			$data['log'] = htmlentities(file_get_contents($file, FILE_USE_INCLUDE_PATH, null));
		} else {
			$data['log'] = '';
		}

		$data['clear_log'] = $this->url->link($this->ePathModification . $this->separator . 'clearlog', 'user_token=' . $this->session->data['user_token'], true);

		return $this->load->view($this->ePathModification . '_list', $data);
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', $this->ePathModification)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	/**
	 * @return void
	 */
	public function form(): void {
		$this->load->language($this->ePathModification);

		$this->document->setTitle($this->language->get('heading_title'));

		$data['text_form'] = !isset($this->request->get['modification_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->ePathModification, 'user_token=' . $this->session->data['user_token'] . $url)
		];

		$data['save'] = $this->url->link($this->ePathModification . $this->separator . 'save', 'user_token=' . $this->session->data['user_token']);
		$data['save_and_refresh_modification_url'] = $this->url->link($this->ePathModification . $this->separator . 'save', 'user_token=' . $this->session->data['user_token'] . '&refresh=1');
		$data['back'] = $this->url->link($this->ePathModification, 'user_token=' . $this->session->data['user_token'] . $url);

		if (isset($this->request->get['modification_id'])) {
			$data['modification_id'] = (int)$this->request->get['modification_id'];
		} else {
			$data['modification_id'] = 0;
		}

		if ($data['modification_id']) {
			$this->load->model($this->ePathModificationModel);

			$modification_info = $this->model_extension_ces_ocmod_setting_modification->getModification($data['modification_id']);
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (!empty($modification_info)) {
			$data['xml'] = str_replace('`', '\`', $modification_info['xml']);
		} else {
			$data['xml'] = '';
		}

		$this->document->addScript('../extension/ces_ocmod/admin/view/javascript/editor.bundle.js');

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->ePathModification . '_form', $data));
	}

	/**
	 * @return void
	 */
	public function save(): void {
		$this->load->language($this->ePathModification);

		$json = $data = [];

		if (!$this->user->hasPermission('modify', $this->ePathModification)) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		$xml = (string) $this->request->post['xml'] ? html_entity_decode($this->request->post['xml']) : '';

		$this->load->model($this->ePathModificationModel);

		if (oc_strlen($xml) < 20) {
			$json['error']['xml'] = $this->language->get('error_name');
		} else {
			$response = $this->isValidXML($xml);
			$data = $response['data'];
			if ($response['error']) {
				$json['error']['xml'] = $response['error'];
			}
		}

		if (isset($json['error']) && !isset($json['error']['warning'])) {
			$json['error']['warning'] = end($json['error']);
		}

		if (!$json) {
			$modification_id = isset($this->request->post['modification_id']) ? (int) $this->request->post['modification_id'] : 0;

			$data['xml'] = html_entity_decode($xml);
			if ($modification_id) {

				$this->model_extension_ces_ocmod_setting_modification->editModification($modification_id, $data);
			} else {
                if (!empty($data['code'])) {
                    // Check to see if the modification is already installed or not.
                    $modification_info = $this->model_extension_ces_ocmod_setting_modification->getModificationByCode($data['code']);

                    if ($modification_info) {
                        $this->model_extension_ces_ocmod_setting_modification->deleteModification($modification_info['modification_id']);
                    }
                }

				// hardcoded
				$data['extension_install_id'] = 0;
				$data['status'] = 1;
				$json['modification_id'] = $this->model_extension_ces_ocmod_setting_modification->addModification($data);
			}

			if (!empty($this->request->get['refresh'])) {
				$this->saveData([]);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	private function saveData($data) {
		// Just before files are deleted, if config settings say maintenance mode is off then turn it on
		$maintenance = $this->config->get('config_maintenance');

		$this->load->model('setting/setting');

		$this->model_setting_setting->editValue('config', 'config_maintenance', true);

		//Log
		$log = array();

		// Clear all modification files
		$files = array();

		// Make path into an array
		$path = array(DIR_MODIFICATION . '*');

		// While the path array is still populated keep looping through
		while (count($path) != 0) {
			$next = array_shift($path);

			foreach (glob($next) as $file) {
				// If directory add to path array
				if (is_dir($file)) {
					$path[] = $file . '/*';
				}

				// Add the file to the files to be deleted array
				$files[] = $file;
			}
		}

		// Reverse sort the file array
		rsort($files);

		// Clear all modification files
		foreach ($files as $file) {
			if ($file != DIR_MODIFICATION . 'index.html') {
				// If file just delete
				if (is_file($file)) {
					unlink($file);

				// If directory use the remove directory function
				} elseif (is_dir($file)) {
					rmdir($file);
				}
			}
		}

		// Begin
		$xml = array();

		// Load the default modification XML
		$xml[] = file_get_contents(DIR_MODIFICATION_DEFAULT_FILE);


		// This is purly for developers so they can run mods directly and have them run without upload after each change.
		// $files = glob(DIR_EXTENSION . '*/system/*.ocmod.xml');
		$extension_codes = $this->model_extension_ces_ocmod_setting_modification->getOtherExtensions('opencart');

		foreach ($extension_codes as $code) {
			$files = glob(DIR_EXTENSION . $code . '/system/*.ocmod.xml');
			if ($files) {
				foreach ($files as $file) {
					$xml[] = file_get_contents($file);
				}
			}
		}

		// Get the default modification file
		$results = $this->model_extension_ces_ocmod_setting_modification->getModifications();

		foreach ($results as $result) {
			if ($result['status']) {
				$xml[] = $result['xml'];
			}
		}

		$modification = array();

		foreach ($xml as $xml) {
			if (empty($xml)){
				continue;
			}

			$dom = new \DOMDocument('1.0', 'UTF-8');
			$dom->preserveWhiteSpace = false;
			$dom->loadXml($xml);

			// Log
			$log[] = 'MOD: ' . $dom->getElementsByTagName('name')->item(0)->textContent;

			// Wipe the past modification store in the backup array
			$recovery = array();

			// Set the a recovery of the modification code in case we need to use it if an abort attribute is used.
			if (isset($modification)) {
				$recovery = $modification;
			}

			$files = $dom->getElementsByTagName('modification')->item(0)->getElementsByTagName('file');

			foreach ($files as $file) {
				$operations = $file->getElementsByTagName('operation');

				$files = explode('|', str_replace("\\", '/', $file->getAttribute('path')));

				foreach ($files as $file) {
					$path = '';

					// Get the full path of the files that are going to be used for modification
					if ((substr($file, 0, 7) == 'catalog')) {
						$path = DIR_CATALOG . substr($file, 8);
					}

					if ((substr($file, 0, 5) == 'admin')) {
						$path = DIR_APPLICATION . substr($file, 6);
					}

					if ((substr($file, 0, 6) == 'system')) {
						$path = DIR_SYSTEM . substr($file, 7);
					}

					if ($path) {
						$files = glob($path, GLOB_BRACE);

						if ($files) {
							foreach ($files as $file) {
								// Get the key to be used for the modification cache filename.
								if (substr($file, 0, strlen(DIR_CATALOG)) == DIR_CATALOG) {
									$key = 'catalog/' . substr($file, strlen(DIR_CATALOG));
								}

								if (substr($file, 0, strlen(DIR_APPLICATION)) == DIR_APPLICATION) {
									$key = 'admin/' . substr($file, strlen(DIR_APPLICATION));
								}

								if (substr($file, 0, strlen(DIR_SYSTEM)) == DIR_SYSTEM) {
									$key = 'system/' . substr($file, strlen(DIR_SYSTEM));
								}

								// If file contents is not already in the modification array we need to load it.
								if (!isset($modification[$key])) {
									$content = file_get_contents($file);

									$modification[$key] = preg_replace('~\r?\n~', "\n", $content);
									$original[$key] = preg_replace('~\r?\n~', "\n", $content);

									// Log
									$log[] = PHP_EOL . 'FILE: ' . $key;

								} else {
									// Log
									$log[] = PHP_EOL . 'FILE: (sub modification) ' . $key;

								}

								foreach ($operations as $operation) {
									$error = $operation->getAttribute('error');

									// Ignoreif
									$ignoreif = $operation->getElementsByTagName('ignoreif')->item(0);

									if ($ignoreif) {
										if ($ignoreif->getAttribute('regex') != 'true') {
											if (strpos($modification[$key], $ignoreif->textContent) !== false) {
												continue;
											}
										} else {
											if (preg_match($ignoreif->textContent, $modification[$key])) {
												continue;
											}
										}
									}

									$status = false;

									// Search and replace
									if ($operation->getElementsByTagName('search')->item(0)->getAttribute('regex') != 'true') {
										// Search
										$search = $operation->getElementsByTagName('search')->item(0)->textContent;
										$trim = $operation->getElementsByTagName('search')->item(0)->getAttribute('trim');
										$index = $operation->getElementsByTagName('search')->item(0)->getAttribute('index');

										// Trim line if no trim attribute is set or is set to true.
										if (!$trim || $trim == 'true') {
											$search = trim($search);
										}

										// Add
										$add = $operation->getElementsByTagName('add')->item(0)->textContent;
										$trim = $operation->getElementsByTagName('add')->item(0)->getAttribute('trim');
										$position = $operation->getElementsByTagName('add')->item(0)->getAttribute('position');
										$offset = $operation->getElementsByTagName('add')->item(0)->getAttribute('offset');

										if ($offset == '') {
											$offset = 0;
										}

										// Trim line if is set to true.
										if ($trim == 'true') {
											$add = trim($add);
										}

										// Log
										$log[] = 'CODE: ' . $search;

										// Check if using indexes
										if ($index !== '') {
											$indexes = explode(',', $index);
										} else {
											$indexes = array();
										}

										// Get all the matches
										$i = 0;

										$lines = explode("\n", $modification[$key]);

										for ($line_id = 0; $line_id < count($lines); $line_id++) {
											$line = $lines[$line_id];

											// Status
											$match = false;

											// Check to see if the line matches the search code.
											if (stripos($line, $search) !== false) {
												// If indexes are not used then just set the found status to true.
												if (!$indexes) {
													$match = true;
												} elseif (in_array($i, $indexes)) {
													$match = true;
												}

												$i++;
											}

											// Now for replacing or adding to the matched elements
											if ($match) {
												switch ($position) {
													default:
													case 'replace':
														$new_lines = explode("\n", $add);

														if ($offset < 0) {
															array_splice($lines, $line_id + $offset, abs($offset) + 1, array(str_replace($search, $add, $line)));

															$line_id -= $offset;
														} else {
															array_splice($lines, $line_id, $offset + 1, array(str_replace($search, $add, $line)));
														}
														break;
													case 'before':
														$new_lines = explode("\n", $add);

														array_splice($lines, $line_id - $offset, 0, $new_lines);

														$line_id += count($new_lines);
														break;
													case 'after':
														$new_lines = explode("\n", $add);

														array_splice($lines, ($line_id + 1) + $offset, 0, $new_lines);

														$line_id += count($new_lines);
														break;
												}

												// Log
												$log[] = 'LINE: ' . $line_id;

												$status = true;
											}
										}

										$modification[$key] = implode("\n", $lines);
									} else {
										$search = trim($operation->getElementsByTagName('search')->item(0)->textContent);
										$limit = $operation->getElementsByTagName('search')->item(0)->getAttribute('limit');
										$replace = trim($operation->getElementsByTagName('add')->item(0)->textContent);

										// Limit
										if (!$limit) {
											$limit = -1;
										}

										// Log
										$match = array();

										preg_match_all($search, $modification[$key], $match, PREG_OFFSET_CAPTURE);

										// Remove part of the the result if a limit is set.
										if ($limit > 0) {
											$match[0] = array_slice($match[0], 0, $limit);
										}

										if ($match[0]) {
											$log[] = 'REGEX: ' . $search;

											for ($i = 0; $i < count($match[0]); $i++) {
												$log[] = 'LINE: ' . (substr_count(substr($modification[$key], 0, $match[0][$i][1]), "\n") + 1);
											}

											$status = true;
										}

										// Make the modification
										$modification[$key] = preg_replace($search, $replace, $modification[$key], $limit);
									}

									if (!$status) {
										// Abort applying this modification completely.
										if ($error == 'abort') {
											$modification = $recovery;
											// Log
											$log[] = 'NOT FOUND - ABORTING!';
											break 5;
										}
										// Skip current operation or break
										elseif ($error == 'skip') {
											// Log
											$log[] = 'NOT FOUND - OPERATION SKIPPED!';
											continue;
										}
										// Break current operations
										else {
											// Log
											$log[] = 'NOT FOUND - OPERATIONS ABORTED!';
										 	break;
										}
									}
								}
							}
						}
					}
				}
			}

			// Log
			$log[] = '----------------------------------------------------------------';
		}

		// Log
		$ocmod = new \Opencart\System\Library\Log('ocmod.log');
		$ocmod->write(implode("\n", $log));

		// Write all modification files
		foreach ($modification as $key => $value) {
			// Only create a file if there are changes
			if ($original[$key] != $value) {
				$path = '';

				$directories = explode('/', dirname($key));

				foreach ($directories as $directory) {
					$path = $path . '/' . $directory;

					if (!is_dir(DIR_MODIFICATION . $path)) {
						@mkdir(DIR_MODIFICATION . $path, 0777);
					}
				}

				$handle = fopen(DIR_MODIFICATION . $key, 'w');

				fwrite($handle, $value);

				fclose($handle);
			}
		}

		// Maintance mode back to original settings
		$this->model_setting_setting->editValue('config', 'config_maintenance', $maintenance);

		// Do not return success message if refresh() was called with $data
		$this->session->data['success'] = $this->language->get('text_success');

	}
}
