<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\GoogleAI\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class CustomApiKeyField extends Field
{
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = $element->getElementHtml();

        $validateButtonHtml = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id'    => 'validate_google_api_key_button',
                'label' => __('Validate'),
            ]
        )->toHtml();

        $html .= '<div class="admin__field-control">';
        $html .= $validateButtonHtml;
        $html .= '</div>';

        $html .= '<script type="text/javascript">
           require(["jquery", "mage/translate"], function ($, $t) {
               $("#validate_google_api_key_button").click(function () {
                   $.ajax({
                       url: "' . $this->getUrl('mageworx_googleai/googleai/testkey') . '",
                       type: "POST",
                       data: {
                           form_key: window.FORM_KEY,
                           sk: $("#' . $element->getHtmlId() . '").val()
                       },
                       success: function (response) {
                           if (response.is_key_valid) {
                               alert($t("The API key is valid."));
                           } else {
                               alert($t("Provided API key is not valid. You can find your key in your account at the Google AI portal."));
                           }
                       },
                       error: function () {
                           alert($t("Error occurred while validating the API key."));
                       }
                   });
               });
           });
       </script>';

        $html .= <<<HTML
<style>

#validate_google_api_key_button {
    background-color: #6C7287;
    border-color: #575962;
    color: #FFFFFF;
    border-radius: 3px;
    padding: 8px 12px;
    box-shadow: none;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

#validate_google_api_key_button:hover {
    background-color: #5B616F;
}

.input-text.admin__control-text {
    width: 100%;
    box-sizing: border-box;
}

.admin__field-control {
    margin-top: 10px;
    display: flex;
    align-items: center;
    justify-content: flex-start;
}

.note {
    font-size: 12px;
    color: #737373;
    margin-top: 10px;
}
</style>
HTML;

        return $html;
    }
}
