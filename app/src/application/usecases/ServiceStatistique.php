<?php
declare(strict_types=1);

namespace nrv\application\usecases;

use nrv\application\dto\Montant;
use nrv\application\dto\StatistiqueSoireeDTO;
use nrv\application\ports\api\ServiceStatistiqueInterface;
use nrv\application\ports\spi\StatistiqueRepositoryInterface;

final class ServiceStatistique implements ServiceStatistiqueInterface
{
    public function __construct(
        private StatistiqueRepositoryInterface $statistiques
    ) {
    }

    public function getStatistiques(): array
    {
        $soirees = [];
        $totalBilletsVendus = 0;
        $recetteTotale = 0;

        foreach ($this->statistiques->trouverParSoiree() as $statistique) {
            $soirees[] = StatistiqueSoireeDTO::fromEntity(
                $statistique
            )->toArray();

            $totalBilletsVendus += $statistique->getPlacesVendues();
            $recetteTotale += $statistique->getRecette();
        }

        return [
            'soirees' => $soirees,
            'total_billets_vendus' => $totalBilletsVendus,
            'recette_totale' => Montant::formater($recetteTotale),
            'devise' => 'EUR',
        ];
    }
}