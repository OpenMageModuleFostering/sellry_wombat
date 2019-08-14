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

class Sellry_Wombat_Model_Connection extends Mage_Core_Model_Abstract
{
    private $_pushURL = 'https://push.wombat.co';

    protected $_client;
    protected $_requestObjects;

    public function _construct()
    {
        // If the access tokens have been configured
        $accessTokens = Mage::helper('wombat')->getXHubTokens();
        if($accessTokens['store'] && $accessTokens['access']) {
            // Initialize the http client
            $this->_client = new Varien_Http_Client($this->_pushURL);
            $this->_client->setMethod(Varien_Http_Client::POST);

            $this->_client->setHeaders('X-Hub-Store', $accessTokens['store']);
            $this->_client->setHeaders('X-Hub-Access-Token', $accessTokens['access']);

            $this->_requestObjects = new stdClass();
        }
    }

    public function addObjectToRequest($object, $type)
    {
        if(gettype($object) == 'object') {
            if(!property_exists($this->_requestObjects, $type)) {
                $this->_requestObjects->$type = array();
            }
            $this->_requestObjects->$type = array_merge(
                $this->_requestObjects->$type,
                array($object)
            );
            return true;
        }

        return false;
    }

    public function sendRequest()
    {
        if( $this->_client ) {
            $jsonData = Mage::helper('core')->jsonEncode($this->_requestObjects);
            $this->_client->setRawData($jsonData);

            try {
                $response = $this->_client->request();
                return $response->getBody();
            } catch (Exception $e) {
                if(Mage::helper('wombat')->isLogEnabled()) {
                    Mage::log(sprintf('[exception] %s', $e->getMessage()), null, 'wombat.log');
                }
            }
        }
    }
}
