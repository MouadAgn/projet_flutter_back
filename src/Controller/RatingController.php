<?php

namespace App\Controller;

use App\Entity\Leisure;
use App\Entity\Rating;
use App\Repository\LeisureRepository;
use App\Repository\RatingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RatingController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private LeisureRepository $leisureRepository;
    private RatingRepository $ratingRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        LeisureRepository $leisureRepository,
        RatingRepository $ratingRepository
    ) {
        $this->entityManager = $entityManager;
        $this->leisureRepository = $leisureRepository;
        $this->ratingRepository = $ratingRepository;
    }

    
    /**
 * @Route("/api/rate-leisure", name="rateLeisure", methods={"POST"})
 */

 public function rateLeisure(Request $request): JsonResponse
 {
     $data = json_decode($request->getContent(), true);
     
     if (!is_array($data)) {
         return new JsonResponse(['message' => 'Invalid input. Expected an array of ratings.'], Response::HTTP_BAD_REQUEST);
     }
 
     $results = [];
     foreach ($data as $rating) {
         if (empty($rating['leisure_id']) || !isset($rating['score']) || !is_numeric($rating['score']) || $rating['score'] < 1 || $rating['score'] > 10) {
             $results[] = [
                 'leisure_id' => $rating['leisure_id'] ?? null,
                 'status' => 'error',
                 'message' => 'Invalid input. Please provide a valid leisure_id and a score between 1 and 10.'
             ];
             continue;
         }
 
         $leisure = $this->leisureRepository->find($rating['leisure_id']);
 
         if (!$leisure) {
             $results[] = [
                 'leisure_id' => $rating['leisure_id'],
                 'status' => 'error',
                 'message' => 'Leisure not found'
             ];
             continue;
         }
 
         try {
             $ratingEntity = new Rating();
             $ratingEntity->setLeisure($leisure);
             $ratingEntity->setScore((int)$rating['score']);
 
             $this->entityManager->persist($ratingEntity);
             $this->entityManager->flush();
 
             $results[] = [
                 'leisure_id' => $rating['leisure_id'],
                 'status' => 'success',
                 'message' => 'Rating added successfully'
             ];
         } catch (\Exception $e) {
             $results[] = [
                 'leisure_id' => $rating['leisure_id'],
                 'status' => 'error',
                 'message' => 'An error occurred while adding the rating: ' . $e->getMessage()
             ];
         }
     }
 
     $this->entityManager->flush();
 
     return new JsonResponse($results, Response::HTTP_OK);
 }


      /**
 /**
 * @Route("/api/top-rated-leisures", name="topRatedLeisures", methods={"GET"})
 */
public function topRatedLeisures(): JsonResponse
{
    try {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('l.id', 'l.name', 'c.id as categoryId', 'c.name as categoryName', 'AVG(r.score) as averageRating', 'COUNT(r.id) as ratingCount')
            ->from(Leisure::class, 'l')
            ->leftJoin('l.ratings', 'r')
            ->leftJoin('l.category', 'c') // Joindre la catégorie liée
            ->groupBy('l.id', 'c.id') // Grouper par l'id du loisir et de la catégorie
            ->orderBy('averageRating', 'DESC')
            ->having('averageRating > 5') // Filtrer par averageRating > 5
            ->setMaxResults(5);

        $result = $qb->getQuery()->getResult();

        $formattedResult = array_map(function ($item) {
            return [
                'id' => $item['id'],
                'name' => $item['name'],
                'categoryId' => $item['categoryId'],
                'categoryName' => $item['categoryName'],
                'averageRating' => round($item['averageRating'], 2),
            ];
        }, $result);

        return new JsonResponse($formattedResult);
    } catch (\Exception $e) {
        return new JsonResponse(
            ['message' => 'Une erreur est survenue lors de la récupération des loisirs les mieux notés : ' . $e->getMessage()],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}

}