<?php
class HusseyCoding_CustomOrderGrid_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function registeredStatuses()
    {
        return array(1 => 'Guest', 0 => 'Registered');
    }
    
    public function virtualStatuses()
    {
        return array(1 => 'Yes', 0 => 'No');
    }
    
    public function paymentMethods()
    {
        $data = array();
        foreach (Mage::getModel('payment/config')->getActiveMethods() as $method):
            $data[$method->getCode()] = $method->getTitle();
        endforeach;

	    if (Mage::getConfig()->getNode('modules/Payone_Core')) {
		    $storeId = 0;
		    $payoneConfig = Mage::helper( 'payone_core/config' )->getConfigPayment( $storeId );
		    $methods = $payoneConfig->getAvailableMethods();

		    foreach ( $methods as $method ) {
			    $data['payone_' . $method->getCode()] = $method->getName();
		    }
	    }
        
        return $data;
    }
    
    public function ccTypes()
    {
        $data = array();
        foreach (Mage::getModel('payment/config')->getCcTypes() as $code => $title):
            $data[$code] = $title;
        endforeach;
        
        return $data;
    }
    
    public function shippingMethods()
    {
        $data = array();
        foreach (Mage::getModel('shipping/config')->getActiveCarriers() as $carrier):
            $methods = $carrier->getAllowedMethods();
            if (!empty($methods)):
                foreach ($methods as $code => $title):
                    $code = $carrier->getCarrierCode() . '_' . $code;
                    $title = Mage::getStoreConfig('carriers/' . $carrier->getCarrierCode() . '/title') . ' - ' . $title;
                    if (!empty($code) && !empty($title)):
                        $data[$code] = $title;
                    endif;
                endforeach;
            endif;
        endforeach;
        
        return $data;
    }
    
    public function customerGroups()
    {
        $data = array();
        foreach (Mage::getResourceModel('customer/group_collection') as $group):
            $data[$group->getCustomerGroupId()] = $group->getCustomerGroupCode();
        endforeach;
        
        return $data;
    }
}