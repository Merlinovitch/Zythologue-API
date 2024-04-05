<?php

namespace App\Controller;


use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


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
                'description' => $result['beer_description'],
                'abv' => $result['beer_abv'],
                'created_at' => $result['created_at'],
                'updated_at' => $result['updated_at'],
            ];
        }

        return $this->json($formattedResults);
    }


    #[Route('/beers/{id}', name: 'app_beer', methods: ['GET'])]
    public function getBeerById(Connection $connection, int $id): JsonResponse
    {
        $sql = "SELECT * FROM beer WHERE beer_id = :id";
        $result = $connection->fetchAssociative($sql, ['id' => $id]);


        if (!$result) {
            return new JsonResponse(['error' => 'Beer not found'], 404);
        }

        $formattedResult = [
            'id' => $result['beer_id'],
            'name' => $result['beer_name'],
            'description' => $result['beer_description'],
            'abv' => $result['beer_abv'],
            'created_at' => $result['created_at'],
            'updated_at' => $result['updated_at'],
        ];

        return $this->json($formattedResult);
    }

    #[Route('/beers', name: 'app_add_beer', methods: ['POST'])]
    public function addBeer(Connection $connection, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $name = $data['name'] ?? null;
        $description = $data['description'] ?? null;
        $abv = $data['abv'] ?? null;
        $brewery_id = $data['brewery_id'] ?? null;

        if (!$name || !$description || !$abv || !$brewery_id) {
            return new JsonResponse(['error' => 'Missing required parameters'], 400);
        }

        $sql = "INSERT INTO beer (beer_name, beer_description, beer_abv, brewery_id) 
                VALUES (:name, :description, :abv, :brewery_id)";

        $connection->executeStatement($sql, [
            'name' => $name,
            'description' => $description,
            'abv' => $abv,
            'brewery_id' => $brewery_id,
        ]);

        // Get the ID of the inserted beer
        $beerId = $connection->lastInsertId();

        // Fetch the newly inserted beer to return in the response
        $sql = "SELECT * FROM beer WHERE beer_id = :id";
        $newBeer = $connection->fetchAssociative($sql, ['id' => $beerId]);

        return new JsonResponse($newBeer, 201);
    }

    #[Route('/beers', name: 'app_put_beer', methods: ['PUT'])]
    public function updateBeer(Connection $connection, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $id = $data['id'] ?? null;
        $name = $data['name'] ?? null;
        $description = $data['description'] ?? null;
        $abv = $data['abv'] ?? null;
        $brewery_id = $data['brewery_id'] ?? null;
        $updated_at = date('Y-m-d H:i:s');

        if (!$id) {
            return new JsonResponse(['error' => 'Missing ID'], 400);
        }

        $sqlSelect = "SELECT * FROM beer WHERE beer_id = :id";
        $currentBeer = $connection->fetchAssociative($sqlSelect, ['id' => $id]);

        $name = $name ?? $currentBeer['beer_name'];
        $description = $description ?? $currentBeer['beer_description'];
        $abv = $abv ?? $currentBeer['beer_abv'];
        $brewery_id = $brewery_id ?? $currentBeer['brewery_id'];

        $sql = "UPDATE beer SET beer_name = :name, beer_description = :description, beer_abv = :abv, brewery_id = :brewery_id, updated_at = :updated_at WHERE beer_id = :id";

        $connection->executeStatement($sql, [
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'abv' => $abv,
            'brewery_id' => $brewery_id,
            'updated_at' => $updated_at,
        ]);

        $sql = "SELECT * FROM beer WHERE beer_id = :id";
        $updatedBeer = $connection->fetchAssociative($sql, ['id' => $id]);
        return new JsonResponse($updatedBeer, 200);
    }

    #[Route('/beers/{id}', name: 'app_delete_beer', methods: ['DELETE'])]
    public function deleteBeer(Connection $connection, int $id): JsonResponse
    {
        try {
            // Begin transaction
            $connection->beginTransaction();

            // Delete associated entries in favorite table
            $sqlDeleteFavorite = "DELETE FROM favorite WHERE beer_id = :id";
            $connection->executeStatement($sqlDeleteFavorite, ['id' => $id]);

            // Delete associated entries in review table
            $sqlDeleteReview = "DELETE FROM review WHERE beer_id = :id";
            $connection->executeStatement($sqlDeleteReview, ['id' => $id]);

            // Delete associated entries in photo table
            $sqlDeletePhoto = "DELETE FROM photo WHERE beer_id = :id";
            $connection->executeStatement($sqlDeletePhoto, ['id' => $id]);

            // Delete associated entries in category_beer table
            $sqlDeleteCategoryBeer = "DELETE FROM category_beer WHERE beer_id = :id";
            $connection->executeStatement($sqlDeleteCategoryBeer, ['id' => $id]);

            // Delete associated entries in beer_ingredient table
            $sqlDeleteBeerIngredient = "DELETE FROM beer_ingredient WHERE beer_id = :id";
            $connection->executeStatement($sqlDeleteBeerIngredient, ['id' => $id]);

            // Delete beer
            $sqlDeleteBeer = "DELETE FROM beer WHERE beer_id = :id";
            $connection->executeStatement($sqlDeleteBeer, ['id' => $id]);

            // Commit transaction
            $connection->commit();

            return new JsonResponse(['message' => 'Beer deleted'], 200);
        } catch (\Throwable $e) {
            // Rollback transaction on error
            $connection->rollBack();
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
