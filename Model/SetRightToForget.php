<?php

declare(strict_types=1);

namespace Tuqiri\GDPR\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;
use Tuqiri\GDPR\Api\Data\SetRightToForgetMessageInterface;
use Tuqiri\GDPR\Api\SetRightToForgetInterface;

use Magento\Framework\Exception\LocalizedException;

class SetRightToForget implements SetRightToForgetInterface
{
    /** @var RightToForget */
    protected RightToForget $rightToForget;

    /** @var LoggerInterface */
    protected LoggerInterface $logger;

    /** @var CustomerRepositoryInterface */
    protected CustomerRepositoryInterface $customerRepository;

    /**
     * @param RightToForget $rightToForget
     * @param LoggerInterface $logger
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct (
        RightToForget $rightToForget,
        LoggerInterface $logger,
        CustomerRepositoryInterface $customerRepository,
    ) {
        $this->rightToForget = $rightToForget;
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritDoc
     */
    public function save(int $customerId): SetRightToForgetMessageInterface
    {
        try {
            $customer = $this->customerRepository->getById($customerId);

            // If customer has already submitted a right to forget request then ignore and send feedback back to customer
            if ($customer->getCustomAttribute(RightToForget::RIGHT_TO_FORGET_CUSTOMER_ATTRIBUTE)->getValue()) {
                return new SetRightToForgetMessage(
                    __('Right to Forget request already sent. Please contact us if you require assistance.'),
                    false
                );
            }

            // Send email to configured location and set right to forget customer attribute
            $this->rightToForget->sendRightToForgetEmail((int) $customer->getId());
            $customer->setCustomAttribute(RightToForget::RIGHT_TO_FORGET_CUSTOMER_ATTRIBUTE, 1);
            $this->customerRepository->save($customer);
        } catch (\Exception $e) {
            // Log the critical for debugging
            $this->logger->critical($e);

            throw new LocalizedException(
                __('Error submitting Right to Forget request. Please try again later or contact us.')
            );
        }

        return new SetRightToForgetMessage(
            __('Right to Forget request sent.'),
            true
        );
    }
}
