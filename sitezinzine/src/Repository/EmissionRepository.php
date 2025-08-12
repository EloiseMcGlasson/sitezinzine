<?php

namespace App\Repository;

use App\Entity\Emission;
use App\Entity\Categories;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\ORM\Query;


/**
 * @extends ServiceEntityRepository<Emission>
 * This repository is used to manage Emission entities.
 * It provides methods to paginate emissions, filter by user, and perform advanced searches.
 * It also includes methods for grouping emissions by themes and categories.
 * The repository uses the PaginatorInterface for pagination and supports complex queries with joins.
 * It is designed to work with the Emission entity and its related entities such as Categories and User.
 * The repository methods are used in various parts of the application, including the admin interface and public
 */
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
     * Cette méthode est utilisée pour afficher les émissions dans l'espace admin.
     * Elle exclut les émissions sans diffusion et celles dont l'URL correspond à excludeUrl
     * ainsi que celles dont la catégorie a un ID de 0.
     * @param int $page Le numéro de la page à paginer.
     * @param string $excludeUrl L'URL à exclure des résultats.
     * @param User|null $user L'utilisateur pour lequel on filtre les émissions (null pour toutes les émissions).
     * @param bool $isAdmin Indique si l'utilisateur est un administrateur (true pour toutes les émissions, false pour celles de l'utilisateur).
     * @return PaginationInterface La pagination des émissions.
     */
public function paginateEmissionsAdmin(int $page, string $excludeUrl, ?User $user = null, bool $isAdmin = false)
{
    $qb = $this->createQueryBuilder('e')
        ->select('e', 'c', '(SELECT MAX(d2.horaireDiffusion) FROM App\Entity\Diffusion d2 WHERE d2.emission = e) AS HIDDEN lastDiffusion')
        ->leftJoin('e.categorie', 'c')
        ->andWhere('(e.url != :excludeUrl) OR (e.url = :excludeUrl AND e.user IS NOT NULL)') 
        ->andWhere('c.id != 0')
        ->setParameter('excludeUrl', $excludeUrl)
        ->orderBy('lastDiffusion', 'DESC');

    if ($user && !$isAdmin) {
        $qb->andWhere('e.user = :user')
           ->setParameter('user', $user);
    }

    /** @var PaginationInterface $pagination */
    $pagination = $this->paginator->paginate($qb, $page, 20, [
        'distinct' => true,
        'sortFieldAllowList' => ['e.titre'],
    ]);

    // Hydrate manuellement la propriété lastDiffusion
    foreach ($pagination as $row) {
        // Pour chaque $row (qui est un Emission)
        $lastDiffusion = $this->createQueryBuilder('e2')
            ->select('MAX(d.horaireDiffusion)')
            ->leftJoin('e2.diffusions', 'd')
            ->andWhere('e2.id = :id')
            ->setParameter('id', $row->getId())
            ->getQuery()
            ->getSingleScalarResult();

        $row->setLastDiffusion($lastDiffusion ? new \DateTime($lastDiffusion) : null);
    }

    return $pagination;
}






    /**
     * Paginate les émissions publiques.
     * Cette méthode est utilisée pour afficher les émissions sur le site public.
     * Elle exclut les émissions sans diffusion et celles dont l'URL correspond à excludeUrl
     * ainsi que celles dont la catégorie a un ID de 0.
     * @param int $page Le numéro de la page à paginer.
     * @param string $excludeUrl L'URL à exclure des résultats.
     */
