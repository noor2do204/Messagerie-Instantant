<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Friendship;
use App\Repository\UserRepository;
use App\Repository\FriendshipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class FriendshipController extends AbstractController
{
    #[Route('/friends', name: 'friends')]
    public function viewFriends(UserInterface $currentUser, FriendshipRepository $friendshipRepository, UserRepository $userRepository): Response
    {
        $currentUserFriends = $friendshipRepository->findFriendsOfUser($currentUser);
        $pendingRequests = $friendshipRepository->findPendingRequests($currentUser);
        $allUsers = $userRepository->findAll();

        // Filtrer pour obtenir les utilisateurs qui ne sont pas encore amis
        $usersNotFriends = array_filter($allUsers, function($user) use ($currentUser, $currentUserFriends) {
            if ($user === $currentUser) {
                return false; // Exclure l'utilisateur actuel
            }
            foreach ($currentUserFriends as $friendship) {
                if ($friendship->getUserOne() === $user || $friendship->getUserTwo() === $user) {
                    return false; // L'utilisateur est déjà ami
                }
            }
            return true; // L'utilisateur n'est pas encore ami
        });

        return $this->render('/friend/friends.html.twig', [
            'currentUserFriends' => $currentUserFriends,
            'pendingRequests' => $pendingRequests,
            'users' => $usersNotFriends,
        ]);
    }

    #[Route('/add-friend/{id}', name: 'add_friend')]
    public function addFriend(User $user, EntityManagerInterface $entityManager, UserInterface $currentUser): Response
    {
        if ($currentUser === $user) {
            // Gérer cette situation, peut-être retourner un message d'erreur
        }

        $friendship = new Friendship();
        $friendship->setUserOne($currentUser);
        $friendship->setUserTwo($user);
        $friendship->setStatus('pending'); // ou un autre statut initial selon vos besoins

        $entityManager->persist($friendship);
        $entityManager->flush();

        return $this->redirectToRoute('friends');
    }

    #[Route('/accept-request/{id}', name: 'accept_request')]
    public function acceptRequest(Friendship $friendship, EntityManagerInterface $entityManager): Response
    {

        $friendship->setStatus('accepted');
        $entityManager->flush();

        return $this->redirectToRoute('friends');
    }

    #[Route('/reject-request/{id}', name: 'reject_request')]
    public function rejectRequest(Friendship $friendship, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($friendship);
        $entityManager->flush();

        return $this->redirectToRoute('friends');
    }

    #[Route('/remove-friend/{id}', name: 'remove_friend')]
    public function removeFriend(User $user, EntityManagerInterface $entityManager, UserInterface $currentUser): Response
    {
        $friendship = $entityManager->getRepository(Friendship::class)->findOneBy([
            'userOne' => $currentUser,
            'userTwo' => $user,
            // 'status' => 'accepted' // selon la logique de votre application
        ]);

        if (!$friendship) {
            // Gérer l'absence de relation d'amitié
        }

        $entityManager->remove($friendship);
        $entityManager->flush();

        return $this->redirectToRoute('some_route');
    }
    #[Route('/search-users', name: 'search_users')]
    public function searchUsers(Request $request, UserRepository $userRepository): JsonResponse
    {
        $query = $request->query->get('query', '');
        $users = $userRepository->findBySearchQuery($query);

        return $this->json([
            'users' => $users // Assurez-vous de transformer les utilisateurs en un format approprié pour le JSON
        ]);
    }

}
