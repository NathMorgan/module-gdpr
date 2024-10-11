<?php

declare(strict_types=1);

namespace Ruroc\GDPR\Setup\Patch\Data;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Attribute as AttributeResource;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Ruroc\GDPR\Model\RightToForget;

class AddRequestToForgetAttributeToCustomer implements DataPatchInterface, PatchRevertableInterface
{
    /** @var AttributeResource */
    protected AttributeResource $attributeResource;

    /** @var ModuleDataSetupInterface */
    protected ModuleDataSetupInterface $moduleDataSetup;

    /** @var CustomerSetupFactory */
    protected CustomerSetupFactory $customerSetupFactory;

    /**
     * @param AttributeResource $attributeResource
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        AttributeResource $attributeResource,
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->attributeResource = $attributeResource;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    public function apply()
    {
        // Start setup
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $customerSetup->addAttribute(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            RightToForget::RIGHT_TO_FORGET_CUSTOMER_ATTRIBUTE,
            [
                'type' => 'int',
                'label' => 'Requested Right To Forget',
                'input' => 'select',
                'source' => Boolean::class,
                'required' => false,
                'user_defined' => true,
                'sort_order' => 150,
                'visible' => true,
                'system' => false,
                'default' => 0
            ]
        );

        $customerSetup->addAttributeToSet(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            null,
            RightToForget::RIGHT_TO_FORGET_CUSTOMER_ATTRIBUTE
        );

        $attribute = $customerSetup
            ->getEavConfig()
            ->getAttribute(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                RightToForget::RIGHT_TO_FORGET_CUSTOMER_ATTRIBUTE
            );

        $attribute->setData('used_in_forms', [
            'adminhtml_customer'
        ]);

        $this->attributeResource->save($attribute);

        // End setup
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert()
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->removeAttribute(Customer::ENTITY, RightToForget::RIGHT_TO_FORGET_CUSTOMER_ATTRIBUTE);
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
