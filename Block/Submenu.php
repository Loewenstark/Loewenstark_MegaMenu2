<?php

namespace Loewenstark\MegaMenu\Block;

use Magento\Customer\Model\Context as CustomerContext;

class Submenu extends \Magento\Framework\View\Element\Template
{
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
     * @var \Magento\Framework\App\Http\Context
     */
    protected $_httpContext;


    /**
     * 
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection
     */
    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context, \Magento\Catalog\Helper\Category $categoryHelper, \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection, \Magento\Framework\App\Http\Context $httpContext)
    {
        $this->_storeManager = $context->getStoreManager();
        $this->_categoryHelper = $categoryHelper;
        $this->_categoryCollection = $categoryCollection;
        $this->_request = $context->getRequest();
        $this->_httpContext = $httpContext;
        parent::__construct($context);

        $this->addData(
                [
                    'cache_lifetime' => 86400,
                    'cache_tags' => [
                        \Magento\Catalog\Model\Category::CACHE_TAG,
                        \Magento\Catalog\Model\Category::CACHE_TAG . '_' . $this->getCategoryId()
                    ]
                ]
        );
    }

    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return [
            'MEGAMENU_SUBMENU',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->_httpContext->getValue(CustomerContext::CONTEXT_GROUP),
            'template' => $this->getTemplate(),
            $this->getCategoryId()
        ];
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
            echo 'Need ID...';
            die();
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
     * @param \Magento\Catalog\Model\Category $category
     * @return int
     */
    public function getLevel(\Magento\Catalog\Model\Category $category)
    {
        $level = (int) $category->getLevel();
        return $level - 2;
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
                if (in_array($category->getId(), $id_category->getPathIds()))
                {
                    return true;
                }
            }
        }
        return false;
    }

}
