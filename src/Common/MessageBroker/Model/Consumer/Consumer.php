<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Model\Consumer;

use Gaming\Common\MessageBroker\Model\Context\Context;
use Gaming\Common\MessageBroker\Model\Message\Message;
use Gaming\Common\MessageBroker\Model\Subscription\Subscription;

interface Consumer
{
    public function handle(Message $message, Context $context): void;

    /**
     * @return Subscription[]
     */
    public function subscriptions(): array;

    public function name(): Name;
}
