<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Leisure;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class LeisureController extends AbstractController
{
    private SerializerInterface $serializer;
    private CategoryRepository $categoryRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(SerializerInterface $serializer, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager)
    {
        $this->serializer = $serializer;
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $entityManager;
    }


   /**
 * @Route("/api/add-leisure", name="addLeisure", methods={"POST"})
 */
 
 public function AddLeisure(Request $request): JsonResponse
 {
     $data = json_decode($request->getContent(), true);
 
     // Validate required fields
     if (empty($data['name']) || empty($data['category_name']) || empty($data['authorOrDirector'])) {
         return new JsonResponse(['message' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
     }
 
     // Find category by name
     $categoryName = $data['category_name'];
     $category = $this->categoryRepository->findOneBy(['name' => $categoryName]);
 
     if (!$category instanceof Category) {
         return new JsonResponse(['message' => 'Category not found'], Response::HTTP_NOT_FOUND);
     }
 
     // Create and populate Leisure entity
     $leisure = new Leisure();
     $leisure->setName($data['name']);
     $leisure->setAuthorOrDirector($data['authorOrDirector']);
     $leisure->setCategory($category);
 
     // Handle optional date and nbrPages
     if (isset($data['date'])) {
         $leisure->setDate(\DateTime::createFromFormat('Y-m-d', $data['date']));
     }
 
     // Handle nbr_pages (choose one approach)
     // Option 1: Set a default value (if optional)
     $leisure->setNbrPages($data['nbrPages'] ?? 0); // Nullish coalescing operator (if nbrPages is null, set to 0)
 
     // Option 2: Throw an exception for missing value (if required)
     // if (!isset($data['nbrPages'])) {
     //     throw new \Exception('Missing required field: nbrPages');
     // }
     // $leisure->setNbrPages($data['nbrPages']);
 
     // Persist and flush the entity
     $this->entityManager->persist($leisure);
     $this->entityManager->flush();
 
     // Return successful response with serialized data
     return new JsonResponse(
         [
             'message' => 'Leisure added successfully',
             'leisure' => $this->serializer->serialize($leisure, 'json'),
         ],
         Response::HTTP_CREATED
     );
 }


    /**
 * @Route("/api/leisures", name="listLeisures", methods={"GET"})
 */
public function listLeisures(): JsonResponse
{
    $leisureRepository = $this->entityManager->getRepository(Leisure::class);
    $leisures = $leisureRepository->findAll();

    // Optional: Transform entities to desired format (e.g., arrays)
    $leisureData = [];
    foreach ($leisures as $leisure) {
        $leisureData[] = [
            'id' => $leisure->getId(),
            'name' => $leisure->getName(),
            'authorOrDirector' => $leisure->getAuthorOrDirector(),
            'date' => $leisure->getDate() ? $leisure->getDate()->format('Y-m-d') : null,
            'nbrPages' => $leisure->getNbrPages(),
            'category' => [
                'id' => $leisure->getCategory()->getId(),
                'name' => $leisure->getCategory()->getName(),
            ],
        ];
    }

    return new JsonResponse($leisureData);
}

}
