# Laravel Eloquent Casts

**Advanced Eloquent casts for Laravel including Money and JSON handling**

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-12.x%20%7C%2013.x%20%7C%2014.x%20%7C%2015.x-blue)](https://laravel.com)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

---

## Installation

```bash
composer require andydefer/laravel-eloquent-casts
```

### Prérequis

- PHP 8.1 ou supérieur
- Laravel 12.x, 13.x, 14.x ou 15.x

---

## Casts disponibles

| Cast | Description | Stockage DB | Affichage |
|------|-------------|-------------|-----------|
| `MoneyCast` | Montants monétaires | Integer (centimes) | Float (euros/dollars) |
| `JsonCast` | Données structurées | JSON string | PHP array |

---

## MoneyCast

Le `MoneyCast` convertit automatiquement les montants monétaires entre les centimes (stockage en base de données) et les euros/dollars (affichage dans l'application).

### Pourquoi ?

Stockez vos prix en centimes (entiers) pour éviter les problèmes de précision des nombres flottants en base de données.

### Utilisation

```php
<?php

namespace App\Models;

use AndyDefer\LaravelCasts\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $casts = [
        'price' => MoneyCast::class,
    ];
}
```

### Exemple

```php
// Création d'un produit
$product = new Product();
$product->price = 12.34;  // Stocké comme 1234 en base

// Récupération
echo $product->price;      // Affiche 12.34

// Valeurs nulles
$product->price = null;    // Stocké comme NULL en base
```

### Gestion des arrondis

```php
$product->price = 12.345;  // Stocké comme 1235 (arrondi)
$product->price = 0.055;   // Stocké comme 6 (arrondi supérieur)
```

### Valeurs négatives

```php
$product->price = -5.00;   // Stocké comme -500
$product->price = -0.50;   // Stocké comme -50
```

### Requêtes

```php
// Recherche par prix exact (en centimes)
Product::where('price', 1234)->get();  // Prix = 12.34
Product::where('price', '>', 1000)->get();  // Prix > 10.00
```

---

## JsonCast

Le `JsonCast` convertit automatiquement les chaînes JSON en tableaux PHP et vice-versa.

### Pourquoi ?

Manipulez naturellement des données JSON en PHP sans avoir à gérer manuellement l'encodage/décodage.

### Utilisation

```php
<?php

namespace App\Models;

use AndyDefer\LaravelCasts\Casts\JsonCast;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $casts = [
        'preferences' => JsonCast::class,
    ];
}
```

### Exemple

```php
// Création d'un utilisateur
$user = new User();
$user->preferences = [
    'theme' => 'dark',
    'notifications' => true,
    'language' => 'fr'
];
$user->save();  // Stocké comme '{"theme":"dark","notifications":true,"language":"fr"}'

// Récupération
$preferences = $user->preferences;
echo $preferences['theme'];  // Affiche 'dark'

// Valeurs nulles
$user->preferences = null;   // Stocké comme NULL en base
```

### Conservation des JSON existants

Si vous stockez déjà une chaîne JSON valide, elle est préservée :

```php
$user->preferences = '{"theme":"light"}';  // Stocké tel quel
```

### Gestion des erreurs

Les JSON invalides retournent `null` au lieu de planter :

```php
$user->preferences = '{invalid json';  // Retourne null
```

### Décodage non-tableau

Si un JSON décodé n'est pas un tableau, un tableau vide est retourné :

```php
$user->preferences = '"just a string"';  // Retourne []
```

---

## Installation dans un modèle existant

### Migration avec MoneyCast

```php
// Création d'une colonne pour MoneyCast
Schema::table('products', function (Blueprint $table) {
    $table->integer('price')->nullable();  // Stocke les centimes
});
```

### Migration avec JsonCast

```php
// Création d'une colonne pour JsonCast
Schema::table('users', function (Blueprint $table) {
    $table->json('preferences')->nullable();  // Type JSON recommandé
    // ou
    $table->text('preferences')->nullable();  // Alternative pour vieilles versions MySQL
});
```

---

## Tests

Le package inclut une suite complète de tests unitaires.

### Exécution des tests

```bash
composer test
```

### Couverture des tests MoneyCast

| Test | Description |
|------|-------------|
| `test_get_converts_cents_to_euros_with_two_decimals` | Conversion centimes → euros |
| `test_get_rounds_cents_correctly` | Arrondi des centimes |
| `test_get_handles_large_amounts` | Grands montants |
| `test_get_handles_negative_amounts` | Valeurs négatives |
| `test_get_returns_null_when_value_is_null` | Valeurs nulles |
| `test_set_converts_euros_to_cents` | Conversion euros → centimes |
| `test_set_rounds_cents_correctly` | Arrondi des euros |
| `test_null_values_are_preserved_through_round_trip` | Préservation des nulls |

### Couverture des tests JsonCast

| Test | Description |
|------|-------------|
| `test_get_returns_null_when_value_is_null` | Valeurs nulles |
| `test_get_decodes_valid_json_string_to_array` | Décodage JSON valide |
| `test_get_returns_null_when_json_string_is_invalid` | JSON invalide |
| `test_set_converts_array_to_json_string` | Conversion tableau → JSON |
| `test_set_throws_json_exception_for_non_encodable_values` | Valeurs non encodables |

---

## Exemples complets

### E-commerce : Gestion des prix

```php
<?php

namespace App\Models;

use AndyDefer\LaravelCasts\Casts\MoneyCast;
use AndyDefer\LaravelCasts\Casts\JsonCast;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $casts = [
        'subtotal' => MoneyCast::class,
        'tax' => MoneyCast::class,
        'total' => MoneyCast::class,
        'shipping_address' => JsonCast::class,
        'items' => JsonCast::class,
    ];
}

// Utilisation
$order = Order::create([
    'subtotal' => 99.99,
    'tax' => 20.00,
    'total' => 119.99,
    'shipping_address' => [
        'street' => '123 Main St',
        'city' => 'Paris',
        'zip' => '75001'
    ],
    'items' => [
        ['id' => 1, 'name' => 'Product A', 'price' => 49.99, 'quantity' => 2],
        ['id' => 2, 'name' => 'Product B', 'price' => 50.00, 'quantity' => 1],
    ]
]);

// Requêtes
$expensiveOrders = Order::where('total', '>', 10000)->get();  // > 100.00 €
```

### Configuration utilisateur avancée

```php
<?php

namespace App\Models;

use AndyDefer\LaravelCasts\Casts\JsonCast;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $casts = [
        'settings' => JsonCast::class,
        'metadata' => JsonCast::class,
    ];
    
    // Accesseur avec valeur par défaut
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }
    
    // Mutateur pour mise à jour partielle
    public function setSetting(string $key, mixed $value): self
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        return $this;
    }
}

// Utilisation
$user = User::find(1);
$user->setSetting('theme', 'dark');
$user->setSetting('notifications', ['email' => true, 'sms' => false]);
$user->save();

echo $user->getSetting('theme', 'light');  // 'dark'
```

### Inventaire avec attributs variables

```php
<?php

namespace App\Models;

use AndyDefer\LaravelCasts\Casts\MoneyCast;
use AndyDefer\LaravelCasts\Casts\JsonCast;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $casts = [
        'price' => MoneyCast::class,
        'attributes' => JsonCast::class,
    ];
    
    // Scope pour filtrage JSON
    public function scopeWithAttribute($query, string $key, mixed $value)
    {
        return $query->whereRaw("JSON_EXTRACT(attributes, '$.{$key}') = ?", [$value]);
    }
}

// Utilisation
$product = Product::create([
    'name' => 'T-Shirt',
    'price' => 29.99,
    'attributes' => [
        'color' => 'blue',
        'size' => 'L',
        'material' => 'cotton',
        'stock' => 50
    ]
]);

// Recherche par attribut JSON
$blueTshirts = Product::withAttribute('color', 'blue')->get();

// Mise à jour partielle
$product = Product::find(1);
$attributes = $product->attributes;
$attributes['stock'] = 45;
$product->attributes = $attributes;
$product->save();
```

---

## Performance

| Cast | Opération | Impact |
|------|-----------|--------|
| `MoneyCast` | Conversion int ↔ float | Minime (opérations mathématiques simples) |
| `JsonCast` | Encodage/décodage JSON | Modéré (dépend de la taille des données) |

---

## API Reference

### MoneyCast

| Méthode | Type | Description |
|---------|------|-------------|
| `get(Model $model, string $key, mixed $value, array $attributes)` | `?float` | Convertit les centimes → euros |
| `set(Model $model, string $key, mixed $value, array $attributes)` | `?int` | Convertit les euros → centimes |

### JsonCast

| Méthode | Type | Description |
|---------|------|-------------|
| `get(Model $model, string $key, mixed $value, array $attributes)` | `?array` | JSON → tableau PHP |
| `set(Model $model, string $key, mixed $value, array $attributes)` | `?string` | Tableau PHP → JSON |

---

## Limitations

### MoneyCast
- Précision fixe à 2 décimales (euros/cents)
- Ne gère pas les devises avec plus de 2 décimales (ex: Koweït Dinar)
- Valeur max : ~21 millions d'euros avec stockage sur 32 bits

### JsonCast
- La taille des JSON est limitée par MySQL (max 1 Go pour type JSON)
- Les tableaux très profonds (> 512 niveaux) sont tronqués
- Les ressources PHP ne peuvent pas être encodées (exception levée)

---

## Contribuer

1. Fork le repository
2. Créer une branche (`git checkout -b feature/amazing-feature`)
3. Commiter les changements (`git commit -m 'feat: add amazing feature'`)
4. Pusher (`git push origin feature/amazing-feature`)
5. Ouvrir une Pull Request

### Développement

```bash
# Installer les dépendances
composer install

# Exécuter les tests
composer test

# Vérifier le code (si pint installé)
./vendor/bin/pint
```

---

## Licence

MIT © [Andy Defer](https://github.com/andydefer)
```