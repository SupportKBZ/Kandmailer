<?php

use KandMailer\MailerClient;

beforeEach(function () {
    $this->mailer = createMailer();
});

describe('Constructor', function () {
    it('sets api key and endpoint', function () {
        expect($this->mailer->getApiKey())->toBe('test-api-key');
        expect($this->mailer->getEndpoint())->toBe('https://api.exemple.com');
    });

    it('trims trailing slash from endpoint', function () {
        $mailer = new MailerClient('key', 'https://api.exemple.com/');
        expect($mailer->getEndpoint())->toBe('https://api.exemple.com');
    });
});

describe('Fluent Setters', function () {
    it('sets template', function () {
        $this->mailer->template('welcome');
        expect($this->mailer->getTemplate())->toBe('welcome');
    });

    it('sets firstName and lastName', function () {
        $this->mailer->firstName('John')->lastName('Doe');
        expect($this->mailer->getFirstName())->toBe('John');
        expect($this->mailer->getLastName())->toBe('Doe');
    });

    it('sets scenario', function () {
        $this->mailer->scenario('inscription');
        expect($this->mailer->getScenario())->toBe('inscription');
    });

    it('sets accountId', function () {
        $this->mailer->accountId('12345');
        expect($this->mailer->getAccountId())->toBe('12345');
    });

    it('returns self for chaining', function () {
        $result = $this->mailer
            ->template('welcome')
            ->firstName('John')
            ->lastName('Doe');

        expect($result)->toBeInstanceOf(MailerClient::class);
    });
});

describe('Recipients', function () {
    it('sets single email with toEmail()', function () {
        $this->mailer->toEmail('john@example.com');

        expect($this->mailer->getEmail())->toBe('john@example.com');
    });

    it('sets multiple emails with toEmail()', function () {
        $this->mailer->toEmail(['john@example.com', 'jane@example.com']);

        expect($this->mailer->getEmail())->toBeArray();
        expect($this->mailer->getEmail())->toHaveCount(2);
        expect($this->mailer->getEmail())->toBe(['john@example.com', 'jane@example.com']);
    });

    it('sets single phone with toPhone()', function () {
        $this->mailer->toPhone('+33612345678');

        expect($this->mailer->getPhone())->toBe('+33612345678');
    });

    it('sets multiple phones with toPhone()', function () {
        $this->mailer->toPhone(['+33612345678', '+33698765432']);

        expect($this->mailer->getPhone())->toBeArray();
        expect($this->mailer->getPhone())->toHaveCount(2);
        expect($this->mailer->getPhone())->toBe(['+33612345678', '+33698765432']);
    });

    it('validates email format', function () {
        expect(fn() => $this->mailer->toEmail('invalid-email'))
            ->toThrow(InvalidArgumentException::class, 'Email invalide: invalid-email');
    });

    it('validates phone format', function () {
        expect(fn() => $this->mailer->toPhone('123'))
            ->toThrow(InvalidArgumentException::class, 'Numéro de téléphone invalide: 123');
    });
});

describe('Options', function () {
    it('sets single option', function () {
        $this->mailer->option('lang', 'fr');

        expect($this->mailer->getOption('lang'))->toBe('fr');
    });

    it('sets multiple options', function () {
        $this->mailer->option('lang', 'fr')->option('priority', 'high');

        expect($this->mailer->getOptions())->toBe([
            'lang' => 'fr',
            'priority' => 'high'
        ]);
    });

    it('sets all options at once', function () {
        $this->mailer->options(['lang' => 'fr', 'priority' => 'high']);

        expect($this->mailer->getOptions())->toBe([
            'lang' => 'fr',
            'priority' => 'high'
        ]);
    });

    it('returns default when option not found', function () {
        expect($this->mailer->getOption('nonexistent'))->toBeNull();
        expect($this->mailer->getOption('nonexistent', 'default'))->toBe('default');
    });
});

describe('Date', function () {
    it('sets createdAt', function () {
        $date = new DateTime('2024-01-15 10:30:00');
        $this->mailer->createdAt($date);

        expect($this->mailer->getCreatedAt())->toBe($date);
    });
});

describe('Payload', function () {
    it('generates payload with set fields only', function () {
        $this->mailer
            ->template('welcome')
            ->toEmail('john@example.com')
            ->firstName('John');

        $payload = $this->mailer->getPayload();

        expect($payload)->toHaveKey('template', 'welcome');
        expect($payload)->toHaveKey('firstName', 'John');
        expect($payload)->toHaveKey('email');
        expect($payload)->not->toHaveKey('lastName');
        expect($payload)->not->toHaveKey('scenario');
    });

    it('formats date correctly', function () {
        $date = new DateTime('2024-01-15T10:30:00+00:00');
        $this->mailer->createdAt($date);

        $payload = $this->mailer->getPayload();

        expect($payload['created_at'])->toBe('2024-01-15T10:30:00+00:00');
    });

    it('includes options when not empty', function () {
        $this->mailer->option('lang', 'fr');

        $payload = $this->mailer->getPayload();

        expect($payload)->toHaveKey('options');
        expect($payload['options'])->toBe(['lang' => 'fr']);
    });
});

describe('Reset', function () {
    it('clears all values except apiKey and endpoint', function () {
        $this->mailer
            ->template('welcome')
            ->firstName('John')
            ->option('lang', 'fr')
            ->reset();

        expect($this->mailer->getTemplate())->toBeNull();
        expect($this->mailer->getFirstName())->toBeNull();
        expect($this->mailer->getOptions())->toBe([]);

        // apiKey et endpoint conservés
        expect($this->mailer->getApiKey())->toBe('test-api-key');
        expect($this->mailer->getEndpoint())->toBe('https://api.exemple.com');
    });
});