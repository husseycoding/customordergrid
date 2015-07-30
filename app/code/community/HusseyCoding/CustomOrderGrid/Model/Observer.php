<?php
class HusseyCoding_CustomOrderGrid_Model_Observer extends Varien_Event_Observer
{
    public function salesOrderGridCollectionLoadBefore(Varien_Event_Observer $observer)
    {
        $selected = Mage::getStoreConfig('customordergrid/configure/columnsorder');
        $this->_selected = isset($selected) && $selected ? explode(',', $selected) : false;

        $collection = $observer->getOrderGridCollection();
        $collection
            ->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('shipping_name')
            ->addAttributeToSelect('billing_name')
            ->addAttributeToSelect('increment_id')
            ->addAttributeToSelect('store_id')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('base_grand_total')
            ->addAttributeToSelect('grand_total')
            ->addAttributeToSelect('status');
        $select = $collection->getSelect();
        $resource = Mage::getSingleton('core/resource');

        $select
            ->join(
                array('order' => $resource->getTableName('sales/order')),
                'main_table.entity_id = order.entity_id',
                array('is_virtual','shipping_method','coupon_code','customer_email','base_shipping_amount','shipping_amount','base_subtotal','subtotal','base_tax_amount','tax_amount','customer_is_guest',
                    'order_currency_code','total_qty_ordered','base_discount_amount','total_item_count')
            );

        $billing = array('billing_company', 'billing_postcode', 'billing_region', 'billing_country');
        $shipping = array('shipping_company', 'shipping_postcode', 'shipping_region', 'shipping_country');

        if (array_intersect($billing, $this->_selected)):
            $select->join(
                array('billing' => $resource->getTableName('sales/order_address')),
                'order.billing_address_id = billing.entity_id',
                array('billing_company' => 'company', 'billing_postcode' => 'postcode', 'billing_region' => 'region', 'billing_country' => 'country_id')
            );
        endif;

        if (array_intersect($shipping, $this->_selected)):
            $select->joinLeft(
                array('shipping' => $resource->getTableName('sales/order_address')),
                'order.shipping_address_id = shipping.entity_id',
                array('shipping_company' => 'company', 'shipping_postcode' => 'postcode', 'shipping_region' => 'region', 'shipping_country' => 'country_id')
            );
        endif;

        if (in_array('method', $this->_selected) || in_array('cc_type', $this->_selected)):
            $select->join(
                array('payment_method' => $resource->getTableName('sales/order_payment')),
                'order.entity_id = payment_method.parent_id',
                array('method', 'cc_type')
            );
        endif;


        if (in_array('sku', $this->_selected) || in_array('name', $this->_selected)):
            $select->joinLeft('sales_flat_order_item',
                'sales_flat_order_item.order_id = main_table.entity_id',
                array('sku' => new Zend_Db_Expr('group_concat(sales_flat_order_item.sku SEPARATOR ", ")'), 'name' => new Zend_Db_Expr('group_concat(sales_flat_order_item.name SEPARATOR ", ")'))
            );
            $select->group('main_table.entity_id');
        endif;
    }


    public function appendColumns(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        if (!isset($block)) {
            return $this;
        }

        if ($block->getType() == 'adminhtml/sales_order_grid') {

            $block->addColumn('real_order_id', array(
                'header'=> Mage::helper('sales')->__('Order #'),
                'width' => '80px',
                'type'  => 'text',
                'filter_index' => 'main_table.increment_id',
                'index' => 'increment_id'
            ));

            $selected = Mage::getStoreConfig('customordergrid/configure/columnsorder');
            $this->_selected = isset($selected) && $selected ? explode(',', $selected) : false;
            $widths = Mage::getStoreConfig('customordergrid/configure/columnswidth');
            $widths = isset($widths) ? explode(',', $widths) : false;
            $columnWidths = array();
            $i = 0;
            foreach($widths as $width)
            {
                $split = explode(':', $width);
                $split[1] = preg_replace('/\D/', '', $split[1]); //remove all non integer chars
                $columnWidths[$split[0]] = $split[1];
                $i++;
            }

            foreach ($this->_selected as $column):
                $width = ($columnWidths[$column] == "") ? '80px' : $columnWidths[$column] . 'px';
                $this->_addNewColumn($column, $block, $width);
            endforeach;
        }
    }

