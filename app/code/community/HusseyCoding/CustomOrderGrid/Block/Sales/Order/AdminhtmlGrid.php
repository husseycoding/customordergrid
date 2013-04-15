<?php
class HusseyCoding_CustomOrderGrid_Block_Sales_Order_AdminhtmlGrid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    private $_selected;
            
    public function __construct()
    {
        parent::__construct();
        $columnsort = Mage::getStoreConfig('customordergrid/configure/columnsort');
        $selected = Mage::getStoreConfig('customordergrid/configure/columnsorder');
        $this->_selected = $selected ? explode(',', Mage::getStoreConfig('customordergrid/configure/columnsorder')) : false;
        if (!$columnsort):
            $columnsort = 'real_order_id';
        endif;
        $sortdirection = Mage::getStoreConfig('customordergrid/configure/sortdirection') ? Mage::getStoreConfig('customordergrid/configure/sortdirection') : 'DESC';
        if ($this->_selected):
            $this->setDefaultSort($columnsort);
            $this->setDefaultDir($sortdirection);
        endif;
    }
    
    protected function _prepareCollection()
    {
        if (!$this->_selected) return parent::_prepareCollection();
        
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $select = $collection->getSelect();
        $resource = Mage::getSingleton('core/resource');
        
        $select
            ->join(
                array('order' => $resource->getTableName('sales/order')),
                'main_table.entity_id = order.entity_id'
            );
        if (in_array('sku', $this->_selected)):
            $skuquery = clone $select;
            $skuquery
                ->reset()
                ->from(
                    array('item_table' => $resource->getTableName('sales/order_item')),
                    array(new Zend_Db_Expr('GROUP_CONCAT(CONCAT_WS(" x ", TRIM(TRAILING "." FROM TRIM(TRAILING "0" FROM qty_ordered)), sku) SEPARATOR ", ") as sku, order_id'))
                )
                ->group('item_table.order_id');
            
            $select
                ->join(
                    array('sku_table' => $skuquery),
                    'main_table.entity_id = sku_table.order_id',
                    array('sku')
                );
        endif;
        
        $this->setCollection($collection);
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        if (!$this->_selected) return parent::_prepareColumns();
        
        $this->addColumn('real_order_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'filter_index' => 'main_table.increment_id',
            'index' => 'increment_id'
        ));
        
        foreach ($this->_selected as $column):
            $this->addNewColumn($column);
        endforeach;

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('action',
                array(
                    'header'    => Mage::helper('sales')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('sales')->__('View'),
                            'url'     => array('base'=>'*/sales_order/view'),
                            'field'   => 'order_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true
            ));
        }
        $this->addRssList('rss/order/new', Mage::helper('sales')->__('New Order RSS'));

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));

        return $this->sortColumnsByOrder();
    }
    
    private function addNewColumn($column)
    {
        switch ($column):
            case 'store_id':
                if (!Mage::app()->isSingleStoreMode()) {
                    $this->addColumn('store_id', array(
                        'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
                        'index'     => 'store_id',
                        'filter_index' => 'main_table.store_id',
                        'type'      => 'store',
                        'store_view'=> true,
                        'display_deleted' => true
                    ));
                }
                break;
            case 'created_at':
                $this->addColumn('created_at', array(
                    'header' => Mage::helper('sales')->__('Purchased On'),
                    'index' => 'created_at',
                    'filter_index' => 'main_table.created_at',
                    'type' => 'datetime',
                    'width' => '100px'
                ));
                break;
            case 'updated_at':
                $this->addColumn('updated_at', array(
                    'header' => Mage::helper('sales')->__('Order Modified'),
                    'index' => 'updated_at',
                    'filter_index' => 'main_table.updated_at',
                    'type' => 'datetime',
                    'width' => '100px'
                ));
                break;
            case 'billing_name':
                $this->addColumn('billing_name', array(
                    'header' => Mage::helper('sales')->__('Bill to Name'),
                    'index' => 'billing_name'
                ));
                break;
            case 'shipping_name':
                $this->addColumn('shipping_name', array(
                    'header' => Mage::helper('sales')->__('Ship to Name'),
                    'index' => 'shipping_name'
                ));
                break;
            case 'base_grand_total':
                $this->addColumn('base_grand_total', array(
                    'header' => Mage::helper('sales')->__('G.T. (Base)'),
                    'index' => 'base_grand_total',
                    'filter_index' => 'main_table.base_grand_total',
                    'type'  => 'currency',
                    'currency' => 'base_currency_code'
                ));
                break;
            case 'grand_total':
                $this->addColumn('grand_total', array(
                    'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
                    'index' => 'grand_total',
                    'filter_index' => 'main_table.grand_total',
                    'type'  => 'currency',
                    'currency' => 'order_currency_code',
                ));
                break;
            case 'status':
                $this->addColumn('status', array(
                    'header' => Mage::helper('sales')->__('Status'),
                    'index' => 'status',
                    'filter_index' => 'main_table.status',
                    'type'  => 'options',
                    'width' => '70px',
                    'options' => Mage::getSingleton('sales/order_config')->getStatuses()
                ));
                break;
            case 'sku':
                $this->addColumn('sku', array(
                    'header' => Mage::helper('sales')->__('SKU'),
                    'index' => 'sku',
                    'width' => '80px'
                ));
                break;
            case 'shipping_description':
                $this->addColumn('shipping_description', array(
                    'header' => Mage::helper('sales')->__('Shipping Method'),
                    'index' => 'shipping_description'
                ));
                break;
            case 'coupon_code':
                $this->addColumn('coupon_code', array(
                    'header' => Mage::helper('sales')->__('Coupon Code'),
                    'index' => 'coupon_code'
                ));
                break;
            case 'customer_email':
                $this->addColumn('customer_email', array(
                    'header' => Mage::helper('sales')->__('Customer Email'),
                    'index' => 'customer_email'
                ));
                break;
            case 'base_shipping_amount':
                $this->addColumn('base_shipping_amount', array(
                    'header' => Mage::helper('sales')->__('Shipping (Base)'),
                    'index' => 'base_shipping_amount',
                    'filter_index' => 'order.base_shipping_amount',
                    'type'  => 'currency',
                    'currency' => 'base_currency_code'
                ));
                break;
            case 'shipping_amount':
                $this->addColumn('shipping_amount', array(
                    'header' => Mage::helper('sales')->__('Shipping (Purchased)'),
                    'index' => 'shipping_amount',
                    'filter_index' => 'order.shipping_amount',
                    'type'  => 'currency',
                    'currency' => 'order_currency_code'
                ));
                break;
            case 'base_subtotal':
                $this->addColumn('base_subtotal', array(
                    'header' => Mage::helper('sales')->__('Subtotal (Base)'),
                    'index' => 'base_subtotal',
                    'filter_index' => 'order.base_subtotal',
                    'type'  => 'currency',
                    'currency' => 'base_currency_code'
                ));
                break;
            case 'subtotal':
                $this->addColumn('subtotal', array(
                    'header' => Mage::helper('sales')->__('Subtotal (Purchased)'),
                    'index' => 'subtotal',
                    'filter_index' => 'order.subtotal',
                    'type'  => 'currency',
                    'currency' => 'order_currency_code'
                ));
                break;
            case 'base_tax_amount':
                $this->addColumn('base_tax_amount', array(
                    'header' => Mage::helper('sales')->__('Tax (Base)'),
                    'index' => 'base_tax_amount',
                    'filter_index' => 'order.base_tax_amount',
                    'type'  => 'currency',
                    'currency' => 'base_currency_code'
                ));
                break;
            case 'tax_amount':
                $this->addColumn('tax_amount', array(
                    'header' => Mage::helper('sales')->__('Tax (Purchased)'),
                    'index' => 'tax_amount',
                    'filter_index' => 'order.tax_amount',
                    'type'  => 'currency',
                    'currency' => 'order_currency_code'
                ));
                break;
            case 'customer_is_guest':
                $this->addColumn('customer_is_guest', array(
                    'header' => Mage::helper('sales')->__('Guest Checkout'),
                    'index' => 'customer_is_guest',
                    'filter_index' => 'order.customer_is_guest',
                    'type'  => 'options',
                    'width' => '70px',
                    'options' => Mage::helper('customordergrid')->registeredStatuses()
                ));
                break;
            case 'order_currency_code':
                $this->addColumn('order_currency_code', array(
                    'header' => Mage::helper('sales')->__('Currency'),
                    'index' => 'order_currency_code',
                    'filter_index' => 'order.order_currency_code',
                    'width' => '70px'
                ));
                break;
            case 'total_item_count':
                $this->addColumn('total_item_count', array(
                    'header' => Mage::helper('sales')->__('Product Count'),
                    'index' => 'total_item_count',
                    'filter_index' => 'order.total_item_count',
                    'type'  => 'currency'
                ));
                break;
        endswitch;
    }
}
