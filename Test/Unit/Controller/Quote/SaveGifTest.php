<?php
declare(strict_types=1);

namespace Study\Meme\Test\Unit\Controller\Quote;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Study\Meme\Controller\Quote\SaveGif;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SaveGifTest extends TestCase
{
    private JsonFactory|MockObject $resultJsonFactoryMock;
    private CartRepositoryInterface|MockObject $quoteRepositoryMock;
    private CheckoutSession|MockObject $checkoutSessionMock;
    private HttpRequest|MockObject $requestMock;
    private Json|MockObject $jsonResultMock;
    private Quote|MockObject $quoteMock;

    private SaveGif $controller;

    protected function setUp(): void
    {
        $this->resultJsonFactoryMock = $this->createMock(JsonFactory::class);
        $this->quoteRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->checkoutSessionMock = $this->createMock(CheckoutSession::class);
        $this->requestMock = $this->createMock(HttpRequest::class);
        $this->jsonResultMock = $this->createMock(Json::class);
        $this->quoteMock = $this->createMock(Quote::class);

        $this->resultJsonFactoryMock
            ->method('create')
            ->willReturn($this->jsonResultMock);

        $this->controller = new SaveGif(
            $this->resultJsonFactoryMock,
            $this->quoteRepositoryMock,
            $this->checkoutSessionMock, // ✅ Исправлено
            $this->requestMock
        );
    }

    /**
     * @dataProvider executeDataProvider
     */
    public function testExecute(string $requestContent, ?string $exceptionMessage, array $expectedResult): void
    {
        $this->requestMock
            ->method('getContent')
            ->willReturn($requestContent);

        $this->jsonResultMock
            ->expects($this->once())
            ->method('setData')
            ->with($expectedResult)
            ->willReturnSelf();

        if ($requestContent !== '') {
            $this->checkoutSessionMock->method('getQuote')->willReturn($this->quoteMock);

            $this->quoteMock
                ->expects($this->once())
                ->method('setData')
                ->with('gif_url', $requestContent);

            if ($exceptionMessage !== null) {
                $this->quoteRepositoryMock
                    ->method('save')
                    ->willThrowException(new \Exception($exceptionMessage));
            } else {
                $this->quoteRepositoryMock
                    ->expects($this->once())
                    ->method('save')
                    ->with($this->quoteMock);
            }
        } else {
            $this->quoteMock
                ->expects($this->never())
                ->method('setData');

            $this->quoteRepositoryMock
                ->expects($this->never())
                ->method('save');
        }

        $this->assertSame($this->jsonResultMock, $this->controller->execute());
    }

    public static function executeDataProvider(): array
    {
        return [
            'valid gif url' => [
                'requestContent' => '{"url":"https://giphy.com/some.gif"}',
                'exceptionMessage' => null,
                'expectedResult' => [
                    'success' => true,
                    'message' => 'GIF saved'
                ]
            ],
            'empty content' => [
                'requestContent' => '',
                'exceptionMessage' => null,
                'expectedResult' => [
                    'success' => false,
                    'message' => 'Missing gif_url parameter'
                ]
            ],
            'exception thrown' => [
                'requestContent' => '{"url":"https://giphy.com/fail.gif"}',
                'exceptionMessage' => 'Save failed',
                'expectedResult' => [
                    'success' => false,
                    'message' => 'Save failed'
                ]
            ],
        ];
    }
}
