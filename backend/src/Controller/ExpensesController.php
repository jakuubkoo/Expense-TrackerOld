<?php

namespace App\Controller;

use App\Entity\Expense;
use App\Entity\User;
use App\Manager\ExpensesManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class ExpensesController
 *
 * This controller handles the expenses related endpoints.
 */
#[Route('/api', name: 'api_')]
class ExpensesController extends AbstractController
{
    /**
     * @var ExpensesManager
     */
    private ExpensesManager $expensesManager;

    /**
     * ExpensesController constructor.
     *
     * @param ExpensesManager $expensesManager The manager for handling expense operations.
     */
    public function __construct(ExpensesManager $expensesManager)
    {
        $this->expensesManager = $expensesManager;
    }

    /**
     * A placeholder endpoint for expenses.
     *
     * @return JsonResponse A JSON response with a message.
     */
    #[Route('/expenses', name: 'expense', methods: ['POST'])]
    public function expenses(): JsonResponse
    {
        return new JsonResponse([
            'message' => ''
        ], Response::HTTP_OK);
    }

    /**
     * Adds a new expense.
     *
     * This endpoint verifies the request data and creates a new Expense entity
     * if the data is valid. It associates the new expense with the current user.
     *
     * @param Request $request The HTTP request containing the expense data.
     * @param EntityManagerInterface $em The entity manager for database operations.
     *
     * @return JsonResponse A JSON response indicating success or containing error messages.
     *
     * @throws \Exception If there is an error during the entity creation process.
     */
    #[Route('/addExpense', name: 'add_expense', methods: ['POST'])]
    public function addExpense(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $verificationResult = $this->expensesManager->verifyExpensesData($request);

        if ($verificationResult instanceof JsonResponse) {
            return $verificationResult;
        }

        $user = $em->getRepository(User::class)->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
        $expense = $this->expensesManager->makeEntity($request, $user);

        $em->persist($expense);
        $em->flush();

        return new JsonResponse([
            'message' => 'Expense added successfully!'
        ], Response::HTTP_OK);
    }
}
