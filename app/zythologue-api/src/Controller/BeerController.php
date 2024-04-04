<?php

namespace App\Controller;


use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class BeerController extends AbstractController
{
    #[Route('/beers', name: 'app_beers', methods: ['GET'])]
    public function getAllBeers(Connection $connection): JsonResponse
    {
        $sql = "SELECT * FROM Beer";
        $results = $connection->fetchAllAssociative($sql);

        $formattedResults = [];

        foreach ($results as $result) {

            $formattedResults[] = [
                'id' => $result['beer_id'],
                'name' => $result['beer_name'],
            ];
        }

        return $this->json($formattedResults);
    }
}
