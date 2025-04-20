<?php

namespace App\Repository;

use App\Entity\Emission;
use App\Entity\Categories;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Bundle\SecurityBundle\Security;

class EmissionRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginatorInterface $paginator
    ) {
        parent::__construct($registry, Emission::class);
    }

    /**
     * Paginate les émissions pour l'espace admin avec contrôle d'accès.
     */
    public function paginateEmissionsAdmin(int $page, string $excludeUrl, ?User $user = null, bool $isAdmin = false)
{
    $qb = $this->createQueryBuilder('e')
        ->select('e', 'c')
        ->leftJoin('e.categorie', 'c')
        ->andWhere('e.url != :excludeUrl')
        ->setParameter('excludeUrl', $excludeUrl)
        ->orderBy('e.datepub', 'DESC');

    if ($user && !$isAdmin) {
        $qb->andWhere('e.user = :user')
           ->setParameter('user', $user);
    }

    return $this->paginator->paginate($qb, $page, 20, [
        'distinct' => true,
        'sortFieldAllowList' => ['e.titre'],
    ]);
}


    /**
     * Paginate les émissions publiques.
     */
    public function paginateEmissions(int $page, string $excludeUrl): PaginationInterface
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e', 'c')
            ->leftJoin('e.categorie', 'c')
            ->andWhere('e.url != :excludeUrl')
            ->setParameter('excludeUrl', $excludeUrl)
            ->orderBy('e.datepub', 'DESC');

        return $this->paginator->paginate($qb, $page, 20, [
            'distinct' => true,
            'sortFieldAllowList' => ['e.titre'],
        ]);
    }

    /**
     * Dernières émissions par date de publication.
     */
    public function lastEmissions(string $excludeUrl): array
    {
        return $this->createQueryBuilder('e')
            ->select('e', 'c')
            ->leftJoin('e.categorie', 'c')
            ->andWhere('e.url != :excludeUrl')
            ->setParameter('excludeUrl', $excludeUrl)
            ->orderBy('e.datepub', 'DESC')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult();
    }

    /**
     * Dernières émissions par thème (1 par thème).
     */
    public function lastEmissionsByTheme(string $excludeUrl): array
    {
        $sql = '
        WITH RankedEmissions AS (
            SELECT 
                e.id AS emission_id,
                e.titre AS emission_titre,
                e.datepub AS emission_datepub,
                e.duree AS emission_duree,
                e.url AS emission_url,
                e.descriptif AS emission_descriptif,
                e.thumbnail AS emission_thumbnail,
                e.categorie_id AS emission_categorie_id,
                e.theme_id AS emission_theme_id,

                c.id AS categorie_id,
                c.titre AS categorie_titre,
                c.editeur AS categorie_editeur,
                c.duree AS categorie_duree,
                c.descriptif AS categorie_descriptif,
                c.thumbnail AS categorie_thumbnail,
                c.active AS categorie_active,

                t.id AS theme_id,
                t.name AS theme_name,
                t.thumbnail AS theme_thumbnail,

                ROW_NUMBER() OVER (PARTITION BY e.theme_id ORDER BY e.datepub DESC) as rn
            FROM emission e
            LEFT JOIN categories c ON e.categorie_id = c.id
            LEFT JOIN theme t ON e.theme_id = t.id
            WHERE e.url != :val AND e.theme_id != 0
        )
        SELECT * FROM RankedEmissions 
        WHERE rn = 1
        ORDER BY emission_datepub DESC
        LIMIT 6';

    $conn = $this->getEntityManager()->getConnection();
    $stmt = $conn->prepare($sql);
    $stmt->bindValue('val', $excludeUrl);

    return $stmt->executeQuery()->fetchAllAssociative();
    }

    /**
     * Recherche avancée avec filtres multiples.
     */
    public function findBySearch(array $criteria, int $page = 1): PaginationInterface
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.categorie', 'c')
            ->leftJoin('e.theme', 't')
            ->andWhere('e.url IS NOT NULL AND e.url != :emptyUrl')
            ->setParameter('emptyUrl', '');

        if (!empty($criteria['titre'])) {
            $titre = $criteria['titre'] ?? '';
            $search = '%' . strtolower((string) $titre) . '%';
            $qb->andWhere('LOWER(e.titre) LIKE :search OR LOWER(e.descriptif) LIKE :search')
                ->setParameter('search', $search);
        }

        if (!empty($criteria['categorie']) && $criteria['categorie'] instanceof Categories) {
            $qb->andWhere('LOWER(c.titre) = :categorie')
            ->setParameter('categorie', strtolower((string) ($criteria['categorie']?->getTitre() ?? '')));

        }

        if (!empty($criteria['theme'])) {
            $qb->andWhere('t.id = :themeId')
                ->setParameter('themeId', $criteria['theme']->getId());
        }

        if (!empty($criteria['dateDebut'])) {
            $qb->andWhere('e.datepub >= :dateDebut')
                ->setParameter('dateDebut', $criteria['dateDebut']);
        }

        if (!empty($criteria['dateFin'])) {
            $qb->andWhere('e.datepub <= :dateFin')
                ->setParameter('dateFin', $criteria['dateFin']);
        }

        return $this->paginator->paginate($qb->orderBy('e.datepub', 'DESC'), $page, 12, [
            'distinct' => true,
            'sortFieldAllowList' => ['e.titre', 'e.datepub'],
        ]);
    }

    /**
     * Emissions avec une durée inférieure à une valeur donnée.
     *
     * @param int $duree
     * @return Emission[]
     */
    public function findWithDureeLowerThan(int $duree): array
    {
        return $this->createQueryBuilder('e')
            ->select('e', 'c')
            ->leftJoin('e.categorie', 'c')
            ->where('e.duree < :duree')
            ->setParameter('duree', $duree)
            ->orderBy('e.duree', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Exemple de méthode personnalisée simple.
     */
    public function findByExampleField(string $value): array
    {
        return $this->createQueryBuilder('e')
            ->select('e', 'c')
            ->leftJoin('e.categorie', 'c')
            ->andWhere('e.url != :value')
            ->setParameter('value', $value)
            ->orderBy('e.datepub', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
