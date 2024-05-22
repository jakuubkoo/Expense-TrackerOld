<?php

namespace App\Middleware;

use App\Manager\TokenManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * TokenValidationMiddleware
 *
 * Middleware to validate JWT tokens in incoming requests.
 */
class TokenValidationMiddleware
{
    private bool $isTestEnvironment;

    /**
     * @var TokenManager
     *
     * Manages operations related to JWT tokens, such as extraction and validation.
     */
    private TokenManager $tokenManager;

    /**
     * TokenValidationMiddleware constructor.
     *
     * @param TokenManager $tokenManager Manages operations related to JWT tokens.
     * @param ParameterBagInterface $parameterBag The parameter bag containing environment information.
     */
    public function __construct(TokenManager $tokenManager, ParameterBagInterface $parameterBag)
    {
        $this->tokenManager = $tokenManager;
        $this->isTestEnvironment = $parameterBag->get('kernel.environment') === 'test';
    }

    /**
     * Handles the kernel request event to validate the JWT token.
     *
     * Extracts the token from the request and checks if it is blacklisted. If the token is blacklisted,
     * the request is terminated with an error message.
     *
     * @param RequestEvent $event The event object that contains the request.
     *
     * @return void
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        // If in test environment, skip token validation
        // TODO: Need fix this
        if ($this->isTestEnvironment) {
            return;
        }

        $request = $event->getRequest();
        $token = $this->tokenManager->getTokenFromRequest($request);

        // Check if token is not blacklisted
        if (!empty($token)) {
            if ($this->tokenManager->isTokenBlacklisted($token)) {
                die('Invalid JWT token');
                // TODO: Handle error
            }
        }
    }
}
