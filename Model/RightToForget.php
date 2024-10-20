<?php

declare(strict_types=1);

namespace Tuqiri\GDPR\Model;

use Magento\Backend\Model\UrlInterface as BackendUrlInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Tuqiri\GDPR\Scope\Config as ScopeConfig;

class RightToForget
{
    /** @var ScopeConfig */
    protected $scopeConfig;

    /** @var BackendUrlInterface */
    protected $backendUrl;

    /** @var TransportBuilder */
    protected $transportBuilder;

    /** @var StoreManagerInterface */
    protected $storeManager;

    public const RIGHT_TO_FORGET_CUSTOMER_ATTRIBUTE = 'right_to_forget';

    /**
     * @param ScopeConfig $scopeConfig
     * @param BackendUrlInterface $backendUrl
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfig $scopeConfig,
        BackendUrlInterface $backendUrl,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->backendUrl = $backendUrl;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * Send right to forget email request to configured email
     *
     * @param int $customerId
     * @return void
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function sendRightToForgetEmail(int $customerId): void
    {
        $storeId = $this->storeManager->getStore()->getId();

        $transport = $this->transportBuilder->setTemplateIdentifier('customer_gdpr_right_to_forget_email_template')
            ->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $storeId
                ]
            )
            ->setTemplateVars([
                'customerId' => $customerId
            ])
            ->setFromByScope(
                $this->scopeConfig->getSenderEmail()
            )
            ->addTo(
                $this->scopeConfig->getRequestEmail()
            )
            ->getTransport();

        $transport->sendMessage();
    }
}
