<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class BeerLoverController extends AbstractController
{
    #[Route('/beerlovers', name: 'app_beerlovers', methods: ['GET'])]
    public function getAllBeerlovers(Connection $connection): JsonResponse
    {
        $sql = "SELECT * FROM beerlover";
        $results = $connection->fetchAllAssociative($sql);

        $formattedResults = [];

        foreach ($results as $result) {

            $formattedResults[] = [
                'id' => $result['beerlover_id'],
                'name' => $result['beerlover_name'],
                'mail' => $result['beerlover_mail'],
                'created_at' => $result['created_at'],
                'updated_at' => $result['updated_at'],
            ];
        }

        return $this->json($formattedResults);
    }

    #[Route('/beerlovers/{id}', name: 'app_beerlover', methods: ['GET'])]
    public function getBeerloverById(Connection $connection, int $id): JsonResponse
    {
        $sql = "SELECT * FROM beerlover WHERE beerlover_id = :id";
        $result = $connection->fetchAssociative($sql, ['id' => $id]);

        if (!$result) {
            return new JsonResponse(['error' => 'Beerlover not found'], 404);
        }

        $formattedResult = [
            'id' => $result['beerlover_id'],
            'name' => $result['beerlover_name'],
            'mail' => $result['beerlover_mail'],
            'created_at' => $result['created_at'],
            'updated_at' => $result['updated_at'],
        ];

        return $this->json($formattedResult);
    }

    #[Route('/beerlovers', name: 'app_add_beerlover', methods: ['POST'])]
    public function addBeerlover(Connection $connection, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $name = $data['name'] ?? null;
        $mail = $data['mail'] ?? null;
        $password = $data['password'] ?? null;

        if (!$name || !$mail || !$password) {
            return new JsonResponse(['error' => 'Missing required parameters'], 400);
        }

        $sql = "INSERT INTO beerlover (beerlover_name, beerlover_mail, beerlover_password)
                VALUES (:name, :mail, :password)";

        $connection->executeStatement($sql, [
            'name' => $name,
            'mail' => $mail,
            'password' => $password,
        ]);

        $beerlover_id = $connection->lastInsertId();

        $sql = "SELECT * FROM beerlover WHERE beerlover_id = :id";
        $newBeerlover = $connection->fetchAssociative($sql, ['id' => $beerlover_id]);

        return new JsonResponse($newBeerlover, 201);
    }

    #[Route('/beerlovers/{id}', name: 'app_update_beerlover', methods: ['PUT'])]
    public function updateBeerlover(Connection $connection, int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $name = $data['name'] ?? null;
        $mail = $data['mail'] ?? null;
        $password = $data['password'] ?? null;

        if (!$name && !$mail && !$password) {
            return new JsonResponse(['error' => 'No parameters provided'], 400);
        }

        $sqlSelect = "SELECT * FROM beerlover WHERE beerlover_id = :id";
        $beerlover = $connection->fetchAssociative($sqlSelect, ['id' => $id]);

        $name = $name ?? $beerlover['beerlover_name'];
        $mail = $mail ?? $beerlover['beerlover_mail'];
        $password = $password ?? $beerlover['beerlover_password'];

        $sql = "UPDATE beerlover SET beerlover_name = :name, beerlover_mail = :mail, beerlover_password = :password, updated_at = NOW() WHERE beerlover_id = :id";
        $connection->executeStatement($sql, [
            'name' => $name,
            'mail' => $mail,
            'password' => $password,
            'id' => $id
        ]);
        $beerlover_id = $id;
        $sql = "SELECT * FROM beerlover WHERE beerlover_id = :id";
        $newBeerlover = $connection->fetchAssociative($sql, ['id' => $beerlover_id]);
        return new JsonResponse($newBeerlover, 200);
    }

    #[Route('/beerlovers/{id}', name: 'app_delete_beerlover', methods: ['DELETE'])]
    public function deleteBeerlover(Connection $connection, int $id): JsonResponse
    {
        try {
            // Begin transaction
            $connection->beginTransaction();

            // Delete beerlover
            $sqlDeleteBeerlover = "DELETE FROM beerlover WHERE beerlover_id = :id";
            $connection->executeStatement($sqlDeleteBeerlover, ['id' => $id]);

            // Commit transaction
            $connection->commit();

            return new JsonResponse(['message' => 'User deleted'], 200);
        } catch (\Throwable $e) {
            // Rollback transaction on error
            $connection->rollBack();
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
