<?php

namespace App\Traits;

trait HasPolishPhone
{
    private function generatePolishPhone(): string
    {
        $prefixes = [
            '500', '501', '502', '503', '504', '505', '506', '507', '508', '509',
            '510', '511', '512', '513', '514', '515', '516', '517', '518', '519',
            '600', '601', '602', '603', '604', '605', '606', '607', '608', '609'
        ];

        $prefix = $this->faker->randomElement($prefixes);
        $number = $this->faker->numerify('######');

        return '+48' . $prefix . $number;
    }
}
