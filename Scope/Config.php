<?php

declare(strict_types=1);

namespace Tuqiri\GDPR\Scope;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    /** @var ScopeConfigInterface */
    protected ScopeConfigInterface $scopeConfig;

    protected const string XML_PATH_GDPR_RIGHT_TO_FORGET_IS_ENABLED = 'tuqiri/right_to_forget/enabled';
    protected const string XML_PATH_GDPR_RIGHT_TO_FORGET_REQUEST_EMAIL = 'tuqiri/right_to_forget/request_email';
    protected const string XML_PATH_MAGENTO_CONTACT_EMAIL_SENDER = 'contact/email/sender_email_identity';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function getIsEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_GDPR_RIGHT_TO_FORGET_IS_ENABLED);
    }

    /**
     * @return string
     */
    public function getRequestEmail(): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_GDPR_RIGHT_TO_FORGET_REQUEST_EMAIL) ?? '';
    }

    /**
     * @return string
     */
    public function getSenderEmail(): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MAGENTO_CONTACT_EMAIL_SENDER) ?? '';
    }
}