public function paginateEmissions(int $page, string $excludeUrl): PaginationInterface
{
    $qb = $this->createQueryBuilder('e')
        ->select('e', 'c', 'MAX(d.horaireDiffusion) AS lastDiffusion')
        ->leftJoin('e.categorie', 'c')
        ->innerJoin('e.diffusions', 'd')
        ->andWhere('e.url != :excludeUrl')
        ->andWhere('c.id != 0')
        ->setParameter('excludeUrl', $excludeUrl)
        ->groupBy('e.id')
        ->orderBy('lastDiffusion', 'DESC');

    return $this->paginator->paginate($qb, $page, 20, [
        'distinct' => true,
        'sortFieldAllowList' => ['lastDiffusion'],
    ]);
}






    /**
     * Émissions devant être diffusées dans les prochaines 24h pour le carrousel de la partial onde. à réactiver quand déploiement final
     * Cette méthode est utilisée pour afficher les émissions à venir dans les 24 heures.
     * Elle exclut les émissions dont l'URL correspond à excludeUrl et celles dont la catégorie a un ID de 0.
     * @param string $excludeUrl L'URL à exclure des résultats.
     * @return Emission[] Un tableau d'entités Emission correspondant aux émissions à venir.
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
     * fonction de remplacement pour upcomingEmissions, donne les émissions du jour, sur une date donnée spécifique.
     * @param \DateTimeImmutable $specificDate La date spécifique pour laquelle on veut les émissions.
     * @param string $excludeUrl L'URL à exclure des résultats. 
     * @return Emission[] Un tableau d'entités Emission correspondant aux émissions du jour.
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









/**
     * Retourne les groupes de thèmes prédéfinis.
     * Cette méthode est utilisée pour regrouper les thèmes en catégories logiques.
     * utilisée dans la méthode lastEmissionsByGroupTheme
     *
     * @return array Un tableau associatif où les clés sont les noms des groupes de thèmes et les valeurs sont des tableaux d'IDs de thèmes.
     */

    public function getThemeGroups(): array
{
    return [
        'musique/littérature/ciné...' => [1, 7, 8],
        'histoire/politique' => [2, 9],
        'agriculture/forêt/écologie' => [3, 10, 5],
        'alimentation/santé' => [4, 11],
        'féminisme/société' => [14, 6],
        'international/migrations' => [13, 12],
    ];
}

    /**
     * Recherche les émissions par groupe de thèmes.
     * Cette méthode utilise une requête SQL complexe pour regrouper les émissions par thème et retourner les dernières diffusions de chaque groupe.
     * utilisée dans la page home/show.html.twig
     *
     * @param array $themeIds Les IDs des thèmes à rechercher.
     * @return Emission[] Un tableau d'entités Emission correspondant aux thèmes spécifiés.
     */
