<?php
/*
 * Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Reference\Currency\Api;

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorageInterface;
use DOMDocument;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class CbrCurrencyRequest
{
    public function __construct(
        private UserProfileTokenStorageInterface $UserProfileTokenStorage,
        private AppCacheInterface $Cache,

    ) {}

    /**
     * Метод возвращает курс валют Центрального Банка России на текущий день по коду валюты
     * @see CbrCurrencyDTO
     */
    public function getCurrency(string $currency): float|false
    {
        $Cache = $this->Cache->init('reference-currency');
        $profile = $this->UserProfileTokenStorage->getProfile();

        return $Cache->get(self::class.$currency.$profile, function(ItemInterface $item) use ($currency): float|false {

            $item->expiresAfter(86400);

            $xml = new DOMDocument();
            $url = 'http://www.cbr.ru/scripts/XML_daily.asp';

            if(@$xml->load($url))
            {
                $root = $xml->documentElement;
                $items = $root->getElementsByTagName('Valute');

                foreach($items as $data)
                {
                    $code = $data->getElementsByTagName('CharCode')->item(0)->nodeValue;
                    $curs = $data->getElementsByTagName('Value')->item(0)->nodeValue;

                    if($code === $currency)
                    {
                        return (float) str_replace(',', '.', $curs);
                    }
                }
            }

            return false;
        });
    }
}