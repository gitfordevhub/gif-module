<?php
declare(strict_types=1);

namespace Study\Meme\Controller\Request;

use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Webapi\Rest\Request;
use Study\Meme\Model\Config\Settings;

class Giphy implements HttpGetActionInterface
{
    /**
     * Search Endpoint
     * https://developers.giphy.com/docs/api/endpoint#search
     */
    private const string API_REQUEST_URI = 'https://api.giphy.com/v1/gifs/search';

    /**
     * @var ClientFactory
     */
    private ClientFactory $clientFactory;

    /**
     * @var ResponseFactory
     */
    private ResponseFactory $responseFactory;

    /**
     * @var Settings
     */
    private Settings $settings;

    /**
    * @param ResultFactory $resultFactory
    */
    private ResultFactory $resultFactory;

    /**
     * @var Session
     */
    private Session $checkoutSession;

    /**
     * GitApiService constructor
     *
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param Settings $settings
     * @param ResultFactory $resultFactory
     * @param Session $checkoutSession
     */
    public function __construct(
        ClientFactory   $clientFactory,
        ResponseFactory $responseFactory,
        Settings        $settings,
        ResultFactory   $resultFactory,
        Session         $checkoutSession
    ) {
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->settings = $settings;
        $this->resultFactory = $resultFactory;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Fetch some data from API
     *
     * @return ResponseInterface|ResultInterface
     */
    public function execute(): ResultInterface|ResponseInterface
    {
        try {
            $quote = $this->checkoutSession->getQuote();
            $items = $quote->getAllVisibleItems();

            $name = '';
            foreach ($items as $item) {
                $product = $item->getProduct();
                $name = $product->getName();
            }
            $searchQueryTerm = strtok($name, ' ');

            $limit = $this->settings->getImageLimit();

            $apiKey = $this->settings->getApikey();
            if (!$apiKey) {
                throw new \RuntimeException('Giphy API key is not configured.');
            }

            $queryParams = [
                'api_key' => $apiKey,
                'q' => $searchQueryTerm,
                'limit' => $limit
            ];

            $requestUri = '?' . http_build_query($queryParams);

            $response = $this->doRequest($requestUri);

            $status = $response->getStatusCode();

            if ($status !== 200) {
                return $this->resultFactory->create(ResultFactory::TYPE_JSON)
                    ->setHttpResponseCode($status)
                    ->setData([
                        'success' => false,
                        'message' => 'Giphy API returned status code ' . $status
                    ]);
            }

            $responseBody = $response->getBody();
            $responseContent = $responseBody->getContents(); // here you will have the API response in JSON format

            $result = json_decode($responseContent);

            if (!isset($result->{'data'})) {
                throw new \RuntimeException('Invalid API response.');
            }

            $gifArray = [];
            foreach ($result->{'data'} as $item) {
                $gifArray[] = $item->{'images'}->{'original'}->{'url'};
            }

            $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

            $result->setData($gifArray);

            return $result;

        } catch (\Throwable $e) {
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Do API request with provided params
     *
     * @param string $uriEndpoint
     * @param array $params
     * @param string $requestMethod
     *
     * @return Response|null
     */
    private function doRequest(
        string $uriEndpoint,
        array  $params = [],
        string $requestMethod = Request::HTTP_METHOD_GET
    ): ?Response {
        $client = $this->clientFactory->create([
            'config' => ['base_uri' => self::API_REQUEST_URI]
        ]);

        try {
            $response = $client->request($requestMethod, $uriEndpoint, $params);
        } catch (GuzzleException $exception) {
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
        }

        return $response;
    }
}