public function findEmissionsByThemeGroup(array $themeIds): array
{
    $now = new \DateTimeImmutable();

    $qb = $this->createQueryBuilder('e')
        ->select('e', 'c', 't')
        ->addSelect('
            (SELECT MAX(d1.horaireDiffusion)
             FROM App\Entity\Diffusion d1
             WHERE d1.emission = e.id
               AND d1.horaireDiffusion <= :now
            ) AS lastDiffusion
        ')
        ->addSelect('
            (SELECT MIN(d2.horaireDiffusion)
             FROM App\Entity\Diffusion d2
             WHERE d2.emission = e.id
               AND d2.horaireDiffusion > :now
            ) AS nextDiffusion
        ')
        ->leftJoin('e.categorie', 'c')
        ->leftJoin('e.theme', 't')
        ->where('e.theme IN (:themeIds)')
        ->setParameter('themeIds', $themeIds)
        ->setParameter('now', $now)
        ->orderBy('lastDiffusion', 'DESC');

    $results = $qb->getQuery()->getResult();

    // Injecter les dates dans chaque émission
    foreach ($results as $key => $row) {
        // $row[0] est l’entité Emission
        $emission = is_array($row) ? $row[0] : $row;

        if (is_array($row)) {
            $last = $row['lastDiffusion'] ?? null;
            $next = $row['nextDiffusion'] ?? null;
        } else {
            $last = null;
            $next = null;
        }

        $emission->setLastDiffusion($last ? new \DateTime($last) : null);
        $emission->setNextDiffusion($next ? new \DateTime($next) : null);

        $results[$key] = $emission;
    }

    return $results;
}

public function paginateEmissionsByThemeGroup(array $themeIds, int $page): PaginationInterface
{
    $now = new \DateTimeImmutable();

    // Requête principale : juste les entités
    $qb = $this->createQueryBuilder('e')
        ->leftJoin('e.categorie', 'c')
        ->leftJoin('e.diffusions', 'd')
        ->andWhere('e.theme IN (:themeIds)')
        ->andWhere('e.url IS NOT NULL')
        ->setParameter('themeIds', $themeIds)
        ->groupBy('e.id')
        ->orderBy('e.datepub', 'DESC'); // trie sur un champ existant, ex: datepub

    $pagination = $this->paginator->paginate($qb, $page, 12);

    // Pour chaque émission, on calcule les dates lastDiffusion et nextDiffusion via des méthodes dédiées
    foreach ($pagination as $emission) {
        $lastDiffusion = $this->getLastDiffusion($emission, $now);
        
        $emission->setLastDiffusion($lastDiffusion);  
    }

    return $pagination;
}

public function getLastDiffusion(Emission $emission, \DateTimeInterface $now): ?\DateTimeInterface
{
    $result = $this->getEntityManager()
        ->createQuery('
            SELECT MAX(d.horaireDiffusion)
            FROM App\Entity\Diffusion d
            WHERE d.emission = :emission AND d.horaireDiffusion <= :now
        ')
        ->setParameter('emission', $emission)
        ->setParameter('now', $now)
        ->getSingleScalarResult();

    if ($result === null) {
        return null;
    }

    return new \DateTime($result);
}

public function getNextDiffusion(Emission $emission, \DateTimeInterface $now): ?\DateTimeInterface
{
    $result = $this->getEntityManager()
        ->createQuery('
            SELECT MIN(d.horaireDiffusion)
            FROM App\Entity\Diffusion d
            WHERE d.emission = :emission AND d.horaireDiffusion > :now
        ')
        ->setParameter('emission', $emission)
        ->setParameter('now', $now)
        ->getSingleScalarResult();

    if ($result === null) {
        return null;
    }

    return new \DateTime($result);
}




    /**
     * Dernières émissions par thème (1 par thème).
     * Cette méthode utilise une requête SQL complexe pour regrouper les émissions par thème et retourner la dernière diffusion de chaque groupe.
     * utilisée dans la page partial/lastemissions.html.twig
     * @param string $excludeUrl L'URL à exclure des résultats. permet d'exclure les émissions n'ayant pas de fichier audio.
     * @return array Un tableau associatif contenant les dernières émissions par thème, avec les informations
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


public function findBySearchAdmin(array $criteria, int $page = 1): PaginationInterface
{
    $params = [];

    // Sous-requête pour filtrer par date de diffusion si nécessaire
    if (!empty($criteria['dateDebut']) || !empty($criteria['dateFin'])) {
        $subQb = $this->createQueryBuilder('e2')
            ->select('e2.id')
            ->leftJoin('e2.diffusions', 'd2')
            ->groupBy('e2.id');

        $havingConditions = [];

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
    }

    // Requête principale
    $qb = $this->createQueryBuilder('e')
        ->addSelect('MAX(d.horaireDiffusion) AS HIDDEN lastDiff')
        ->leftJoin('e.diffusions', 'd')
        ->leftJoin('e.categorie', 'c')
        ->leftJoin('e.theme', 't')
        ->andWhere('c.id != 0');

    if (isset($ids)) {
        $qb->andWhere('e.id IN (:ids)')
           ->setParameter('ids', $ids);
    }

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

    $pagination = $this->paginator->paginate(
        $qb->groupBy('e.id')
           ->orderBy('lastDiff', 'DESC'),
        $page,
        12,
        [
            'distinct' => false,
        ]
    );

    // Injecter la date de dernière diffusion dans l’objet Emission
    foreach ($pagination as $item) {
        if (is_array($item) && isset($item[0], $item['lastDiff'])) {
            /** @var \App\Entity\Emission $emission */
            $emission = $item[0];
            $emission->setLastDiffusion(new \DateTime($item['lastDiff']));
        }
    }

    return $pagination;
}

    /**
     * Recherche avancée avec filtres multiples.
     */

public function findBySearch(array $criteria, int $page = 1): PaginationInterface
{
    $params = [];

    // Sous-requête pour filtrer par date de diffusion si nécessaire
    if (!empty($criteria['dateDebut']) || !empty($criteria['dateFin'])) {
        $subQb = $this->createQueryBuilder('e2')
            ->select('e2.id')
            ->leftJoin('e2.diffusions', 'd2')
            ->groupBy('e2.id');

        $havingConditions = [];

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
    }

    // Requête principale
    $qb = $this->createQueryBuilder('e')
        ->addSelect('MAX(d.horaireDiffusion) AS HIDDEN lastDiff')
        ->leftJoin('e.diffusions', 'd')
        ->leftJoin('e.categorie', 'c')
        ->leftJoin('e.theme', 't')
        ->andWhere('e.url IS NOT NULL AND e.url != :emptyUrl')
        ->andWhere('c.id != 0')
        ->setParameter('emptyUrl', '');

    if (isset($ids)) {
        $qb->andWhere('e.id IN (:ids)')
           ->setParameter('ids', $ids);
    }

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

    $pagination = $this->paginator->paginate(
        $qb->groupBy('e.id')
           ->orderBy('lastDiff', 'DESC'),
        $page,
        12,
        [
            'distinct' => false,
        ]
    );

    // Injecter la date de dernière diffusion dans l’objet Emission
    foreach ($pagination as $item) {
        if (is_array($item) && isset($item[0], $item['lastDiff'])) {
            /** @var \App\Entity\Emission $emission */
            $emission = $item[0];
            $emission->setLastDiffusion(new \DateTime($item['lastDiff']));
        }
    }

    return $pagination;
}


public function findLastDiffusionDate(int $emissionId): ?\DateTimeInterface
{
    $lastDateString = $this->createQueryBuilder('e')
        ->select('MAX(d.horaireDiffusion) AS lastDate')
        ->leftJoin('e.diffusions', 'd')
        ->andWhere('e.id = :id')
        ->setParameter('id', $emissionId)
        ->getQuery()
        ->getSingleScalarResult();

    if ($lastDateString === null) {
        return null;
    }

    return new \DateTime($lastDateString);
}

/**
 * Retourne les 20 émissions d'une catégorie triées par dernière diffusion décroissante.
 */
public function findLatestByCategory(int $categoryId, int $limit = 20): array
{
    $now = new \DateTimeImmutable();

    // On sélectionne uniquement l'ID de l'émission et la date de la dernière diffusion
    $rows = $this->createQueryBuilder('e')
        ->select('e.id AS emissionId, MAX(d.horaireDiffusion) AS lastDiffusion')
        ->leftJoin('e.diffusions', 'd', 'WITH', 'd.horaireDiffusion <= :now')
        ->andWhere('e.categorie = :cat')
        ->setParameter('cat', $categoryId)
        ->setParameter('now', $now)
        ->groupBy('e.id')
        ->orderBy('lastDiffusion', 'DESC')
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult(Query::HYDRATE_ARRAY); // hydrate en tableau pour ne plus avoir d'objet à traiter comme un tableau

    // On transforme ces lignes en objets Emission en ajoutant la propriété lastDiffusion
    $emissions = [];
    foreach ($rows as $row) {
        // $row['emissionId'] contient l'ID de l'émission sélectionnée
        $emission = $this->find($row['emissionId']);
        $last     = $row['lastDiffusion'];
        $emission->setLastDiffusion($last ? new \DateTime($last) : null);
        $emissions[] = $emission;
    }

    return $emissions;
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
