<?php
declare(strict_types=1);

namespace Study\Meme\Test\Unit\ViewModel\Account\Order;

use Magento\Customer\Model\Session;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Study\Meme\ViewModel\Account\Order\Gif;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GifTest extends TestCase
{
    private MockObject $collectionFactoryMock;
    private MockObject $customerSessionMock;
    private MockObject $orderConfigMock;
    private Gif $viewModel;

    protected function setUp(): void
    {
        $this->collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->orderConfigMock = $this->createMock(Config::class);

        $this->viewModel = new Gif(
            $this->collectionFactoryMock,
            $this->customerSessionMock,
            $this->orderConfigMock
        );
    }

    public function testGetGifUrlOrders(): void
    {
        $orderMock = $this->createMock(Order::class);
        $collectionMock = $this->createMock(Collection::class);

        $this->collectionFactoryMock->method('create')->willReturn($collectionMock);
        $this->customerSessionMock->method('getCustomerId')->willReturn(123);
        $this->orderConfigMock->method('getVisibleOnFrontStatuses')->willReturn(['processing']);

        $collectionMock->expects($this->once())->method('addFieldToSelect')->willReturnSelf();
        $collectionMock->expects($this->exactly(3))->method('addFieldToFilter')->willReturnSelf();
        $collectionMock->expects($this->once())->method('setOrder')->willReturnSelf();
        $collectionMock->method('getItems')->willReturn([$orderMock]);

        $result = $this->viewModel->getGifUrlOrders();
        $this->assertCount(1, $result);
        $this->assertSame($orderMock, $result[0]);
    }

    /**
     * @dataProvider getGifUrlsDataProvider
     */
    public function testGetGifUrls(array|bool $orders, array $expected): void
    {
        $viewModelMock = $this->getMockBuilder(Gif::class)
            ->setConstructorArgs([
                $this->collectionFactoryMock,
                $this->customerSessionMock,
                $this->orderConfigMock
            ])
            ->onlyMethods(['getGifUrlOrders'])
            ->getMock();

        $viewModelMock->method('getGifUrlOrders')->willReturn($orders);

        $result = $viewModelMock->getGifUrls(123);
        $this->assertSame($expected, $result);
    }

    public function getGifUrlsDataProvider(): array
    {
        $json = json_encode([
            'first_gif' => ['https://giphy.com/image1', 'https://giphy.com/image2']
        ]);

        $orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $orderMock->method('getData')->with('gif_url')->willReturn($json);

        return [
            'with_valid_data' => [
                'orders' => [$orderMock],
                'expected' => ['https://giphy.com/image1', 'https://giphy.com/image2'],
            ],
            'with_no_orders' => [
                'orders' => false,
                'expected' => [],
            ],
        ];
    }
}
