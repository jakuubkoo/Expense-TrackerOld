<?php
/**
 * This class contains test cases for the ExpensesController class.
 */
namespace App\Tests\Controller;

use App\Entity\Expense;
use App\Repository\UserRepository;
use App\Tests\CustomCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class ExpensesControllerTest extends CustomCase
{

    /**
     * @var EntityManagerInterface $entityManager
     */
    private EntityManagerInterface $entityManager;

    /**
     * @var KernelBrowser Instance for making requests.
     */
    private KernelBrowser $client;

    /**
     * Set up the test environment before each test method is run.
     *
     * This method sets up the client and entity manager for testing purposes.
     * It initializes the client by calling the `createClient` method from the current class.
     * The entity manager is obtained from the kernel container using the `'doctrine.orm.entity_manager'` service.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        parent::setUp();
    }

    /**
     * Test method for adding an expense successfully.
     *
     * This method tests the functionality of adding an expense using the '/api/addExpense' endpoint.
     * It simulates user authentication, sends a POST request with the required data, and asserts that the response is successful.
     *
     * @return void
     */
    public function testAddExpenseSuccess(): void
    {

        // simulate user authentication
        $this->simulateUserAuthentication($this->client);

        // Send the request with the JWT token
        $this->client->request('POST', '/api/addExpense', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'Groceries',
            'amount' => 50.25,
            'date' => '2024-05-21',
            'category' => 'Food',
            'description' => 'Purchased groceries for the week'
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertStringContainsString('Expense added successfully!', $this->client->getResponse()->getContent());
    }

    /**
     * Test case for validating the addition of an expense with missing 'title' field.
     *
     * @return void
     */
    public function testAddExpenseValidationError(): void
    {
        // simulate user authentication
        $this->simulateUserAuthentication($this->client);

        // Send the request with the JWT token and missing 'title' field
        $this->client->request('POST', '/api/addExpense', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'amount' => 50.25,
            'date' => '2024-05-21',
            'category' => 'Food',
            'description' => 'Purchased groceries for the week'
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($this->client->getResponse()->getContent());

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseContent);
        $this->assertEquals("The field 'title' is missing.", $responseContent['message']);

        // simulate user authentication
        $this->simulateUserAuthentication($this->client);

    }

    /**
     * Test for validating the addition of an expense with a validation error.
     *
     * @return void
     */
    public function testAddExpenseValidationError2(): void
    {
        // simulate user authentication
        $this->simulateUserAuthentication($this->client);

        // Send the request with the JWT token and empty 'title' field
        $this->client->request('POST', '/api/addExpense', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => '',
            'amount' => 50.25,
            'date' => '2024-05-21',
            'category' => 'Food',
            'description' => 'Purchased groceries for the week'
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($this->client->getResponse()->getContent());

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseContent);
        $this->assertEquals('Title is required.', $responseContent['message']);
    }

    /**
     * Test case for retrieving expenses successfully.
     */
    public function testGetExpensesSuccess(): void
    {
        // simulate user authentication
        $this->simulateUserAuthentication($this->client);

        // Send the request with the JWT token
        $this->client->request('POST', '/api/expenses', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJson($this->client->getResponse()->getContent());

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('expenses', $responseContent);
        $this->assertIsArray($responseContent['expenses']);
        $this->assertNotEmpty($responseContent['expenses']);

        // Check the content of the first expense
        $expense = $responseContent['expenses'][0];
        $this->assertEquals('Groceries', $expense['title']);
        $this->assertEquals('Purchased groceries for the week', $expense['description']);
        $this->assertEquals('2024-05-21', $expense['date']);
        $this->assertEquals('Food', $expense['category']);
        $this->assertEquals(50.25, $expense['amount']);
    }

    /**
     * Test for editing an expense successfully.
     *
     * @return void
     */
    public function testEditExpenseSuccess(): void
    {
        // Get the fixture expense
        $expense = $this->entityManager->getRepository(Expense::class)->findOneBy(['title' => 'Groceries']);

        // simulate user authentication
        $this->simulateUserAuthentication($this->client);

        // Define the new data for the expense
        $newData = [
            'id' => $expense->getId(),
            'title' => 'Updated Expense',
            'amount' => 150.00,
            'date' => '2024-05-22',
            'category' => 'Updated Category',
            'description' => 'Updated Description'
        ];

        // Send the request with the JWT token
        $this->client->request('POST', '/api/editExpense', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($newData));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertStringContainsString('Expense edited successfully!', $this->client->getResponse()->getContent());

        // Fetch the updated expense from the database
        $updatedExpense = $this->entityManager->getRepository(Expense::class)->find($expense->getId());

        // Assert that the expense was updated correctly
        $this->assertEquals('Updated Expense', $updatedExpense->getTitle());
        $this->assertEquals(150.00, $updatedExpense->getAmount());
        $this->assertEquals('2024-05-22', $updatedExpense->getDate()->format('Y-m-d'));
        $this->assertEquals('Updated Category', $updatedExpense->getCategory());
        $this->assertEquals('Updated Description', $updatedExpense->getDescription());
    }

    /**
     * Test case to simulate editing a non-existing expense.
     *
     * @return void
     */
    public function testEditExpenseNotFound(): void
    {
        // simulate user authentication
        $this->simulateUserAuthentication($this->client);

        // Define the data for a non-existing expense
        $newData = [
            'id' => 99999, // Assuming this ID does not exist
            'title' => 'Updated Expense',
            'amount' => 150.00,
            'date' => '2024-05-22',
            'category' => 'Updated Category',
            'description' => 'Updated Description'
        ];

        // Send the request with the JWT token
        $this->client->request('POST', '/api/editExpense', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($newData));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($this->client->getResponse()->getContent());

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseContent);
        $this->assertEquals('No expense found for id 99999', $responseContent['message']);
    }

    /**
     * Test case to validate the behavior of editing an expense with empty fields.
     *
     * @return void
     */
    public function testEditExpenseValidationErrorEmptyFields(): void
    {
        // Get the fixture expense
        $expense = $this->entityManager->getRepository(Expense::class)->findOneBy(['title' => 'Groceries']);

        // simulate user authentication
        $this->simulateUserAuthentication($this->client);

        // Send the request with empty 'title' field
        $newData = [
            'id' => $expense->getId(),
            'title' => '',
            'amount' => 150.00,
            'date' => '2024-05-22',
            'category' => 'Updated Category',
            'description' => 'Updated Description'
        ];

        // Send the request with the JWT token
        $this->client->request('POST', '/api/editExpense', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($newData));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($this->client->getResponse()->getContent());

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseContent);
        $this->assertEquals('Title is required.', $responseContent['message']);
    }

    /**
     * Test case for editing an expense with validation error for empty field ID.
     *
     * This test verifies that when trying to edit an expense with an empty ID,
     * a validation error message is returned.
     *
     * @return void
     */
    public function testEditExpenseValidationErrorEmptyFieldId(): void
    {

        // simulate user authentication
        $this->simulateUserAuthentication($this->client);

        // Send the request with empty 'title' field
        $newData = [
            'id' => '',
            'title' => 'Test',
            'amount' => 150.00,
            'date' => '2024-05-22',
            'category' => 'Updated Category',
            'description' => 'Updated Description'
        ];

        // Send the request with the JWT token
        $this->client->request('POST', '/api/editExpense', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($newData));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($this->client->getResponse()->getContent());

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseContent);
        $this->assertEquals('ID is required.', $responseContent['message']);
    }

    /**
     * Test for deleting an expense successfully.
     *
     * @return void
     */
    public function testDeleteExpenseSuccess(): void
    {
        // Get the fixture expense
        $expense = $this->entityManager->getRepository(Expense::class)->findOneBy(['title' => 'Groceries']);

        // simulate user authentication
        $this->simulateUserAuthentication($this->client);

        // Define the data for the expense
        $data = [
            'id' => $expense->getId(),
        ];

        // Send the request with the JWT token
        $this->client->request('POST', '/api/deleteExpense', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($data));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertStringContainsString('Expense deleted', $this->client->getResponse()->getContent());

        // Fetch the deleted expense from the database
        $deletedExpense = $this->entityManager->getRepository(Expense::class)->find($data['id']);

        // Assert that the expense was deleted correctly
        $this->assertNull($deletedExpense);
    }

    /**
     * Test case to simulate deleting a non-existing expense.
     *
     * @return void
     */
    public function testDeleteExpenseNotFound(): void
    {
        // simulate user authentication
        $this->simulateUserAuthentication($this->client);

        // Define the data for a non-existing expense
        $data = [
            'id' => 99999, // Assuming this ID does not exist
        ];

        // Send the request with the JWT token
        $this->client->request('POST', '/api/deleteExpense', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($data));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($this->client->getResponse()->getContent());

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseContent);
        $this->assertEquals('No expense found for id 99999', $responseContent['message']);
    }

}
