<?php

namespace Larateam;
class DigitalCurrency
{
    const DOLLAR_WAGE_SELL = 6000;
    const DOLLAR_WAGE_BUY = 5000;
    const WAGE_FACTOR = 1.0009;
    public $coinMarketData;

    public function initialData()
    {
        $url = 'https://coinmarketcap.com/all/views/all/';
        $this->coinMarketData = file_get_contents($url);
        return $this->coinMarketData;
    }

    public function GetCurrencyPrice($symbol)
    {
        preg_match('/<tr.*>.*<div.*>' . $symbol . '<\/div>.*<a.*>\$(.*)<\/a>.*<\/tr>/Ui', $this->coinMarketData, $output_array);
        $price = str_replace(',', '', $output_array[1]);
        if ($symbol === 'USDT') {
            return $price > 1 ? $price : 1;
        } else {
            return (float)$price;
        }
    }

    public function GetUSDPrice()
    {
        $data = file_get_contents('https://www.tgju.org/chart/price_dollar_rl');
        preg_match('/<span itemprop="price">(.*)<\/span>/', $data, $output_array);
        $dollar = (int)str_replace(',', '', $output_array[1]);
        return $dollar;
    }

    public function GetBuyPrice($currency)
    {
        $currencyPrice = ($currency === 'USDT') ? 1 : $this->GetCurrencyPrice($currency);
        return ($this->GetUSDPrice() + self::DOLLAR_WAGE_BUY) * $this->GetCurrencyPrice('USDT') * $currencyPrice;
    }

    public function GetSellPrice($currency)
    {
        $currencyPrice = ($currency === 'USDT') ? 1 : $this->GetCurrencyPrice($currency);
        return ($this->GetUSDPrice() + self::DOLLAR_WAGE_SELL) * self::WAGE_FACTOR * $this->GetCurrencyPrice('USDT') * $currencyPrice;
    }

    public function GetCurrencyList(){
        preg_match_all('/<tr.*><td.*><\/td><td.*><\/td>.*<div.*>(.*)<\/div>.*<\/tr>/Ui', $this->coinMarketData, $output_array);
        return $output_array[1];
    }
}