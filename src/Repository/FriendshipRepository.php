<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Friendship;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class FriendshipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Friendship::class);
    }

    /**
     * Trouver les amis d'un utilisateur donné.
     *
     * @param User $user
     * @return array
     */
    public function findFriendsOfUser(User $user): array
    {
        // Ici, vous devez écrire la logique pour récupérer les amis de l'utilisateur
        // Cette requête est un exemple et peut nécessiter une adaptation
        $qb = $this->createQueryBuilder('f');
        $qb->where('f.userOne = :user OR f.userTwo = :user')
           ->setParameter('user', $user)
           ->andWhere('f.status = :status') // Si vous avez un statut pour les amitiés
           ->setParameter('status', 'accepted'); // Exemple de statut

        return $qb->getQuery()->getResult();
    }

    public function findPendingRequests(UserInterface $currentUser): array
{
    return $this->createQueryBuilder('f')
        ->where('f.userTwo = :user AND f.status = :status')
        ->setParameters([
            'user' => $currentUser,
            'status' => 'pending' // Assurez-vous que c'est le bon statut pour les demandes en attente
        ])
        ->getQuery()
        ->getResult();
}

}

