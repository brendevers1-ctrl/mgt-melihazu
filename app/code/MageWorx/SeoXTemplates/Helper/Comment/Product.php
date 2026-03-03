<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoXTemplates\Helper\Comment;

use Magento\Framework\App\Helper\Context;
use MageWorx\SeoXTemplates\Model\Template\Product as ProductTemplate;

class Product extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Product\Source
     */
    protected $commentSource;

    /**
     * Product constructor.
     *
     * @param Product\Source $commentSource
     * @param Context $context
     */
    public function __construct(
        \MageWorx\SeoXTemplates\Helper\Comment\Product\Source $commentSource,
        Context $context
    ) {
        parent::__construct($context);
        $this->commentSource = $commentSource;
    }

    /**
     * @param string $type
     * Return comments for product template
     *
     * @return string
     * @throws \UnexpectedValueException
     */
    public function getComments($type)
    {
        $comment = '<br><small>' . $this->getVariablesComment() . $this->getRandomizerComment();
        switch ($type) {
            case ProductTemplate::TYPE_PRODUCT_SHORT_DESCRIPTION:
            case ProductTemplate::TYPE_PRODUCT_DESCRIPTION:
            case ProductTemplate::TYPE_PRODUCT_META_DESCRIPTION:
                $comment .= $this->getAdditionalCategoryComment();
                $comment .= $this->getDescriptionExample();
                break;
            case ProductTemplate::TYPE_PRODUCT_META_KEYWORDS:
                $comment .= $this->getAdditionalCategoryComment();
                $comment .= $this->getKeywordsExample();
                break;
            case ProductTemplate::TYPE_PRODUCT_SEO_NAME:
                $comment .= $this->getSeoNameExample();
                break;
            case ProductTemplate::TYPE_PRODUCT_URL_KEY:
                $comment .= $this->getUrlExample();
                break;
            case ProductTemplate::TYPE_PRODUCT_META_TITLE:
                $comment .= $this->getAdditionalCategoryComment();
                $comment .= $this->getMetaTitleExample();
                break;
            case ProductTemplate::TYPE_PRODUCT_GALLERY:
                $comment .= $this->getAdditionalGalleryComment();
                $comment .= $this->getGalleryExample();
                break;
            default:
                throw new \UnexpectedValueException(__('SEO XTemplates: Unknow Product Template Type'));
        }

        return $comment.'</small>';
    }

    /**
     * Return comment for url variables
     *
     * @deprecated For backward compatibility with custom solutions
     * @return string
     */
    protected function getVariablesComment()
    {
        return $this->commentSource->getVariablesComment();
    }

    /**
     * Return additional category comment
     *
     * @deprecated For backward compatibility
     * @return string
     */
    public function getAdditionalCategoryComment()
    {
        return $this->commentSource->getAdditionalGalleryComment();
    }

    /**
     * Return comment for randomizer
     *
     * @deprecated For backward compatibility with custom solutions
     * @return string
     */
    protected function getRandomizerComment()
    {
        return $this->commentSource->getRandomizerComment();
    }

    /**
     * Return example for meta title
     *
     * @deprecated For backward compatibility with custom solutions
     * @return string
     */
    protected function getMetaTitleExample()
    {
        return $this->commentSource->getMetaTitleExample();
    }

    /**
     * Return example for keywords
     *
     * @deprecated For backward compatibility with custom solutions
     * @return string
     */
    protected function getKeywordsExample()
    {
        return $this->commentSource->getKeywordsExample();
    }

    /**
     * Return example for description
     *
     * @deprecated For backward compatibility with custom solutions
     * @return string
     */
    protected function getDescriptionExample()
    {
        return $this->commentSource->getDescriptionExample();
    }

    /**
     * Return example for url
     *
     * @deprecated For backward compatibility with custom solutions
     * @return string
     */
    protected function getUrlExample()
    {
        $this->commentSource->getUrlExample();
    }

    /**
     * Return example for seo name
     *
     * @deprecated For backward compatibility with custom solutions
     * @return string
     */
    protected function getSeoNameExample()
    {
        return $this->commentSource->getSeoNameExample();
    }

    /**
     * @deprecated For backward compatibility with custom solutions
     * @return string
     */
    public function getAdditionalGalleryComment()
    {
        return $this->commentSource->getAdditionalGalleryComment();
    }

    /**
     * Return example for gallery
     *
     * @deprecated For backward compatibility with custom solutions
     * @return string
     */
    protected function getGalleryExample()
    {
        return $this->commentSource->getGalleryExample();
    }
}
