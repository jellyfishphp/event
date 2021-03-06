<?php

declare(strict_types=1);

namespace Jellyfish\Event;

use Codeception\Test\Unit;

class EventDispatcherTest extends Unit
{
    /**
     * @var \Jellyfish\Event\EventListenerProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventListenerProviderMock;

    /**
     * @var \Jellyfish\Event\EventQueueProducerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventQueueProducerMock;

    /**
     * @var \Jellyfish\Event\EventListenerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventListenerMock;

    /**
     * @var \Jellyfish\Event\EventInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventMock;

    /**
     * @var string
     */
    protected string $eventName;

    /**
     * @var \Jellyfish\Event\EventDispatcherInterface
     */
    protected EventDispatcherInterface $eventDispatcher;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->eventListenerProviderMock = $this->getMockBuilder(EventListenerProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventQueueProducerMock = $this->getMockBuilder(EventQueueProducerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventListenerMock = $this->getMockBuilder(EventListenerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventMock = $this->getMockBuilder(EventInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventName = 'test';

        $this->eventDispatcher = new EventDispatcher($this->eventListenerProviderMock, $this->eventQueueProducerMock);
    }

    /**
     * @return void
     */
    public function testDispatchWithoutListeners(): void
    {
        $this->eventMock->expects(static::atLeastOnce())
            ->method('getName')
            ->willReturn($this->eventName);

        $this->eventListenerProviderMock->expects(static::atLeastOnce())
            ->method('getListenersByTypeAndEventName')
            ->withConsecutive([
                EventListenerInterface::TYPE_SYNC,
                $this->eventName
            ], [
                EventListenerInterface::TYPE_ASYNC,
                $this->eventName
            ])->willReturnOnConsecutiveCalls([], []);

        $result = $this->eventDispatcher->dispatch($this->eventMock);

        static::assertEquals($this->eventDispatcher, $result);
    }

    /**
     * @return void
     */
    public function testDispatchSyncListeners(): void
    {
        $this->eventMock->expects(static::atLeastOnce())
            ->method('getName')
            ->willReturn($this->eventName);

        $this->eventListenerProviderMock->expects(static::atLeastOnce())
            ->method('getListenersByTypeAndEventName')
            ->withConsecutive([
                EventListenerInterface::TYPE_SYNC,
                $this->eventName
            ], [
                EventListenerInterface::TYPE_ASYNC,
                $this->eventName
            ])->willReturnOnConsecutiveCalls([$this->eventListenerMock], []);

        $this->eventListenerMock->expects(static::atLeastOnce())
            ->method('handle')
            ->with($this->eventMock);

        static::assertEquals(
            $this->eventDispatcher,
            $this->eventDispatcher->dispatch($this->eventMock)
        );
    }

    /**
     * @return void
     */
    public function testDispatchAsyncListeners(): void
    {
        $this->eventQueueProducerMock->expects(self::atLeastOnce())
            ->method('enqueue')
            ->with($this->eventMock);

        self::assertEquals(
            $this->eventDispatcher,
            $this->eventDispatcher->dispatch($this->eventMock)
        );
    }

    /**
     * @return void
     */
    public function testGetEventListenerProvider(): void
    {
        static::assertEquals(
            $this->eventListenerProviderMock,
            $this->eventDispatcher->getEventListenerProvider()
        );
    }
}
