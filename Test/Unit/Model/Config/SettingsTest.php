<?php

namespace Study\Meme\Test\Unit\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Study\Meme\Model\Config\Settings;
use PHPUnit\Framework\TestCase;

class SettingsTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfigMock;

    /**
     * @var EncryptorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $encryptorMock;

    /**
     * @var Settings
     */
    private Settings $settings;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->encryptorMock = $this->createMock(EncryptorInterface::class);

        $this->settings = new Settings(
            $this->scopeConfigMock,
            $this->encryptorMock
        );
    }

    /**
     * @param string $configValue
     * @param string $expected
     * @return void
     * @dataProvider imageLimitProvider
     */
    public function testGetImageLimit(string $configValue, string $expected): void
    {
        $this->scopeConfigMock
            ->method('getValue')
            ->with('meme/settings/image_limit')
            ->willReturn($configValue);

        $this->assertEquals($expected, $this->settings->getImageLimit());
    }

    public function imageLimitProvider(): array
    {
        return [
            ['42', '42'],
            ['', '1'],
            ['0', '0'],
            ['100', '100']
        ];
    }

    /**
     * @dataProvider apiKeyProvider
     */
    public function testGetApiKey(string $encrypted, string $decrypted): void
    {
        $this->scopeConfigMock
            ->method('getValue')
            ->with('meme/settings/api_key')
            ->willReturn($encrypted);

        $this->encryptorMock
            ->method('decrypt')
            ->with($encrypted)
            ->willReturn($decrypted);

        $this->assertEquals($decrypted, $this->settings->getApikey());
    }

    public static function apiKeyProvider(): array
    {
        return [
            ['enc_123', 'key1'],
            ['enc_456', 'key2'],
            ['something', 'myRealKey'],
        ];
    }
}
