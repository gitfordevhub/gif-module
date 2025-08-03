<?php
declare(strict_types=1);

namespace Study\Meme\Test\Unit\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Study\Meme\Controller\Adminhtml\Customer\SaveGif;
use Study\Meme\ViewModel\Account\Order\Gif;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SaveGifTest extends TestCase
{
    private SaveGif $controller;

    private MockObject $contextMock;
    private MockObject $requestMock;
    private MockObject $jsonFactoryMock;
    private MockObject $jsonResultMock;
    private MockObject $redirectFactoryMock;
    private MockObject $redirectResultMock;
    private MockObject $orderRepositoryMock;
    private MockObject $orderCollectionFactoryMock;
    private MockObject $orderCollectionMock;
    private MockObject $orderMock;
    private MockObject $messageManagerMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->jsonFactoryMock = $this->createMock(JsonFactory::class);
        $this->jsonResultMock = $this->createMock(Json::class);
        $this->redirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->redirectResultMock = $this->createMock(Redirect::class);
        $this->orderRepositoryMock = $this->createMock(OrderRepositoryInterface::class);
        $this->orderCollectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->orderCollectionMock = $this->createMock(Collection::class);
        $this->orderMock = $this->createMock(Order::class);
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);

        $this->contextMock = $this->createMock(Context::class);
        $this->contextMock
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $this->contextMock
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);

        $this->contextMock
            ->method('getResultRedirectFactory')
            ->willReturn($this->redirectFactoryMock);

        $this->jsonFactoryMock
            ->method('create')
            ->willReturn($this->jsonResultMock);

        $this->redirectFactoryMock
            ->method('create')
            ->willReturn($this->redirectResultMock);

        $this->controller = new SaveGif(
            $this->contextMock,
            $this->jsonFactoryMock,
            $this->requestMock,
            $this->orderRepositoryMock,
            $this->orderCollectionFactoryMock,
            $this->messageManagerMock
        );
    }

    /**
     * @dataProvider executeDataProvider
     */
    public function testExecute(
        string $customerId,
        string $selectedGif,
        array $gifUrls,
        ?int $orderId,
        ?string $expectedMessageMethod,
        ?string $expectedMessageText,
        bool $expectSave
    ): void {
        $this->requestMock->method('getParam')
            ->willReturnMap([
                ['customer_id', null, $customerId],
                ['selected_gif', null, $selectedGif],
                ['gif_urls', null, json_encode($gifUrls)],
            ]);

        if ($orderId !== null) {
            $this->orderCollectionFactoryMock
                ->method('create')
                ->willReturn($this->orderCollectionMock);

            $this->orderCollectionMock
                ->method('addFieldToSelect')->willReturnSelf();
            $this->orderCollectionMock
                ->method('addFieldToFilter')->willReturnSelf();
            $this->orderCollectionMock
                ->method('setOrder')->willReturnSelf();
            $this->orderCollectionMock
                ->method('getFirstItem')
                ->willReturn($this->orderMock);

            $this->orderMock
                ->method('getId')
                ->willReturn($orderId);
        } else {
            $this->orderCollectionFactoryMock
                ->method('create')
                ->willReturn($this->orderCollectionMock);

            $this->orderCollectionMock
                ->method('addFieldToSelect')->willReturnSelf();
            $this->orderCollectionMock
                ->method('addFieldToFilter')->willReturnSelf();
            $this->orderCollectionMock
                ->method('setOrder')->willReturnSelf();
            $this->orderCollectionMock
                ->method('getFirstItem')
                ->willReturn($this->orderMock);

            $this->orderMock
                ->method('getId')
                ->willReturn(null);
        }

        if ($expectedMessageMethod && $expectedMessageText) {
            $this->messageManagerMock
                ->expects($this->once())
                ->method($expectedMessageMethod)
                ->with(__($expectedMessageText));
        }

        if ($expectSave) {
            $this->orderMock
                ->expects($this->once())
                ->method('setData')
                ->with(Gif::GIF_URL, json_encode(['gifs' => [$selectedGif, ...array_diff($gifUrls, [$selectedGif])]]));

            $this->orderRepositoryMock
                ->expects($this->once())
                ->method('save')
                ->with($this->orderMock);
        } else {
            $this->orderRepositoryMock
                ->expects($this->never())
                ->method('save');
        }

        $this->redirectResultMock
            ->expects($this->once())
            ->method('setPath')
            ->with('customer/index/edit', ['id' => $customerId])
            ->willReturnSelf();

        $result = $this->controller->execute();
        $this->assertSame($this->redirectResultMock, $result);
    }

    public function executeDataProvider(): array
    {
        return [
            'same gif - show message' => [
                'customerId' => '1',
                'selectedGif' => 'https://giphy.com/g1',
                'gifUrls' => ['https://giphy.com/g1', 'https://giphy.com/g2'],
                'orderId' => 10,
                'expectedMessageMethod' => 'addSuccessMessage',
                'expectedMessageText' => 'You selected same GIF.',
                'expectSave' => false,
            ],
            'different gif - save and message' => [
                'customerId' => '1',
                'selectedGif' => 'https://giphy.com/g2',
                'gifUrls' => ['https://giphy.com/g1', 'https://giphy.com/g2'],
                'orderId' => 10,
                'expectedMessageMethod' => 'addSuccessMessage',
                'expectedMessageText' => 'Selected GIF was saved to the order.',
                'expectSave' => true,
            ],
            'no order found' => [
                'customerId' => '1',
                'selectedGif' => 'https://giphy.com/g2',
                'gifUrls' => ['https://giphy.com/g1', 'https://giphy.com/g2'],
                'orderId' => null,
                'expectedMessageMethod' => 'addErrorMessage',
                'expectedMessageText' => 'No order found for this customer.',
                'expectSave' => false,
            ],
        ];
    }

    public function testExecuteWithExceptionReturnsJson(): void
    {
        $this->requestMock
            ->method('getParam')
            ->willThrowException(new \Exception('Test Exception'));

        $this->jsonResultMock
            ->expects($this->once())
            ->method('setData')
            ->with([
                'success' => false,
                'message' => 'Test Exception'
            ])
            ->willReturnSelf();

        $result = $this->controller->execute();
        $this->assertSame($this->jsonResultMock, $result);
    }
}
