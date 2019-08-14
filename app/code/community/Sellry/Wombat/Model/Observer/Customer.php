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

class Sellry_Wombat_Model_Observer_Customer extends Sellry_Wombat_Model_Observer_Abstract
{
    public function _construct() {
        parent::_construct();
    }

    public function customerSaved($observer)
    {
        if(Mage::helper('wombat')->getPushCustomer()) {
            $customerObject = new stdClass();
            $customerHelper = Mage::helper('wombat/entity_customer');

            $customer = $observer->getCustomer();
            if($customer && $customer->getId()) {
                $customerObject->id = $customer->getId();
                $customerObject->firstname = $customer->getData('firstname');
                $customerObject->lastname = $customer->getData('lastname');
                $customerObject->email = $customer->getData('email');

                if($customer->getDefaultBilling()) {
                    $billingAddress = Mage::getModel('customer/address')->load($customer->getDefaultBilling());

                    $customerObject->billing_address = $customerHelper->convertAddress($billingAddress);
                }

                if($customer->getDefaultShipping()) {
                    $shippingAddress = Mage::getModel('customer/address')->load($customer->getDefaultShipping());

                    $customerObject->shipping_address = $customerHelper->convertAddress($shippingAddress);
                }

                $this->getServerConnection()->addObjectToRequest($customerObject, 'customers');
                $responseBody = $this->getServerConnection()->sendRequest();

                if(Mage::helper('wombat')->logResponseBody($responseBody, 'customer', 'customerSaved'));
            }
        }
    }

    public function customerAddressValidation($observer)
    {
        if(Mage::helper('wombat')->getPushCustomerAddress()) {
            if($observer->getAddress()) {
                $address = $observer->getAddress();

                $customerObject = new stdClass();
                $customerObject->id = $address->getData('customer_id');

                $customerHelper = Mage::helper('wombat/entity_customer');

                $addressObject = $customerHelper->convertAddress($address);

                $customer = Mage::getSingleton('customer/session')->getCustomer();
                if($customer->getDefaultBilling()) {
                    $customerObject->billing_address = $addressObject;
                }
                if($customer->getDefaultShipping()) {
                    $customerObject->shipping_address = $addressObject;
                }

                $this->getServerConnection()->addObjectToRequest($customerObject, 'customers');
                $responseBody = $this->getServerConnection()->sendRequest();

                Mage::helper('wombat')->logResponseBody($responseBody, 'customer', 'customerAddressValidation');
            }
        }
    }
}
