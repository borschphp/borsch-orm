# Borsch ORM

A framework agnostic Object Relational Model implementation, based on [Laminas-Db](https://docs.laminas.dev/laminas-db/), for Borsch Framework.

## Installation

Via [composer](https://getcomposer.org/) :

`composer require borschphp/orm`

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