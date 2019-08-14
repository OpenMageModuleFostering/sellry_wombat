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

class Sellry_Wombat_Helper_Data extends Mage_Core_Helper_Data
{
    const CONFIGURATION_ENABLED = 'wombat/settings/enabled';
    const CONFIGURATION_X_HUB_STORE = 'wombat/settings/x_hub_store';
    const CONFIGURATION_X_HUB_ACCESS_TOKEN = 'wombat/settings/x_hub_access_token';
    const CONFIGURATION_LOG = 'wombat/settings/log';

    const CONFIGURATION_PUSH_PRODUCT = 'wombat/push_settings/product_save';
    const CONFIGURATION_PUSH_ORDER_PRODUCT_STOCK = 'wombat/push_settings/order_product_stock';
    const CONFIGURATION_PUSH_CUSTOMER = 'wombat/push_settings/customer_save';
    const CONFIGURATION_PUSH_CUSTOMER_ADDRESS = 'wombat/push_settings/customer_address_save';
    const CONFIGURATION_PUSH_ORDER_PLACE = 'wombat/push_settings/order_place';
    const CONFIGURATION_PUSH_ORDER_CANCEL = 'wombat/push_settings/order_cancel';
    const CONFIGURATION_PUSH_PAYMENT_PAY = 'wombat/push_settings/payment_pay';

    public function isEnabled()
    {
        return Mage::getStoreConfig(self::CONFIGURATION_ENABLED, Mage::app()->getStore()->getId());
    }

    public function getXHubTokens()
    {
        $storeId = Mage::app()->getStore()->getId();

        return array(
            'store'     => Mage::getStoreConfig(self::CONFIGURATION_X_HUB_STORE, $storeId),
            'access'    => Mage::getStoreConfig(self::CONFIGURATION_X_HUB_ACCESS_TOKEN, $storeId)
        );
    }

    public function isLogEnabled()
    {
        return Mage::getStoreConfig(self::CONFIGURATION_LOG, Mage::app()->getStore()->getId());
    }

    public function logResponseBody($response, $entityType, $action = '')
    {
        if($this->isLogEnabled()) {
            Mage::log(sprintf('[%s][%s] %s', $entityType, $action, $response), null, 'wombat.log');
        }
    }

    public function getPushProduct()
    {
        return Mage::getStoreConfig(self::CONFIGURATION_PUSH_PRODUCT, Mage::app()->getStore()->getId());
    }

    public function getPushOrderProductStock()
    {
        return Mage::getStoreConfig(self::CONFIGURATION_PUSH_ORDER_PRODUCT_STOCK, Mage::app()->getStore()->getId());
    }

    public function getPushCustomer()
    {
        return Mage::getStoreConfig(self::CONFIGURATION_PUSH_CUSTOMER, Mage::app()->getStore()->getId());
    }

    public function getPushCustomerAddress()
    {
        return Mage::getStoreConfig(self::CONFIGURATION_PUSH_CUSTOMER_ADDRESS, Mage::app()->getStore()->getId());
    }

    public function getPushOrder()
    {
        return Mage::getStoreConfig(self::CONFIGURATION_PUSH_ORDER_PLACE, Mage::app()->getStore()->getId());
    }

    public function getPushOrderCancel()
    {
        return Mage::getStoreConfig(self::CONFIGURATION_PUSH_ORDER_CANCEL, Mage::app()->getStore()->getId());
    }

    public function getPushPaymentPay()
    {
        return Mage::getStoreConfig(self::CONFIGURATION_PUSH_PAYMENT_PAY, Mage::app()->getStore()->getId());
    }
}
