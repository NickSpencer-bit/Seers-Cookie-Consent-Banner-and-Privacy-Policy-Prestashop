<?php
/**
 * 2007-2020 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2020 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
	exit;
}

class Seers extends Module
{
	public $apisecrekkey = '$2y$10$9ygTfodVBVM0XVCdyzEUK.0FIuLnJT0D42sIE6dIu9r/KY3XaXXyS';
	public function __construct()
	{
		$this->module_key = 'bf95963c818431a4778551a826a12f86';
		$this->name = 'seers';
		$this->tab = 'analytics_stats';
		$this->author = 'Nick Spencer';
		$this->version='1.0.0';
		$this->bootstrap=true;
		$this->ps_versions_compliancy =  array('min' => '1.6.0.0', 'max' => '1.7.99.99');
		parent::__construct();
		$this->displayName = $this->l('Seers Cookie Consent Banner and Privacy Policy');
		$this->description=	$this->l('Seers cookie consent management platform is trusted by thousands of businesses. Become GDPR, CCPA, ePrivacy and LGPD compliant in three clicks.');
		$this->confirmUninstall = $this->l('Are you sure you want to delete these details?');
	}

	public function install()
	{
		$this->plugin_active_inactive(1);
		return parent::install() && $this->registerHook('displayHeader');
	}

	public function disable()
	{
		$this->plugin_active_inactive(0);
		return true;
	}

	public function uninstall()
	{
		if (! parent::uninstall()) {
			return false;
		}

		$this->plugin_active_inactive(0);
		return true;
	}

	public function hookDisplayHeader()
	{
		//adding css
		/*
		$this->context->controller->addCSS(array(
			$this->_path.'views/css/seers.css'
		));

		// adding JS
		$this->context->controller->addJS(array(
			$this->_path.'views/js/seers.js'
		));
		*/
		$ID = Configuration::get('SEERSGROUPID');
		if ($ID !== '') {
			return '<script data-key=
			"' . $ID . '" data-name="CookieXray" src="https://seersco.com/script/cb.js" type="text/javascript">
			</script>';
		}
		return $ID;
	}

	public function getContent()
	{
		$output = '';

		if (Tools::isSubmit('saveseers')) {
			
			$name = strval(Tools::getValue('print'));
			if (!empty($name)) {
				Configuration::updateValue('SEERSGROUPID', $name);
				$output .= $this->displayConfirmation( $this->l('SEERS Group ID updated successfully'));
			} else {
				$output .= $this->displayError( $this->l('SEERS Group ID update unsuccessful'));
			}
			
			
		}
		$this->context->smarty->assign(array('SEERSGROUPID' => Configuration::get('SEERSGROUPID')));
		return $output . $this->display(__FILE__, 'views/templates/admin/configure.tpl');
	}

	public function plugin_active_inactive($isative = 0){
        $postData = array(
            'domain' => ((Configuration::get('PS_SSL_ENABLED')) ? Configuration::get('PS_SHOP_DOMAIN_SSL') : Configuration::get('PS_SHOP_DOMAIN') ),
            'isactive' => $isative,
            'secret' => $this->apisecrekkey,
            'platform' => 'prestashop',
            'pluginname' => $this->displayName
        );
        $request_headers = array(
            'Content-Type' => 'application/json',
            'Referer' => ((Configuration::get('PS_SSL_ENABLED')) ? Configuration::get('PS_SHOP_DOMAIN_SSL') : Configuration::get('PS_SHOP_DOMAIN') ),
        );
        //$url = "https://seersco.backend/api/plugin-domain";
        $url = "https://cmp.seersco.com/api/plugin-domain";
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => $request_headers,
            CURLOPT_POSTFIELDS => $postData
        ));

        $response = curl_exec($curl);
        $error_number = curl_errno($curl);
        $error_message = curl_error($curl);
        curl_close($curl);
        
        $response =json_decode($response, TRUE);

        return $response;
    }
}
