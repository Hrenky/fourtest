<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Connector
{
    public string $api_link = 'https://candidate-testing.api.royal-apps.io/api/v2/';

    public function __construct(
        private HttpClientInterface $client,
        private CacheInterface $cache
    ) {}

    public function connect(
        string $method,
        string $path,
        array|null $data = null,
        bool $array = true
    ): JsonResponse|ResponseInterface|array|string
    {
        $url = $this->api_link . $path;

        try {
            if (!$this->cache->hasItem('access_token')) {
                $this->getToken();
            }

            $access_token = $this->cache->getItem('access_token')->get();

            $response = $this->clientRequest($method, $url, $access_token, $data);

            if ($response->getStatusCode() === JsonResponse::HTTP_UNAUTHORIZED) {
                throw new BadRequestException();
            }

            if ($response->getStatusCode() !== JsonResponse::HTTP_OK) {
                throw new \Exception($response->getContent());
            }
        } catch (BadRequestException) {
            $this->getToken(true);
            $access_token = $this->cache->getItem('access_token')->get();

            $response = $this->clientRequest($method, $url, $access_token, $data);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Could not connect to API. ' . $e->getMessage()
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        if ($array) {
            return $response->toArray();
        }
        return $response;
    }

    /**
     * @param bool $refresh
     * @param array|null $data
     * @return ResponseInterface|JsonResponse|string
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getToken(bool $refresh = false, array|null $data = null): ResponseInterface|JsonResponse|string
    {
        $method = 'post';
        $url = $this->api_link . 'token';
        if ($refresh) {
            $method = 'get';
            $url .= '/refresh/' . $this->cache->getItem('refresh_token')->get();
        }

        $response = $this->clientRequest($method, $url, data: $data);

        if ($response->getStatusCode() !== JsonResponse::HTTP_OK) {
            return new JsonResponse([
                'message' => $response->getContent()
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $result = json_decode($response->getContent());

        $token = $result->token_key;
        $refreshToken = $result->refresh_token_key;

        $this->cacheItem('access_token', $token);
        $this->cacheItem('refresh_token', $refreshToken);

        if (!$refresh) {
            $this->cacheItem('first_name', $result->user->first_name);
            $this->cacheItem('last_name', $result->user->last_name);
        }

        return $response;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    private function cacheItem(string $key, mixed $value): void
    {
        $cache = $this->cache->getItem($key)->set($value);
        $this->cache->save($cache);
    }

    /**
     * @param string $method
     * @param string $url
     * @param string $token
     * @param array|null $data
     * @return \Symfony\Contracts\HttpClient\ResponseInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function clientRequest(
        string $method,
        string $url,
        string $token = '',
        array|null $data = null
    ): ResponseInterface
    {
        $headers = ['Accept' => 'application/json'];
        if (!empty($token)) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        return $this->client->request(
            strtoupper($method),
            $url,
            [
                'headers' => $headers,
                'max_redirects' => 0,
                'json' => $data
            ]
        );
    }
}