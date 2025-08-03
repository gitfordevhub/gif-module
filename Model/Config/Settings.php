<?php
declare(strict_types=1);

namespace Study\Meme\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class Settings
{
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    private const string IMAGE_LIMIT_XML = 'meme/settings/image_limit';

    private const string API_KEY_XML = 'meme/settings/api_key';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * @return string
     */
    public function getImageLimit(): string
    {
        $limit = $this->scopeConfig->getValue(self::IMAGE_LIMIT_XML);
        //If $limit == "" -> $limit = 50
        //Need logic for if $limit == 0
        return $limit !== "" ? $limit : "1";
    }

    /**
     * @return string
     */
    public function getApikey(): string
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(self::API_KEY_XML));
    }
}
