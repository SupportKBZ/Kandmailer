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

#### Send - Envoi de messages

Utilisez des objets `Recipient` pour une meilleure flexibilité et type-safety :

```php
use KandMailer\Models\Recipient;

// Envoi simple
$client->template('welcome_email')
       ->sendTo(new Recipient(
           email: 'john@example.com',
           firstName: 'John',
           lastName: 'Doe',
           options: ['lang' => 'en', 'priority' => 'high']
       ));

// Envoi multiple avec des options différentes pour chaque destinataire
$recipients = [
    new Recipient(
        email: 'john@example.com',
        firstName: 'John',
        options: ['lang' => 'en', 'crm' => '111']
    ),
    new Recipient(
        email: 'jane@example.com',
        firstName: 'Jane',
        options: ['lang' => 'fr', 'crm' => '222']
    ),
    new Recipient(
        phone: '+33612345678',
        firstName: 'Bob',
        options: ['lang' => 'en', 'crm' => '333']
    ),
];

$client->template('welcome_email')
       ->sendToMultiple($recipients);
```

**Avantages :**
- ✅ Code clair et moins sujet aux erreurs
- ✅ Chaque destinataire a ses propres options indépendantes
- ✅ Validation automatique au niveau de chaque destinataire
- ✅ Excellente compatibilité avec les IDE (autocomplétion)

#### Add - Approche Orientée Objet (Recommandée) ✨

```php
use KandMailer\Models\Recipient;

// Ajout d'un contact
$client->addTo(new Recipient(
    email: 'john@example.com',
    firstName: 'John',
    lastName: 'Doe',
    scenario: 'welcome_sequence',
    options: ['lang' => 'en', 'source' => 'website']
));
```

#### Add - Approche Classique (Toujours supportée)

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

#### Remove - Approche Orientée Objet (Recommandée) ✨

```php
// Suppression d'un contact
$client->removeFrom(new Recipient(
    email: 'john@example.com',
    scenario: 'welcome_sequence'
));
```

#### Remove - Approche Classique (Toujours supportée)

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

#### Objet Recipient - Propriétés disponibles

```php
use KandMailer\Models\Recipient;

$recipient = new Recipient(
    email: 'john@example.com',           // Email du destinataire (optionnel si phone fourni)
    phone: '+33612345678',                // Téléphone du destinataire (optionnel si email fourni)
    firstName: 'John',                    // Prénom (optionnel)
    lastName: 'Doe',                      // Nom (optionnel)
    options: [                            // Options personnalisées (optionnel)
        'lang' => 'en',
        'crm' => '123456',
        'customField' => 'value'
    ],
    scenario: 'welcome',                  // Scénario spécifique (optionnel)
    accountId: 'acc-123',                 // ID compte (optionnel)
    createdAt: new \DateTime('2024-01-15') // Date de création (optionnel)
);

// Créer à partir d'un array
$recipient = Recipient::fromArray([
    'email' => 'john@example.com',
    'firstName' => 'John',
    'options' => ['lang' => 'en']
]);

// Convertir en array
$array = $recipient->toArray();
```

**Priorité des valeurs :**
- Les valeurs définies dans le `Recipient` ont la priorité sur celles du client
- Les options sont fusionnées (options du `Recipient` > options du client)
- Si le `Recipient` n'a pas de valeur, celle du client est utilisée

```php
// Exemple de fusion des options
$client->template('welcome')
       ->option('globalKey', 'globalValue')  // Option globale pour tous
       ->option('lang', 'fr');               // Sera surchargé

$client->sendTo(new Recipient(
    email: 'john@example.com',
    options: ['lang' => 'en', 'customKey' => 'customValue']
));

// Résultat : options = ['globalKey' => 'globalValue', 'lang' => 'en', 'customKey' => 'customValue']
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