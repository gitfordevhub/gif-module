<?php
declare(strict_types=1);

namespace Study\Meme\ViewModel\Account\Order;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class Gif implements ArgumentInterface
{
    public const string GIF_URL = 'gif_url';

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $orderCollectionFactory;

    /**
     * @var Session
     */
    protected Session $customerSession;

    /**
     * @var Config
     */
    protected Config $orderConfig;

    /**
     * @var Collection
     */
    protected Collection $orders;

    /**
     * @param CollectionFactory $orderCollectionFactory
     * @param Session $customerSession
     * @param Config $orderConfig
     */
    public function __construct(
        CollectionFactory $orderCollectionFactory,
        Session $customerSession,
        Config $orderConfig
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->customerSession = $customerSession;
        $this->orderConfig = $orderConfig;
    }

    /**
     * @param int $customerId
     * @return array
     */
    public function getGifUrls(int $customerId = 0): array
    {
        $orders = $this->getGifUrlOrders($customerId);

        if (!$orders) {
            return [];
        }

        $json = $orders[array_key_first($orders)]->getData(self::GIF_URL);
        $gifUrl = json_decode($json, true);
        $gifs = array_key_first(json_decode($json, true));

        return $gifUrl[$gifs];
    }

    /**
     * Get customer orders
     *
     * @param int $customerId
     * @return bool|array
     */
    public function getGifUrlOrders(int $customerId = 0): bool|array
    {
        if ($customerId === 0) {
            if (!($customerId = $this->customerSession->getCustomerId())) {
                return false;
            }
        }

        $this->orders = $this->orderCollectionFactory->create()
            ->addFieldToSelect(['entity_id', self::GIF_URL])
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter(self::GIF_URL, ['notnull' => true])
            ->addFieldToFilter('status', ['in' => $this->orderConfig->getVisibleOnFrontStatuses()])
            ->setOrder('created_at', 'desc');

        return $this->orders->getItems();
    }
}
