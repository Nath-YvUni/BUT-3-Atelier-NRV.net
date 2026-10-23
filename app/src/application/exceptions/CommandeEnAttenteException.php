<?php
declare(strict_types=1);

namespace nrv\application\exceptions;

class CommandeEnAttenteException extends \RuntimeException
{
    public function __construct(
        string $message = 'Une commande est déjà en attente de paiement pour ce panier.'
    ) {
        parent::__construct($message);
    }
}
