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

class Sellry_Wombat_Helper_Entity_Order extends Mage_Core_Helper_Abstract
{
    public function getOrderTotalsObject(&$order)
    {
        $totalsObject = new stdClass();
        $totalsObject->item = $order->getData('subtotal') * 1;
        $totalsObject->tax = $order->getData('tax_amount') + $order->getData('shipping_tax_amount');
        $totalsObject->shipping = $order->getData('shipping_amount') * 1;
        $totalsObject->adjustments = $totalsObject->tax + $totalsObject->shipping;
        $totalsObject->payment = $order->getPayment()->getData('amount_paid') * 1;
        $totalsObject->discount = $order->getData('discount_amount') * 1;
        $totalsObject->order = $order->getData('grand_total') * 1;
        
        return $totalsObject;
    }

    public function getPaymentsCollectionObject(&$order)
    {
        $payments = array();
        foreach($order->getPaymentsCollection() as $payment) {
            $paymentObject = new stdClass();
            $paymentObject->number = $payment->getId();
            $paymentObject->amount = $payment->getData('amount_paid') * 1;
            if($payment->getData('amount_paid') * 1 == $payment->getData('amount_ordered') * 1) {
                $paymentObject->status = 'completed';
            }
            else {
                $paymentObject->status = 'pending';
            }
            $paymentObject->payment_method = $payment->getData('method');

            $payments[] = $paymentObject;
        }
        
        return $payments;
    }

    public function getItemsCollectionObject(&$order)
    {
        $items = array();
        foreach($order->getItemsCollection() as $item) {
            if($item->getProductType() == 'configurable') continue;

            $itemObject = new stdClass();
            $itemObject->product_id = $item->getData('sku');
            $itemObject->name = $item->getData('name');
            $itemObject->quantity = $item->getData('qty_ordered');

            if($item->getData('parent_item_id') && $item->getData('parent_item_id') != '') {
                $parent = $order->getItemById($item->getData('parent_item_id'));
                $itemObject->price = $parent->getData('price');
            }
            else {
                $itemObject->price = $item->getData('price');
            }

            $items[] = $itemObject;
        }
        
        return $items;
    }

    public function getInventoriesForOrderItems(&$order)
    {
        $items = array();
        $productHelper = Mage::helper('wombat/entity_product');

        if($order && $order->getId()) {
            foreach($order->getItemsCollection() as $item) {
                $stockItem = Mage::getModel('cataloginventory/stock_item')
                                ->loadByProduct($item->getProductId());

                $items[] = $productHelper->getStockObject($stockItem);
            }
        }

        return $items;
    }
}
