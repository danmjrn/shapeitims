<?php

namespace App\Repository;

use App\Entity\Invitation;
use App\Entity\Invitee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Invitation>
 *
 * @method Invitation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invitation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invitation[]    findAll()
 * @method Invitation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvitationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invitation::class);
    }

    public function save(Invitation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Invitation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Invitation[] Returns an array of Invitation objects
     */
    public function findInvitationsByRsvp( string $rsvp = Invitation::ANY ): array
    {
        if( $rsvp !== Invitation::ANY )
            return $this->createQueryBuilder('i')
                ->andWhere('i.rsvp = :rsvp')
                ->setParameter( 'rsvp', $rsvp )
                ->orderBy('i.alias', 'ASC')
                ->getQuery()
                ->getResult()
            ;

        return $this->createQueryBuilder('i')
            ->andWhere('i.rsvp is not null')
            ->orderBy('i.alias', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Invitation[] Returns an array of Invitation objects
     */
    public function findInvitationsByInvitationFrom( string $invitationFrom = Invitee::INVITEE_FROM_BOTH ): array
    {
        if( $invitationFrom !== Invitee::INVITEE_FROM_BOTH )
            return $this->createQueryBuilder('i')
                ->join('i.invitationGroups', 'ig')
                ->join('ig.invitee', 'iu')
                ->andWhere('iu.inviteeFrom = :inviteeFrom')
                ->setParameter('inviteeFrom', $invitationFrom)
                ->getQuery()
                ->getResult()
            ;

        return $this->createQueryBuilder('i')
            ->orderBy('i.alias', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param string $uuid
     * @return Invitation|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function findInvitationByUuid( string $uuid ): ?Invitation
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->getQuery()
            ->getSingleResult()
        ;
    }
}
