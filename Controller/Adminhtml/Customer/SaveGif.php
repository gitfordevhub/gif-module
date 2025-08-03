<?php
declare(strict_types=1);

namespace Study\Meme\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Study\Meme\ViewModel\Account\Order\Gif;

class SaveGif extends Action
{
    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var OrderCollectionFactory
     */
    private OrderCollectionFactory $orderCollectionFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param RequestInterface $request
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        OrderRepositoryInterface $orderRepository,
        OrderCollectionFactory $orderCollectionFactory,
        ManagerInterface $messageManager
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->orderRepository = $orderRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * @return Json|ResultInterface|ResponseInterface
     */
    public function execute(): Json|ResultInterface|ResponseInterface
    {
        $result = $this->resultJsonFactory->create();
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $customerId = $this->request->getParam('customer_id');
            $selectedGif = $this->request->getParam('selected_gif');
            $gifUrls = json_decode($this->request->getParam('gif_urls'));

            if ($selectedGif === reset($gifUrls)) {
                $this->messageManager->addSuccessMessage(__('You selected same GIF.'));
                return $resultRedirect->setPath('customer/index/edit', ['id' => $customerId]);
            }

            $key = array_search($selectedGif, $gifUrls);
            if ($key !== false) {
                unset($gifUrls[$key]);
                array_unshift($gifUrls, $selectedGif);
            }

            $gifUrl = [
                'gifs' => $gifUrls
            ];

            $orders  = $this->orderCollectionFactory->create()
                ->addFieldToSelect(['entity_id', Gif::GIF_URL])
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter(Gif::GIF_URL, ['notnull' => true])
                ->setOrder('created_at', 'desc');

            $order = $orders->getFirstItem();

            if ($order && $order->getId()) {
                $order->setData(Gif::GIF_URL, json_encode($gifUrl));
                $this->orderRepository->save($order);
                $this->messageManager->addSuccessMessage(__('Selected GIF was saved to the order.'));
            } else {
                $this->messageManager->addErrorMessage(__('No order found for this customer.'));
            }

            return $resultRedirect->setPath('customer/index/edit', ['id' => $customerId]);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
