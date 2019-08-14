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

class Sellry_Wombat_Model_Observer_Order extends Sellry_Wombat_Model_Observer_Abstract
{
    public function _construct() {
        parent::_construct();
    }

    public function orderPlaced($observer)
    {
        if(Mage::helper('wombat')->getPushOrder()) {
            $order = $observer->getOrder();

            $customerHelper = Mage::helper('wombat/entity_customer');
            $orderHelper = Mage::helper('wombat/entity_order');

            if($order && $order->getId()) {
                $orderObject = new stdClass();

                $orderObject->id = $order->getIncrementId();
                $orderObject->status = $order->getData('status');
                $orderObject->email = $order->getData('customer_email');
                $orderObject->currency = $order->getData('order_currency_code');
                $orderObject->placed_on = $order->getData('created_at');

                // Keep the totals in a separate object to use it's info later
                $totalsObject = $orderHelper->getOrderTotalsObject($order);
                $orderObject->totals = $totalsObject;

                $orderObject->line_items = $orderHelper->getItemsCollectionObject($order);

                $taxObject = new stdClass();
                $taxObject->name = 'Tax';
                $taxObject->tax = $totalsObject->tax;

                $shippingObject = new stdClass();
                $shippingObject->name = 'Shipping';
                $shippingObject->shipping = $totalsObject->shipping;

                $discountObject = new stdClass();
                $discountObject->name = 'Discount';
                $discountObject->discount = $totalsObject->discount;

                $orderObject->adjustments = array(
                    $taxObject,
                    $shippingObject,
                    $discountObject
                );

                $billingAddress = $customerHelper->convertAddress($order->getBillingAddress());
                $orderObject->billing_address = $billingAddress;

                $shippingAddress = $customerHelper->convertAddress($order->getShippingAddress());
                $orderObject->shipping_address = $shippingAddress;

                $orderObject->payment = $orderHelper->getPaymentsCollectionObject($order);

                $orderObject->order = $order->getData('grand_total') * 1;

                if(Mage::helper('wombat')->getPushOrderProductStock()) {
                    $inventories = $orderHelper->getInventoriesForOrderItems($order);
                    foreach($inventories as $inventory) {
                        $this->getServerConnection()->addObjectToRequest($inventory, 'inventory');
                        
                        $productObject = new stdClass();
                        $productObject->id = $inventory->product_id;
                        $productObject->quantity = $inventory->quantity;
                        
                        $this->getServerConnection()->addObjectToRequest($productObject, 'products');
                    }
                }

                $this->getServerConnection()->addObjectToRequest($orderObject, 'order');
                $responseBody = $this->getServerConnection()->sendRequest();

                Mage::helper('wombat')->logResponseBody($responseBody, 'order', 'orderPlaced');
            }
        }
    }

    public function orderCanceled($observer)
    {
        if(Mage::helper('wombat')->getPushOrderCancel()) {
            $order = $observer->getOrder();
            
            if($order && $order->getId()) {
                $orderHelper = Mage::helper('wombat/entity_order');
                
                $orderObject = new stdClass();

                $orderObject->id = $order->getIncrementId();
                $orderObject->status = $order->getData('status');

                if(Mage::helper('wombat')->getPushOrderProductStock()) {
                    $inventories = $orderHelper->getInventoriesForOrderItems($order);
                    foreach($inventories as $inventory) {
                        $this->getServerConnection()->addObjectToRequest($inventory, 'inventory');
                        
                        $productObject = new stdClass();
                        $productObject->id = $inventory->product_id;
                        $productObject->quantity = $inventory->quantity;
                        
                        $this->getServerConnection()->addObjectToRequest($productObject, 'products');
                    }
                }

                $this->getServerConnection()->addObjectToRequest($orderObject, 'order');
                $responseBody = $this->getServerConnection()->sendRequest();

                Mage::helper('wombat')->logResponseBody($responseBody, 'order', 'orderCanceled');
            }
        }
    }

    public function paymentPayed($observer)
    {
        if(Mage::helper('wombat')->getPushPaymentPay()) {
            $payment = $observer->getPayment();

            $orderHelper = Mage::helper('wombat/entity_order');

            if($payment) {
                $orderObject = new stdClass();

                $order = $payment->getOrder();

                $orderObject->id = $order->getIncrementId();
                $orderObject->totals = $orderHelper->getOrderTotalsObject($order);
                $orderObject->payment = $orderHelper->getPaymentsCollectionObject($order);

                $this->getServerConnection()->addObjectToRequest($orderObject, 'order');
                $responseBody = $this->getServerConnection()->sendRequest();

                Mage::helper('wombat')->logResponseBody($responseBody, 'order', 'paymentPayed');
            }
        }
    }
}