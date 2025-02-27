<?php

namespace App\Command;

use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:cleanup-annonces',
    description: 'Supprime les annonces de plus d\'un an',
)]
class DeleteOldAnnoncesCommand extends Command
{
    private AnnonceRepository $annonceRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(AnnonceRepository $annonceRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->annonceRepository = $annonceRepository;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $oneYearAgo = new \DateTimeImmutable('-1 year');
        
        // 🔥 Trouver les annonces concernées
        $oldAnnonces = $this->annonceRepository->findOldAnnonces($oneYearAgo);

        if (empty($oldAnnonces)) {
            $output->writeln('✅ Aucune annonce à supprimer.');
            return Command::SUCCESS;
        }

        // 🔥 Suppression des annonces
        foreach ($oldAnnonces as $annonce) {
            $this->entityManager->remove($annonce);
        }
        $this->entityManager->flush();

        $output->writeln('🔥 Suppression terminée : ' . count($oldAnnonces) . ' annonces supprimées.');
        return Command::SUCCESS;
    }
}
