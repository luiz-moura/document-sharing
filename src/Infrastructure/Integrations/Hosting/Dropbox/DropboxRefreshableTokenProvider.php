<?php

namespace App\Infrastructure\Integrations\Hosting\Dropbox;

use Fig\Http\Message\StatusCodeInterface as Status;
use GuzzleHttp\Exception\ClientException;
use Spatie\Dropbox\RefreshableTokenProvider;
use App\Infrastructure\Integrations\Hosting\Enums\HostingEnum;
use Throwable;

class DropboxRefreshableTokenProvider extends DropboxTokenProvider implements RefreshableTokenProvider
{
    public function refresh(ClientException $exception): bool
    {
        $this->logger->info(sprintf('[%s] Trying to refresh the token', __METHOD__));

        if (Status::STATUS_UNAUTHORIZED !== $exception->getCode()) {
            $this->logger->info(sprintf('[%s] The token was not refreshed because the error is not an auth error', __METHOD__), [
                'exception' => $exception,
            ]);

            return false;
        }

        $hosting = $this->hostingRepository->findBySlug(HostingEnum::DROPBOX->value);

        $newAccessToken = $this->refreshToken($hosting->refreshableToken);
        if (is_null($newAccessToken)) {
            return false;
        }

        $this->hostingRepository->updateAccessTokenBySlug(HostingEnum::DROPBOX->value, $newAccessToken['access_token']);

        $this->cache->set(self::TOKEN_CACHE, $newAccessToken['access_token'], $newAccessToken['expires_in']);

        $this->logger->info(sprintf('[%s] Token has been refreshed', __METHOD__));

        return true;
    }

    /**
     * @return ?array{access_token: string, expires_in: int, token_type: string}
     */
    private function refreshToken(string $refreshableToken): ?array
    {
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
             * @var array {access_token: string, token_type: string, expires_in: int} $body
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
