<?php

namespace Opencart\System\Library\Extension\CesOcmod\Ces;

trait cesOcmodLib {

	protected $mName      = 'ces_ocmod';
	protected $eName      = 'module_ces_ocmod';
    protected $ePath      = 'extension/ces_ocmod/module/ces_ocmod';
    protected $ePathEvent = 'extension/ces_ocmod/event';
    protected $eVersion   = '1.1.0';
    protected $separator   = '.';

    protected $ePathMarketplace = 'extension/ces_ocmod/marketplace';
    protected $ePathModification      = 'extension/ces_ocmod/marketplace/modification';
    protected $ePathModificationModel = 'extension/ces_ocmod/setting/modification';

    protected $ePathInstall      = 'extension/ces_ocmod/marketplace/install';
    protected $ePathInstaller      = 'extension/ces_ocmod/marketplace/installer';

    protected function isValidXML($xml, $is_delete = false)
    {
        $this->load->language($this->ePathModification);

        $error_message = '';
        $data = [];
        try {
            $current_value = libxml_use_internal_errors();
            if (!$current_value) {
                libxml_use_internal_errors(true);
            }
            $doc = new \DOMDocument();

            if ($doc->loadXML($xml)) {
                foreach (['name', 'version', 'code', 'link', 'author'] as $key) {
                    $element = $doc->getElementsByTagName($key)->item(0);
                    if (empty($element)) {
                        $error_message = sprintf($this->language->get('error_invalid_value_for_key'), $key);
                    } else {
                        $data[$key] = $element->textContent;
                    }
                }
            } else {
                $error_message = $this->language->get('error_upload_valid_xml_file_only') .libxml_get_last_error()->message;
            }

            libxml_use_internal_errors($current_value);
        } catch (Exception $err) {
            $error_message = $this->language->get('error_upload_valid_xml_file_only') . $err->getMessage();
        }

        return [
            'error' => $error_message,
            'data' => $data,
        ];
    }
}
