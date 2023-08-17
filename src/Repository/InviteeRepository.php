<?php

namespace App\Repository;

use App\Entity\Invitee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Invitee>
 *
 * @method Invitee|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invitee|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invitee[]    findAll()
 * @method Invitee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InviteeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invitee::class);
    }

    public function save(Invitee $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Invitee $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Invitee[] Returns an array of Invitee objects
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

    /**
     * @param string $username
     * @return Invitee|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findInviteeByUsername( string $username ): ?Invitee
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.username = :username')
            ->setParameter( 'username',$username )
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * @param string $internalUserUuid
     * @param string $username
     * @return Invitee|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findInviteeByInternalUserAndUsername(string $internalUserUuid, string $username ): ?Invitee
    {
        return $this->createQueryBuilder('i')
            ->join('i.internalUser', 'iu')
            ->where('iu.uuid = :internalUserUuid')
            ->andWhere('i.username = :username')
            ->setParameters(
                [
                    'internalUserUuid' => $internalUserUuid,
                    'username' => $username,
                ]
            )
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findInviteesByInvitationAlias( string $invitationAlias ): array
    {
        return $this->createQueryBuilder('i')
            ->join('i.invitationGroup', 'ig')
            ->join('ig.invitation', 'invitation')
            ->where('invitation.alias IN (:invitationAlias)')
            ->setParameters(
                [
                    'invitationAlias' => $invitationAlias,
                ]
            )
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Invitee[] Returns an array of Invitation objects
     */
    public function findInvitationsByInvitationFrom( string $invitationFrom = Invitee::INVITEE_FROM_BOTH ): array
    {
        if( $invitationFrom !== Invitee::INVITEE_FROM_BOTH )
            return $this->createQueryBuilder('i')
                ->andWhere('i.invitationFrom = :invitationFrom')
                ->setParameter( 'invitationFrom', $invitationFrom )
                ->orderBy('i.alias', 'ASC')
                ->getQuery()
                ->getResult()
                ;

        return $this->createQueryBuilder('i')
            ->orderBy('i.alias', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
}
