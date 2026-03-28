<?php

namespace App\Support;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ExchangeRateManager
{
    /**
     * @return array<string, float>
     */
    public function syncLatestRates(): array
    {
        $baseCurrency = $this->baseCurrency();
        $quoteCurrencies = $this->quoteCurrencies();
        $response = Http::baseUrl($this->baseUrl())
            ->timeout(10)
            ->get('/latest', [
                'base' => $baseCurrency,
                'symbols' => implode(',', $quoteCurrencies),
            ])
            ->throw()
            ->json();

        if (! is_array($response) || ! is_array($response['rates'] ?? null)) {
            throw new RuntimeException('Frankfurter returned an invalid exchange rate payload.');
        }

        $fetchedAt = now();
        $normalizedRates = [];

        foreach ($quoteCurrencies as $quoteCurrency) {
            $rate = $response['rates'][$quoteCurrency] ?? null;

            if (! is_numeric($rate) || (float) $rate <= 0) {
                throw new RuntimeException("Frankfurter did not return a valid {$quoteCurrency} rate.");
            }

            ExchangeRate::query()->updateOrCreate(
                [
                    'base_currency' => $baseCurrency,
                    'quote_currency' => $quoteCurrency,
                ],
                [
                    'rate' => (float) $rate,
                    'fetched_at' => $fetchedAt,
                ],
            );

            $normalizedRates[$quoteCurrency] = (float) $rate;
        }

        return $normalizedRates;
    }

    public function latestRate(string $quoteCurrency): float
    {
        $normalizedQuoteCurrency = strtoupper(trim($quoteCurrency));

        if ($normalizedQuoteCurrency === $this->baseCurrency()) {
            return 1.0;
        }

        $rate = ExchangeRate::query()
            ->where('base_currency', $this->baseCurrency())
            ->where('quote_currency', $normalizedQuoteCurrency)
            ->value('rate');

        if (is_numeric($rate) && (float) $rate > 0) {
            return (float) $rate;
        }

        $this->syncLatestRates();

        $freshRate = ExchangeRate::query()
            ->where('base_currency', $this->baseCurrency())
            ->where('quote_currency', $normalizedQuoteCurrency)
            ->value('rate');

        if (is_numeric($freshRate) && (float) $freshRate > 0) {
            return (float) $freshRate;
        }

        throw new RuntimeException("Exchange rate for {$normalizedQuoteCurrency} is unavailable.");
    }

    public function convertEuroCentsToCurrencyCents(int $euroCents, string $quoteCurrency, ?float $lockedRate = null): int
    {
        $rate = $lockedRate ?? $this->latestRate($quoteCurrency);

        if (strtoupper(trim($quoteCurrency)) === $this->baseCurrency()) {
            return $euroCents;
        }

        return (int) round(($euroCents / 100) * $rate * 100);
    }

    /**
     * @return list<string>
     */
    public function supportedCheckoutCurrencies(): array
    {
        /** @var list<string> $currencies */
        $currencies = config('business.supported_checkout_currencies', ['EUR', 'RON', 'GBP']);

        return array_values(array_unique(array_map(
            static fn (string $currency): string => strtoupper($currency),
            $currencies,
        )));
    }

    public function baseCurrency(): string
    {
        return strtoupper((string) config('business.base_currency', 'EUR'));
    }

    /**
     * @return list<string>
     */
    private function quoteCurrencies(): array
    {
        return array_values(array_filter(
            $this->supportedCheckoutCurrencies(),
            fn (string $currency): bool => $currency !== $this->baseCurrency(),
        ));
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('services.frankfurter.base_url', 'https://api.frankfurter.dev/v1'), '/');
    }
}
