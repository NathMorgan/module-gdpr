<?php

declare(strict_types=1);

namespace Tuqiri\GDPR\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;
use Tuqiri\GDPR\Api\Data\SetRightToForgetMessageInterface;
use Tuqiri\GDPR\Api\SetRightToForgetInterface;

use Magento\Framework\Message\Manager as MessageManager;

use Magento\Framework\Exception\LocalizedException;

class SetRightToForget implements SetRightToForgetInterface
{
    /** @var RightToForget */
    protected RightToForget $rightToForget;

    /** @var LoggerInterface */
    protected LoggerInterface $logger;

    /** @var CustomerRepositoryInterface */
    protected CustomerRepositoryInterface $customerRepository;

    /** @var MessageManager */
    protected MessageManager $messageManager;

    /**
     * @param RightToForget $rightToForget
     * @param LoggerInterface $logger
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct (
        RightToForget $rightToForget,
        LoggerInterface $logger,
        CustomerRepositoryInterface $customerRepository,
        MessageManager $messageManager,
    ) {
        $this->rightToForget = $rightToForget;
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
        $this->messageManager = $messageManager;
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

                $errorMessage = __(
                    'Right to Forget request already sent. Please contact us if you require assistance.'
                );

                // Use message manager for non-hyva Magento installs
                $this->messageManager->addErrorMessage($errorMessage);

                return new SetRightToForgetMessage($errorMessage, false);
            }

            // Send email to configured location and set right to forget customer attribute
            $this->rightToForget->sendRightToForgetEmail((int) $customer->getId());
            $customer->setCustomAttribute(RightToForget::RIGHT_TO_FORGET_CUSTOMER_ATTRIBUTE, 1);
            $this->customerRepository->save($customer);
        } catch (\Exception $e) {
            // Log the critical for debugging
            $this->logger->critical($e);

            $errorMessage = __('Error submitting Right to Forget request. Please try again later or contact us.');

            // Use message manager for non-hyva Magento installs
            $this->messageManager->addErrorMessage($errorMessage);

            throw new LocalizedException($errorMessage);
        }

        $successMessage = __('Right to Forget request has been sent.');

        // Use message manager for non-hyva Magento installs
        $this->messageManager->addSuccessMessage($successMessage);

        return new SetRightToForgetMessage($successMessage, true);
    }
}
