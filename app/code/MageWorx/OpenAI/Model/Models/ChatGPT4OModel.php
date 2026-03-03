<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\Models;

class ChatGPT4OModel extends AbstractChatGPTModel
{
    /** @var string Model type */
    protected string $type = 'gpt-4o';
    protected string $path = 'v1/chat/completions';
    protected int    $maxContextLength = 128000;
}
