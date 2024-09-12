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
        return $this->createQueryBuilder('r')
        ->select('r', 'c', 't')
        ->orderBy('r.datepub', 'DESC')
        ->leftJoin('r.categorie', 'c')
        ->leftJoin('r.theme', 't')
        ->Where('r.url != :val')
        ->andWhere('r.theme != 0')
        ->setParameter('val', $value)
        ->GroupBy( 'r.theme')
        
        ->getQuery()
        ->getResult();
    }
}
