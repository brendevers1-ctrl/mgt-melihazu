<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\Models;

class ChatGPT4Model extends AbstractChatGPTModel
{
    /** @var string Model type */
    protected string $type             = 'gpt-4';
    protected string $path             = 'v1/chat/completions';
    protected int    $maxContextLength = 8192;
}
