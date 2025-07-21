<?php

namespace App\Repository;

use App\Entity\Emission;
use App\Entity\Categories;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\ORM\Query\Expr;

use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;


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
        ->select('e', 'c', 'MAX(d.horaireDiffusion) AS lastDiffusion')
        ->leftJoin('e.categorie', 'c')
        ->leftJoin('App\Entity\Diffusion', 'd', 'WITH', 'd.emission = e')
        ->andWhere('e.url != :excludeUrl')
        ->andWhere('c.id != 0')
        ->setParameter('excludeUrl', $excludeUrl)
        ->groupBy('e.id')
        ->orderBy('lastDiffusion', 'DESC');

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
        ->select('e', 'c', 'MAX(d.horaire_diffusion) AS HIDDEN lastDiffusion')
        ->leftJoin('e.categorie', 'c')
        ->leftJoin('App\Entity\Diffusion', 'd', 'WITH', 'd.emission = e')
        ->andWhere('e.url != :excludeUrl')
        ->andWhere('c.id != 0')
        ->andWhere('d.horaire_diffusion IS NOT NULL') // ⛔ Exclut les émissions sans diffusion
        ->setParameter('excludeUrl', $excludeUrl)
        ->groupBy('e.id')
        ->orderBy('lastDiffusion', 'DESC');

    return $this->paginator->paginate($qb, $page, 20, [
        'distinct' => true,
        'sortFieldAllowList' => ['e.titre'],
    ]);
}



    /**
     * Émissions devant être diffusées dans les prochaines 24h pour le carrousel de la partial onde. à réactiver quand déploiement final
     */
   /* public function upcomingEmissions(string $excludeUrl): array
{
    $now = new \DateTimeImmutable();
    $in24Hours = $now->modify('+24 hours');

    return $this->createQueryBuilder('e')
        ->select('e', 'c', 'MIN(d.horaireDiffusion) AS next_diffusion')
        ->leftJoin('e.categorie', 'c')
        ->leftJoin('e.diffusions', 'd')
        ->andWhere('e.url != :excludeUrl')
        ->andWhere('c.id != 0')
        ->andWhere('d.horaireDiffusion BETWEEN :now AND :in24Hours')
        ->groupBy('e.id')
        ->orderBy('next_diffusion', 'ASC')
        ->setParameter('excludeUrl', $excludeUrl)
        ->setParameter('now', $now)
        ->setParameter('in24Hours', $in24Hours)
        ->setMaxResults(6)
        ->getQuery()
        ->getResult();
} */

        /**
     * fonction de remplacement pour les émissions du jour, sur une date donnée spécifique.
     * @param \DateTimeImmutable $specificDate La date spécifique pour laquelle on veut les émissions.
     * @param string $excludeUrl L'URL à exclure des résultats. 
     */
public function findEmissionsByDate(\DateTime $date): array
{
    $start = (clone $date)->setTime(0, 0, 0);
    $end = (clone $date)->setTime(23, 59, 59);

    return $this->createQueryBuilder('e')
        ->addSelect('d', 'c') // jointures pour eager load
        ->join('e.diffusions', 'd')
        ->leftJoin('e.categorie', 'c')
        ->where('d.horaireDiffusion BETWEEN :start AND :end')
        ->orderBy('d.horaireDiffusion', 'ASC')
        ->setParameter('start', $start)
        ->setParameter('end', $end)
        ->getQuery()
        ->getResult();
}











    public function getThemeGroups(): array
{
    return [
        'culture / musique / litterature' => [1, 7, 8],
        'histoire / politique' => [2, 9],
        'agriculture / foret / ecologie' => [3, 10, 5],
        'alimentation / santé' => [4, 11],
        'feminisme / societe' => [14, 6],
        'international / migrations' => [13, 12],
    ];
}

