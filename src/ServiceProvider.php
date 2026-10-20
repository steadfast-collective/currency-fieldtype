<?php

namespace Doefom\CurrencyFieldtype;

use Doefom\CurrencyFieldtype\Fieldtypes\CurrencyFieldtype;
use Illuminate\Contracts\Foundation\Application;
use InvalidArgumentException;
use NumberFormatter;
use Statamic\Facades\Site;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Support\Arr;

class ServiceProvider extends AddonServiceProvider
{

    protected $vite = [
        'input' => [
            'resources/js/currency-fieldtype.js',
            // 'resources/css/currency-fieldtype.css',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    /**
     * @var array<string, NumberFormatter>
     */
    protected static array $formatters = [];

    public function register()
    {
        $this->app->bind(NumberFormatter::class, function (Application $app, array $params = []): NumberFormatter {
            if (!Arr::has($params, 'iso')) {
                throw new InvalidArgumentException('The required iso key is missing in params array');
            }

            $style = Arr::get($params, 'style', NumberFormatter::CURRENCY);
            $iso = Arr::get($params, 'iso');

            return $this->makeFormatter(Site::current()->locale(), $style, $iso);
        });
    }

    /**
     * Returns a formatter configured for the given locale, style and currency.
     */
    protected function makeFormatter(string $locale, int $style, ?string $iso): NumberFormatter
    {
        $key = "{$locale}|{$style}|{$iso}";

        if (!isset(static::$formatters[$key])) {
            $fmt = new NumberFormatter($locale, $style);
            $fmt->setTextAttribute(NumberFormatter::CURRENCY_CODE, $iso);
            static::$formatters[$key] = $fmt;
        }

        return clone static::$formatters[$key];
    }

    public function bootAddon()
    {
        CurrencyFieldtype::register();
    }
}
