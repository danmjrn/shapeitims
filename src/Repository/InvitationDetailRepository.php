<?php

namespace App\Repository;

use App\Entity\InvitationDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InvitationDetail>
 *
 * @method InvitationDetail|null find($id, $lockMode = null, $lockVersion = null)
 * @method InvitationDetail|null findOneBy(array $criteria, array $orderBy = null)
 * @method InvitationDetail[]    findAll()
 * @method InvitationDetail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvitationDetailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvitationDetail::class);
    }

    public function save(InvitationDetail $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(InvitationDetail $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return InvitationDetail[] Returns an array of InvitationDetail objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?InvitationDetail
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * @param string $content
     * @return InvitationDetail|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByContent(string $content): ?InvitationDetail
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.content = :content')
            ->setParameter('content', $content)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param string $INVITATION_DETAIL_WW_TYPE
     * @return InvitationDetail|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findInvitationDetailByType(string $INVITATION_DETAIL_WW_TYPE): ?InvitationDetail
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.type = :type')
            ->setParameter('type', $INVITATION_DETAIL_WW_TYPE)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
