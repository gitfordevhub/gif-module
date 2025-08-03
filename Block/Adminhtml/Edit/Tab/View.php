<?php
declare(strict_types=1);

namespace Study\Meme\Block\Adminhtml\Edit\Tab;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Phrase;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * Customer account form block
 * copy this class Magento\Customer\Block\Adminhtml\Edit\Tab\View
 */
class View extends Template implements TabInterface
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @param Context $context
     * @param RequestInterface $request
     * @param array $data
     */
    public function __construct(
        Context $context,
        RequestInterface $request,
        array $data = []
    ) {
        $this->request = $request;
        parent::__construct($context, $data);
    }

    /**
     * @return string|null
     */
    public function getCustomerId(): ?string
    {
        return $this->request->getParam('id');
    }

    /**
     * @return Phrase
     */
    public function getTabLabel(): Phrase
    {
        return __('Meme');
    }

    /**
     * @return Phrase
     */
    public function getTabTitle(): Phrase
    {
        return __('Meme');
    }

    /**
     * @return bool
     */
    public function canShowTab(): bool
    {
        if ($this->getCustomerId()) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isHidden(): bool
    {
        if ($this->getCustomerId()) {
            return false;
        }
        return true;
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass(): string
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl(): string
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded(): bool
    {
        return false;
    }
}
