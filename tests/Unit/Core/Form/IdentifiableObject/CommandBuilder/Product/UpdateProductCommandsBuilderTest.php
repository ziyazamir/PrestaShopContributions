<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace Tests\Unit\Core\Form\IdentifiableObject\CommandBuilder\Product;

use PrestaShop\PrestaShop\Core\Domain\Product\Command\UpdateProductCommand;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductCondition;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductVisibility;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\CommandBuilder\Product\UpdateProductCommandsBuilder;

class UpdateProductCommandsBuilderTest extends AbstractProductCommandBuilderTest
{
    /**
     * @dataProvider getExpectedCommands
     *
     * @param array $formData
     * @param array $expectedCommands
     */
    public function testBuildCommand(array $formData, array $expectedCommands)
    {
        $builder = new UpdateProductCommandsBuilder(self::MODIFY_ALL_SHOPS_PREFIX);
        $builtCommands = $builder->buildCommands($this->getProductId(), $formData, $this->getSingleShopConstraint());
        $this->assertEquals($expectedCommands, $builtCommands);
    }

    /**
     * @return iterable
     */
    public function getExpectedCommands(): iterable
    {
        yield [
            [
                'specifications' => [
                    'condition' => null,
                ],
            ],
            [],
        ];

        yield [
            [
                'no_data' => ['useless value'],
            ],
            [],
        ];

        yield [
            [
                'description' => [
                    'not_handled' => 0,
                ],
            ],
            [],
        ];

        $command = $this->getSingleShopCommand();
        $localizedNames = [
            1 => 'Nom français',
            2 => 'French name',
        ];
        $command->setLocalizedNames($localizedNames);
        yield [
            [
                'header' => [
                    'name' => $localizedNames,
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $localizedDescriptions = [
            1 => 'Description française',
            2 => 'English description',
        ];
        $command->setLocalizedDescriptions($localizedDescriptions);
        yield [
            [
                'description' => [
                    'description' => $localizedDescriptions,
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $localizedShortDescriptions = [
            1 => 'Résumé français',
            2 => 'English summary',
        ];
        $command->setLocalizedShortDescriptions($localizedShortDescriptions);
        yield [
            [
                'description' => [
                    'description_short' => $localizedShortDescriptions,
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setManufacturerId(1);
        yield [
            [
                'description' => [
                    'manufacturer' => '1',
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setCondition(ProductCondition::NEW);
        yield [
            [
                'specifications' => [
                    'condition' => 'new',
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setShowCondition(true);
        yield [
            [
                'specifications' => [
                    'not_handled' => 0,
                    'show_condition' => 1,
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setOnlineOnly(true);
        yield [
            [
                'options' => [
                    'not_handled' => 0,
                    'visibility' => [
                        'online_only' => true,
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setShowPrice(false);
        yield [
            [
                'options' => [
                    'not_handled' => 0,
                    'visibility' => [
                        'show_price' => false,
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setAvailableForOrder(true);
        yield [
            [
                'options' => [
                    'not_handled' => 0,
                    'visibility' => [
                        'available_for_order' => '1',
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setVisibility(ProductVisibility::INVISIBLE);
        yield [
            [
                'options' => [
                    'not_handled' => 0,
                    'visibility' => [
                        'visibility' => ProductVisibility::INVISIBLE,
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setVisibility(ProductVisibility::INVISIBLE);
        $command->setLocalizedShortDescriptions($localizedShortDescriptions);
        $command->setLocalizedDescriptions($localizedDescriptions);
        $command->setLocalizedNames($localizedNames);
        yield [
            [
                'header' => [
                    'name' => $localizedNames,
                ],
                'description' => [
                    'description_short' => $localizedShortDescriptions,
                    'description' => $localizedDescriptions,
                ],
                'options' => [
                    'not_handled' => 0,
                    'visibility' => [
                        'visibility' => ProductVisibility::INVISIBLE,
                    ],
                ],
            ],
            [$command],
        ];
    }

    /**
     * @dataProvider getExpectedCommandsMultiShop
     *
     * @param array $formData
     * @param array $expectedCommands
     */
    public function testBuildCommandMultiShop(array $formData, array $expectedCommands): void
    {
        $builder = new UpdateProductCommandsBuilder(self::MODIFY_ALL_SHOPS_PREFIX);
        $builtCommands = $builder->buildCommands($this->getProductId(), $formData, $this->getSingleShopConstraint());
        $this->assertEquals($expectedCommands, $builtCommands);
    }

    public function getExpectedCommandsMultiShop(): iterable
    {
        $localizedNames = [
            1 => 'Nom français',
            2 => 'French name',
        ];
        $localizedDescriptions = [
            1 => 'Description française',
            2 => 'English description',
        ];
        $localizedShortDescriptions = [
            1 => 'Résumé français',
            2 => 'English summary',
        ];

        $command = $this->getAllShopsCommand();
        $command
            ->setLocalizedNames($localizedNames)
            ->setLocalizedDescriptions($localizedDescriptions)
            ->setLocalizedShortDescriptions($localizedShortDescriptions)
        ;
        yield [
            [
                'header' => [
                    'name' => $localizedNames,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'name' => true,
                ],
                'description' => [
                    'description' => $localizedDescriptions,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'description' => true,
                    'description_short' => $localizedShortDescriptions,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'description_short' => true,
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command
            ->setLocalizedNames($localizedNames)
            ->setLocalizedDescriptions($localizedDescriptions)
            ->setLocalizedShortDescriptions($localizedShortDescriptions)
        ;
        yield [
            [
                'header' => [
                    'name' => $localizedNames,
                ],
                'description' => [
                    'description' => $localizedDescriptions,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'description' => false,
                    'description_short' => $localizedShortDescriptions,
                ],
            ],
            [$command],
        ];

        $allShopsCommand = $this->getAllShopsCommand();
        $singleCommand = $this->getSingleShopCommand();
        $singleCommand
            ->setLocalizedDescriptions($localizedDescriptions)
        ;
        $allShopsCommand
            ->setLocalizedNames($localizedNames)
            ->setLocalizedShortDescriptions($localizedShortDescriptions)
        ;
        yield [
            [
                'header' => [
                    'name' => $localizedNames,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'name' => true,
                ],
                'description' => [
                    'description' => $localizedDescriptions,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'description' => false,
                    'description_short' => $localizedShortDescriptions,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'description_short' => true,
                ],
            ],
            [$singleCommand, $allShopsCommand],
        ];

        $allShopsCommand = $this->getAllShopsCommand();
        $singleCommand = $this->getSingleShopCommand();
        $singleCommand
            ->setLocalizedNames($localizedNames)
            ->setLocalizedShortDescriptions($localizedShortDescriptions)
        ;
        $allShopsCommand
            ->setLocalizedDescriptions($localizedDescriptions)
        ;
        yield [
            [
                'header' => [
                    'name' => $localizedNames,
                ],
                'description' => [
                    'description' => $localizedDescriptions,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'description' => true,
                    'description_short' => $localizedShortDescriptions,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'description_short' => false,
                ],
            ],
            [$singleCommand, $allShopsCommand],
        ];

        $command = $this->getAllShopsCommand();
        $command->setCondition(ProductCondition::NEW);
        yield [
            [
                'specifications' => [
                    'condition' => 'new',
                    self::MODIFY_ALL_SHOPS_PREFIX . 'condition' => true,
                ],
            ],
            [$command],
        ];

        $command = $this->getAllShopsCommand();
        $command->setShowCondition(false);
        yield [
            [
                'specifications' => [
                    'not_handled' => 0,
                    'show_condition' => 0,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'show_condition' => true,
                ],
            ],
            [$command],
        ];

        $command = $this->getAllShopsCommand();
        $command->setShowPrice(false);
        yield [
            [
                'options' => [
                    'not_handled' => 0,
                    'visibility' => [
                        'show_price' => false,
                        self::MODIFY_ALL_SHOPS_PREFIX . 'show_price' => true,
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getAllShopsCommand();
        $command->setAvailableForOrder(true);
        yield [
            [
                'options' => [
                    'visibility' => [
                        'available_for_order' => '1',
                        self::MODIFY_ALL_SHOPS_PREFIX . 'available_for_order' => true,
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getAllShopsCommand();
        $command->setVisibility(ProductVisibility::INVISIBLE);
        yield [
            [
                'options' => [
                    'visibility' => [
                        'visibility' => ProductVisibility::INVISIBLE,
                        self::MODIFY_ALL_SHOPS_PREFIX . 'visibility' => true,
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getAllShopsCommand();
        $command->setOnlineOnly(false);
        yield [
            [
                'options' => [
                    'visibility' => [
                        'online_only' => false,
                        self::MODIFY_ALL_SHOPS_PREFIX . 'online_only' => true,
                    ],
                ],
            ],
            [$command],
        ];

        $singleShopCommand = $this->getSingleShopCommand();
        $singleShopCommand->setVisibility(ProductVisibility::VISIBLE_EVERYWHERE);
        $allShopsCommand = $this->getAllShopsCommand();
        $allShopsCommand->setAvailableForOrder(true);
        yield [
            [
                'options' => [
                    'not_handled' => 0,
                    'visibility' => [
                        'visibility' => ProductVisibility::VISIBLE_EVERYWHERE,
                        'available_for_order' => true,
                        self::MODIFY_ALL_SHOPS_PREFIX . 'available_for_order' => true,
                    ],
                ],
            ],
            [$singleShopCommand, $allShopsCommand],
        ];

        $singleShopCommand = $this->getSingleShopCommand();
        $singleShopCommand
            ->setVisibility(ProductVisibility::VISIBLE_EVERYWHERE)
            ->setLocalizedShortDescriptions($localizedShortDescriptions)
        ;
        $allShopsCommand = $this->getAllShopsCommand();
        $allShopsCommand
            ->setAvailableForOrder(true)
            ->setLocalizedNames($localizedNames)
            ->setLocalizedDescriptions($localizedDescriptions)
        ;

        yield [
            [
                'header' => [
                    'name' => $localizedNames,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'name' => true,
                ],
                'description' => [
                    'description' => $localizedDescriptions,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'description' => true,
                    'description_short' => $localizedShortDescriptions,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'description_short' => false,
                ],
                'options' => [
                    'not_handled' => 0,
                    'visibility' => [
                        'visibility' => ProductVisibility::VISIBLE_EVERYWHERE,
                        'available_for_order' => true,
                        self::MODIFY_ALL_SHOPS_PREFIX . 'available_for_order' => true,
                    ],
                ],
            ],
            [$singleShopCommand, $allShopsCommand],
        ];
    }

    private function getSingleShopCommand(): UpdateProductCommand
    {
        return new UpdateProductCommand($this->getProductId()->getValue(), $this->getSingleShopConstraint());
    }

    private function getAllShopsCommand(): UpdateProductCommand
    {
        return new UpdateProductCommand($this->getProductId()->getValue(), ShopConstraint::allShops());
    }
}