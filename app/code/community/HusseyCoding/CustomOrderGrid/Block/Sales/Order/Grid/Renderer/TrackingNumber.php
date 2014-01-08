<?php
class HusseyCoding_CustomOrderGrid_Block_Sales_Order_Grid_Renderer_TrackingNumber extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $order = Mage::getModel('sales/order')->load((int) $row->getId());
        $tracking = $order->getTracksCollection();
        if ($tracking->count()):
            foreach ($tracking as $track):
                $html = '<a href="javascript:void(0)" onclick="popWin(\'' . Mage::helper('shipping')->getTrackingPopupUrlBySalesModel($order) . '\',\'trackorder\',\'width=800,height=600,resizable=yes,scrollbars=yes\')">' . $track->getTrackNumber() . '</a>';
            endforeach;
        endif;
        
        return isset($html) ? $html : '';
    }
}