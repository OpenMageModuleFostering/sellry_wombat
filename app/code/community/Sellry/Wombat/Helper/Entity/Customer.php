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

class Sellry_Wombat_Helper_Entity_Customer extends Mage_Core_Helper_Abstract
{
    public function convertAddress(&$address)
    {
        $addressObject = new stdClass();
        $addressObject->firstname = $address->getData('firstname');
        $addressObject->lastname = $address->getData('lastname');

        $street = explode("\n", $address->getData('street'));
        if(count($street) > 0) {
            $addressObject->address1 = $street[0];
        }
        if(count($street) > 1) {
            $addressObject->address2 = $street[1];
        }
        $addressObject->zipcode = $address->getData('postcode');
        $addressObject->city = $address->getData('city');
        $addressObject->state = $address->getData('region');
        $addressObject->country = $address->getData('country_id'); // Country ISO code
        $addressObject->phone = $address->getData('telephone');

        return $addressObject;
    }
}