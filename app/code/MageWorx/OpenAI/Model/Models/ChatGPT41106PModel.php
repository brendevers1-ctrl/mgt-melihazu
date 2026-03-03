<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\Models;

class ChatGPT41106PModel extends AbstractChatGPTModel
{
    /** @var string Model type */
    protected string $type             = 'gpt-4-1106-preview';
    protected string $path             = 'v1/chat/completions';
    protected int    $maxContextLength = 128000;
}
