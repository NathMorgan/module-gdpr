<?php

declare(strict_types=1);

namespace Ruroc\GDPR\Controller\Rtf;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\Result\JsonFactory as JsonResultFactory;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Message\Manager as MessageManager;
use Psr\Log\LoggerInterface;
use Ruroc\GDPR\Controller\RtfInterface;
use Ruroc\GDPR\Model\RightToForget;

class SubmitRequest implements RtfInterface, HttpPostActionInterface
{
    /** @var RightToForget */
    protected RightToForget $rightToForget;

    /** @var CurrentCustomer */
    protected CurrentCustomer $currentCustomer;

    /** @var ResultRedirectFactory */
    protected ResultRedirectFactory $resultRedirectFactory;

    /** @var RequestInterface */
    protected $request;

    /** @var JsonResultFactory */
    protected JsonResultFactory $jsonResultFactory;

    /** @var FormKeyValidator */
    protected FormKeyValidator $formKeyValidator;

    /** @var LoggerInterface */
    protected LoggerInterface $logger;

    /** @var MessageManager */
    protected MessageManager $messageManager;

    /** @var CustomerRepositoryInterface */
    protected $customerRepository;

    /**
     * @param RightToForget $rightToForget
     * @param CurrentCustomer $currentCustomer
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param RequestInterface $request
     * @param JsonResultFactory $jsonResultFactory
     * @param FormKeyValidator $formKeyValidator
     * @param LoggerInterface $logger
     * @param MessageManager $messageManager
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct (
        RightToForget $rightToForget,
        CurrentCustomer $currentCustomer,
        ResultRedirectFactory $resultRedirectFactory,
        RequestInterface $request,
        JsonResultFactory $jsonResultFactory,
        FormKeyValidator $formKeyValidator,
        LoggerInterface $logger,
        MessageManager $messageManager,
        CustomerRepositoryInterface $customerRepository,
    ) {
        $this->rightToForget = $rightToForget;
        $this->currentCustomer = $currentCustomer;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->request = $request;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
        $this->customerRepository = $customerRepository;
    }

    public function execute()
    {
        /** @var JsonResult $jsonResult */
        $jsonResult = $this->jsonResultFactory->create();
        $currentCustomer = $this->currentCustomer->getCustomer();

        // Add form key validation to prevent it from being submitted automatically
        if (!$this->formKeyValidator->validate($this->request)) {
            $this->messageManager->addNoticeMessage(
                __('Invalid form key please refresh the page and try again.')
            );

            return $jsonResult->setData([
                'success' => false
            ]);
        }

        // If customer has already submitted a right to forget request then ignore and send feedback back to customer
        if ($currentCustomer->getCustomAttribute(RightToForget::RIGHT_TO_FORGET_CUSTOMER_ATTRIBUTE)->getValue()) {
            $this->messageManager->addNoticeMessage(
                __('Right to Forget request already sent. Please contact us if you require assistance.')
            );

            return $jsonResult->setData([
                'success' => false
            ]);
        }

        try {
            // Send email to configured location and set right to forget customer attribute
            $this->rightToForget->sendRightToForgetEmail((int) $currentCustomer->getId());
            $currentCustomer->setCustomAttribute(RightToForget::RIGHT_TO_FORGET_CUSTOMER_ATTRIBUTE, 1);
            $this->customerRepository->save($currentCustomer);
        } catch (\Exception $e) {
            // Log the critical for debugging
            $this->logger->critical($e);

            $this->messageManager->addErrorMessage(
                __('Error submitting Right to Forget request. Please try again later or contact us.')
            );

            // Report to the user that there is an error
            return $jsonResult->setData([
                'success' => false
            ]);
        }

        $this->messageManager->addSuccessMessage(
            __('Right to Forget request sent.')
        );

        return $jsonResult->setData([
            'success' => true
        ]);
    }
}
