<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_BannersLite
 */


namespace Amasty\BannersLite\Model\SalesRule;

use Amasty\BannersLite\Api\BannerRepositoryInterface;
use Amasty\BannersLite\Api\BannerRuleRepositoryInterface;
use Amasty\BannersLite\Api\Data\BannerInterface;
use Amasty\BannersLite\Api\Data\BannerRuleInterface;
use Amasty\BannersLite\Model\BannerFactory;
use Amasty\BannersLite\Model\BannerRuleFactory;
use Amasty\BannersLite\Model\ImageProcessor;
use Amasty\Base\Model\Serializer;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\SalesRule\Api\Data\RuleInterface as SalesRuleInterface;

/**
 * Class SaveHandler
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var BannerRepositoryInterface
     */
    private $bannerRepository;

    /**
     * @var BannerFactory
     */
    private $bannerFactory;

    /**
     * @var BannerRuleRepositoryInterface
     */
    private $bannerRuleRepository;

    /**
     * @var BannerRuleFactory
     */
    private $bannerRuleFactory;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var Serializer
     */
    private $serializerBase;

    public function __construct(
        BannerRepositoryInterface $bannerRepository,
        MetadataPool $metadataPool,
        BannerFactory $bannerFactory,
        BannerRuleRepositoryInterface $bannerRuleRepository,
        BannerRuleFactory $bannerRuleFactory,
        ImageProcessor $imageProcessor,
        Serializer $serializerBase
    ) {
        $this->bannerRepository = $bannerRepository;
        $this->metadataPool = $metadataPool;
        $this->bannerFactory = $bannerFactory;
        $this->bannerRuleRepository = $bannerRuleRepository;
        $this->bannerRuleFactory = $bannerRuleFactory;
        $this->imageProcessor = $imageProcessor;
        $this->serializerBase = $serializerBase;
    }

    /**
     * Stores Promo Banners value from Sales Rule extension attributes
     *
     * @param \Magento\SalesRule\Model\Rule|\Magento\SalesRule\Model\Data\Rule $entity
     * @param array $arguments
     *
     * @return \Magento\SalesRule\Model\Rule|\Magento\SalesRule\Model\Data\Rule
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        /** @var array $attributes */
        $attributes = $entity->getExtensionAttributes() ?: [];

        if (isset($attributes[BannerInterface::EXTENSION_CODE])) {
            $this->saveBannerData($entity, $attributes);
        }

        $this->saveBannerRule($entity, $attributes);

        return $entity;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $entity
     * @param array $attributes
     */
    private function saveBannerRule($entity, $attributes)
    {
        $linkField = $this->metadataPool->getMetadata(SalesRuleInterface::class)->getLinkField();
        $ruleLinkId = (int)$entity->getDataByKey($linkField);

        try {
            /** @var \Amasty\BannersLite\Model\BannerRule $bannerRule */
            $bannerRule = $this->bannerRuleRepository->getBySalesruleId($ruleLinkId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            /** @var \Amasty\BannersLite\Model\BannerRule $bannerRule */
            $bannerRule = $this->bannerRuleFactory->create();
        }

        $this->convertCategoryIds($attributes);

        $bannerRule->addData($attributes);

        if (!isset($attributes[BannerRuleInterface::BANNER_PRODUCT_SKU]) && !$bannerRule->getBannerProductSku()) {
            $bannerRule->setBannerProductSku("");
        }

        if ((int)$bannerRule->getSalesruleId() !== $ruleLinkId) {
            $bannerRule->setEntityId(null);
            $bannerRule->setSalesruleId($ruleLinkId);
        }

        $this->bannerRuleRepository->save($bannerRule);
    }

    /**
     * @param array $attributes
     */
    private function convertCategoryIds(&$attributes)
    {
        if (isset($attributes[BannerRuleInterface::BANNER_PRODUCT_CATEGORIES])
            && is_array($attributes[BannerRuleInterface::BANNER_PRODUCT_CATEGORIES])
        ) {
            $attributes[BannerRuleInterface::BANNER_PRODUCT_CATEGORIES]
                = implode(',', $attributes[BannerRuleInterface::BANNER_PRODUCT_CATEGORIES]);
        } elseif (isset($attributes[BannerRuleInterface::SHOW_BANNER_FOR])
            && $attributes[BannerRuleInterface::SHOW_BANNER_FOR] == '2'
            && !isset($attributes[BannerRuleInterface::BANNER_PRODUCT_CATEGORIES])
        ) {
            $attributes[BannerRuleInterface::BANNER_PRODUCT_CATEGORIES] = '';
        } elseif (!isset($attributes[BannerRuleInterface::SHOW_BANNER_FOR])) {
            $attributes[BannerRuleInterface::SHOW_BANNER_FOR] = BannerRuleInterface::ALL_PRODUCTS;
        }
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $entity
     * @param array $attributes
     */
    private function saveBannerData(\Magento\SalesRule\Model\Rule $entity, &$attributes)
    {
        $linkField = $this->metadataPool->getMetadata(SalesRuleInterface::class)->getLinkField();
        $ruleLinkId = (int)$entity->getDataByKey($linkField);
        $inputData = $attributes[BannerInterface::EXTENSION_CODE];
        unset($attributes[BannerInterface::EXTENSION_CODE]);

        /** @var array|BannerInterface $data */
        foreach ($inputData as $key => $data) {
            try {
                /** @var \Amasty\BannersLite\Model\Banner $promoBanner */
                $promoBanner = $this->bannerRepository->getByBannerType($ruleLinkId, $key);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                /** @var \Amasty\BannersLite\Model\Banner $promoBanner */
                $promoBanner = $this->bannerFactory->create();
            }

            if ($data instanceof BannerInterface) {
                $data = $data->getData();
            }

            if (!array_key_exists(BannerInterface::BANNER_IMAGE, $data)) {
                $this->imageProcessor->deleteImage($promoBanner->getBannerImage());
                $promoBanner->setBannerImage(null);
            } else {
                $this->isEqualImage($promoBanner, $data);
            }

            $promoBanner->addData($data);
            $promoBanner->setBannerType($key);

            if ((int)$promoBanner->getSalesruleId() !== $ruleLinkId) {
                $promoBanner->setEntityId(null);
                $promoBanner->setSalesruleId($ruleLinkId);
            }

            $this->bannerRepository->save($promoBanner);
        }
    }

    /**
     * @param \Amasty\BannersLite\Model\Banner $promoBanner
     * @param array $newData
     */
    private function isEqualImage(\Amasty\BannersLite\Model\Banner $promoBanner, $newData)
    {
        $bannerImage = $this->serializerBase->unserialize($promoBanner->getBannerImage());
        if ($bannerImage) {
            $bannerImageName = $bannerImage[0]['name'];
            $newDataImage = is_array($newData[BannerInterface::BANNER_IMAGE]) ? $newData[BannerInterface::BANNER_IMAGE]
                : $this->serializerBase->unserialize($newData[BannerInterface::BANNER_IMAGE]);

            if ($newDataImage[0]['name'] !== $bannerImageName) {
                $this->imageProcessor->deleteImage($promoBanner->getBannerImage());
            }
        }
    }
}
