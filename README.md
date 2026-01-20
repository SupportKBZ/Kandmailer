# KandMailer PHP

![PHP Version](https://img.shields.io/badge/PHP-8.4%2B-blue)
![License](https://img.shields.io/badge/license-MIT-green)
![Tests](https://img.shields.io/badge/tests-Pest-orange)

Client PHP léger pour KandMailer

## Installation

```bash
composer require supportkbz/kandmailer
```

## Usage
### Config
```php

use KandMailer\MailerClient;

$client = new MailerClient('your_api_key', 'https://exemple.com');

```

### Tips
#### Send
```php
// Set template (required for sending messages)
$client->template('welcome_email');

// Set single
$client->email('john@example.com');
$client->phone('+33612345678');
$client->option('lang', 'en');

// Set multiple
$client->email(['john@example.com', 'jane@example.com']);
$client->phone(['+33612345678', '+33612345679']);
$client->firstName(['John', 'Jane']);
$client->lastName(['Doe', 'White']);
$client->multiOptions([['lang' => 'en', 'priority' => 'high'],['lang' => 'fr', 'priority' => 'low']]);

// Call
$client->sendMultiple();

// Chaining
$client->template('welcome_email')
       ->email('john@example.com')
       ->options(['lang' => 'en', 'priority' => 'high'])
       ->sendSingle();
```

> **⚠️ Important : Gestion des options**
> 
> - **`options()`** : À utiliser avec `sendSingle()` pour définir les options d'un seul destinataire
> - **`multiOptions()`** : À utiliser exclusivement avec `sendMultiple()` pour définir des options spécifiques à chaque destinataire
> 
> ❌ **Ne pas faire** : `$client->multiOptions([...])->sendSingle()` - Cela générera une erreur
> 
> ✅ **Correct** :
> ```php
> // Pour un envoi unique
> $client->email('john@example.com')->options(['lang' => 'en'])->sendSingle();
> 
> // Pour plusieurs envois avec options différentes
> $client->email(['john@example.com', 'jane@example.com'])
>        ->multiOptions([['lang' => 'en'], ['lang' => 'fr']])
>        ->sendMultiple();
> ```

#### Add
```php
// Set value
$client->scenario('welcome_scenario');
$client->firstName('John');
$client->lastName('Doe');
$client->email('john@example.com');
$client->phone('+33612345678');
$client->accountId('12345');

// Options, remove, exists
$client->options(['lang' => 'en']);
$client->setRemove(['old_tag']);
$client->setExists(['check_tag']);

// Call
$client->add();

// Chaining
$client->scenario('welcome')
       ->firstName('John')
       ->lastName('Doe')
       ->email('john@example.com')
       ->phone('+33612345678')
       ->add();
```

#### Remove
```php
// Set scenario (required) and email
$client->scenario('welcome_scenario');
$client->email('john@example.com');
$client->remove();

// Set scenario (required) and phone
$client->scenario('welcome_scenario');
$client->phone('+33612345678');
$client->remove();

// Chaining
$client->scenario('welcome_scenario')->email('john@example.com')->remove();
```

#### Méthodes avancées

##### Reset - Réutiliser le client
```php
// Envoyer à plusieurs destinataires avec la même instance
$client->template('newsletter')
       ->email('john@example.com')
       ->options(['lang' => 'en'])
       ->sendSingle();

// Réinitialiser pour un nouvel envoi
$client->reset();

$client->template('newsletter')
       ->email('jane@example.com')
       ->options(['lang' => 'fr'])
       ->sendSingle();
```

##### CreatedAt - Définir une date personnalisée
```php
// Définir une date de création pour le contact
$client->scenario('welcome')
       ->email('john@example.com')
       ->createdAt(new \DateTime('2024-01-15'))
       ->add();

// Avec DateTimeImmutable
$client->scenario('welcome')
       ->email('jane@example.com')
       ->createdAt(new \DateTimeImmutable('2024-01-15 10:30:00'))
       ->add();
```

### Tests
Ce package inclut une suite de tests complète utilisant Pest.

```bash
# Lancer tous les tests
composer test
```

### Exigences
- PHP 8.4+
- Extension `curl`

### Licence
MIT. Voir le fichier `LICENSE`.