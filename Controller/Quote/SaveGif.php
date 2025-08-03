<?php
declare(strict_types=1);

namespace Study\Meme\Controller\Quote;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\App\ActionInterface;

class SaveGif implements ActionInterface
{
    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;
    /**
     * @var CartRepositoryInterface
     */

    private CartRepositoryInterface $quoteRepository;
    /**
     * @var RequestInterface
     */

    private RequestInterface $request;
    /**
     * @var Session
     */
    private Session $checkoutSession;

    /**
     * @param JsonFactory $resultJsonFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param Session $checkoutSession
     * @param RequestInterface $request
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        CartRepositoryInterface $quoteRepository,
        Session $checkoutSession,
        RequestInterface $request
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
        $this->request = $request;
    }

    /**
     * @return Json|ResultInterface|ResponseInterface
     */
    public function execute(): Json|ResultInterface|ResponseInterface
    {
        $result = $this->resultJsonFactory->create();

        try {
            $gifUrl = $this->request->getContent();

            if (strlen($gifUrl) === 0) {
                return $result->setData(['success' => false, 'message' => 'Missing gif_url parameter']);
            }

            $quote = $this->checkoutSession->getQuote();

            $quote->setData('gif_url', $gifUrl);
            $this->quoteRepository->save($quote);

            return $result->setData(['success' => true, 'message' => 'GIF saved']);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
