<?php

declare(strict_types=1);

namespace KandMailer;

use KandMailer\Http\HttpClientInterface;
use KandMailer\Http\CurlHttpClient;
use KandMailer\Helpers\Makers;

use KandMailer\Models\File;

class MailerClient
{
    use \KandMailer\Traits\SetDatas;
    use \KandMailer\Traits\GetDatas;
    use \KandMailer\Traits\ValidateDatas;

    private string $apiKey;
    private string $endpoint;
    private HttpClientInterface $httpClient;

    private ?string $template = null;
    private string|array|null $firstName = null;
    private string|array|null $lastName = null;
    private string|array|null $email = null;
    private string|array|null $phone = null;
    private ?string $scenario = null;
    private ?string $accountId = null;
    private ?\DateTimeInterface $createdAt = null;

    /** @var array<string,mixed> */
    private array $options = [];

    /** @var array<int,array<string,mixed>> */
    private array $multiOptions = [];

    /** @var array<File> */
    private array $files = [];

    /** @var array<string> */
    private array $remove = [];

    /** @var array<string> */
    private array $exists = [];

    /**
     * Constructor.
     * 
     * @param string $apiKey The API key.
     * @param string $endpoint The endpoint.
     * @param HttpClientInterface|null $httpClient (Internal: for testing only)
     * @internal The $httpClient parameter is reserved for internal testing purposes
     */
    public function __construct(
        string $apiKey,
        string $endpoint,
        ?HttpClientInterface $httpClient = null
    ) {
        $this->apiKey = $apiKey;
        $this->endpoint = rtrim($endpoint, '/');
        $this->httpClient = $httpClient ?? new CurlHttpClient();
    }

    /**
     * Send a message to a single recipient.
     *
     * @throws \InvalidArgumentException If multiOptions is used
     * @throws \RuntimeException When the API responds with an error.
     *
     * @return string
     */
    public function sendSingle(): string
    {
        $this->validateNoMultiOptions();
        return (new Makers($this, 'POST', '/send/single'))->executeSingle();
    }

    /**
     * Send a message to multiple recipients.
     *
     * @throws \RuntimeException When the API responds with an error.
     *
     * @return string
     */
    public function sendMultiple(): string
    {
        return (new Makers($this, 'POST', '/send/list'))->executeMultiple();
    }

    /**
     * Add a contact.
     *
     * @throws \InvalidArgumentException If recipient is multiple or not properly set
     * @throws \RuntimeException When the API responds with an error.
     *
     * @return string
     */
    public function add(): string
    {
        $this->validateSingleRecipient('add');

        return (new Makers($this, 'POST', '/contact/add'))->executeSingle();
    }

    /**
     * Remove a contact.
     *
     * @throws \InvalidArgumentException If recipient is multiple or not properly set
     * @throws \RuntimeException When the API responds with an error.
     *
     * @return string
     */
    public function remove(): string
    {
        $this->validateSingleRecipient('remove');

        return (new Makers($this, 'POST', '/contact/remove'))->executeSingle();
    }
}