    private function _addNewColumn($column, $block, $width)
    {
        switch ($column):
            case 'store_id':
                if (!Mage::app()->isSingleStoreMode()) {
                    $block->addColumn('store_id', array(
                        'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
                        'index'     => 'store_id',
                        'filter_index' => 'main_table.store_id',
                        'type'      => 'store',
                        'store_view'=> true,
                        'display_deleted' => true,
                        'width' => $width
                    ));
                }
                break;
            case 'created_at':
                $block->addColumn('created_at', array(
                    'header' => Mage::helper('sales')->__('Purchased On'),
                    'index' => 'created_at',
                    'filter_index' => 'main_table.created_at',
                    'type' => 'datetime',
                    'width' => $width
                ));
                break;
            case 'updated_at':
                $block->addColumn('updated_at', array(
                    'header' => Mage::helper('sales')->__('Order Modified'),
                    'index' => 'updated_at',
                    'filter_index' => 'main_table.updated_at',
                    'type' => 'datetime',
                    'width' => $width
                ));
                break;
            case 'billing_name':
                $block->addColumn('billing_name', array(
                    'header' => Mage::helper('sales')->__('Bill to Name'),
                    'index' => 'billing_name',
                    'width' => $width
                ));
                break;
            case 'shipping_name':
                $block->addColumn('shipping_name', array(
                    'header' => Mage::helper('sales')->__('Ship to Name'),
                    'index' => 'shipping_name',
                    'width' => $width
                ));
                break;
            case 'base_grand_total':
                $block->addColumn('base_grand_total', array(
                    'header' => Mage::helper('sales')->__('G.T. (Base)'),
                    'index' => 'base_grand_total',
                    'filter_index' => 'main_table.base_grand_total',
                    'type'  => 'currency',
                    'currency' => 'base_currency_code',
                    'width' => $width
                ));
                break;
            case 'grand_total':
                $block->addColumn('grand_total', array(
                    'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
                    'index' => 'grand_total',
                    'filter_index' => 'main_table.grand_total',
                    'type'  => 'currency',
                    'currency' => 'order_currency_code',
                    'width' => $width
                ));
                break;
            case 'billing_company':
                $block->addColumn('billing_company', array(
                    'header' => Mage::helper('sales')->__('Billing Company'),
                    'index' => 'billing_company',
                    'filter_index' => 'billing.company',
                    'width' => $width
                ));
                break;
            case 'shipping_company':
                $block->addColumn('shipping_company', array(
                    'header' => Mage::helper('sales')->__('Ship to Company'),
                    'index' => 'shipping_company',
                    'filter_index' => 'shipping.company',
                    'width' => $width
                ));
                break;
            case 'status':
                $block->addColumn('status', array(
                    'header' => Mage::helper('sales')->__('Status'),
                    'index' => 'status',
                    'filter_index' => 'main_table.status',
                    'type'  => 'options',
                    'width' => $width,
                    'options' => Mage::getSingleton('sales/order_config')->getStatuses()
                ));
                break;
            case 'sku':
                $block->addColumn('sku', array(
                    'header' => Mage::helper('sales')->__('SKU'),
                    'index' => 'sku',
                    'width' => $width,
                    'filter_condition_callback' => array('HusseyCoding_CustomOrderGrid_Helper_Data', 'filterSkus')
                ));
                break;
            case 'name':
                $block->addColumn('name', array(
                    'header' => Mage::helper('sales')->__('Product Name'),
                    'index' => 'name',
                    'filter_index' => 'sku_table.name',
                    'width' => $width
                ));
                break;
            case 'is_virtual':
                $block->addColumn('is_virtual', array(
                    'header' => Mage::helper('sales')->__('Is Virtual'),
                    'index' => 'is_virtual',
                    'filter_index' => 'order.is_virtual',
                    'type' => 'options',
                    'width' => $width,
                    'options' => Mage::helper('customordergrid')->virtualStatuses()
                ));
                break;
            case 'shipping_method':
                $block->addColumn('shipping_method', array(
                    'header' => Mage::helper('sales')->__('Shipping Method'),
                    'index' => 'shipping_method',
                    'type'  => 'options',
                    'width' => $width,
                    'options' => Mage::helper('customordergrid')->shippingMethods()
                ));
                break;
            case 'coupon_code':
                $block->addColumn('coupon_code', array(
                    'header' => Mage::helper('sales')->__('Coupon Code'),
                    'index' => 'coupon_code',
                    'width' => $width
                ));
                break;
            case 'customer_email':
                $block->addColumn('customer_email', array(
                    'header' => Mage::helper('sales')->__('Customer Email'),
                    'index' => 'customer_email',
                    'width' => $width
                ));
                break;
            case 'base_shipping_amount':
                $block->addColumn('base_shipping_amount', array(
                    'header' => Mage::helper('sales')->__('Shipping (Base)'),
                    'index' => 'base_shipping_amount',
                    'filter_index' => 'order.base_shipping_amount',
                    'type'  => 'currency',
                    'currency' => 'base_currency_code',
                    'width' => $width
                ));
                break;
            case 'shipping_amount':
                $block->addColumn('shipping_amount', array(
                    'header' => Mage::helper('sales')->__('Shipping (Purchased)'),
                    'index' => 'shipping_amount',
                    'filter_index' => 'order.shipping_amount',
                    'type'  => 'currency',
                    'currency' => 'order_currency_code',
                    'width' => $width
                ));
                break;
            case 'base_subtotal':
                $block->addColumn('base_subtotal', array(
                    'header' => Mage::helper('sales')->__('Subtotal (Base)'),
                    'index' => 'base_subtotal',
                    'filter_index' => 'order.base_subtotal',
                    'type'  => 'currency',
                    'currency' => 'base_currency_code',
                    'width' => $width
                ));
                break;
            case 'subtotal':
                $block->addColumn('subtotal', array(
                    'header' => Mage::helper('sales')->__('Subtotal (Purchased)'),
                    'index' => 'subtotal',
                    'filter_index' => 'order.subtotal',
                    'type'  => 'currency',
                    'currency' => 'order_currency_code',
                    'width' => $width
                ));
                break;
            case 'base_tax_amount':
                $block->addColumn('base_tax_amount', array(
                    'header' => Mage::helper('sales')->__('Tax (Base)'),
                    'index' => 'base_tax_amount',
                    'filter_index' => 'order.base_tax_amount',
                    'type'  => 'currency',
                    'currency' => 'base_currency_code',
                    'width' => $width
                ));
                break;
            case 'tax_amount':
                $block->addColumn('tax_amount', array(
                    'header' => Mage::helper('sales')->__('Tax (Purchased)'),
                    'index' => 'tax_amount',
                    'filter_index' => 'order.tax_amount',
                    'type'  => 'currency',
                    'currency' => 'order_currency_code',
                    'width' => $width
                ));
                break;
            case 'customer_is_guest':
                $block->addColumn('customer_is_guest', array(
                    'header' => Mage::helper('sales')->__('Guest Checkout'),
                    'index' => 'customer_is_guest',
                    'filter_index' => 'order.customer_is_guest',
                    'type'  => 'options',
                    'width' => $width,
                    'options' => Mage::helper('customordergrid')->registeredStatuses()
                ));
                break;
            case 'order_currency_code':
                $block->addColumn('order_currency_code', array(
                    'header' => Mage::helper('sales')->__('Currency'),
                    'index' => 'order_currency_code',
                    'filter_index' => 'order.order_currency_code',
                    'width' => $width
                ));
                break;
            case 'method':
                $block->addColumn('method', array(
                    'header' => Mage::helper('sales')->__('Payment Method'),
                    'index' => 'method',
                    'filter_index' => 'payment_method.method',
                    'type'  => 'options',
                    'options' => Mage::helper('customordergrid')->paymentMethods(),
                    'width' => $width
                ));
                break;
            case 'cc_type':
                $block->addColumn('cc_type', array(
                    'header' => Mage::helper('sales')->__('Credit Card Type'),
                    'index' => 'cc_type',
                    'filter_index' => 'payment_method.cc_type',
                    'type'  => 'options',
                    'options' => Mage::helper('customordergrid')->ccTypes(),
                    'width' => $width
                ));
                break;
            case 'total_item_count':
                $block->addColumn('total_item_count', array(
                    'header' => Mage::helper('sales')->__('Product Count'),
                    'index' => 'total_item_count',
                    'filter_index' => 'order.total_item_count',
                    'type'  => 'currency',
                    'width' => $width
                ));
                break;
            case 'total_qty_ordered':
                $block->addColumn('total_qty_ordered', array(
                    'header' => Mage::helper('sales')->__('Product Quantity'),
                    'index' => 'total_qty_ordered',
                    'filter_index' => 'order.total_qty_ordered',
                    'type'  => 'currency',
                    'width' => $width
                ));
                break;
            case 'billing_postcode':
                $block->addColumn('billing_postcode', array(
                    'header' => Mage::helper('sales')->__('Billing Postcode'),
                    'index' => 'billing_postcode',
                    'filter_index' => 'billing.postcode',
                    'width' => $width
                ));
                break;
            case 'shipping_postcode':
                $block->addColumn('shipping_postcode', array(
                    'header' => Mage::helper('sales')->__('Ship to Postcode'),
                    'index' => 'shipping_postcode',
                    'filter_index' => 'shipping.postcode',
                    'width' => $width
                ));
                break;
            case 'billing_region':
                $block->addColumn('billing_region', array(
                    'header' => Mage::helper('sales')->__('Billing Region'),
                    'index' => 'billing_region',
                    'filter_index' => 'billing.region',
                    'width' => $width
                ));
                break;
            case 'shipping_region':
                $block->addColumn('shipping_region', array(
                    'header' => Mage::helper('sales')->__('Ship to Region'),
                    'index' => 'shipping_region',
                    'filter_index' => 'shipping.region',
                    'width' => $width
                ));
                break;
            case 'billing_country':
                $block->addColumn('billing_country', array(
                    'header' => Mage::helper('sales')->__('Billing Country'),
                    'index' => 'billing_country',
                    'filter_index' => 'billing.country_id',
                    'width' => $width
                ));
                break;
            case 'shipping_country':
                $block->addColumn('shipping_country', array(
                    'header' => Mage::helper('sales')->__('Ship to Country'),
                    'index' => 'shipping_country',
                    'filter_index' => 'shipping.country_id',
                    'width' => $width
                ));
                break;
            case 'tracking_number':
                $block->addColumn('tracking_number', array(
                    'header' => Mage::helper('sales')->__('Tracking Number'),
                    'index' => 'tracking_number',
                    'renderer' => 'customordergrid/sales_order_grid_renderer_trackingNumber',
                    'filter' => false,
                    'sortable' => false,
                    'width' => $width
                ));
                break;
            case 'base_discount_amount':
                $block->addColumn('base_discount_amount', array(
                    'header' => Mage::helper('sales')->__('Discount (Base)'),
                    'index' => 'base_discount_amount',
                    'filter_index' => 'order.base_discount_amount',
                    'type' => 'currency',
                    'currency' => 'base_currency_code',
                    'width' => $width
                ));
                break;
        endswitch;
    }

    public function filterSkus($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $collection->getSelect()->having(
            "group_concat(`sales_flat_order_item`.sku SEPARATOR ', ') like ?", "%$value%");

        return $this;
    }
}
