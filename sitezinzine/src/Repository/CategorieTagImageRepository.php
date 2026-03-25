<?php

namespace App\Repository;

use App\Entity\CategorieTagImage;
use App\Entity\Categories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CategorieTagImage>
 */
class CategorieTagImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategorieTagImage::class);
    }

    public function findOneByCategorieAndYear(Categories $categorie, int $annee): ?CategorieTagImage
    {
        return $this->createQueryBuilder('cti')
            ->andWhere('cti.categorie = :categorie')
            ->andWhere('cti.annee = :annee')
            ->setParameter('categorie', $categorie)
            ->setParameter('annee', $annee)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return CategorieTagImage[]
     */
    public function findByCategorieOrderedByYearDesc(Categories $categorie): array
    {
        return $this->createQueryBuilder('cti')
            ->andWhere('cti.categorie = :categorie')
            ->setParameter('categorie', $categorie)
            ->orderBy('cti.annee', 'DESC')
            ->addOrderBy('cti.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return CategorieTagImage[]
     */
    public function findAllOrdered(): array
{
    return $this->createQueryBuilder('cti')
        ->leftJoin('cti.categorie', 'c')
        ->addSelect('c')
        ->orderBy('c.titre', 'ASC')     // 🔥 tri alpha catégorie
        ->addOrderBy('cti.annee', 'DESC') // optionnel mais logique
        ->getQuery()
        ->getResult();
}
}
