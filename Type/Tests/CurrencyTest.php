<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Reference\Currency\Type\Tests;

use BaksDev\Reference\Currency\Type\Currencies\Collection\CurrencyCollection;
use BaksDev\Reference\Currency\Type\Currencies\RUR;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Currency\Type\CurrencyType;
use BaksDev\Wildberries\Orders\Type\WildberriesStatus\Status\Collection\WildberriesStatusInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('reference-currency')]
final class CurrencyTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @var CurrencyCollection $CurrencyCollection */
        $CurrencyCollection = self::getContainer()->get(CurrencyCollection::class);

        /** @var CurrencyCollection $case */
        foreach($CurrencyCollection->cases() as $case)
        {
            $Currency = new Currency($case->getValue());

            self::assertTrue($Currency->equals($case::class)); // немспейс интерфейса
            self::assertTrue($Currency->equals($case)); // объект интерфейса
            self::assertTrue($Currency->equals($case->getValue())); // срока
            self::assertTrue($Currency->equals($Currency)); // объект класса

            $CurrencyType = new CurrencyType();
            $platform = $this
                ->getMockBuilder(AbstractPlatform::class)
                ->getMock();

            $convertToDatabase = $CurrencyType->convertToDatabaseValue($Currency, $platform);
            self::assertEquals($Currency->getCurrencyValue(), $convertToDatabase);

            $convertToPHP = $CurrencyType->convertToPHPValue($convertToDatabase, $platform);
            self::assertInstanceOf(Currency::class, $convertToPHP);
            self::assertEquals($case, $convertToPHP->getCurrency());

        }

        $Currency = new Currency();
        self::assertTrue($Currency->equals(RUR::class));

    }
}