<?php
class HusseyCoding_CustomOrderGrid_Model_System_Config_Source_Sort
{
    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' => Mage::helper('adminhtml')->__('-- Please Select --')),
            array('value' => 'real_order_id', 'label' => Mage::helper('adminhtml')->__('Order #')),
            array('value' => 'store_id', 'label'=>Mage::helper('adminhtml')->__('Purchased From (Store)')),
            array('value' => 'created_at', 'label'=>Mage::helper('adminhtml')->__('Purchased On')),
            array('value' => 'updated_at', 'label'=>Mage::helper('adminhtml')->__('Order Modified')),
            array('value' => 'billing_name', 'label'=>Mage::helper('adminhtml')->__('Bill to Name')),
            array('value' => 'shipping_name', 'label'=>Mage::helper('adminhtml')->__('Ship to Name')),
            array('value' => 'base_grand_total', 'label'=>Mage::helper('adminhtml')->__('G.T. (Base)')),
            array('value' => 'grand_total', 'label'=>Mage::helper('adminhtml')->__('G.T. (Purchased)')),
            array('value' => 'status', 'label'=>Mage::helper('adminhtml')->__('Status')),
            array('value' => 'sku', 'label'=>Mage::helper('adminhtml')->__('SKU')),
            array('value' => 'shipping_description', 'label'=>Mage::helper('adminhtml')->__('Shipping Method')),
            array('value' => 'coupon_code', 'label'=>Mage::helper('adminhtml')->__('Coupon Code')),
            array('value' => 'customer_email', 'label'=>Mage::helper('adminhtml')->__('Customer Email')),
            array('value' => 'base_shipping_amount', 'label'=>Mage::helper('adminhtml')->__('Shipping (Base)')),
            array('value' => 'shipping_amount', 'label'=>Mage::helper('adminhtml')->__('Shipping (Purchased)')),
            array('value' => 'base_subtotal', 'label'=>Mage::helper('adminhtml')->__('Subtotal (Base)')),
            array('value' => 'subtotal', 'label'=>Mage::helper('adminhtml')->__('Subtotal (Purchased)')),
            array('value' => 'base_tax_amount', 'label'=>Mage::helper('adminhtml')->__('Tax (Base)')),
            array('value' => 'tax_amount', 'label'=>Mage::helper('adminhtml')->__('Tax (Purchased)')),
            array('value' => 'customer_is_guest', 'label'=>Mage::helper('adminhtml')->__('Guest Checkout')),
            array('value' => 'order_currency_code', 'label'=>Mage::helper('adminhtml')->__('Currency')),
            array('value' => 'total_item_count', 'label'=>Mage::helper('adminhtml')->__('Product Count'))
        );
    }
}
