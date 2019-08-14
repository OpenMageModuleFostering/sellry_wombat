<?php
/*
 * Wombat Integration
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * Wombat
 *
 * @category   Sellry
 * @package    Sellry_Wombat
 * @copyright  Copyright (c) 2014 Sellry (http://sellry.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Michael Bower <sales@sellry.com>
 */

class Sellry_Wombat_Block_Adminhtml_System_Config_Fieldset_Hint
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'sellry/wombat/system/config/fieldset/hint.phtml';
    
    protected $_dataUrl = 'http://sellry.com/magento-extensions/index.php';
    
    public function __construct() {
        parent::__construct();
        
        $this->setMainHeading(Mage::helper('wombat')->__('Wombat Integration by <a href="%s" target="_new">sellry</a>', $this->getSellryUrl()));
        $this->setCallToActionMessage(Mage::helper('wombat')->__('Need help with this extension? Let us know.'));
        $this->setLogoImage('http://sellry.com/magento-extensions/logo.jpg');
        
        $this->getModuleData();
        
        return $this;
    }
    
    public function isConnected()
    {
        $keys = Mage::helper('wombat')->getXHubTokens();
        return (bool)($keys['store'] && $keys['access']);
    }

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }

    public function getModuleVersion()
    {
    	return (string) Mage::getConfig()->getNode('modules/Sellry_Wombat/version');
    }
    
    public function getModuleData()
    {
        $modulesArray = (array)Mage::getConfig()->getNode('modules')->children();
	$aux = (array_key_exists('Enterprise_Enterprise', $modulesArray))? 'EE' : 'CE' ;
        
        $data = array(
            'module'    => 'Wombat',
            'version'   => $this->getModuleVersion(),
            'magento_version'   => Mage::getVersion(),
            'magento_edition'   => $aux,
            'locale'            => Mage::app()->getLocale()->getLocaleCode()
        );
        
        $client = new Varien_Http_Client($this->_dataUrl);
        $client->setMethod(Varien_Http_Client::POST);
        $client->setParameterPost('data', Mage::helper('core')->jsonEncode($data));

        try{
            $response = $client->request();
            if ($response->isSuccessful()) {
                $body = $response->getBody();
                $requestData = Mage::helper('core')->jsonDecode($body);
                if(!$requestData['error']) {
                    foreach($requestData['result'] as $key => $value) {
                        $this->setData($key, $value);
                    }
                }
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }
    
    public function getSellryUrl()
    {
        return "http://sellry.com/magento-extensions?utm_source=".Mage::getBaseUrl()."&utm_medium=config-link&utm_campaign=wombat-extension&utm_content=".$this->getModuleVersion();
    }
    
    public function getLatestDownloadUrl()
    {
        return "http://sellry.com/magento-extensions/wombat-integration#latest?utm_source=".Mage::getBaseUrl()."&utm_medium=config-message&utm_campaign=update&utm_content=".$this->getModuleVersion();
    }
    
    public function getDocumentationUrl()
    {
        return 'http://sellry.com/magento-extensions/wombat-integration#docs?&utm_source='.Mage::getBaseUrl().'&utm_medium=config-link&utm_campaign=wombat-extension&utm_content='.$this->getModuleVersion();
    }
    
    public function getHelpUrl()
    {
        return 'http://sellry.com/contact?&utm_source='.Mage::getBaseUrl().'&utm_medium=config-link&utm_campaign=wombat-extension&utm_content='.$this->getModuleVersion();
    }
    
    public function getFlowsUrl()
    {
        return 'http://sellry.com/magento-extensions/wombat-integration#flow-setup?&utm_source='.Mage::getBaseUrl().'&utm_medium=config-link&utm_campaign=wombat-extension&utm_content='.$this->getModuleVersion();
    }
}
