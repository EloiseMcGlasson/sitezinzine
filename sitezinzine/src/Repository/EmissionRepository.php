<?php

namespace App\Repository;

use App\Entity\Categories;
use App\Entity\Emission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

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
        /* return new Paginator($this
            ->createQueryBuilder('r')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->select('r', 'c')
            ->andWhere('r.url != :val')
            ->setParameter('val', $value)
            ->orderBy('r.datepub', 'DESC')
            ->leftJoin('r.categorie', 'c')
            ->getQuery()
            ->setHint(Paginator::HINT_ENABLE_DISTINCT, false)
            
        ); */
            
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
        /* return $this->createQueryBuilder('r')
        ->select('r', 'c', 't')
        ->leftJoin('r.categorie', 'c')
        ->leftJoin('r.theme', 't')
        ->addOrderBy('r.datepub', 'DESC')
        ->Where('r.url != :val')
        ->andWhere('r.theme != 0')
        ->setParameter('val', $value)
        ->GroupBy( 'r.theme')
        ->getQuery()
        ->getResult(); */

        $connection = $this->getEntityManager()->getConnection();
$sql = '
    SELECT e.id AS emission_id,
    e.titre AS emission_titre,
    e.keyword AS emission_keyword,
    e.datepub AS emission_datepub,
    e.ref AS emission_ref,
    e.duree AS emission_duree,
    e.url AS emission_url,
    e.descriptif AS emission_descriptif,
    e.thumbnail AS emission_thumbnail,
    e.updatedat AS emission_updatedat,
    e.categorie_id AS emission_categorie_id,
    e.theme_id AS emission_theme_id,
    e.user_id AS emission_user_id,
    e.editeur_id AS emission_editeur_id,
    c.id AS categorie_id,
    c.titre AS categorie_titre,
    c.oldid AS categorie_oldid,
    c.editeur AS categorie_editeur,
    c.duree AS categorie_duree,
    c.descriptif AS categorie_descriptif,
    c.thumbnail AS categorie_thumbnail,
    c.updated_at AS categorie_updated_at,
    c.active AS categorie_active,
    t.id AS theme_id,
    t.name AS theme_name,
    t.thumbnail AS theme_thumbnail,
    t.updated_at AS theme_updated_at
    FROM emission e
    LEFT JOIN categories c ON e.categorie_id = c.id
    LEFT JOIN theme t ON e.theme_id = t.id
    WHERE e.url != :val
      AND e.theme_id != 0
    GROUP BY e.theme_id DESC
    LIMIT 6
';
$stmt = $connection->prepare($sql);
$stmt->bindValue('val', $value);

$result = $stmt->executeQuery()->fetchAllAssociative();
    return $result;

    }
}
