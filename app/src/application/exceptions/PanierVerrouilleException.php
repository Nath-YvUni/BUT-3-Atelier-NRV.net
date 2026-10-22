<?php
declare(strict_types=1);

namespace nrv\application\exceptions;

class PanierVerrouilleException extends \RuntimeException
{
    public function __construct(
        string $message = 'Le panier est validé : une commande est en attente de paiement.'
    ) {
        parent::__construct($message);
    }
}
