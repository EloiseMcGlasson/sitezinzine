<?php

namespace App\Repository;


use App\Entity\Emission;
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
    public function findBySearch($criteria): array
    {
        $qb = $this->createQueryBuilder('e');

        if (!empty($criteria['titre'])) {
            $qb->andWhere('e.titre LIKE :titre')
               ->setParameter('titre', '%' . $criteria['titre'] . '%');
        }
    
        if (!empty($criteria['datepub'])) {
            $qb->andWhere('e.datepub = :datepub')
               ->setParameter('datepub', $criteria['datepub']);
        }
    
        return $qb->orderBy('e.datepub', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

}
