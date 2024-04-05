<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


class BreweryController extends AbstractController
{
    #[Route('/breweries', name: 'app_all_breweries', methods: ['GET'])]
    public function getAllBreweries(Connection $connection): JsonResponse
    {
        $sql = "SELECT * FROM brewery";
        $results = $connection->fetchAllAssociative($sql);

        $formattedResults = [];

        foreach ($results as $result) {

            $formattedResults[] = [
                'name' => $result['brewery_name'],
                'country' => $result['brewery_country'],
                'created_at' => $result['created_at'],
                'updated_at' => $result['updated_at'],
            ];
        }

        return $this->json($formattedResults);
    }

    #[Route('/breweries/{id}', name: 'app_breweries_by_id', methods: ['GET'])]
    public function getBreweryById(Connection $connection, int $id): JsonResponse
    {
        $sql = "SELECT * FROM brewery WHERE brewery_id = :id";
        $result = $connection->fetchAssociative($sql, ['id' => $id]);

        if (!$result) {
            return new JsonResponse(['error' => 'brewery not found'], 404);
        }

        $formattedResult = [
            'id' => $result['brewery_id'],
            'name' => $result['brewery_name'],
            'country' => $result['brewery_country'],
            'created_at' => $result['created_at'],
            'updated_at' => $result['updated_at'],
        ];

        return $this->json($formattedResult);
    }

    #[Route('/breweries', name: 'app_add_breweries', methods: ['POST'])]
    public function addBreweries(Connection $connection, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $name = $data['name'] ?? null;
        $country = $data['country'] ?? null;


        if (!$name || !$country) {
            return new JsonResponse(['error' => 'Missing required parameters'], 400);
        }

        $sql = "INSERT INTO brewery (brewery_name, brewery_country) 
                VALUES (:name, :country)";

        $connection->executeStatement($sql, [
            'name' => $name,
            'country' => $country,
        ]);

        // Get the ID of the inserted beer
        $brewery_id = $connection->lastInsertId();

        // Fetch the newly inserted beer to return in the response
        $sql = "SELECT * FROM brewery WHERE brewery_id = :id";
        $newBrewery = $connection->fetchAssociative($sql, ['id' => $brewery_id]);

        return new JsonResponse($newBrewery, 201);
    }

    #[Route('/breweries', name: 'app_put_breweries', methods: ['PUT'])]
    public function updateBreweries(Connection $connection, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $id = $data['id'] ?? null;
        $name = $data['name'] ?? null;
        $country = $data['country'] ?? null;

        if (!$id) {
            return new JsonResponse(['error' => 'Missing ID'], 400);
        }

        $sqlSelect = "SELECT * FROM brewery WHERE brewery_id = :id";
        $currentBrewery = $connection->fetchAssociative($sqlSelect, ['id' => $id]);

        $name = $name ?? $currentBrewery['brewery_name'];
        $country = $country ?? $currentBrewery['brewery_country'];

        $sql = "UPDATE brewery SET brewery_name = :name, brewery_country = :country, updated_at = NOW() WHERE brewery_id = :id";

        $connection->executeStatement($sql, [
            'id' => $id,
            'name' => $name,
            'country' => $country,
        ]);

        $sql = "SELECT * FROM brewery WHERE brewery_id = :id";
        $updatedBrewery = $connection->fetchAssociative($sql, ['id' => $id]);
        return new JsonResponse($updatedBrewery, 200);
    }


    #[Route('/breweries/{id}', name: 'app_delete_brewery', methods: ['DELETE'])]
    public function deleteBrewery(Connection $connection, int $id): JsonResponse
    {
        try {
            // Begin transaction
            $connection->beginTransaction();

            // Delete brewery
            $sqlDeleteBrewery = "DELETE FROM brewery WHERE brewery_id = :id";
            $connection->executeStatement($sqlDeleteBrewery, ['id' => $id]);

            // Commit transaction
            $connection->commit();

            return new JsonResponse(['message' => 'Brewery deleted'], 200);
        } catch (\Throwable $e) {
            // Rollback transaction on error
            $connection->rollBack();
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
