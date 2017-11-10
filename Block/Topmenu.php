<?php

namespace Loewenstark\MegaMenu\Block;

class Topmenu extends \Magento\Framework\View\Element\Template
{
    /**
     * @var int
     */
    protected $_storeRootId;
    /**
     * @var \Magento\Catalog\Helper\Category 
     */
    protected $_categoryHelper;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory 
     */
    protected $_categoryCollection;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;
    /**
     *
     * @var array
     */
    protected $_currentPath = null;
    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager = null;
    
    protected $_categoryId = null;

    /**
     * 
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection
     */
    public function __construct(
            \Magento\Framework\View\Element\Template\Context $context,
            \Magento\Catalog\Helper\Category $categoryHelper,
            \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection)
    {
        $this->_storeManager = $context->getStoreManager();
        $this->_storeRootId = $context->getStoreManager()->getGroup()->getRootCategoryId();
        $this->_categoryHelper = $categoryHelper;
        $this->_categoryCollection = $categoryCollection;
        $this->_request = $context->getRequest();
        $this->setData('cache_lifetime', 3600); // 3600 = 1 hour
        parent::__construct($context);
    }

    /**
     * 
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $data = parent::getCacheKeyInfo();
        $data = array_merge($data, array(
            get_class($this),
            $this->getCategoryId()
        ));
        return $data;
    }

    /**
     * Return categories helper
     */
    public function getCategoryHelper()
    {
        return $this->_categoryHelper;
    }

    /**
     * 
     * @param int $parent_id
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function getCategoryCollection($parent_id = null)
    {
        if (is_null($parent_id))
        {
            $parent_id = (int) $this->_storeRootId;
        }
        $parent_id = (int) $parent_id;
        $collection = $this->_categoryCollection->create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('parent_id', $parent_id)
                ->addAttributeToFilter('is_active', 1)
                ->addAttributeToFilter('include_in_menu', 1)
                ->addAttributeToSort('position')
        ;

        return $collection;
    }

    /**
     * 
     * @return int
     */
    public function getCategoryId()
    {
        if (is_null($this->_categoryId))
        {
            $category_id = (int) $this->_request->getParam('category_id', 0);
            if (!$category_id)
            {
                $this->_categoryId = 0;
                return 0;
            }
            $parent_id = (int) $this->_storeRootId;
            $id_category = $this->_categoryCollection->create()
                    ->addAttributeToSelect(array('path'))
                    ->addAttributeToFilter('entity_id', $category_id)
                    ->addAttributeToFilter('path', array('like' => '%/'.$parent_id.'/%'))
                    ->setPageSize(1)
                    ->setCurPage(1)
                    ->getSize();
            if ($id_category > 0)
            {
                $this->_categoryId = $category_id;
                return $category_id;
            }
            $this->_categoryId = 0;
            return 0;
        }
        return $this->_categoryId;
    }

    /**
     * 
     * @param \Magento\Catalog\Model\Category $category
     * @return boolean
     */
    public function isActive(\Magento\Catalog\Model\Category $category)
    {
        $category_id = (int) $this->getCategoryId();
        if ($category_id == $category->getId())
        {
            return true;
        }
        if ($category_id > 0)
        {
            $id_category = $this->_categoryCollection->create()
                ->addAttributeToSelect(array('path'))
                ->addAttributeToFilter('entity_id', $category_id)
                ->setPageSize(1)
                ->setCurPage(1)
                ->getFirstItem();
            if ($id_category && $id_category->getId())
            {
                if(in_array($category->getId(), $id_category->getPathIds()))
                {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 
     * @param \Magento\Catalog\Model\Category $category
     * @return int
     */
    public function getLevel(\Magento\Catalog\Model\Category $category)
    {
        $level = (int) $category->getLevel();
        return $level-2;
    }

    /**
     * 
     * @return boolean
     */
    public function hasSubmenu()
    {
        return true;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        return trim(parent::_toHtml());
    }
}
