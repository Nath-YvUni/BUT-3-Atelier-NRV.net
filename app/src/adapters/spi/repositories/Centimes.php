<?php
declare(strict_types=1);

namespace nrv\adapters\spi\repositories;

final class Centimes
{
    public static function depuisDecimal(string|int|float $valeur): int
    {
        return (int) round(((float) $valeur) * 100);
    }

    public static function versDecimal(int $centiemes): string
    {
        return sprintf('%d.%02d', intdiv($centiemes, 100), $centiemes % 100);
    }
}
