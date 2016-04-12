<?php
class HusseyCoding_CustomOrderGrid_Model_Observer extends Varien_Event_Observer
{
    public function adminhtmlCoreBlockAbstractPrepareLayoutBefore($observer)
    {
        if ($observer->getBlock()->getType() == 'adminhtml/widget_grid_massaction'):
            if ($block = Mage::app()->getLayout()->getBlock('sales_order.grid')):
                if ($this->_isEnabled() && $block->getColumns()):
                    $sort = Mage::getStoreConfig('customordergrid/configure/columnsort');
                    if (!$sort || $sort == 'tracking_number'):
                        $sort = 'real_order_id';
                    endif;
                    $direction = Mage::getStoreConfig('customordergrid/configure/sortdirection') ? Mage::getStoreConfig('customordergrid/configure/sortdirection') : 'DESC';
                    $block->setDefaultSort($sort)->setDefaultDir($direction);

                    if (method_exists($block, 'removeColumn')):
                        $block
                            ->removeColumn('store_id')
                            ->removeColumn('created_at')
                            ->removeColumn('billing_name')
                            ->removeColumn('shipping_name')
                            ->removeColumn('base_grand_total')
                            ->removeColumn('grand_total')
                            ->removeColumn('status');
                    endif;

                    $selected = Mage::getStoreConfig('customordergrid/configure/columnsorder');
                    $selected = !empty($selected) ? explode(',', $selected) : array();
                    $selected = array_reverse($selected);
                    $widths = Mage::getStoreConfig('customordergrid/configure/columnswidth');
                    $widths = isset($widths) ? explode(',', $widths) : array();
                    $columnWidths = array();
                    foreach ($widths as $width):
                        $split = explode(':', $width);
                        if (!empty($split[1])):
                            $thiswidth = preg_replace('/\D/', '', $split[1]);
                        else:
                            $thiswidth = '';
                        endif;
                        $columnWidths[$split[0]] = $thiswidth;
                    endforeach;

                    foreach ($selected as $column):
                        $width = empty($columnWidths[$column]) ? null : $columnWidths[$column] . 'px';
                        $this->_addNewColumn($column, $block, $width);
                    endforeach;
                    $block->getColumn('real_order_id')->addData(array('filter_index' => 'main_table.increment_id'));
                    $block->sortColumnsByOrder();
                endif;
            endif;
        endif;
    }
    
    public function adminhtmlSalesOrderGridCollectionLoadBefore($observer)
    {
        if ($this->_isEnabled()):
            $selected = Mage::getStoreConfig('customordergrid/configure/columnsorder');
            $selected = isset($selected) && $selected ? explode(',', $selected) : false;
            
            $select = $observer->getOrderGridCollection()->getSelect();
            $resource = Mage::getSingleton('core/resource');
            
            $select
                ->join(
                    array('order' => $resource->getTableName('sales/order')),
                    'main_table.entity_id = order.entity_id',
                    array('is_virtual', 'shipping_method', 'coupon_code', 'customer_email', 'base_shipping_amount', 'shipping_amount', 'base_subtotal', 'subtotal', 'base_tax_amount', 'tax_amount', 'customer_is_guest', 'total_qty_ordered', 'base_discount_amount', 'total_item_count', 'customer_group_id')
                );

            $billing = array('billing_company', 'billing_postcode', 'billing_region', 'billing_country');
            $shipping = array('shipping_company', 'shipping_postcode', 'shipping_region', 'shipping_country');
            
            if (array_intersect($billing, $selected)):
                $select->join(
                    array('billing' => $resource->getTableName('sales/order_address')),
                    'order.billing_address_id = billing.entity_id',
                    array('billing_company' => 'company', 'billing_postcode' => 'postcode', 'billing_region' => 'region', 'billing_country' => 'country_id')
                );
            endif;
            
            if (array_intersect($shipping, $selected)):
                $select->joinLeft(
                    array('shipping' => $resource->getTableName('sales/order_address')),
                    'order.shipping_address_id = shipping.entity_id',
                    array('shipping_company' => 'company', 'shipping_postcode' => 'postcode', 'shipping_region' => 'region', 'shipping_country' => 'country_id')
                );
            endif;
            
            if (in_array('method', $selected) || in_array('cc_type', $selected)):
                $select->join(
                    array('payment_method' => $resource->getTableName('sales/order_payment')),
                    'order.entity_id = payment_method.parent_id',
                    array('method', 'cc_type')
                );
            endif;
            
            if (in_array('sku', $selected) || in_array('name', $selected)):
                $select->joinLeft(
                    array('items' => $resource->getTableName('sales/order_item')),
                    'items.order_id = main_table.entity_id AND items.product_type = "simple"',
                    array('sku' => new Zend_Db_Expr('GROUP_CONCAT(items.sku SEPARATOR ", ")'), 'name' => new Zend_Db_Expr('GROUP_CONCAT(items.name SEPARATOR ", ")'))
                );
                $select->group('main_table.entity_id');
            endif;
        endif;
    }

