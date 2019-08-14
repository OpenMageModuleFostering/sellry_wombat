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

class Sellry_Wombat_Helper_Entity_Product extends Mage_Core_Helper_Abstract
{
    public function getProductMediaImages(&$product)
    {
        $mediaImages = $product->getMediaGalleryImages();
        $images = array();
        foreach($mediaImages as $image) {
            $imageObject = new stdClass();
            $imageObject->url = $image->getUrl();

            $images[] = $imageObject;
        }
        
        return $images;
    }

    public function getProductVariants(&$product)
    {
        $attributes = $product
            ->getTypeInstance(true)
            ->getConfigurableAttributes($product);
        $options = array();
        foreach ($attributes as $attribute) {
            $options[] = $attribute->getData('label');
        }

        $simpleProducts = $product
            ->getTypeInstance(true)
            ->getUsedProductCollection($product);

        $variants = array();
        foreach($simpleProducts as $simpleProduct) {
            $variantObject = new stdClass();

            $simpleProduct = $simpleProduct->load($simpleProduct->getId());
            $variantObject->id = $simpleProduct->getSku();
            $variantObject->sku = $simpleProduct->getSku();
            $variantObject->price = $this->getProductPrice($simpleProduct);

            $stockItem = $simpleProduct->getStockItem();
			if($stockItem) {
				$variantObject->quantity = floatval($stockItem->getQty());
			}
            $options = array();
            foreach($attributes as $attribute) {
                $attributeCode = $attribute->getProductAttribute()->getAttributeCode();
                $option = new stdClass();
                $option->$attributeCode = $simpleProduct->getAttributeText($attributeCode);
                $options[] = $option;
            }
            if(count($options)) {
                $variantObject->options = $options;
            }

            $mediaImages = $simpleProduct->getMediaGalleryImages();
            $images = array();
            foreach($mediaImages as $image) {
                $imageObject = new stdClass();
                $imageObject->url = $image->getUrl();

                $images[] = $imageObject;
            }

            $variantObject->images = $images;

            $variants[] = $variantObject;
        }

        return array('options' => $options, 'variants' => $variants);
    }
    
    public function getStockObject(&$object)
    {
        $stockObject = null;

        $stockItem = null;
        $productId = '';

        if($object instanceof Mage_CatalogInventory_Model_Stock_Item) {
            $stockItem = $object;
            $product = Mage::getResourceModel('catalog/product_collection')->addFieldToFilter('entity_id', $object->getProductId())->getFirstItem();
            $productId = $product->getSku();
        }
        elseif($object instanceof Mage_Catalog_Model_Product) {
            $stockItem = $object->getStockItem();
            $productId = $object->getSku();
        }

        if($stockItem && $stockItem->getId()) {
            $stockObject = new stdClass();
            $stockObject->id = $stockItem->getId();
            $stockObject->location = 'store_warehouse';
            $stockObject->product_id = $productId;
            $stockObject->quantity = floatval($stockItem->getQty());
        }

        return $stockObject;
    }

    public function getProductTaxons(&$product)
    {
        $categoryCollection = $product->getCategoryCollection()
                                ->addAttributeToSelect('name');
        $categories = array();

        foreach($categoryCollection as $category) {
            $parent = null;
            $path = explode('/', $category->getPath());
            if(count($path)) {
                foreach($path as $idx => $pid) {
                    if($parent == null && $idx == 0) {
                        if(!isset($categories[$pid])) {
                            $categories[$pid] = array('name' => '');
                        }
                        $parent = &$categories[$pid];
                    }
                    else {
                        if(!isset($parent[$pid])) {
                            $parent[$pid] = array('name' => '');
                        }
                        $parent = &$parent[$pid];
                    }
                }
            }
            $parent['name'] = $category->getName();
            unset($parent);
        }

        return $this->_buildTaxonString($categories);
    }

    protected function _buildTaxonString(&$categories)
    {
        $taxons = array();
        foreach($categories as $key => $value) {
            if($key == 'name' && $value != '') {
                $taxons[] = $value;
            }
            elseif($key != 'name') {
                $newTaxon = $this->_buildTaxonString($value);
                if(is_array($newTaxon) && count($newTaxon) == 1) {
                    $newTaxon = $newTaxon[0];
                }
                $taxons[] = $newTaxon;
            }
        }
        if(count($taxons) == 1 && is_array($taxons[0])) {
                return $taxons[0];
        }
        return $taxons;
    }

    public function getProductPrice(&$product) {
        if($product->getData('special_price') && 
            strtotime($product->getData('special_from_data')) <= time() &&
            strtotime($product->getData('special_to_date')) >= time()) {
            return floatval($product->getData('special_price'));
        }
        return floatval($product->getData('price'));
    }
}