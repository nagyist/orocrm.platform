<?php

namespace Oro\Bundle\SyncBundle\Topic;

use Gos\Bundle\WebSocketBundle\Client\ClientManipulatorInterface;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;

/**
 * Allows to subscribe user to message which secured via his identifier.
 */
class SecuredTopic extends BroadcastTopic
{
    use UserAwareTopicTrait;

    public function __construct(string $topicName, ClientManipulatorInterface $clientManipulator)
    {
        parent::__construct($topicName);

        $this->clientManipulator = $clientManipulator;
    }

    #[\Override]
    public function onSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        if (!$this->isApplicable($connection, $this->getUserId($request))) {
            $this->disallowSubscribe($connection, $topic);
        }
    }

    private function getUserId(WampRequest $request): int
    {
        return $request->getAttributes()->getInt('user_id');
    }
}
