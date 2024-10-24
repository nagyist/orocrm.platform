<?php

namespace Oro\Bundle\SyncBundle\Topic;

use Gos\Bundle\WebSocketBundle\Router\WampRequest;
use Gos\Bundle\WebSocketBundle\Topic\TopicInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;

abstract class AbstractTopic implements TopicInterface
{
    /** @var string */
    protected $topicName;

    public function __construct(string $topicName)
    {
        $this->topicName = $topicName;
    }

    #[\Override]
    public function onSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
    }

    #[\Override]
    public function onUnSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
    }

    #[\Override]
    public function getName(): string
    {
        return $this->topicName;
    }
}
