<?php
class HusseyCoding_CustomOrderGrid_Model_System_Config_Source_Direction
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'DESC', 'label' => Mage::helper('adminhtml')->__('Descending')),
            array('value' => 'ASC', 'label' => Mage::helper('adminhtml')->__('Ascending'))
        );
    }
}