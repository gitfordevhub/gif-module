<?php

namespace Study\Meme\Test\Unit\Observer;

use Magento\Framework\DataObject\Copy;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Study\Meme\Observer\CopyGifToOrderObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CopyGifToOrderObserverTest extends TestCase
{
    private CopyGifToOrderObserver $observerInstance;

    private MockObject|Copy $objectCopyServiceMock;
    private MockObject|Quote $quoteMock;
    private MockObject|Order $orderMock;

    protected function setUp(): void
    {
        $this->objectCopyServiceMock = $this->createMock(Copy::class);
        $this->observerInstance = new CopyGifToOrderObserver($this->objectCopyServiceMock);

        $this->quoteMock = $this->createMock(Quote::class);
        $this->orderMock = $this->createMock(Order::class);
    }

    /**
     * @dataProvider executeDataProvider
     */
    public function testExecute(?string $gifUrl, int $expectedCalls): void
    {
        $this->quoteMock
            ->method('getData')
            ->with('gif_url')
            ->willReturn($gifUrl);

        $this->objectCopyServiceMock
            ->expects($this->exactly($expectedCalls))
            ->method('copyFieldsetToTarget');

        $event = new \Magento\Framework\Event([
            'quote' => $this->quoteMock,
            'order' => $this->orderMock
        ]);

        $observer = new \Magento\Framework\Event\Observer(['event' => $event]);

        $this->observerInstance->execute($observer);
    }

    public function executeDataProvider(): array
    {
        return [
            'gif_url_present' => [
                'gifUrl' => 'https://example.com/gif.gif',
                'expectedCalls' => 1
            ],
            'gif_url_missing' => [
                'gifUrl' => null,
                'expectedCalls' => 0
            ],
        ];
    }
}
