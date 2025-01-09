<?php

declare(strict_types=1);

namespace App\Search\Status;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class Solr implements StatusInterface
{
    private string $baseUrl;

    private HttpClientInterface $httpClient;

    public function __construct(string $baseUrl, HttpClientInterface $httpClient)
    {
        $this->baseUrl = $baseUrl;
        $this->httpClient = $httpClient;
    }

    public function isAlive(): bool
    {
        if (empty($this->baseUrl)) {
            return false;
        }

        try {
            return 200 === $this->httpClient->request('GET', "{$this->baseUrl}/admin/ping")->getStatusCode();
        } catch (Throwable $throwable) {
            return false;
        }
    }
}
