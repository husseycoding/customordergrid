<?php
class HusseyCoding_CustomOrderGrid_Model_System_Config_Source_Sort
{
    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' => Mage::helper('adminhtml')->__('-- Please Select --')),
            array('value' => 'created_at', 'label' => Mage::helper('adminhtml')->__('Purchased On')),
            array('value' => 'store_id', 'label' => Mage::helper('adminhtml')->__('Purchased From (Store)')),
            array('value' => 'updated_at', 'label' => Mage::helper('adminhtml')->__('Order Modified')),
            array('value' => 'status', 'label' => Mage::helper('adminhtml')->__('Status')),
            array('value' => 'shipping_method', 'label' => Mage::helper('adminhtml')->__('Shipping Method')),
            array('value' => 'tracking_number', 'label' => Mage::helper('adminhtml')->__('Tracking Number')),
            array('value' => 'order_currency_code', 'label' => Mage::helper('adminhtml')->__('Currency')),
            array('value' => 'method', 'label' => Mage::helper('adminhtml')->__('Payment Method')),
            array('value' => 'cc_type', 'label' => Mage::helper('adminhtml')->__('Credit Card Type')),
            array('value' => 'base_subtotal', 'label' => Mage::helper('adminhtml')->__('Subtotal (Base)')),
            array('value' => 'subtotal', 'label' => Mage::helper('adminhtml')->__('Subtotal (Purchased)')),
            array('value' => 'base_grand_total', 'label' => Mage::helper('adminhtml')->__('G.T. (Base)')),
            array('value' => 'grand_total', 'label' => Mage::helper('adminhtml')->__('G.T. (Purchased)')),
            array('value' => 'base_tax_amount', 'label' => Mage::helper('adminhtml')->__('Tax (Base)')),
            array('value' => 'tax_amount', 'label' => Mage::helper('adminhtml')->__('Tax (Purchased)')),
            array('value' => 'base_shipping_amount', 'label' => Mage::helper('adminhtml')->__('Shipping (Base)')),
            array('value' => 'shipping_amount', 'label' => Mage::helper('adminhtml')->__('Shipping (Purchased)')),
            array('value' => 'base_discount_amount', 'label' => Mage::helper('adminhtml')->__('Discount (Base)')),
            array('value' => 'discount_amount', 'label' => Mage::helper('adminhtml')->__('Discount (Purchased)')),
            array('value' => 'sku', 'label' => Mage::helper('adminhtml')->__('SKU')),
            array('value' => 'name', 'label' => Mage::helper('adminhtml')->__('Product Name')),
            array('value' => 'is_virtual', 'label' => Mage::helper('adminhtml')->__('Is Virtual')),
            array('value' => 'coupon_code', 'label' => Mage::helper('adminhtml')->__('Coupon Code')),
            array('value' => 'total_item_count', 'label' => Mage::helper('adminhtml')->__('Product Count')),
            array('value' => 'total_qty_ordered', 'label' => Mage::helper('adminhtml')->__('Product Quantity')),
            array('value' => 'billing_name', 'label' => Mage::helper('adminhtml')->__('Billing Name')),
            array('value' => 'shipping_name', 'label' => Mage::helper('adminhtml')->__('Ship to Name')),
            array('value' => 'billing_company', 'label' => Mage::helper('adminhtml')->__('Billing Company')),
            array('value' => 'shipping_company', 'label' => Mage::helper('adminhtml')->__('Ship to Company')),
            array('value' => 'billing_postcode', 'label' => Mage::helper('adminhtml')->__('Billing Postcode')),
            array('value' => 'shipping_postcode', 'label' => Mage::helper('adminhtml')->__('Ship to Postcode')),
            array('value' => 'billing_region', 'label' => Mage::helper('adminhtml')->__('Billing Region')),
            array('value' => 'shipping_region', 'label' => Mage::helper('adminhtml')->__('Ship to Region')),
            array('value' => 'billing_country', 'label' => Mage::helper('adminhtml')->__('Billing Country')),
            array('value' => 'shipping_country', 'label' => Mage::helper('adminhtml')->__('Ship to Country')),
            array('value' => 'customer_email', 'label' => Mage::helper('adminhtml')->__('Customer Email')),
            array('value' => 'customer_group_id', 'label' => Mage::helper('adminhtml')->__('Customer Group')),
            array('value' => 'customer_is_guest', 'label' => Mage::helper('adminhtml')->__('Guest Checkout'))
        );
    }
}