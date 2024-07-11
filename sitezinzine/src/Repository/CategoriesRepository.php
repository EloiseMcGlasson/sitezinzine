<?php

namespace App\Repository;

use App\Entity\Categories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Categories>
 */
class CategoriesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Categories::class);
    }

    public function paginateCategoriesWithCount(int $page, $value): PaginationInterface
    {
        
        return $this->paginator->paginate(
            
            $this->createQueryBuilder('c')
            ->select('NEW App\\DTO\\CategoriesWithCountDTO(c.id, c.titre, c.descriptif, COUNT(c.id))')
            ->leftJoin('c.emissions', 'r')
            ->groupBy('c.id')
            ->getQuery()
            ->getResult(),
            $page,
            15
            
        );
    }

public function findAllAsc(): array
        {
            return $this->createQueryBuilder('c')
                
                ->orderBy('c.titre', 'ASC')
               
                ->getQuery()
                ->getResult()
            ;
        }

        /**
         * Undocumented function
         *
         * @return CategoriesWithCountDTO[]
         */
        public function findAllWithCount(): array
        {
            return $this->createQueryBuilder('c')
            ->select('NEW App\\DTO\\CategoriesWithCountDTO(c.id, c.titre, c.descriptif, COUNT(c.id))')
            ->leftJoin('c.emissions', 'r')
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();
        }
                


    //    /**
    //     * @return Categories[] Returns an array of Categories objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Categories
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
