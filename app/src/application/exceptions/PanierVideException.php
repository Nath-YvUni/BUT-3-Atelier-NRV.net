<?php
declare(strict_types=1);

namespace nrv\application\exceptions;

class PanierVideException extends \RuntimeException
{
    public function __construct(
        string $message = 'Le panier est vide.'
    ) {
        parent::__construct($message);
    }
}
