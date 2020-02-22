<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    function findByParametersPagine($page, $nbMaxParPage = 20, $parameters = []){

        if(!is_numeric($page)){
            throw new InvalidArgumentException('La valeur de l\'argument $page est incorrecte (valeur : ' . $page . ').');
        }
        if($page < 1){
            throw new InvalidArgumentException('La page demandé n\'existe pas.');
        }
        if(!is_numeric($nbMaxParPage) || $nbMaxParPage < 1){
            throw new InvalidArgumentException('La valeur de l\'argument $nbMaxParPage est incorrecte (valeur : ' . $nbMaxParPage . ').');
        }
        
        $qb = $this->createQueryBuilder('a');
 
        if(isset($parameters['libelle'])){
            $qb->andWhere('UPPER(a.libelle) LIKE :str')
                ->setParameter('str', '%' . strtoupper($parameters['libelle']) . '%');
        }
        if(isset($parameters['description'])){
            $qb->andWhere('UPPER(a.description) LIKE :desc')
                ->setParameter('desc', '%' . strtoupper($parameters['description']) . '%');
        }
        if(isset($parameters['critere_tri'])){
            $triOrdre = 'ASC';
            if(isset($parameters['tri_ordre'])){
                $triOrdre = strtoupper($parameters['tri_ordre']);
            }
            $qb->orderBy('a.' . $parameters['critereTri'], $triOrdre);
        }
        if(isset($parameters['prix_entre'])){
            $prix = explode("_",$parameters['prix_entre']);
            $qb->andWhere('a.prix_u BETWEEN :prix1 AND :prix2')
                ->setParameter('prix1', $prix[0])
                ->setParameter('prix2', $prix[1]);
        }
        if(isset($parameters['section'])){
            $qb->select('a')
            ->leftJoin('a.sections', 's')
            ->addSelect('s')
            ->andWhere('UPPER(s.libelle) = :slib')
            ->setParameter('slib', strtoupper($parameters['section']));
        }
        if(isset($parameters['type_article'])){
            $qb->select('a')
            ->leftJoin('a.type_article', 't')
            ->addSelect('t')
            ->andWhere('UPPER(t.libelle) = :tlib')
            ->setParameter('tlib', strtoupper($parameters['type_article']));
        }
        if(isset($parameters['categorie'])){
            if(!in_array('t', $qb->getAllAliases())){
                $qb->select('a')
                ->leftJoin('a.type_article', 't');
            }
        $qb->addSelect('t')
            ->leftJoin('t.categorie', 'c')
            ->addSelect('c')
            ->andWhere('UPPER(c.libelle) = :clib')
            ->setParameter('clib', strtoupper($parameters['categorie']));
        }
        if(isset($parameters['taille'])){
            $qb->addSelect('a')
            ->leftJoin('a.quantite_tailles', 'q')
            ->addSelect('q')
            ->leftJoin('q.taille', 'ta')
            ->andWhere('UPPER(ta.libelle) LIKE :taille')
            ->setParameter('taille',  strtoupper($parameters['taille']));
        }

        $debut = ($page -1) * $nbMaxParPage;
        $query = $qb->getQuery();
        $query->setFirstResult($debut)
            ->setMaxResults($nbMaxParPage);

        $paginator = new Paginator($query);

        if($paginator->count() <= $debut && $page != 1){
            throw new NotFoundHttpException('La page demandée n\'existe pas.');
        }

        return $paginator;

    }

    function findByParameters($parameters){
        $qb = $this->createQueryBuilder('a');

        if(isset($parameters['libelle'])){
            $qb->andWhere('UPPER(a.libelle) LIKE :str')
                ->setParameter('str', '%' . strtoupper($parameters['libelle']) . '%');
        }
        if(isset($parameters['description'])){
            $qb->andWhere('UPPER(a.description) LIKE :desc')
                ->setParameter('desc', '%' . strtoupper($parameters['description']) . '%');
        }
        if(isset($parameters['critere_tri'])){
            $triOrdre = 'ASC';
            if(isset($parameters['tri_ordre'])){
                $triOrdre = strtoupper($parameters['tri_ordre']);
            }
            $qb->orderBy('a.' . $parameters['critereTri'], $triOrdre);
        }
        if(isset($parameters['prix_entre'])){
            $prix = explode("_",$parameters['prix_entre']);
            $qb->andWhere('a.prix_u BETWEEN :prix1 AND :prix2')
                ->setParameter('prix1', $prix[0])
                ->setParameter('prix2', $prix[1]);
        }
        if(isset($parameters['section'])){
            $qb->select('a')
            ->leftJoin('a.sections', 's')
            ->addSelect('s')
            ->andWhere('UPPER(s.libelle) = :slib')
            ->setParameter('slib', strtoupper($parameters['section']));
        }
        if(isset($parameters['type_article'])){
            $qb->select('a')
            ->leftJoin('a.type_article', 't')
            ->addSelect('t')
            ->andWhere('UPPER(t.libelle) = :tlib')
            ->setParameter('tlib', strtoupper($parameters['type_article']));
        }
        if(isset($parameters['categorie'])){
            if(!in_array('t', $qb->getAllAliases())){
                $qb->select('a')
                ->leftJoin('a.type_article', 't');
            }
        $qb->addSelect('t')
            ->leftJoin('t.categorie', 'c')
            ->addSelect('c')
            ->andWhere('UPPER(c.libelle) = :clib')
            ->setParameter('clib', strtoupper($parameters['categorie']));
        }
        if(isset($parameters['taille'])){
            $qb->addSelect('a')
            ->leftJoin('a.quantite_tailles', 'q')
            ->addSelect('q')
            ->leftJoin('q.taille', 'ta')
            ->andWhere('UPPER(ta.libelle) LIKE :taille')
            ->setParameter('taille',  strtoupper($parameters['taille']));
        }

        return $qb->getQuery()
                ->getResult();
    }

// http://127.0.0.1:8000/article?libelle=jupe&section=homme&critere_tri=prix_u&tri_ordre=DESC&taille=L&type_article=jupe&categorie=vetement&prix_entre=20_30&description=pull

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
