<?php

namespace Feature;

use Doefom\CurrencyFieldtype\Fieldtypes\CurrencyFieldtype;
use Doefom\CurrencyFieldtype\ServiceProvider;
use NumberFormatter;
use ReflectionProperty;
use Statamic\Facades\Site;
use Statamic\Fields\Field;
use Tests\TestCase;

class NumberFormatterReuseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Site::setCurrent('en_US');

        $this->formatters()->setValue(null, []);
    }

    public function test_reuses_one_formatter_per_currency_and_style()
    {
        app(NumberFormatter::class, ['iso' => 'USD']);
        app(NumberFormatter::class, ['iso' => 'USD']);
        app(NumberFormatter::class, ['iso' => 'EUR']);
        app(NumberFormatter::class, ['iso' => 'USD', 'style' => NumberFormatter::DECIMAL]);

        $this->assertCount(3, $this->formatters()->getValue());
    }

    public function test_returns_a_separate_copy_each_time()
    {
        $first = app(NumberFormatter::class, ['iso' => 'USD']);
        $first->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);

        $second = app(NumberFormatter::class, ['iso' => 'USD']);

        $this->assertNotSame($first, $second);
        $this->assertEquals(2, $second->getAttribute(NumberFormatter::FRACTION_DIGITS));
        $this->assertEquals('$1,234.56', $second->formatCurrency(1234.56, 'USD'));
    }

    public function test_augmented_currency_creates_formatter_only_when_needed()
    {
        $resolved = 0;
        $this->app->resolving(NumberFormatter::class, function () use (&$resolved) {
            $resolved++;
        });

        $field = new Field('price', ['iso' => 'USD', 'type' => 'currency']);
        $currency = (new CurrencyFieldtype())->setField($field)->augment(1234.56);

        $this->assertEquals(1234.56, $currency->augmentedValue('value')->value());
        $this->assertEquals('USD', $currency->augmentedValue('iso')->value());
        $this->assertEquals(0, $resolved);

        $augmented = $currency->newAugmentedInstance();
        $this->assertEquals('$1,234.56', $augmented->get('formatted')->value());
        $this->assertEquals('$', $augmented->get('symbol')->value());
        $this->assertEquals(1, $resolved);
    }

    protected function formatters(): ReflectionProperty
    {
        return new ReflectionProperty(ServiceProvider::class, 'formatters');
    }
}
