<?php

declare(strict_types=1);

namespace Gaming\Identity\Port\Adapter\Messaging;

use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\EventStore\StoredEvent;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\Common\MessageBroker\Model\Message\Message;
use Gaming\Common\MessageBroker\Model\Message\Name;
use Gaming\Common\MessageBroker\Publisher;
use Gaming\Common\Normalizer\Normalizer;
use Gaming\Identity\Domain\Model\User\Event\UserArrived;
use Gaming\Identity\Domain\Model\User\Event\UserSignedUp;
use RuntimeException;

final class PublishStoredEventsToMessageBrokerSubscriber implements StoredEventSubscriber
{
    public function __construct(
        private readonly Publisher $publisher,
        private readonly Normalizer $normalizer
    ) {
    }

    public function handle(StoredEvent $storedEvent): void
    {
        $domainEvent = $storedEvent->domainEvent();

        // We should definitely filter the events we are going to publish,
        // since that belongs to our public interface for the other contexts.
        // However, it's not done for simplicity in this sample project.
        // We could
        //     * publish specific messages by name.
        //     * filter out specific properties in the payload.
        //     * translate when the properties for an event in the payload changed.
        //
        // We could use a strong message format like json schema, protobuf etc. to have
        // a clearly defined interface with other domains.
        $this->publisher->send(
            new Message(
                new Name('Identity', $this->nameFromDomainEvent($domainEvent)),
                json_encode(
                    $this->normalizer->normalize($domainEvent, $domainEvent::class),
                    JSON_THROW_ON_ERROR
                )
            )
        );
    }

    public function commit(): void
    {
        $this->publisher->flush();
    }

    private function nameFromDomainEvent(DomainEvent $domainEvent): string
    {
        return match ($domainEvent::class) {
            UserArrived::class => 'UserArrived',
            UserSignedUp::class => 'UserSignedUp',
            default => throw new RuntimeException($domainEvent::class . ' must be handled.')
        };
    }
}
