<?php

namespace App\Repository;

use App\Entity\Betrothed;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Betrothed>
 *
 * @method Betrothed|null find($id, $lockMode = null, $lockVersion = null)
 * @method Betrothed|null findOneBy(array $criteria, array $orderBy = null)
 * @method Betrothed[]    findAll()
 * @method Betrothed[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BetrothedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Betrothed::class);
    }

    public function save(Betrothed $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Betrothed $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    /**
     * @throws NonUniqueResultException
     */
    public function findByEmail(string $email): ?Betrothed
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
