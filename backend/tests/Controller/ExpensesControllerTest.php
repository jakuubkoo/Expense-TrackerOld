<?php
/**
 * This class contains test cases for the ExpensesController class.
 */
namespace App\Tests\Controller;

use App\Repository\UserRepository;
use App\Tests\CustomCase;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class ExpensesControllerTest extends CustomCase
{

    /**
     * @var KernelBrowser Instance for making requests.
     */
    private object $client;

    /**
     * Set up before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        parent::setUp();
    }

    /**
     * Test case for adding an expense successfully.
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
     * Test case for validating expense addition with missing or empty fields.
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
}
