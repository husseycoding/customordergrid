<?php

class HusseyCoding_CustomOrderGrid_Model_Resource_Sales_Order_Grid extends Mage_Sales_Model_Resource_Order_Grid_Collection
{
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();

        if (Mage::app()->getRequest()->getControllerName() == 'sales_order') {
            $countSelect->reset(Zend_Db_Select::GROUP);
            $countSelect->reset(Zend_Db_Select::COLUMNS);
            $countSelect->columns("COUNT(DISTINCT main_table.entity_id)");
            $havingCondition = $countSelect->getPart(Zend_Db_Select::HAVING);
            if (count($havingCondition)) {
                $countSelect->where(
                    str_replace("group_concat(`sales_flat_order_item`.sku SEPARATOR ', ')", 'sales_flat_order_item.sku', $havingCondition[0])
                );
                $countSelect->reset(Zend_Db_Select::HAVING);
            }
        }
        return $countSelect;
    }
}