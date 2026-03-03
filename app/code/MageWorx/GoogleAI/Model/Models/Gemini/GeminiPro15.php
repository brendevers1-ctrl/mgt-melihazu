<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\GoogleAI\Model\Models\Gemini;

class GeminiPro15 extends AbstractGeminiModel
{
    protected string $type = 'gemini-1.5-pro';
    protected string $path = 'models/gemini-1.5-pro:generateContent';
    protected int    $maxContextLength = 128000;
}
