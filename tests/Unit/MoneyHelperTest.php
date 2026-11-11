<?php

namespace Tests\Unit;

use App\Utility\MoneyHelper;
use PHPUnit\Framework\TestCase;

class MoneyHelperTest extends TestCase
{
    public function test_an_amount_is_written_out_in_words(): void
    {
        $this->assertSame('One Thousand Five Hundred Pesos Only', MoneyHelper::toWords('1500.00'));
        $this->assertSame('One Hundred Pesos Only', MoneyHelper::toWords('100'));
        $this->assertSame('One Peso Only', MoneyHelper::toWords('1.00'));
        $this->assertSame('One Million Pesos Only', MoneyHelper::toWords('1000000.00'));
    }

    public function test_centavos_are_written_as_a_fraction(): void
    {
        $this->assertSame('Zero Pesos and 01/100 Only', MoneyHelper::toWords('0.01'));
        $this->assertSame('One Thousand One Pesos and 25/100 Only', MoneyHelper::toWords('1001.25'));
        $this->assertSame('Nine Hundred Ninety Nine Pesos and 99/100 Only', MoneyHelper::toWords('999.99'));
    }
}
