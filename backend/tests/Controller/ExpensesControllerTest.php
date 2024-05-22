<?php
/**
 * This class contains test cases for the ExpensesController class.
 */
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ExpensesControllerTest extends WebTestCase
{

    /**
     * Test case for adding an expense successfully.
     */
    public function testAddExpenseSuccess(): void
    {
        $client = static::createClient();

        // Create a user and get the JWT token (adjust this part according to your user creation and login logic)
        $client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'test@test.com',
                'password' => 'test'
            ])
        );
        $loginResponse = $client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $loginResponse->getStatusCode());
        $loginData = json_decode($loginResponse->getContent(), true);
        $this->assertArrayHasKey('token', $loginData);
        $this->assertNotEmpty($loginData['token']);

        // Send the request with the JWT token
        $client->request('POST', '/api/addExpense', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Authorization' => 'Bearer ' . $loginData['token'],
        ], json_encode([
            'title' => 'Groceries',
            'amount' => 50.25,
            'date' => '2024-05-21',
            'category' => 'Food',
            'description' => 'Purchased groceries for the week'
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJson($client->getResponse()->getContent());
        $this->assertStringContainsString('Expense added successfully!', $client->getResponse()->getContent());
    }

    /**
     * Test case for validating expense addition with missing or empty fields.
     */
    public function testAddExpenseValidationError(): void
    {
        $client = static::createClient();

        // Create a user and get the JWT token
        // Create a user and get the JWT token (adjust this part according to your user creation and login logic)
        $client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'test@test.com',
                'password' => 'test'
            ])
        );
        $loginResponse = $client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $loginResponse->getStatusCode());
        $loginData = json_decode($loginResponse->getContent(), true);
        $this->assertArrayHasKey('token', $loginData);
        $this->assertNotEmpty($loginData['token']);

        // Send the request with the JWT token and missing 'title' field
        $client->request('POST', '/api/addExpense', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Authorization' => 'Bearer ' . $loginData['token'],
        ], json_encode([
            'amount' => 50.25,
            'date' => '2024-05-21',
            'category' => 'Food',
            'description' => 'Purchased groceries for the week'
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($client->getResponse()->getContent());

        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseContent);
        $this->assertEquals("The field 'title' is missing.", $responseContent['message']);

        // Send the request with the JWT token and empty 'title' field
        $client->request('POST', '/api/addExpense', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Authorization' => 'Bearer ' . $loginData['token'],
        ], json_encode([
            'title' => '',
            'amount' => 50.25,
            'date' => '2024-05-21',
            'category' => 'Food',
            'description' => 'Purchased groceries for the week'
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJson($client->getResponse()->getContent());

        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseContent);
        $this->assertEquals('Title is required.', $responseContent['message']);
    }
}
