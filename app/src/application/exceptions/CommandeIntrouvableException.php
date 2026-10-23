<?php
declare(strict_types=1);

namespace nrv\application\exceptions;

class CommandeIntrouvableException extends \RuntimeException
{
    public function __construct(
        string $message = 'Cette commande n’existe pas.'
    ) {
        parent::__construct($message);
    }
}
