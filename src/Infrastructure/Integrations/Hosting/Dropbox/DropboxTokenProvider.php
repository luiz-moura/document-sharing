<?php

namespace App\Infrastructure\Integrations\Hosting\Dropbox;

use App\Domain\Sender\Contracts\HostingRepository;
use App\Infrastructure\Integrations\Hosting\Dropbox\Exceptions\AccessTokenNotDefinedException;
use App\Infrastructure\Integrations\Hosting\Enums\HostingEnum;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Spatie\Dropbox\TokenProvider;
use GuzzleHttp\Exception\ClientException;
use Fig\Http\Message\StatusCodeInterface as Status;
use Spatie\Dropbox\RefreshableTokenProvider;

class DropboxTokenProvider implements TokenProvider, RefreshableTokenProvider
{
    protected const string TOKEN_CACHE = 'dropbox-access-token';

    public function __construct(
        protected readonly DropboxClient $client,
        protected readonly HostingRepository $hostingRepository,
        protected readonly CacheInterface $cache,
        protected readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws AccessTokenNotDefinedException
     */
    public function getToken(): string
    {
        $this->logger->info(sprintf('[%s] Trying to get access token', __METHOD__));

        if ($this->cache->has(self::TOKEN_CACHE)) {
            $this->logger->info(sprintf('[%s] Access token has been found in cache', __METHOD__));

            return $this->cache->get(self::TOKEN_CACHE);
        }

        $accessToken = $this->hostingRepository->findBySlug(HostingEnum::DROPBOX->value)->accessToken;
        if ($accessToken) {
            $this->logger->info(sprintf('[%s] Access token has been found in database', __METHOD__));

            $this->cache->set(self::TOKEN_CACHE, $accessToken);

            return $accessToken;
        }

        $this->logger->info(sprintf('[%s] Access token is not defined', __METHOD__));

        $newAccessToken = $this->client->generateToken();
        if (! $newAccessToken) {
            throw new AccessTokenNotDefinedException();
        }

        // TODO:
        $this->hostingRepository->updateAccessTokenBySlug(HostingEnum::DROPBOX->value, $newAccessToken['access_token'] ?? '');
        $this->hostingRepository->updateRefreshableTokenBySlug(HostingEnum::DROPBOX->value, $newAccessToken['refresh_token'] ?? '');

        $this->cache->set(self::TOKEN_CACHE, $newAccessToken['access_token']);

        $this->logger->info(sprintf('[%s] New token has been generated', __METHOD__));

        return $newAccessToken['access_token'];
    }

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

        $newAccessToken = $this->client->refreshToken($hosting->refreshableToken);
        if (is_null($newAccessToken)) {
            return false;
        }

        $this->hostingRepository->updateAccessTokenBySlug(HostingEnum::DROPBOX->value, $newAccessToken['access_token']);

        $this->cache->set(self::TOKEN_CACHE, $newAccessToken['access_token'], $newAccessToken['expires_in']);

        $this->logger->info(sprintf('[%s] Token has been refreshed', __METHOD__));

        return true;
    }
}
