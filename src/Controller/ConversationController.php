<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Conversation;
use App\Form\ConversationType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ConversationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/conversation')]
class ConversationController extends AbstractController
{
    #[Route('/', name: 'app_conversation_index', methods: ['GET'])]
    public function index(ConversationRepository $conversationRepository): Response
    {
        $user = $this->getUser();
        $conversations = $conversationRepository->findConversationsByUser($user);

        return $this->render('conversation/index.html.twig', [
            'conversations' => $conversations,
        ]);
    }

    #[Route('/new', name: 'app_conversation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response {
        $currentUser = $this->getUser();
        $friends = $userRepository->findFriendsOfUser($currentUser);
        $conversation = new Conversation();
        $conversation->setType('private');
        $form = $this->createForm(ConversationType::class, $conversation, [
            'friends' => $friends,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$conversation->getUsers()->contains($currentUser)) {
                $conversation->addUser($currentUser);
            }

            if (count($conversation->getUsers()) > 2) {
                $conversation->setType('group');
            }

            $names = $conversation->getUsers()->map(function(User $user) {
                return $user->getName();
            })->toArray();
            $conversation->setName('Conversation avec ' . implode(', ', $names));

            $entityManager->persist($conversation);
            $entityManager->flush();

            return $this->redirectToRoute('app_conversation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('conversation/new.html.twig', [
            'conversation' => $conversation,
            'form' => $form,
        ]);
}

    #[Route('/{id}', name: 'app_conversation_show', methods: ['GET'])]
    public function show(Conversation $conversation): Response
    {
        return $this->render('conversation/show.html.twig', [
            'conversation' => $conversation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_conversation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Conversation $conversation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ConversationType::class, $conversation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_conversation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('conversation/edit.html.twig', [
            'conversation' => $conversation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_conversation_delete', methods: ['POST'])]
    public function delete(Request $request, Conversation $conversation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$conversation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($conversation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_conversation_index', [], Response::HTTP_SEE_OTHER);
    }
}
