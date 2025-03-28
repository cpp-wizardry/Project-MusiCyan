<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Form\ArtistType;
use App\Repository\ArtistRepository;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
class ArtistController extends AbstractController
{
    public function getArtists(ArtistRepository $artistRepository): JsonResponse
    {
        $artists = $artistRepository->findAll();

        $data = array_map(fn($artist) => [
            "id" => $artist->getId(),
            "name" => $artist->getName(),
            "desc" => $artist->getDesc(),
            "image" => $artist->getImage()
        ], $artists);

        if (!$data) {
            return $this->json(['message' => 'Aucun artiste trouvé'], 404);
        }

        return $this->json($data);
    }

    public function getArtist(int $id, ArtistRepository $artistRepository): JsonResponse
    {
        $artist = $artistRepository->find($id);

        if (!$artist) {
            return $this->json(['message' => 'Artiste non trouvé'], 404);
        }

        $data = [
            "id" => $artist->getId(),
            "name" => $artist->getName(),
            "desc" => $artist->getDesc(),
            "image" => $artist->getImage()
        ];

        return $this->json($data);
    }

    #[Route('/artists', name: 'app_artists')]
    public function index(ArtistRepository $artistRepository, Request $request): Response
    {
        $nameArtist = $request->query->get('nameArtist', '');
        if ($nameArtist) {
            $artists = $artistRepository->searchByName($nameArtist);
        } else {
            $artists = $artistRepository->findBy([], ['id' => 'DESC']);
        }

        return $this->render('artist/index.html.twig', [
            'artists' => $artists,
            "searchTermArtist" => $nameArtist,
        ]);
    }

    #[Route('/artists/create', name: 'app_artists_create')]
    public function createArtist(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $artist = new Artist();
        $form = $this->createForm(ArtistType::class, $artist);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile instanceof UploadedFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('artist_images_directory'),
                    $newFilename
                );
                $artist->setImage($newFilename);
            }

            $entityManager->persist($artist);
            $entityManager->flush();

            return $this->redirectToRoute('app_artists_success');
        }

        return $this->render('artist/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/artists/{id}/edit', name: 'app_artists_edit')]
    public function editArtist(int $id, Request $request, ArtistRepository $artistRepository, EntityManagerInterface $entityManager): Response
    {
        $artist = $artistRepository->find($id);

        if (!$artist) {
            throw $this->createNotFoundException('Artist not found');
        }

        $originalImage = $artist->getImage();
        $form = $this->createForm(ArtistType::class, $artist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                if ($originalImage) {
                    $filesystem = new Filesystem();
                    $imagePath = $this->getParameter('artist_images_directory') . '/' . $originalImage;

                    if ($filesystem->exists($imagePath)) {
                        $filesystem->remove($imagePath);
                    }
                }

                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('artist_images_directory'),
                    $newFilename
                );

                $artist->setImage($newFilename);
            } else {
                $artist->setImage($originalImage);
            }

            $entityManager->persist($artist);
            $entityManager->flush();

            return $this->redirectToRoute('app_artists_show', ['id' => $artist->getId()]);
        }

        return $this->render('artist/edit.html.twig', [
            'form' => $form->createView(),
            'artist' => $artist,
        ]);
    }


    #[Route('/artists/success', name: 'app_artists_success')]
    public function success(): Response
    {
        return $this->render('artist/success.html.twig');
    }

    #[Route('/artists/{id}', name: 'app_artists_show', methods: ['GET'])]
    public function showArtist(int $id, ArtistRepository $artistRepository, EventRepository $eventRepository): Response
    {
        $artist = $artistRepository->find($id);

        if (!$artist) {
            throw $this->createNotFoundException('Artiste non trouvé.');
        }

        $events = $eventRepository->findByArtist($id);

        return $this->render('artist/show.html.twig', [
            'artist' => $artist,
            'events' => $events
        ]);
    }

}