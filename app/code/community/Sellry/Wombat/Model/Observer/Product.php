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

class Sellry_Wombat_Model_Observer_Product extends Sellry_Wombat_Model_Observer_Abstract
{
    public function _construct() {
        parent::_construct();
    }

    public function productSaved($observer)
    {
        if(Mage::helper('wombat')->getPushProduct()) {
            $product = $observer->getProduct();

            if($product) {
                $this->addProductToRequest($product);

                $responseBody = $this->getServerConnection()->sendRequest();
                Mage::helper('wombat')->logResponseBody($responseBody, 'product', 'productSaved');
            }
        }
    }

    public function addProductToRequest($product)
    {
        $productObject = new stdClass();

        $productHelper = Mage::helper('wombat/entity_product');

        $productObject->id = $product->getData('sku');
        $productObject->name = $product->getData('name');
        $productObject->sku = $product->getData('sku');
        $productObject->description = $product->getData('description');
        $productObject->price = $productHelper->getProductPrice($product);
        $productObject->available_on = $product->getData('created_at');
        $productObject->permalink = $product->getData('url_key');
        $productObject->meta_description = $product->getData('meta_description');
        $productObject->meta_keywords = $product->getData('meta_keywords');
        
        $stockItem = $product->getStockData();
        if($stockItem) {
            $productObject->quantity = floatval($stockItem['qty']);
        }

        $productObject->taxons = $productHelper->getProductTaxons($product);

        $productObject->images = $productHelper->getProductMediaImages($product);

        if($product->isConfigurable()) {
            $configurable = $productHelper->getProductVariants($product);

            if(count($configurable['options'])) {
                $productObject->options = $configurable['options'];
            }

            if(count($configurable['variants'])) {
                $productObject->variants = $configurable['variants'];
            }
        }

        $this->getServerConnection()->addObjectToRequest($productObject, 'products');

        $stockObject = $productHelper->getStockObject($product);
        if($stockObject) {
            $this->getServerConnection()->addObjectToRequest($stockObject, 'inventory');
        }
    }
}