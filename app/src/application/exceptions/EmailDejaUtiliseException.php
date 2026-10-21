<?php
declare(strict_types=1);

namespace nrv\application\exceptions;

final class EmailDejaUtiliseException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Cette adresse e-mail est déjà utilisée.');
    }
}