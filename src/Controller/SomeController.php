<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Mercure\HubInterface;


class MessageController extends AbstractController
{
    public function newMessage(HubInterface $hub): Response
    {   
        $update = new Update(
        'http://localhost/messages',
        json_encode(['message' => 'Hello, Mercure!'])
    );

    $hub->publish($update);

        return $this->render('message/new_message.html.twig', [
            'message' => 'Hello, Mercure!'
        ]);
    }
}