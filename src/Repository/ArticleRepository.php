<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    function findByParameters($parameters){
        $qb = $this->createQueryBuilder('a');

        if(isset($parameters['libelle'])){
            $qb->andWhere('UPPER(a.libelle) LIKE :str')
                ->setParameter('str', '%' . strtoupper($parameters['libelle']) . '%');
        }
        if(isset($parameters['critereTri'])){
            $triOrdre = 'ASC';
            if(isset($parameters['triOrdre'])){
                $triOrdre = strtoupper($parameters['triOrdre']);
            }
            $qb->orderBy('a.' . $parameters['critereTri'], $triOrdre);
        }
        if(isset($parameters['section'])){
            $qb->select('a')
            ->leftJoin('a.sections', 's')
            ->addSelect('s')
            ->andWhere('UPPER(s.libelle) = :slib')
            ->setParameter('slib', strtoupper($parameters['section']));
        }

        return $qb->getQuery()
                ->getResult();
    }

// http://127.0.0.1:8000/article?libelle=jupe&section=homme&critereTri=prix_u&triOrdre=DESC

    function findBySection($section){
        $qb = $this->createQueryBuilder('as');
        return $this->createQueryBuilder('a')
            ->select('a')
            ->leftJoin('a.sections', 's')
            ->addSelect('s')
            ->add('where', $qb->expr()->in('s', ':s') )
            ->setParameter('s', $section)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Article[] Returns an array of Article objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
