<?php
declare(strict_types=1);

namespace nrv\application\exceptions;

class SpectacleNotFoundException extends \RuntimeException
{
    public function __construct(string $message = 'Aucun spectacle trouvé')
    {
        parent::__construct($message);
    }
}
