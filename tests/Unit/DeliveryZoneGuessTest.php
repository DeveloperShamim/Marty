<?php

namespace Tests\Unit;

use App\Support\DeliveryZoneGuess;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DeliveryZoneGuessTest extends TestCase
{
    public static function addresses(): array
    {
        return [
            'Dhaka area'                 => ['House 12, Road 5, Dhanmondi', '', 'inside_dhaka'],
            'Dhaka as the city'          => ['Holding 4, Station Road', 'Dhaka', 'inside_dhaka'],
            'Uttara sector'              => ['Sector 7, Uttara', '', 'inside_dhaka'],
            'Bangla Dhaka area'          => ['বাসা ১২, মিরপুর ১০', '', 'inside_dhaka'],
            'district'                   => ['Agrabad', 'Chattogram', 'outside_dhaka'],
            'old spelling'               => ['Zindabazar', 'Sylhet', 'outside_dhaka'],
            "Cox's Bazar"                => ["Kolatoli, Cox's Bazar", '', 'outside_dhaka'],
            'same area name, other town' => ['Mirpur', 'Kushtia', 'outside_dhaka'],
            'suburb counts as outside'   => ['Savar', 'Dhaka', 'outside_dhaka'],
            'Dhaka Road in another town' => ['Dhaka Road', 'Mymensingh', 'outside_dhaka'],
            'Bangla district'            => ['আগ্রাবাদ', 'চট্টগ্রাম', 'outside_dhaka'],
            'no clue'                    => ['Holding 4, Station Road', '', null],
            'empty'                      => ['', '', null],
            'part of a word only'        => ['Feniton Lane', 'Abc', null],
        ];
    }

    #[DataProvider('addresses')]
    public function test_guess(string $address, string $city, ?string $zone): void
    {
        $this->assertSame($zone, DeliveryZoneGuess::guess($address, $city));
    }
}
