<?php

namespace Loewenstark\MegaMenu\Controller\Index;

use Magento\Framework\App\Action\Context;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_resultJsonFactory;
    protected $_layoutFactory;
    protected $_storeManager = null;
    protected $_storeRootId;
    protected $_categoryCollection;

    public function __construct(Context $context, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection, \Magento\Framework\View\LayoutFactory $layoutFactory)
    {
        $this->_storeRootId = $this->getStoreManager()->getGroup()->getRootCategoryId();
        $this->_categoryCollection = $categoryCollection;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_layoutFactory = $layoutFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $layoutFactory = $this->_layoutFactory->create();
        $result = $this->_resultJsonFactory->create();
        $data = [];
        
        $top_level_categories = $this->getCategoryCollection(null, array('entity_id', 'name'));
        foreach ($top_level_categories as $top_level_category)
        {
            $tmp = [];
            $tmp['id'] = $top_level_category->getId();
            $tmp['name'] = $top_level_category->getName();
            $tmp['submenu_html'] = $layoutFactory->createBlock("Loewenstark\MegaMenu\Block\Submenu")
                    ->setTemplate("Loewenstark_MegaMenu::submenu.phtml")
                    ->setCategoryId($top_level_category->getId())
                    ->toHtml();
            $data[] = $tmp;
        }

        $result = $result->setData($data);

        return $result;
    }

    /**
     * 
     * @param int $parent_id
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function getCategoryCollection($parent_id = null, $attributes = '*')
    {
        if (is_null($parent_id))
        {
            $parent_id = (int) $this->_storeRootId;
        }
        $parent_id = (int) $parent_id;
        $collection = $this->_categoryCollection->create()
                ->addAttributeToSelect($attributes)
                ->addAttributeToFilter('parent_id', $parent_id)
                ->addAttributeToFilter('is_active', 1)
                ->addAttributeToFilter('include_in_menu', 1)
                ->addAttributeToSort('position')
        ;

        return $collection;
    }

    /**
     * @return StoreManagerInterface
     * @deprecated
     */
    private function getStoreManager()
    {
        if (null === $this->_storeManager)
        {
            $this->_storeManager = \Magento\Framework\App\ObjectManager::getInstance()
                    ->get('Magento\Store\Model\StoreManagerInterface');
        }
        return $this->_storeManager;
    }

}
