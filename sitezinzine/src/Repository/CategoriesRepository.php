<?php

namespace App\Repository;

use App\Entity\Categories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Categories>
 */
class CategoriesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Categories::class);
    }

    public function paginateCategoriesWithCount(int $page, $value, ?UserInterface $user = null): PaginationInterface
{
    $qb = $this->createQueryBuilder('c')
        ->select('c', 'COUNT(r.id) AS total')
        ->leftJoin('c.emissions', 'r')
        ->andWhere('c.softDelete = false')
        ->groupBy('c.id')
        ->orderBy('c.titre', 'ASC');

    if ($user && !in_array('ROLE_ADMIN', $user->getRoles()) && !in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
        $qb->andWhere('c.user = :user')
           ->setParameter('user', $user);
    }

    return $this->paginator->paginate(
        $qb->getQuery(),
        $page,
        15
    );
}

public function findAllAsc(): array
{
    return $this->createQueryBuilder('c')
        ->andWhere('c.softDelete = false') // 👈 ici aussi
        ->orderBy('c.titre', 'ASC')
        ->getQuery()
        ->getResult();
}


        /**
 * @return CategoriesWithCountDTO[]
 */
public function findAllWithCount(): array
{
    return $this->createQueryBuilder('c')
        ->select('NEW App\\DTO\\CategoriesWithCountDTO(c.id, c.titre, c.thumbnail, c.descriptif, COUNT(c.id))')
        ->leftJoin('c.emissions', 'r')
        ->andWhere('c.softDelete = false') // 👈 filtre ajouté
        ->groupBy('c.id')
        ->getQuery()
        ->getResult();
}

/**
 * Retourne les 20 dernières émissions pour une catégorie.
 */
public function findLatestEmissions(int $categoryId, int $limit = 20): array
{
    return $this->createQueryBuilder('c')
        ->join('c.emissions', 'e')
        ->andWhere('c.id = :id')
        ->setParameter('id', $categoryId)
        ->orderBy('e.datepub', 'DESC')
        ->setMaxResults($limit)
        ->select('e')
        ->getQuery()
        ->getResult();
}



}
