<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class EventController extends AbstractController
{

    public function getEvents(EventRepository $repo){

        $events = $repo->findAll();

        $data = array_map(fn($event) => [
            "id" => $event->getId(),
            "name" => $event->getName(),
            "date" => $event->getDate(),
            "artist" => $event->getArtist() ? [
                "id" => $event->getArtist()->getId(),
                "name" => $event->getArtist()->getName(),
                "image" => $event->getArtist()->getImage(),
                "desc" => $event->getArtist()->getDesc(),
            ] : null,
            "users" => array_map(fn($user) => $user->getEmail(), $event->getUsers()->toArray())
        ], $events);

        if (empty($data)) {
            return $this->json(['message' => 'Pas de évenements'], 404);

        }
        return $this->json($data, 200);
    }


    public function getEvent(int $id, EventRepository $eventRepository): JsonResponse
    {
        $event = $eventRepository->find($id);

        if (!$event) {
            return $this->json(['message' => 'aucun évenement a cet id'], 404);
        }

        $data = [
            "id" => $event->getId(),
            "name" => $event->getName(),
            "date" => $event->getDate(),
            "artist" => $event->getArtist() ? [
                "id" => $event->getArtist()->getId(),
                "name" => $event->getArtist()->getName(),
                "image" => $event->getArtist()->getImage(),
                "desc" => $event->getArtist()->getDesc(),
            ] : null,
            "users" => array_map(fn($user) => $user->getEmail(), $event->getUsers()->toArray())
        ];

        return $this->json($data);
    }


    #[Route('/events', name: 'app_events')]
    public function index(EventRepository $eventRepository, Request $request): Response
    {
        $dateFilter = $request->query->get('dateEvent');
        if ($dateFilter) {
            $date = \DateTime::createFromFormat('Y-m-d', $dateFilter);
            $events = $eventRepository->findByDate($date);
        } else {
            $events = $eventRepository->findBy([], ['id' => 'DESC']);
        }


        return $this->render('event/index.html.twig', [
            'events' => $events,
            'dateFilter' => $dateFilter
        ]);
    }
    #[Route('/events/create', name: 'app_events_create')]
    public function createEvent(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);

        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $event->addUser($user);

            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('app_events_success');
        }

        return $this->render('event/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}