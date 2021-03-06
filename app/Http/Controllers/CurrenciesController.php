<?php

namespace App\Http\Controllers;

use App\Currency;
use App\CurrencyInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CurrenciesController extends Controller
{
    public function show(Request $request)
    {
        $currency = new Currency;
        $currencyInfo = new CurrencyInfo;
        $todayDate = date('Y-m-d');

        if (!$currencyInfo->isTodayParsed()) {
            Artisan::call('currency:parse');
        }

        if (empty($request["history"])) {
            $request["history"] = 2;
        }

        if (empty($request["currency"])) {
            $request["currency"] = 'USD';
        }

        $date = [
            'to'  => $todayDate,
            'from'    => date('Y-m-d', strtotime("- ".$request["history"]." day"))
        ];

        if ($request["history"] == 'all') {
            $date = 'all';
        }

        $availableCurrencies = $currency->getAvailableCurrencies();

        $formedCurrencies = $this->formCurrenciesData($availableCurrencies);

        $formedData = $this->formDataForGraph($request["currency"], $date);

        return view('home', ['currencies' => $formedCurrencies, 'data' => $formedData]);
    }

    private function formDataForGraph($curr, $date)
    {
        $currency = new Currency;

        $data = $currency->getDataByCurrencyAndDate($date, $curr);

        $data = $this->formCurrenciesData($data);

        return $data;
    }

    private function formCurrenciesData($currencyData)
    {
        $formedData = [];

        foreach ($currencyData as $currency) {
            $formedData[] = $currency->getAttributes();
        }

        return $formedData;
    }
}
