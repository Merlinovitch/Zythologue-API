<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ReviewController extends AbstractController
{
    #[Route('/reviews', name: 'app_all_reviews', methods: ['GET'])]
    public function getAllReviews(Connection $connection): JsonResponse
    {
        $sql = "SELECT * FROM review";
        $results = $connection->fetchAllAssociative($sql);

        $formattedResults = [];

        foreach ($results as $result) {

            $formattedResults[] = [
                'beer_id' => $result['beer_id'],
                'beerlover_id' => $result['beerlover_id'],
                'note' => $result['review_note'],
                'review' => $result['review_comment'],
                'created_at' => $result['created_at'],
                'updated_at' => $result['updated_at'],
            ];
        }

        return $this->json($formattedResults);
    }

    #[Route('/reviews/{id}', name: 'app_reviews_by_id', methods: ['GET'])]
    public function getBreweryById(Connection $connection, int $id): JsonResponse
    {
        $sql = "SELECT * FROM review WHERE review_id = :id";
        $result = $connection->fetchAssociative($sql, ['id' => $id]);

        if (!$result) {
            return new JsonResponse(['error' => 'review not found'], 404);
        }

        $formattedResult = [
            'beer_id' => $result['beer_id'],
            'beerlover_id' => $result['beerlover_id'],
            'note' => $result['review_note'],
            'review' => $result['review_comment'],
            'created_at' => $result['created_at'],
            'updated_at' => $result['updated_at'],
        ];

        return $this->json($formattedResult);
    }

    #[Route('/reviews', name: 'app_add_review', methods: ['POST'])]
    public function addReview(Connection $connection, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $note = $data['review_note'] ?? null;
        $comment = $data['review_comment'] ?? null;
        $beerlover_id = $data['beerlover_id'] ?? null;
        $beer_id = $data['beer_id'] ?? null;

        if (!$note || !$comment || !$beer_id || !$beerlover_id) {
            return new JsonResponse(['error' => 'Missing required parameters'], 400);
        }

        $sql = "INSERT INTO review (review_note, review_comment, beer_id, beerlover_id) 
                VALUES (:note, :comment, :beer_id, :beerlover_id)";

        $connection->executeStatement($sql, [
            'note' => $note,
            'comment' => $comment,
            'beer_id' => $beer_id,
            'beerlover_id' => $beerlover_id
        ]);

        $review_id = $connection->lastInsertId();

        $sql = "SELECT * FROM review WHERE review_id = :id";
        $newReview = $connection->fetchAssociative($sql, ['id' => $review_id]);

        return new JsonResponse($newReview, 201);
    }

    #[Route('/reviews/{id}', name: 'app_put_review', methods: ['PUT'])]
    public function putReview(Connection $connection, int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $note = $data['review_note'] ?? null;
        $comment = $data['review_comment'] ?? null;

        if (!$note || !$comment) {
            return new JsonResponse(['error' => 'Missing required parameters'], 400);
        }

        $sql = "UPDATE review SET review_note = :note, review_comment = :comment, updated_at = NOW() WHERE review_id = :id";
        $connection->executeStatement($sql, [
            'note' => $note,
            'comment' => $comment,
            'id' => $id
        ]);

        $sql = "SELECT * FROM review WHERE review_id = :id";
        $newReview = $connection->fetchAssociative($sql, ['id' => $id]);
        return new JsonResponse($newReview, 200);
    }

    #[Route('/reviews/{id}', name: 'app_delete_review', methods: ['DELETE'])]
    public function deleteReview(Connection $connection, int $id): JsonResponse
    {
        try {
            // Begin transaction
            $connection->beginTransaction();

            // Delete review
            $sqlDeleteReview = "DELETE FROM review WHERE review_id = :id";
            $connection->executeStatement($sqlDeleteReview, ['id' => $id]);

            // Commit transaction
            $connection->commit();

            return new JsonResponse(['message' => 'Review deleted'], 200);
        } catch (\Throwable $e) {
            // Rollback transaction on error
            $connection->rollBack();
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
