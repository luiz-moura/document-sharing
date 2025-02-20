<?php

namespace App\Infrastructure\Integrations\Hosting\Dropbox;

use App\Application\Settings\SettingsInterface;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client as GuzzleClient;
use Throwable;

class DropboxClient
{
    protected GuzzleClient $guzzleClient;

    protected string $uri;

    protected float $timeout;

    protected string $clientId;

    protected string $clientSecret;

    protected string $accessCode;

    public function __construct(
        protected readonly SettingsInterface $settings,
        private readonly LoggerInterface $logger,
    ) {
        $this->clientId = $this->settings->get('dropbox.app_key');
        $this->clientSecret = $this->settings->get('dropbox.app_secret');
        $this->accessCode = $this->settings->get('dropbox.access_code');

        $this->uri = $this->settings->get('dropbox.uri');
        $this->timeout = $this->settings->get('dropbox.timeout');

        $this->guzzleClient = new GuzzleClient([
            'base_uri' => $this->uri,
            'timeout' => $this->timeout,
        ]);
    }

    /**
     * return ?array{access_token: string, token_type: string, expires_in: int, refresh_token: string, scope: string, uid: string, account_id: string}
     */
    public function generateToken(): ?array
    {
        $this->logger->info(sprintf('[%s] Trying to generate a new token', __METHOD__));

        try {
            $response = $this->guzzleClient->post('oauth2/token', [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'code' => $this->accessCode,
                ],
            ]);

            /**
             * @var array{access_token: string, token_type: string, expires_in: int, refresh_token: string, scope: string, uid: string, account_id: string} $body
             */
            $body = json_decode($response->getBody()->getContents(), true);
        } catch (Throwable $exception) {
            $this->logger->warning(sprintf('[%s] Failed to generate new token', __METHOD__), [
                'exception' => $exception,
            ]);

            return null;
        }

        return $body;
    }

    /**
     * @return ?array{access_token: string, expires_in: int, token_type: string}
     */
    public function refreshToken(string $refreshableToken): ?array
    {
        $this->logger->info(sprintf('[%s] Trying to refresh the token', __METHOD__));

        try {
            $response = $this->guzzleClient->post('oauth2/token', [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshableToken,
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ],
            ]);

            /**
             * @var array{access_token: string, token_type: string, expires_in: int} $body
             */
            $body = json_decode($response->getBody()->getContents(), true);
        } catch (Throwable $exception) {
            $this->logger->info(sprintf('[%s] Failed to generate new token', __METHOD__), [
                'exception' => $exception,
            ]);

            return null;
        }

        return $body;
    }
}
