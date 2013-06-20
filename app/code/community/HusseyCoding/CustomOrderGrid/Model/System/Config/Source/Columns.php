<?php
class HusseyCoding_CustomOrderGrid_Model_System_Config_Source_Columns
{
    public function toOptionArray()
    {
        return array(
            array(
                'label' => 'Order Details',
                'value' => array(
                    array('value' => 'created_at', 'label' => Mage::helper('adminhtml')->__('Purchased On')),
                    array('value' => 'store_id', 'label' => Mage::helper('adminhtml')->__('Purchased From (Store)')),
                    array('value' => 'updated_at', 'label' => Mage::helper('adminhtml')->__('Order Modified')),
                    array('value' => 'status', 'label' => Mage::helper('adminhtml')->__('Status')),
                    array('value' => 'shipping_description', 'label' => Mage::helper('adminhtml')->__('Shipping Method')),
                    array('value' => 'order_currency_code', 'label' => Mage::helper('adminhtml')->__('Currency'))
                ),
            ),
            array(
                'label' => 'Pricing Information',
                'value' => array(
                    array('value' => 'base_subtotal', 'label' => Mage::helper('adminhtml')->__('Subtotal (Base)')),
                    array('value' => 'subtotal', 'label' => Mage::helper('adminhtml')->__('Subtotal (Purchased)')),
                    array('value' => 'base_grand_total', 'label' => Mage::helper('adminhtml')->__('G.T. (Base)')),
                    array('value' => 'grand_total', 'label' => Mage::helper('adminhtml')->__('G.T. (Purchased)')),
                    array('value' => 'base_tax_amount', 'label' => Mage::helper('adminhtml')->__('Tax (Base)')),
                    array('value' => 'tax_amount', 'label' => Mage::helper('adminhtml')->__('Tax (Purchased)')),
                    array('value' => 'base_shipping_amount', 'label' => Mage::helper('adminhtml')->__('Shipping (Base)')),
                    array('value' => 'shipping_amount', 'label' => Mage::helper('adminhtml')->__('Shipping (Purchased)'))
                ),
            ),
            array(
                'label' => 'Product Information',
                'value' => array(
                    array('value' => 'sku', 'label' => Mage::helper('adminhtml')->__('SKU')),
                    array('value' => 'coupon_code', 'label' => Mage::helper('adminhtml')->__('Coupon Code')),
                    array('value' => 'total_item_count', 'label' => Mage::helper('adminhtml')->__('Product Count'))
                ),
            ),
            array(
                'label' => 'Customer Details',
                'value' => array(
                    array('value' => 'billing_name', 'label' => Mage::helper('adminhtml')->__('Billing Name')),
                    array('value' => 'shipping_name', 'label' => Mage::helper('adminhtml')->__('Ship to Name')),
                    array('value' => 'billing_company', 'label' => Mage::helper('adminhtml')->__('Billing Company')),
                    array('value' => 'shipping_company', 'label' => Mage::helper('adminhtml')->__('Ship to Company')),
                    array('value' => 'billing_postcode', 'label' => Mage::helper('adminhtml')->__('Billing Postcode')),
                    array('value' => 'shipping_postcode', 'label' => Mage::helper('adminhtml')->__('Ship to Postcode')),
                    array('value' => 'billing_country', 'label' => Mage::helper('adminhtml')->__('Billing Country')),
                    array('value' => 'shipping_country', 'label' => Mage::helper('adminhtml')->__('Ship to Country')),
                    array('value' => 'customer_email', 'label' => Mage::helper('adminhtml')->__('Customer Email')),
                    array('value' => 'customer_is_guest', 'label' => Mage::helper('adminhtml')->__('Guest Checkout'))
                )
            )
        );
    }
}
