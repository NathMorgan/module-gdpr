<?php

declare(strict_types=1);

namespace Ruroc\GDPR\Plugin;

use Closure;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Response\Http as HttpResponse;
use Ruroc\GDPR\Controller\RtfInterface;
use Ruroc\GDPR\Scope\Config;

class ValidateRtfControllerModuleEnabled
{
    /** @var Config */
    protected Config $config;

    /** @var CustomerUrl */
    protected CustomerUrl $customerUrl;

    /** @var HttpResponse */
    protected HttpResponse $httpResponse;

    /**
     * @param Config $config
     * @param CustomerUrl $customerUrl
     * @param HttpResponse $httpResponse
     */
    public function __construct(
        Config $config,
        CustomerUrl $customerUrl,
        HttpResponse $httpResponse,
    ) {
        $this->config = $config;
        $this->customerUrl = $customerUrl;
        $this->httpResponse = $httpResponse;
    }

    /**
     * Plugin around execute to check if module is enabled so each controller request can verify
     *
     * @param RtfInterface $subject
     * @param Closure $proceed
     * @return mixed
     */
    public function aroundExecute(RtfInterface $subject, Closure $proceed)
    {
        if (!$this->config->getIsEnabled()) {
            $this->httpResponse->setRedirect(
                $this->customerUrl->getLoginUrl()
            );
        }

        return $proceed();
    }
}
