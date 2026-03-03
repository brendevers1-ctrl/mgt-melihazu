<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoAll\Helper;

use Magento\Customer\Model\Group;
use Magento\Framework\App\Helper\Context;
use Magento\Customer\Model\Session;

class SeoFeaturesStatusProvider extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_DISABLE_SEO_FEATURES = 'mageworx_seo/all/disable_seo_features';

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * constructor
     *
     * @param Session $session
     * @param Context $context
     */
    public function __construct(
        Session $session,
        Context $context
    ) {
        $this->session = $session;
        parent::__construct($context);
    }

    /**
     * @return array
     */
    protected function getSeoFeaturesForCustomerPage(): array
    {
        $seoFeaturesForCustomerPage = (string)$this->scopeConfig->getValue(
            self::XML_PATH_DISABLE_SEO_FEATURES
        );

        return $seoFeaturesForCustomerPage ? explode(',', $seoFeaturesForCustomerPage) : [];
    }

    /**
     * @param string $moduleName
     * @return bool
     */
    protected function getIsDisabled(string $moduleName): bool
    {
        if (!$moduleName) {
            return false;
        }

        $disabledModules = $this->getSeoFeaturesForCustomerPage();

        return in_array($moduleName, $disabledModules);
    }

    /**
     * @param string $moduleName
     * @return bool
     */
    public function getStatus(string $moduleName): bool
    {
        return $this->getIsDisabled($moduleName)
            && (int)$this->session->getCustomerGroupId() !== Group::NOT_LOGGED_IN_ID;
    }
}
