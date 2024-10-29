<?php

namespace App\Infrastructure\Integrations\Hosting\Dropbox;

use App\Infrastructure\Integrations\Hosting\Dropbox\Exceptions\AccessTokenNotDefinedException;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Log\LoggerInterface;
use Spatie\Dropbox\TokenProvider;
use Throwable;

class DropboxTokenProvider implements TokenProvider
{
    protected ?string $accessToken = null;
    protected ?string $refreshToken = null;
    protected GuzzleClient $guzzleClient;

    public function __construct(
        protected string $clientId,
        protected string $clientSecret,
        protected string $accessCode,
        protected LoggerInterface $logger,
    ) {
        $this->guzzleClient = new GuzzleClient([
            'base_uri' => 'https://api.dropboxapi.com',
            'timeout' => 2.0,
        ]);
    }

    /**
     * @throws AccessTokenNotDefinedException
     */
    public function getToken(): string
    {
        if (! $this->accessToken &&  ! $this->generateToken()) {
            throw new AccessTokenNotDefinedException();
        }

        return $this->accessToken;
    }

    private function generateToken(): bool
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
             * @var array {access_token: string, token_type: string, expires_in: int, refresh_token: string, scope: string, uid: string, account_id: string} $body
             */
            $body = json_decode($response->getBody()->getContents(), true);

            $this->accessToken = $body['access_token'];
            $this->refreshToken = $body['refresh_token'];

            $this->logger->info(sprintf('[%s] New token has been generated', __METHOD__));

            return true;
        } catch (Throwable $e) {
            $this->logger->warning(sprintf('[%s] Failed to generate new token', __METHOD__), [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
