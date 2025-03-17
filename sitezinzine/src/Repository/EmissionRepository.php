<?php

namespace App\Repository;

use App\Entity\Theme;
use App\Entity\Emission;
use App\Entity\Categories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;


/**
 * @extends ServiceEntityRepository<Emission>
 */
class EmissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Emission::class);
    }

    public function paginateEmissions(int $page, $value): PaginationInterface
    {

        return $this->paginator->paginate(

            $this->createQueryBuilder('r')
                ->select('r', 'c')
                ->leftJoin('r.categorie', 'c')
                ->andWhere('r.url != :val')
                ->setParameter('val', $value)
                ->orderBy('r.datepub', 'DESC'),
            $page,
            20,
            [
                'distinct' => true,
                'sortFieldAllowList' => ['r.titre']
            ]
        );
    }
    //    /**
    //     * @return Emission[] Returns an array of Emission objects
    //     */
    public function findByExampleField($value): array
    {
        return $this->createQueryBuilder('e')
            ->select('e', 'c')
            ->andWhere('e.url != :val')
            ->setParameter('val', $value)
            ->orderBy('e.datepub', 'DESC')
            ->leftJoin('e.categorie', 'c')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param integer $duree
     * @return Emission[]
     */
    public function findWithDureeLowerThan(int $duree): array
    {
        return $this->createQueryBuilder('r')
            ->select('r', 'c')
            ->where('r.duree < :duree')
            ->orderBy('r.duree', 'ASC')
            ->leftJoin('r.categorie', 'c')
            ->setParameter('duree', $duree)
            ->getQuery()
            ->getResult();
    }
    //    public function findOneBySomeField($value): ?Emission
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function lastEmissions($value): array
    {
        return $this->createQueryBuilder('r')
            ->select('r', 'c')
            ->andWhere('r.url != :val')
            ->setParameter('val', $value)
            ->orderBy('r.datepub', 'DESC')
            ->leftJoin('r.categorie', 'c')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult();
    }

    public function lastEmissionsByTheme($value): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = 'SELECT *
                FROM (
                SELECT e.id AS emission_id,
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
                    t.thumbnail AS theme_thumbnail
                FROM emission e
                LEFT JOIN categories c ON e.categorie_id = c.id
                LEFT JOIN theme t ON e.theme_id = t.id
                WHERE e.url != :val
                AND e.theme_id != 0
                ORDER BY e.theme_id, e.datepub DESC
                ) subquery
                    GROUP BY emission_theme_id
                    ORDER BY emission_theme_id DESC
                    LIMIT 6';

        $stmt = $connection->prepare($sql);
        $stmt->bindValue('val', $value);

        $result = $stmt->executeQuery()->fetchAllAssociative();
        return $result;
    }

     /**
     * Recherche des émissions en fonction des critères.
     *
     * @param string|null $titre
     * @param \DateTime|null $dateDiffusion
     * @return Emission[]
     */
    public function findBySearch(array $criteria, int $page = 1): PaginationInterface
    {
        
        $qb = $this->createQueryBuilder('e')
        ->leftJoin('e.categorie', 'c')
        ->leftJoin('e.theme', 't')
        ->andWhere('e.url IS NOT NULL AND e.url != :emptyUrl') // Ajout de cette condition
        ->setParameter('emptyUrl', ''); // Pour exclure aussi les URLs vides


 // Recherche par mot-clé (titre ou descriptif) - utilise toujours le même paramètre
 if (!empty($criteria['titre'])) {
     $qb->andWhere('LOWER(e.titre) LIKE LOWER(:search) OR LOWER(e.descriptif) LIKE LOWER(:search)')
        ->setParameter('search', '%' . strtolower($criteria['titre']) . '%');
 }

 // Filtre par catégorie si présente
 if (!empty($criteria['categorie']) && $criteria['categorie'] instanceof Categories) {
     $qb->andWhere('LOWER(c.titre) = LOWER(:categorie)')
        ->setParameter('categorie', trim(strtolower($criteria['categorie']->getTitre())));
 }

 // Filtre par thème
 if (!empty($criteria['theme'])) {
    $qb->andWhere('t.id = :theme_id')
       ->setParameter('theme_id', $criteria['theme']->getId());
}

 // Recherche par plage de dates
 if (!empty($criteria['dateDebut'])) {
     $qb->andWhere('e.datepub >= :dateDebut')
        ->setParameter('dateDebut', $criteria['dateDebut']);
 }

 if (!empty($criteria['dateFin'])) {
     $qb->andWhere('e.datepub <= :dateFin')
        ->setParameter('dateFin', $criteria['dateFin']);
 }

// Retourner le résultat paginé
return $this->paginator->paginate(
    $qb->orderBy('e.datepub', 'DESC'),
    $page,
    12, // Nombre d'éléments par page
    [
        'distinct' => true,
        'sortFieldAllowList' => ['e.titre', 'e.datepub']
    ]
);
    }

}
