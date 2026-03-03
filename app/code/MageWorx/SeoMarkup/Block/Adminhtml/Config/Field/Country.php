<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Block\Adminhtml\Config\Field;

class Country extends \Magento\Config\Block\System\Config\Form\Field
{
    const LIMIT = '50';

    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setComment(__("Limit of 50 elements"));
        return parent::render($element);
    }

    protected function _renderValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = parent::_renderValue($element);
        $multiselectId = $element->getHtmlId();
        $script = $this->getMultiselectLimitedScript($multiselectId);

        $html .= '<script type="text/javascript">' . $script . '</script>';

        return $html;
    }

    protected function getMultiselectLimitedScript(string $multiselectId):string
    {
        if($multiselectId === '') {
            return '';
        }

        $limit = self::LIMIT;
        return <<<JS
    (function() {
        var multiselect = document.getElementById("$multiselectId"),
            options = multiselect.options,
            limit = $limit;
        
        for (option of options) {
            if(option instanceof Element) {
                option.addEventListener("mouseup",function(event) {
                    let selectedOption = event.target,
                        lastSelectedElement = selectedOption.index,
                        firstSelectedElement = multiselect.selectedIndex,
                        selectedOptions = multiselect.selectedOptions,
                        selectedOptionsCount = selectedOptions.length;

                    if (selectedOptionsCount > limit) {
                        if(selectedOptionsCount === 4) {
                           selectedOption.selected = false;
                        } else {
                            if(firstSelectedElement === -1) {return;}
                            let disableElements = [];
                            if(lastSelectedElement == firstSelectedElement) {
                                console.log(selectedOptions);
                                for(let i = 0; i < selectedOptionsCount-limit; i++) {
                                    disableElements.push(selectedOptions[i])
                                }
                            } else if(lastSelectedElement > firstSelectedElement) {
                                for(let i = limit; i < selectedOptionsCount; i++) {
                                    disableElements.push(selectedOptions[i])
                                }
                            }
                            
                            if(disableElements.length > 0) {
                                for (let i = 0; i < disableElements.length; i++) {
                                    disableElements[i].selected = false;
                                }
                            }
                        }
                    }
                });
            } 
        }

    })();
JS;
    }
}
