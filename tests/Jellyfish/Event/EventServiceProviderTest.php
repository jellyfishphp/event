<?php

namespace Jellyfish\Event;

use Codeception\Test\Unit;
use Jellyfish\Event\Command\EventQueueConsumeCommand;
use Jellyfish\Event\Command\EventQueueWorkerStartCommand;
use Jellyfish\Process\ProcessFactoryInterface;
use Jellyfish\Queue\MessageFactoryInterface;
use Jellyfish\Queue\QueueClientInterface;
use Pimple\Container;
use Jellyfish\Serializer\SerializerInterface;

class EventServiceProviderTest extends Unit
{
    /**
     * @var \Jellyfish\Serializer\SerializerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $serializerMock;

    /**
     * @var \Pimple\Container;
     */
    protected $container;

    /**
     * @var \Jellyfish\Event\EventServiceProvider
     */
    protected $eventServiceProvider;

    /**
     * @return void
     *
     * @throws \Exception
     */
    protected function _before(): void
    {
        parent::_before();

        $self = $this;

        $this->container = new Container();

        $this->container->offsetSet('root_dir', function () {
            return DIRECTORY_SEPARATOR;
        });

        $this->container->offsetSet('commands', function () {
            return [];
        });

        $this->container->offsetSet('serializer', function () use ($self) {
            return $self->getMockBuilder(SerializerInterface::class)
                ->disableOriginalConstructor()
                ->getMock();
        });

        $this->container->offsetSet('message_factory', function () use ($self) {
            return $self->getMockBuilder(MessageFactoryInterface::class)
                ->disableOriginalConstructor()
                ->getMock();
        });

        $this->container->offsetSet('process_factory', function () use ($self) {
            return $self->getMockBuilder(ProcessFactoryInterface::class)
                ->disableOriginalConstructor()
                ->getMock();
        });

        $this->container->offsetSet('queue_client', function () use ($self) {
            return $self->getMockBuilder(QueueClientInterface::class)
                ->disableOriginalConstructor()
                ->getMock();
        });

        $this->eventServiceProvider = new EventServiceProvider();
    }

    /**
     * @return void
     */
    public function testRegister(): void
    {
        $this->eventServiceProvider->register($this->container);

        $this->assertTrue($this->container->offsetExists('event_factory'));
        $this->assertInstanceOf(EventFactory::class, $this->container->offsetGet('event_factory'));

        $this->assertTrue($this->container->offsetExists('event_queue_name_generator'));
        $this->assertInstanceOf(
            EventQueueNameGenerator::class,
            $this->container->offsetGet('event_queue_name_generator')
        );

        $this->assertTrue($this->container->offsetExists('event_mapper'));
        $this->assertInstanceOf(EventMapper::class, $this->container->offsetGet('event_mapper'));

        $this->assertTrue($this->container->offsetExists('event_queue_consumer'));
        $this->assertInstanceOf(
            EventQueueConsumer::class,
            $this->container->offsetGet('event_queue_consumer')
        );

        $this->assertTrue($this->container->offsetExists('event_queue_producer'));
        $this->assertInstanceOf(
            EventQueueProducer::class,
            $this->container->offsetGet('event_queue_producer')
        );

        $this->assertTrue($this->container->offsetExists('event_dispatcher'));
        $this->assertInstanceOf(EventDispatcher::class, $this->container->offsetGet('event_dispatcher'));


        $this->assertTrue($this->container->offsetExists('event_queue_worker'));
        $this->assertInstanceOf(EventQueueWorker::class, $this->container->offsetGet('event_queue_worker'));

        $this->assertTrue($this->container->offsetExists('commands'));

        $commands = $this->container->offsetGet('commands');

        $this->assertCount(2, $commands);
        $this->assertInstanceOf(EventQueueConsumeCommand::class, $commands[0]);
        $this->assertInstanceOf(EventQueueWorkerStartCommand::class, $commands[1]);
    }
}