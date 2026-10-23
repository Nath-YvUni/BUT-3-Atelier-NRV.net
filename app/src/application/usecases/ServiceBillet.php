<?php
declare(strict_types=1);

namespace nrv\application\usecases;

use nrv\application\dto\BilletDTO;
use nrv\application\exceptions\BilletIntrouvableException;
use nrv\application\ports\api\ServiceBilletInterface;
use nrv\application\ports\spi\BilletRepositoryInterface;
use nrv\domain\entities\Panier;
use nrv\domain\entities\Billet;

final class ServiceBillet implements ServiceBilletInterface
{
    private const FORMAT_TOKEN = '/\A[0-9a-f]{64}\z/';

    public function __construct(
        private BilletRepositoryInterface $billets,
    ) {
    }


    public function getUserBillets(int $utilisateurId): array
    {
        $billets = $this->billets->findAllByUser($utilisateurId);

        return array_map(
            fn(Billet $billet): array =>
                BilletDTO::fromEntity($billet)->toArray(),
            $billets
        );
    }


    public function genererBillets(int $commandeId, Panier $panier): void
    {
        $nouveaux = [];

        foreach ($panier->getLignes() as $ligne) {
            for ($i = 0; $i < $ligne->getQuantite(); $i++) {
                $nouveaux[] = $this->nouveauBillet(
                    $ligne->getSoiree()->getId(),
                    $ligne->getTarif()->getId(),
                    null,
                    $ligne->getTarif()->getPrix()
                );
            }
        }

        foreach ($panier->getPass() as $pass) {
            $soirees = $pass->getSoirees();
            $nombre = count($soirees);

            if ($nombre === 0) {
                continue;
            }


            $parBillet = intdiv($pass->sousTotal(), $nombre);
            $reste = $pass->sousTotal() - $parBillet * $nombre;

            foreach (array_values($soirees) as $rang => $soiree) {
                $nouveaux[] = $this->nouveauBillet(
                    $soiree->getId(),
                    $pass->getTarif()->getId(),
                    $pass->getId(),
                    $parBillet + ($rang < $reste ? 1 : 0)
                );
            }
        }

        if ($nouveaux === []) {
            return;
        }

        $this->billets->creerPourCommande($commandeId, $nouveaux);
    }

    public function consulterBillet(string $token): array
    {
        // Un token mal formé ne peut pas exister : inutile d'interroger la base.
        if (preg_match(self::FORMAT_TOKEN, $token) !== 1) {
            throw new BilletIntrouvableException();
        }

        $billet = $this->billets->trouverParToken($token);

        if ($billet === null) {
            throw new BilletIntrouvableException();
        }

        return BilletDTO::fromEntity($billet)->toArray();
    }

    private function nouveauBillet(int $soireeId, int $tarifId, ?int $passId, int $prix): array
    {
        return [
            'soiree_id' => $soireeId,
            'tarif_id' => $tarifId,
            'pass_id' => $passId,
            'prix' => $prix,
            'token' => bin2hex(random_bytes(32)),
        ];
    }
}
