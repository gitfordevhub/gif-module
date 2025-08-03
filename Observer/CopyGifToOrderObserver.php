<?php
declare(strict_types=1);

namespace Study\Meme\Observer;

use Magento\Framework\DataObject\Copy;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class CopyGifToOrderObserver implements ObserverInterface
{
    /**
     * @var Copy
     */
    protected Copy $objectCopyService;

    /**
     * @param Copy $objectCopyService
     */
    public function __construct(
        Copy $objectCopyService
    ) {
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /* @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        /* @var Order $order */
        $order = $observer->getEvent()->getOrder();

        if ($quote->getData('gif_url')) {
            //$order->setData('gif_url', $quote->getData('gif_url')); - do without fieldset.xml
            $this->objectCopyService->copyFieldsetToTarget('sales_convert_quote', 'to_order', $quote, $order);
        }
    }
}