    private function _addNewColumn($column, $block, $width)
    {
        switch ($column):
            case 'store_id':
                if (!Mage::app()->isSingleStoreMode()):
                    $block->addColumnAfter('store_id', array(
                        'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
                        'index'     => 'store_id',
                        'filter_index' => 'main_table.store_id',
                        'type'      => 'store',
                        'store_view'=> true,
                        'display_deleted' => true,
                        'width' => $width
                    ), 'real_order_id');
                endif;
                break;
            case 'created_at':
                $block->addColumnAfter('created_at', array(
                    'header' => Mage::helper('sales')->__('Purchased On'),
                    'index' => 'created_at',
                    'filter_index' => 'main_table.created_at',
                    'type' => 'datetime',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'updated_at':
                $block->addColumnAfter('updated_at', array(
                    'header' => Mage::helper('sales')->__('Order Modified'),
                    'index' => 'updated_at',
                    'filter_index' => 'main_table.updated_at',
                    'type' => 'datetime',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'billing_name':
                $block->addColumnAfter('billing_name', array(
                    'header' => Mage::helper('sales')->__('Bill to Name'),
                    'index' => 'billing_name',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'shipping_name':
                $block->addColumnAfter('shipping_name', array(
                    'header' => Mage::helper('sales')->__('Ship to Name'),
                    'index' => 'shipping_name',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'base_grand_total':
                $block->addColumnAfter('base_grand_total', array(
                    'header' => Mage::helper('sales')->__('G.T. (Base)'),
                    'index' => 'base_grand_total',
                    'filter_index' => 'main_table.base_grand_total',
                    'type'  => 'currency',
                    'currency' => 'base_currency_code',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'grand_total':
                $block->addColumnAfter('grand_total', array(
                    'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
                    'index' => 'grand_total',
                    'filter_index' => 'main_table.grand_total',
                    'type'  => 'currency',
                    'currency' => 'order_currency_code',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'billing_company':
                $block->addColumnAfter('billing_company', array(
                    'header' => Mage::helper('sales')->__('Billing Company'),
                    'index' => 'billing_company',
                    'filter_index' => 'billing.company',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'shipping_company':
                $block->addColumnAfter('shipping_company', array(
                    'header' => Mage::helper('sales')->__('Ship to Company'),
                    'index' => 'shipping_company',
                    'filter_index' => 'shipping.company',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'status':
                $block->addColumnAfter('status', array(
                    'header' => Mage::helper('sales')->__('Status'),
                    'index' => 'status',
                    'filter_index' => 'main_table.status',
                    'type'  => 'options',
                    'width' => $width,
                    'options' => Mage::getSingleton('sales/order_config')->getStatuses()
                ), 'real_order_id');
                break;
            case 'sku':
                $block->addColumnAfter('sku', array(
                    'header' => Mage::helper('sales')->__('SKU'),
                    'index' => 'sku',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'name':
                $block->addColumnAfter('name', array(
                    'header' => Mage::helper('sales')->__('Product Name'),
                    'index' => 'name',
                    'filter_index' => 'items.name',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'is_virtual':
                $block->addColumnAfter('is_virtual', array(
                    'header' => Mage::helper('sales')->__('Is Virtual'),
                    'index' => 'is_virtual',
                    'filter_index' => 'order.is_virtual',
                    'type' => 'options',
                    'width' => $width,
                    'options' => Mage::helper('customordergrid')->virtualStatuses()
                ), 'real_order_id');
                break;
            case 'shipping_method':
                $block->addColumnAfter('shipping_method', array(
                    'header' => Mage::helper('sales')->__('Shipping Method'),
                    'index' => 'shipping_method',
                    'type'  => 'options',
                    'width' => $width,
                    'options' => Mage::helper('customordergrid')->shippingMethods()
                ), 'real_order_id');
                break;
            case 'coupon_code':
                $block->addColumnAfter('coupon_code', array(
                    'header' => Mage::helper('sales')->__('Coupon Code'),
                    'index' => 'coupon_code',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'customer_email':
                $block->addColumnAfter('customer_email', array(
                    'header' => Mage::helper('sales')->__('Customer Email'),
                    'index' => 'customer_email',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'customer_group_id':
                $block->addColumnAfter('customer_group_id', array(
                    'header' => Mage::helper('sales')->__('Customer Group'),
                    'index' => 'customer_group_id',
                    'width' => $width,
                    'type' => 'options',
                    'options' => Mage::helper('customordergrid')->customerGroups()
                ), 'real_order_id');
                break;
            case 'base_shipping_amount':
                $block->addColumnAfter('base_shipping_amount', array(
                    'header' => Mage::helper('sales')->__('Shipping (Base)'),
                    'index' => 'base_shipping_amount',
                    'filter_index' => 'order.base_shipping_amount',
                    'type'  => 'currency',
                    'currency' => 'base_currency_code',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'shipping_amount':
                $block->addColumnAfter('shipping_amount', array(
                    'header' => Mage::helper('sales')->__('Shipping (Purchased)'),
                    'index' => 'shipping_amount',
                    'filter_index' => 'order.shipping_amount',
                    'type'  => 'currency',
                    'currency' => 'order_currency_code',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'base_subtotal':
                $block->addColumnAfter('base_subtotal', array(
                    'header' => Mage::helper('sales')->__('Subtotal (Base)'),
                    'index' => 'base_subtotal',
                    'filter_index' => 'order.base_subtotal',
                    'type'  => 'currency',
                    'currency' => 'base_currency_code',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'subtotal':
                $block->addColumnAfter('subtotal', array(
                    'header' => Mage::helper('sales')->__('Subtotal (Purchased)'),
                    'index' => 'subtotal',
                    'filter_index' => 'order.subtotal',
                    'type'  => 'currency',
                    'currency' => 'order_currency_code',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'base_tax_amount':
                $block->addColumnAfter('base_tax_amount', array(
                    'header' => Mage::helper('sales')->__('Tax (Base)'),
                    'index' => 'base_tax_amount',
                    'filter_index' => 'order.base_tax_amount',
                    'type'  => 'currency',
                    'currency' => 'base_currency_code',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'tax_amount':
                $block->addColumnAfter('tax_amount', array(
                    'header' => Mage::helper('sales')->__('Tax (Purchased)'),
                    'index' => 'tax_amount',
                    'filter_index' => 'order.tax_amount',
                    'type'  => 'currency',
                    'currency' => 'order_currency_code',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'customer_is_guest':
                $block->addColumnAfter('customer_is_guest', array(
                    'header' => Mage::helper('sales')->__('Guest Checkout'),
                    'index' => 'customer_is_guest',
                    'filter_index' => 'order.customer_is_guest',
                    'type'  => 'options',
                    'width' => $width,
                    'options' => Mage::helper('customordergrid')->registeredStatuses()
                ), 'real_order_id');
                break;
            case 'order_currency_code':
                $block->addColumnAfter('order_currency_code', array(
                    'header' => Mage::helper('sales')->__('Currency'),
                    'index' => 'order_currency_code',
                    'filter_index' => 'main_table.order_currency_code',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'method':
                $block->addColumnAfter('method', array(
                    'header' => Mage::helper('sales')->__('Payment Method'),
                    'index' => 'method',
                    'filter_index' => 'payment_method.method',
                    'type'  => 'options',
                    'options' => Mage::helper('customordergrid')->paymentMethods(),
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'cc_type':
                $block->addColumnAfter('cc_type', array(
                    'header' => Mage::helper('sales')->__('Credit Card Type'),
                    'index' => 'cc_type',
                    'filter_index' => 'payment_method.cc_type',
                    'type'  => 'options',
                    'options' => Mage::helper('customordergrid')->ccTypes(),
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'total_item_count':
                $block->addColumnAfter('total_item_count', array(
                    'header' => Mage::helper('sales')->__('Product Count'),
                    'index' => 'total_item_count',
                    'filter_index' => 'order.total_item_count',
                    'type'  => 'currency',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'total_qty_ordered':
                $block->addColumnAfter('total_qty_ordered', array(
                    'header' => Mage::helper('sales')->__('Product Quantity'),
                    'index' => 'total_qty_ordered',
                    'filter_index' => 'order.total_qty_ordered',
                    'type'  => 'currency',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'billing_postcode':
                $block->addColumnAfter('billing_postcode', array(
                    'header' => Mage::helper('sales')->__('Billing Postcode'),
                    'index' => 'billing_postcode',
                    'filter_index' => 'billing.postcode',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'shipping_postcode':
                $block->addColumnAfter('shipping_postcode', array(
                    'header' => Mage::helper('sales')->__('Ship to Postcode'),
                    'index' => 'shipping_postcode',
                    'filter_index' => 'shipping.postcode',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'billing_region':
                $block->addColumnAfter('billing_region', array(
                    'header' => Mage::helper('sales')->__('Billing Region'),
                    'index' => 'billing_region',
                    'filter_index' => 'billing.region',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'shipping_region':
                $block->addColumnAfter('shipping_region', array(
                    'header' => Mage::helper('sales')->__('Ship to Region'),
                    'index' => 'shipping_region',
                    'filter_index' => 'shipping.region',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'billing_country':
                $block->addColumnAfter('billing_country', array(
                    'header' => Mage::helper('sales')->__('Billing Country'),
                    'index' => 'billing_country',
                    'filter_index' => 'billing.country_id',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'shipping_country':
                $block->addColumnAfter('shipping_country', array(
                    'header' => Mage::helper('sales')->__('Ship to Country'),
                    'index' => 'shipping_country',
                    'filter_index' => 'shipping.country_id',
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'tracking_number':
                $block->addColumnAfter('tracking_number', array(
                    'header' => Mage::helper('sales')->__('Tracking Number'),
                    'index' => 'tracking_number',
                    'renderer' => 'customordergrid/sales_order_grid_renderer_trackingNumber',
                    'filter' => false,
                    'sortable' => false,
                    'width' => $width
                ), 'real_order_id');
                break;
            case 'base_discount_amount':
                $block->addColumnAfter('base_discount_amount', array(
                    'header' => Mage::helper('sales')->__('Discount (Base)'),
                    'index' => 'base_discount_amount',
                    'filter_index' => 'order.base_discount_amount',
                    'type' => 'currency',
                    'currency' => 'base_currency_code',
                    'width' => $width
                ), 'real_order_id');
                break;
        endswitch;
    }
    
    private function _isEnabled()
    {
        return (bool) Mage::getStoreConfig('customordergrid/configure/enabled');
    }
}