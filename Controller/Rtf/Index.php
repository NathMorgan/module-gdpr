<?php

declare(strict_types=1);

namespace Ruroc\GDPR\Controller\Rtf;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory as ResultPageFactory;
use Ruroc\GDPR\Controller\RtfInterface;

class Index implements RtfInterface, HttpGetActionInterface
{
    /** @var ResultPageFactory */
    protected ResultPageFactory $resultPageFactory;

    /**
     * @param ResultPageFactory $resultPageFactory
     */
    public function __construct (
        ResultPageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('GDPR - Right to Forget'));
        return $resultPage;
    }
}
