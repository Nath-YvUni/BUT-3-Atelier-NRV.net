<?php
declare(strict_types=1);

namespace nrv\application\dto;

final class Montant
{
    public static function formater(int $centiemes): string
    {
        return sprintf('%d.%02d', intdiv($centiemes, 100), $centiemes % 100);
    }
}
