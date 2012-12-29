<?php
class HusseyCoding_CustomOrderGrid_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function registeredStatuses()
    {
        return array(1 => 'Guest', 0 => 'Registered');
    }
}