<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\GoogleAI\Model\Models\Gemini;

class GeminiFlash15 extends AbstractGeminiModel
{
    protected string $type = 'gemini-1.5-flash';
    protected string $path = 'models/gemini-1.5-flash:generateContent';
    protected int    $maxContextLength = 128000;
}
