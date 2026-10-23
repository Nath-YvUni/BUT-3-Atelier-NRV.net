<?php
declare(strict_types=1);

namespace nrv\application\exceptions;

class PlacesInsuffisantesException extends \RuntimeException
{
    public function __construct(
        private array $soirees,
        string $message = 'La commande ne peut pas être payée : certaines soirées n\'ont pas assez de places.'
    ) {
        parent::__construct($message);
    }

    public function getSoirees(): array
    {
        return $this->soirees;
    }
}
