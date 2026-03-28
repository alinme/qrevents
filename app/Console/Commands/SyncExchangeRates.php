<?php

namespace App\Console\Commands;

use App\Support\ExchangeRateManager;
use Illuminate\Console\Command;
use Throwable;

class SyncExchangeRates extends Command
{
    protected $signature = 'app:sync-exchange-rates';

    protected $description = 'Sync EUR exchange rates for supported business checkout currencies.';

    public function handle(ExchangeRateManager $exchangeRateManager): int
    {
        try {
            $rates = $exchangeRateManager->syncLatestRates();
        } catch (Throwable $throwable) {
            $this->error($throwable->getMessage());

            return self::FAILURE;
        }

        foreach ($rates as $currency => $rate) {
            $this->line("EUR -> {$currency}: ".number_format($rate, 6, '.', ''));
        }

        return self::SUCCESS;
    }
}
