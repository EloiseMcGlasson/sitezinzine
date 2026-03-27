<?php

namespace App\Service\LibreTime;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class LibreTimeWeekInfoClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $weekInfoUrl,
    ) {}

    public function fetchWeekInfo(): array
    {
        $response = $this->httpClient->request('GET', $this->weekInfoUrl, [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'timeout' => 10,
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Erreur API LibreTime week-info');
        }

        return $response->toArray(false);
    }
}