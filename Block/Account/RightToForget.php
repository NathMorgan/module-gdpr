<?php

declare(strict_types=1);

namespace Ruroc\GDPR\Block\Account;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class RightToForget extends Template
{
    /** @var CustomerSessionFactory */
    protected CustomerSessionFactory $customerSessionFactory;

    /** @var CustomerRepositoryInterface */
    protected CustomerRepositoryInterface $customerRepository;

    /** @var HttpContext */
    protected HttpContext $httpContext;

    /**
     * @param Context $context
     * @param CustomerSessionFactory $customerSessionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param HttpContext $httpContext
     * @param array $data
     */
    public function __construct (
        Context $context,
        CustomerSessionFactory $customerSessionFactory,
        CustomerRepositoryInterface $customerRepository,
        HttpContext $httpContext,
        array $data = [],
    ) {
        parent::__construct($context, $data);

        $this->customerSessionFactory = $customerSessionFactory;
        $this->customerRepository = $customerRepository;
        $this->httpContext = $httpContext;
    }

    /**
     * Get repository customer
     *
     * A few checks are done here to confirm that the customer is logged in via httpContext
     *
     * @return CustomerInterface|null
     */
    public function getCustomerRepository(): ?CustomerInterface
    {
        try {
            /** @var CustomerSession $customerSession */
            $customerSession = $this->customerSessionFactory->create();

            // Check if the customer is logged in through the HTTP context
            if ($this->httpContext->getValue(CustomerContext::CONTEXT_AUTH)) {
                $customerId = $customerSession->getCustomerId();

                if ($customerId) {
                    // Fetch the customer by ID
                    return $this->customerRepository->getById($customerId);
                }
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * Get unique cache key for each customer to prevent cache sticking between customer accounts
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        /** @var CustomerSession $customerSession */
        $customerSession = $this->customerSessionFactory->create();

        $cacheKey = parent::getCacheKeyInfo();
        $customerId = $customerSession->getCustomerId();

        // Append customer ID to the cache key
        if ($customerId) {
            $cacheKey[] = 'customer_' . $customerId;
        }

        return $cacheKey;
    }
}
