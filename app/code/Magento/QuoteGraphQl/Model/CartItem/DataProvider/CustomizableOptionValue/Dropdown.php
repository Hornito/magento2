<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem\DataProvider\CustomizableOptionValue;

use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Type\Select as SelectOptionType;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Item\Option as SelectedOption;
use Magento\QuoteGraphQl\Model\CartItem\DataProvider\CustomizableOptionValueInterface;

/**
 * @inheritdoc
 */
class Dropdown implements CustomizableOptionValueInterface
{
    /**
     * Option type name
     */
    private const OPTION_TYPE = 'custom-option';

    /**
     * @var PriceUnitLabel
     */
    private $priceUnitLabel;

    /**
     * @param PriceUnitLabel $priceUnitLabel
     */
    public function __construct(
        PriceUnitLabel $priceUnitLabel
    ) {
        $this->priceUnitLabel = $priceUnitLabel;
    }

    /**
     * @inheritdoc
     */
    public function getData(
        QuoteItem $cartItem,
        Option $option,
        SelectedOption $selectedOption
    ): array {
        /** @var SelectOptionType $optionTypeRenderer */
        $optionTypeRenderer = $option->groupFactory($option->getType())
            ->setOption($option)
            ->setConfigurationItemOption($selectedOption);

        $selectedValue = $selectedOption->getValue();
        $optionValue = $option->getValueById($selectedValue);
        $optionPriceType = (string)$optionValue->getPriceType();
        $priceValueUnits = $this->priceUnitLabel->getData($optionPriceType);

        $optionDetails = [
            self::OPTION_TYPE,
            $option->getOptionId(),
            $optionValue->getOptionTypeId()
        ];

        $selectedOptionValueData = [
            'id' => $selectedOption->getId(),
            'customizable_option_value_uid' => base64_encode((string)implode('/', $optionDetails)),
            'label' => $optionTypeRenderer->getFormattedOptionValue($selectedValue),
            'value' => $selectedValue,
            'price' => [
                'type' => strtoupper($optionPriceType),
                'units' => $priceValueUnits,
                'value' => $optionValue->getPrice(),
            ]
        ];
        return [$selectedOptionValueData];
    }
}
