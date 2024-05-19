<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Reference\Currency\Type;

use BaksDev\Reference\Currency\Type\Currencies\Collection\CurrencyInterface;
use BaksDev\Reference\Currency\Type\Currencies\RUR;
use InvalidArgumentException;

/** Валюта */
final class Currency
{
	public const TYPE = 'currency_type';

	public const TEST = RUR::class;

	
	private CurrencyInterface $currency;

	public function __construct(CurrencyInterface|self|string|null $currency = null)
	{
        if($currency === null)
        {
            $currency = RUR::class;
        }

        if(is_string($currency) && class_exists($currency))
        {
            $instance = new $currency();

            if($instance instanceof CurrencyInterface)
            {
                $this->currency = $instance;
                return;
            }
        }

        if($currency instanceof CurrencyInterface)
        {
            $this->currency = $currency;
            return;
        }

        if($currency instanceof self)
        {
            $this->currency = $currency->getCurrency();
            return;
        }

        /** @var CurrencyInterface $declare */
        foreach(self::getDeclared() as $declare)
        {
            if($declare::equals($currency))
            {
                $this->currency = new $declare;
                return;
            }
        }

        /** По умолчанию присваиваем RUR */
        $this->currency = new RUR();
	}
	
	
	public function __toString(): string
	{
		return $this->currency->getValue();
	}

    public function getCurrency() : CurrencyInterface
    {
        return $this->currency;
    }

    public function getCurrencyValue(): string
    {
        return $this->currency->getValue();
    }

    public function getCurrencyValueUpper(): string
    {
        return mb_strtoupper($this->currency->getValue());
    }


    public static function cases(): array
    {
        $case = [];

        foreach(self::getDeclared() as $declared)
        {
            /** @var CurrencyInterface $declared */
            $class = new $declared;
            $case[$class::sort()] = new self($class);
        }

        return $case;
    }

    public static function getDeclared(): array
    {
        return array_filter(
            get_declared_classes(),
            static function($className) {
                return in_array(CurrencyInterface::class, class_implements($className), true);
            }
        );
    }


    public function equals(mixed $status): bool
    {
        $status = new self($status);

        return $this->getCurrencyValue() === $status->getCurrencyValue();
    }

}