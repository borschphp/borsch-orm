# Borsch ORM

A framework agnostic Object Relational Model implementation, based on [Laminas-Db](https://docs.laminas.dev/laminas-db/), for Borsch Framework.

## Installation

Via [composer](https://getcomposer.org/) :

`composer require borschphp/orm`

## Integration in Borsch Framework

Create a new file `config/database.php` file then add this content inside :

```php
use Borsch\Container\Container;
use Borsch\Db\Db;
use Laminas\Db\Adapter\AdapterInterface;

/**
 * Setup the database informations into the Borsch\Db\Db class.
 * It will be used later to deal with models.
 *
 * @param Container $container
 */
return function (Container $container): void {
    Db::addConnection($container->get(AdapterInterface::class), 'default');
};
```

Open the file `config/container.php` then add your AdapterInterface (=database connection) definition.  
Example _(we've set some values in .env before)_ :

```php
/*
 * Database definitions
 * --------------------
 *
 * Borsch uses the laminas-db package, please check it out for more information :
 *     https://docs.laminas.dev/laminas-db/adapter/
 * You can update the database information in the .env file.
 */
$container->set(AdapterInterface::class, function () {
    return new Adapter([
        'driver'   => env('DB_DRIVER'),
        'database' => env('DB_NAME'),
        'username' => env('DB_USER'),
        'password' => env('DB_PWD'),
        'hostname' => env('DB_HOST'),
        'port' => env('DB_PORT'),
        'charset' => env('DB_CHARSET'),
        'driver_options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_general_ci'
        ]
    ]);
})->cache(true);
```

Open the file `public/index.php`, then load your database settings, below the container instance :

```php
/** @var ContainerInterface $container */
$container = (require_once __DIR__.'/../config/container.php');

(require_once __DIR__.'/../config/database.php')($container); // <-- Here
```

Done :tada: !

## Usage

Create a class representing a table in your database which extends `Borsch\ORM\Model`.

```php
use Borsch\ORM\Model;

/**
 * Class Album
 *
 * @property int $id
 * @property string $artist
 * @property string $title
 * @property string $artist_title
 * @property string $created_at
 * @property string $updated_at
 */
class Album extends Model
{

    protected $artist;
    protected $title;

    public function getCreatedAtProperty($date): DateTimeInterface
    {
        return new DateTime($date, new DateTimeZone('Europe/Paris'));
    }

    public function getArtistTitleProperty()
    {
        return sprintf('%s: %s', $this->artist, $this->title);
    }

    public function setUpdatedAtProperty($value)
    {
        if (is_numeric($value)) {
            $value = date('Y-m-d H:i:s', $value);
        } elseif ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d H:i:s');
        }

        $this->updated_at = $value;
    }
}
```

You can create **accessors** and **mutators** for your class properties (_getCreatedAtProperty_, _setUpdatedAtProperty_).  
It is also possible to create accessors for no properties (_getArtistTitleProperty_).

Your class $id, $created_at and $updated are already set in `Borsch\ORM\Model`, you do not need to define them.

## Active Records

You can easily create or update element in your database in an Active Record way :

```php
$album = new Album();
$album->artist = 'Muse';
$album->title = 'The Resistance';
$album->save(); // Saved

$album = Album::where(['title' => 'The Resistance', 'artist' => 'Muse'])->first();
$album->title = 'Absolution';
$album->save(); // Updated

$album->delete(); // Deleted
```

## Query Builder

As said, the package is based on [Laminas-Db](https://docs.laminas.dev/laminas-db/), therefore you can use the fluent [SQL Abstraction](https://docs.laminas.dev/laminas-db/sql/) to fetch your data.

## License

The package is licensed under the MIT license. See [License File](https://github.com/borschphp/orm/blob/master/LICENSE.md) for more information.