<?php

use KandMailer\MailerClient;
use KandMailer\Http\MockHttpClient;

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/
function createMailer(): MailerClient
{
    $mockHttp = new MockHttpClient();
    return new MailerClient('test-api-key', 'https://api.exemple.com', $mockHttp);
}

function getMockHttp(MailerClient $client): MockHttpClient
{
    $httpClient = $client->getHttpClient();
    
    if (!$httpClient instanceof MockHttpClient) {
        throw new RuntimeException('HTTP client is not a MockHttpClient');
    }
    
    return $httpClient;
}