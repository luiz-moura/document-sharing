<?php

namespace App\Infrastructure\Integrations\Hosting\Dropbox;

use App\Domain\Sender\Contracts\HostingRepository;
use App\Infrastructure\Integrations\Hosting\Dropbox\Exceptions\AccessTokenNotDefinedException;
use App\Infrastructure\Integrations\Hosting\Enums\HostingEnum;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Log\LoggerInterface;
use Spatie\Dropbox\TokenProvider;
use Throwable;

class DropboxTokenProvider implements TokenProvider
{
    protected GuzzleClient $guzzleClient;

    public function __construct(
        protected readonly string $clientId,
        protected readonly string $clientSecret,
        protected readonly string $accessCode,
        protected readonly LoggerInterface $logger,
        protected readonly HostingRepository $hostingRepository
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
        // TODO: cache
        $accessToken = $this->hostingRepository->findBySlug(HostingEnum::DROPBOX->value)->accessToken;
        if ($accessToken) {
            return $accessToken;
        }

        $newAccessToken = $this->generateToken();
        if (! $newAccessToken) {
            throw new AccessTokenNotDefinedException();
        }

        // TODO: set cache access token
        $this->hostingRepository->updateAccessTokenBySlug(HostingEnum::DROPBOX->value, $newAccessToken['access_token'] ?? '');
        $this->hostingRepository->updateRefreshableTokenBySlug(HostingEnum::DROPBOX->value, $newAccessToken['refresh_token'] ?? '');

        $this->logger->info(sprintf('[%s] New token has been generated', __METHOD__));

        return $accessToken;
    }

    private function generateToken(): ?array
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
        } catch (Throwable $exception) {
            $this->logger->warning(sprintf('[%s] Failed to generate new token', __METHOD__), [
                'error' => $exception,
            ]);

            return null;
        }

        return $body;
    }
}
