<?php

namespace Tuqiri\GDPR\ViewModel\Customer\Account;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Block\Address\Renderer\RendererInterface;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Customer\Model\Address\Mapper as AddressMapper;
use Magento\Customer\Model\Attribute;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Serialize\Serializer\Json as Json;
use Magento\Framework\UrlInterface as UrlBuilderInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class RightToForget implements ArgumentInterface
{
    /** @var CustomerInterface */
    protected CustomerInterface $customer;

    /** @var AddressConfig */
    protected AddressConfig $addressConfig;

    /** @var AddressMapper */
    protected AddressMapper $addressMapper;

    /** @var EavConfig */
    protected EavConfig $eavConfig;

    /** @var UrlBuilderInterface  */
    protected UrlBuilderInterface $urlBuilder;

    /** @var FormKey */
    protected FormKey $formKey;

    /** @var Json */
    protected Json $json;

    /** @var LoggerInterface */
    protected LoggerInterface $logger;

    /** @var OrderRepositoryInterface */
    protected OrderRepositoryInterface $orderRepository;

    /** @var SearchCriteriaBuilder */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /** @var FilterBuilder */
    protected FilterBuilder $filterBuilder;

    /**
     * @param AddressConfig $addressConfig
     * @param AddressMapper $addressMapper
     * @param EavConfig $eavConfig
     * @param UrlBuilderInterface $urlBuilder
     * @param FormKey $formKey
     * @param Json $json
     * @param LoggerInterface $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        AddressConfig $addressConfig,
        AddressMapper $addressMapper,
        EavConfig $eavConfig,
        UrlBuilderInterface $urlBuilder,
        FormKey $formKey,
        Json $json,
        LoggerInterface $logger,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        filterBuilder $filterBuilder,
    ) {
        $this->addressConfig = $addressConfig;
        $this->addressMapper = $addressMapper;
        $this->eavConfig = $eavConfig;
        $this->urlBuilder = $urlBuilder;
        $this->formKey = $formKey;
        $this->json = $json;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Set customer interface from block due to caching
     *
     * @param CustomerInterface $customer
     * @return void
     */
    public function setCustomer(CustomerInterface $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * Get RTF config as json string
     *
     * This function can be extended if any other account details are needed to be shown
     *
     * @return string
     */
    public function getRtfConfig(): string
    {
        return $this->json->serialize([
            'generalAccountDetails' => $this->getCustomerAccountDetailsAsArray(),
            'addresses' => $this->getCustomerAddressesAsArray(),
            'orders' => $this->getCustomerOrdersAsArray(),
            'submitRightToForgetActionUrl' => $this->getSubmitRtfRequestUrl(),
        ]);
    }

    /**
     * Get customer account details as array
     *
     * This function can be extended if any other account details are needed to be shown
     *
     * @return array[]
     */
    public function getCustomerAccountDetailsAsArray(): array
    {
        return [
            [
                'title' => __('Suffix'),
                'value' => $this->customer->getSuffix(),
            ],
            [
                'title' => __('First Name'),
                'value' => $this->customer->getFirstname(),
            ],
            [
                'title' => __('Middle Name'),
                'value' => $this->customer->getMiddlename(),
            ],
            [
                'title' => __('Last Name'),
                'value' => $this->customer->getLastname(),
            ],
            [
                'title' => __('Email'),
                'value' => $this->customer->getEmail(),
            ],
            [
                'title' => __('Date of Birth'),
                'value' => $this->customer->getDob(),
            ],
            [
                'title' => __('Gender'),
                'value' => $this->getCustomerGenderAsString($this->customer->getGender()),
            ],
        ];
    }

    /**
     * Get customer addresses as array
     *
     * @return array
     */
    public function getCustomerAddressesAsArray(): array
    {
        $customerAddresses = $this->customer->getAddresses();
        $customerAddressesAsHtmlArray = [];

        foreach ($customerAddresses as $customerAddress) {
            $customerAddressesAsHtmlArray[] = $this->getAddressHtml($customerAddress);
        }

        return $customerAddressesAsHtmlArray;
    }

    /**
     * Get customer orders as array
     *
     * @return array
     */
    public function getCustomerOrdersAsArray(): array
    {
        $ordersAsArray = [];

        $filter = $this->filterBuilder->setField('customer_id')
            ->setValue($this->customer->getId())
            ->setConditionType('eq')
            ->create();

        // Apply the filter to the search criteria
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilters([$filter])
            ->create();

        // Fetch the list of orders
        $orders = $this->orderRepository->getList($searchCriteria)->getItems();

        /** @var OrderInterface $order */
        foreach ($orders as $order) {

            $ordersAsArray[] = [
                'orderId' => $order->getIncrementId(),
                'date' => $order->getCreatedAt(),
                'viewOrderUrl' => $this->urlBuilder->getUrl('sales/order/view', ['order_id' => $order->getEntityId()]),
            ];
        }

        return $ordersAsArray;
    }

    public function getSubmitRtfRequestUrl(): string
    {
        return $this->urlBuilder->getUrl('rest/V1/customers/me/right-to-forget');
    }

    /**
     * Format customer address as HTML output
     *
     * @param $address
     * @return string
     */
    private function getAddressHtml($address): string
    {
        /** @var RendererInterface $renderer */
        $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();
        return $renderer->renderArray($this->addressMapper->toFlatArray($address));
    }

    /**
     * Get customer gender as string
     *
     * @param int $genderId
     * @return string
     */
    private function getCustomerGenderAsString(?int $genderId): string
    {
        try {
            /** @var Attribute $genderAttribute */
            $genderAttribute = $this->eavConfig->getAttribute(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                CustomerInterface::GENDER
            );
            return $genderAttribute->getSource()->getOptionText($genderId);
        } catch (\Exception $e) {
            // Log the critical for debugging
            $this->logger->critical($e);
            return '';
        }
    }
}
