<?php

declare(strict_types=1);

/*
 * This file is part of the "elao/enum" package.
 *
 * Copyright (C) Elao
 *
 * @author Elao <contact@elao.com>
 */

namespace Elao\Enum\Tests\Unit\Bridge\Symfony\Bundle\DependencyInjection;

use Elao\Enum\Bridge\Symfony\Bundle\DependencyInjection\Configuration;
use Elao\Enum\Tests\Fixtures\Enum\Permissions;
use Elao\Enum\Tests\Fixtures\Enum\RequestStatus;
use Elao\Enum\Tests\Fixtures\Enum\Suit;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    use ExpectDeprecationTrait;

    public function testDefaultConfig(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [[]]);

        self::assertEquals($this->getDefaultConfig(), $config);
    }

    public function testDoctrineConfig(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [[
            'doctrine' => [
                'types' => [
                    Suit::class => ['class' => Suit::class],
                    Permissions::class => Permissions::class,
                    RequestStatus::class => ['class' => RequestStatus::class, 'default' => RequestStatus::Success->value],
                ],
            ],
        ]]);

        self::assertEquals([
            'doctrine' => [
                'types' => [
                    Suit::class => ['class' => Suit::class, 'default' => null],
                    Permissions::class => ['class' => Permissions::class, 'default' => null],
                    RequestStatus::class => ['class' => RequestStatus::class, 'default' => RequestStatus::Success->value],
                ],
            ],
        ] + $this->getDefaultConfig(), $config);
    }

    public function testDoctrineConfigNameAsEnumClass(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [[
            'doctrine' => [
                'types' => [
                    Suit::class => [],
                    Permissions::class => null,
                ],
            ],
        ]]);

        self::assertEquals([
            'doctrine' => [
                'types' => [
                    Suit::class => ['class' => Suit::class, 'default' => null],
                    Permissions::class => ['class' => Permissions::class, 'default' => null],
                ],
            ],
        ] + $this->getDefaultConfig(), $config);
    }

    public function testDoctrineTypeConfigWithInvalidEnumClass(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration for path "elao_enum.doctrine.types.std.class": Invalid class. Expected instance of "BackedEnum". Got "stdClass".');

        $processor = new Processor();
        $processor->processConfiguration(new Configuration(), [[
            'doctrine' => [
                'types' => [
                    'std' => ['class' => \stdClass::class],
                ],
            ],
        ]]);
    }

    private function getDefaultConfig(): array
    {
        return [
            'doctrine' => [
                'types' => [],
            ],
        ];
    }
}