public function findEmissionsByThemeGroup(array $themeIds): array
{
    return $this->createQueryBuilder('e')
        ->leftJoin('e.diffusions', 'd') // Joindre la table des diffusions
        ->andWhere('e.theme IN (:themeIds)')
        ->setParameter('themeIds', $themeIds)

        // Filtrer uniquement par la dernière diffusion
        ->andWhere('d.horaireDiffusion = (
            SELECT MAX(d2.horaireDiffusion)
            FROM App\Entity\Diffusion d2
            WHERE d2.emission = e.id
        )')

        // Trier par date de diffusion
        ->orderBy('d.horaireDiffusion', 'DESC')

        ->getQuery()
        ->getResult();
}



    /**
     * Dernières émissions par thème (1 par thème).
     */
   public function lastEmissionsByGroupTheme(string $excludeUrl): array
{
    $themeGroups = $this->getThemeGroups();

    // Construction dynamique du bloc CASE
    $cases = [];
    foreach ($themeGroups as $label => $ids) {
        $idList = implode(', ', $ids);
        $cases[] = "WHEN e.theme_id IN ($idList) THEN '$label'";
    }
    $caseSql = implode("\n", $cases);

    $sql = "
   WITH GroupedEmissions AS (
    SELECT 
        e.id AS emission_id,
        e.titre AS emission_titre,
        MAX(d.horaire_diffusion) AS last_diffusion,
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

        CASE
            $caseSql
            ELSE 'autre'
        END AS theme_group,

        ROW_NUMBER() OVER (
            PARTITION BY 
                CASE
                    $caseSql
                    ELSE 'autre'
                END
            ORDER BY MAX(d.horaire_diffusion) DESC
        ) AS rn

    FROM emission e
    LEFT JOIN categories c ON e.categorie_id = c.id
    LEFT JOIN theme t ON e.theme_id = t.id
    LEFT JOIN diffusion d ON d.emission_id = e.id
    WHERE e.url != :val 
      AND e.theme_id != 0
      AND d.horaire_diffusion IS NOT NULL
      AND d.horaire_diffusion <= NOW()  -- Condition ajoutée ici pour exclure les émissions à venir
    GROUP BY 
        e.id, e.titre, e.duree, e.url, e.descriptif, e.thumbnail, e.categorie_id, e.theme_id,
        c.id, c.titre, c.editeur, c.duree, c.descriptif, c.thumbnail, c.active,
        t.id, t.name, t.thumbnail
)

SELECT * FROM GroupedEmissions
WHERE rn = 1 AND theme_group != 'autre'
ORDER BY last_diffusion DESC
LIMIT 6

";


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
    $subQb = $this->createQueryBuilder('e2')
        ->select('e2.id')
        ->leftJoin('e2.diffusions', 'd2')
        ->groupBy('e2.id');

    $havingConditions = [];
    $params = [];

    if (!empty($criteria['dateDebut'])) {
        $havingConditions[] = 'MAX(d2.horaireDiffusion) >= :dateDebut';
        $params['dateDebut'] = $criteria['dateDebut'];
    }

    if (!empty($criteria['dateFin'])) {
        $havingConditions[] = 'MAX(d2.horaireDiffusion) <= :dateFin';
        $params['dateFin'] = $criteria['dateFin'];
    }

    if (!empty($havingConditions)) {
        $subQb->having(implode(' AND ', $havingConditions));
    }

    $ids = array_column(
        $subQb->getQuery()
              ->setParameters($params)
              ->getScalarResult(),
        'id'
    );

    if (empty($ids)) {
        return $this->paginator->paginate([], $page, 12);
    }

    $qb = $this->createQueryBuilder('e')
        ->leftJoin('e.categorie', 'c')
        ->leftJoin('e.theme', 't')
        ->andWhere('e.url IS NOT NULL AND e.url != :emptyUrl')
        ->andWhere('c.id != 0')
        ->andWhere('e.id IN (:ids)')
        ->setParameter('ids', $ids)
        ->setParameter('emptyUrl', '');

    if (!empty($criteria['titre'])) {
        $search = '%' . strtolower((string) $criteria['titre']) . '%';
        $qb->andWhere('LOWER(e.titre) LIKE :search OR LOWER(e.descriptif) LIKE :search')
           ->setParameter('search', $search);
    }

    if (!empty($criteria['categorie']) && $criteria['categorie'] instanceof Categories) {
        $qb->andWhere('LOWER(c.titre) = :categorie')
           ->setParameter('categorie', strtolower($criteria['categorie']->getTitre()));
    }

    if (!empty($criteria['theme'])) {
        $qb->andWhere('t.id = :themeId')
           ->setParameter('themeId', $criteria['theme']->getId());
    }

    return $this->paginator->paginate(
        $qb->orderBy('e.titre', 'ASC'),
        $page,
        12,
        [
            'distinct' => true,
            'sortFieldAllowList' => ['e.titre'],
        ]
    );
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

}
