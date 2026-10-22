<?php
declare(strict_types=1);

namespace nrv\application\ports\api;

interface ServiceSoireeInterface {
    
    public function getSoirees(?string $date = null) : array;

    public function getDetails(int $id);
}