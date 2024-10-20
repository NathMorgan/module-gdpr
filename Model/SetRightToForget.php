<?php

declare(strict_types=1);

namespace Tuqiri\GDPR\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Manager as MessageManager;
use Psr\Log\LoggerInterface;
use Tuqiri\GDPR\Api\Data\SetRightToForgetMessageInterface;
use Tuqiri\GDPR\Api\SetRightToForgetInterface;
use Tuqiri\GDPR\Model\Data\SetRightToForgetMessage;

class SetRightToForget implements SetRightToForgetInterface
{
    /** @var EmailNotification */
    protected EmailNotification $emailNotification;

    /** @var LoggerInterface */
    protected LoggerInterface $logger;

    /** @var CustomerRepositoryInterface */
    protected CustomerRepositoryInterface $customerRepository;

    /** @var MessageManager */
    protected MessageManager $messageManager;

    public const RIGHT_TO_FORGET_CUSTOMER_ATTRIBUTE = 'right_to_forget';

    /**
     * @param EmailNotification $emailNotification
     * @param LoggerInterface $logger
     * @param CustomerRepositoryInterface $customerRepository
     * @param MessageManager $messageManager
     */
    public function __construct (
        EmailNotification $emailNotification,
        LoggerInterface $logger,
        CustomerRepositoryInterface $customerRepository,
        MessageManager $messageManager,
    ) {
        $this->emailNotification = $emailNotification;
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
            if ($customer->getCustomAttribute(self::RIGHT_TO_FORGET_CUSTOMER_ATTRIBUTE)->getValue()) {

                $errorMessage = __(
                    'Right to Forget request already sent. Please contact us if you require assistance.'
                );

                // Use message manager for non-hyva Magento installs
                $this->messageManager->addErrorMessage($errorMessage);

                return new SetRightToForgetMessage($errorMessage, false);
            }

            // Send email to configured location and set right to forget customer attribute
            $this->emailNotification->rightToForgetAdmin((int) $customer->getId());

            // Set right to forget attribute to customer to prevent duplicate requests
            $customer->setCustomAttribute(self::RIGHT_TO_FORGET_CUSTOMER_ATTRIBUTE, 1);
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
