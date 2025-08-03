<?php

declare(strict_types=1);

namespace Study\Meme\Test\Unit\Block\Adminhtml\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\RequestInterface;
use Study\Meme\Block\Adminhtml\Edit\Tab\View;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    private RequestInterface|MockObject $requestMock;
    private Context|MockObject $contextMock;
    private View $view;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->contextMock = $this->createMock(Context::class);

        $this->view = new View(
            $this->contextMock,
            $this->requestMock
        );
    }

    public function testGetTabLabel(): void
    {
        $this->assertEquals(__('Meme'), $this->view->getTabLabel());
    }

    public function testGetTabTitle(): void
    {
        $this->assertEquals(__('Meme'), $this->view->getTabTitle());
    }

    public function testGetCustomerId(): void
    {
        $this->requestMock
            ->method('getParam')
            ->with('id')
            ->willReturn('123');

        $this->assertEquals('123', $this->view->getCustomerId());
    }

    /**
     * @dataProvider canShowTabDataProvider
     */
    public function testCanShowTab(?string $customerId, bool $expected): void
    {
        $this->requestMock
            ->method('getParam')
            ->with('id')
            ->willReturn($customerId);

        $this->assertSame($expected, $this->view->canShowTab());
    }

    public static function canShowTabDataProvider(): array
    {
        return [
            'customer id exists' => ['customerId' => '123', 'expected' => true],
            'customer id is null' => ['customerId' => null, 'expected' => false],
        ];
    }

    /**
     * @dataProvider isHiddenDataProvider
     */
    public function testIsHidden(?string $customerId, bool $expected): void
    {
        $this->requestMock
            ->method('getParam')
            ->with('id')
            ->willReturn($customerId);

        $this->assertSame($expected, $this->view->isHidden());
    }

    public static function isHiddenDataProvider(): array
    {
        return [
            'customer id exists' => ['customerId' => '456', 'expected' => false],
            'customer id is null' => ['customerId' => null, 'expected' => true],
        ];
    }

    public function testGetTabClass(): void
    {
        $this->assertEquals('', $this->view->getTabClass());
    }

    public function testGetTabUrl(): void
    {
        $this->assertEquals('', $this->view->getTabUrl());
    }

    public function testIsAjaxLoaded(): void
    {
        $this->assertFalse($this->view->isAjaxLoaded());
    }
}
