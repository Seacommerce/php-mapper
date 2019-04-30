[![Build Status](https://travis-ci.org/Seacommerce/php-mapper.svg?branch=master)](https://travis-ci.org/Seacommerce/php-mapper)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

# Seacommerce Mapper
A mapper for PHP to map data between objects that's optimized for performance and validation.

## Features
- Mapping from type to type.
- Automapping of matching properties, custom from->to, custom callbacks.
- Ignore properties.
- Validation (prevent unmapped properties).
- Compiled (using symfony/property-accessor for now, will add native compiler) 

## Installation
```bash
composer require seacommerce/mapper
```

## Why an automapper?
Having to deal with a big variety of data objects throughout the layers in your application, often requires a lot of copying of data between those data objects. Usually you find yourself calling a bunch of getters and setters in a row to go from one object to another and then again to go to the next layer (for example: Doctrine Entity > Domain object > Form/View and back again). This it typically where an automapper can save you a lot of manual coding by automatically mapping properties with a similar name for example. 

For example, mapping between the following two data structures
```php
class A
{
  private $id;
  private $name;
  private $desc;
  private $day;
  private $type;
  private $active;
  
  // Getters & Setters
}

class B
{
  private $id;
  private $name;
  private $type
  private $active
  
  
  // Getters & Setters
}
```

Could be reduced from 
```php
$b = new B();
$b->setId($a->getId());
$b->setName($a->getName());
$b->setType($a->getType());
$b->setIsActive($a->getIsActive());
```

To just
```php
$b = $mapper->map($a, B::class);
```


## Why another mapper?
You might be wondering why this library exists since there are already (at least) three similar mapper libraries for PHP:

- [mark-gerarts/automapper-plus](https://github.com/mark-gerarts/automapper-plus)
- [janephp/automapper](https://github.com/janephp/janephp)
- [idr0id/papper](https://github.com/idr0id/Papper)

Although all of these libraries have their own strengths and features, none of them provide the features that I like to see in a mapper which are mainly validation and compilation (= performance).

### Validation
Automatically mapping properties between objects removes a lot of manual work but does not make the process less prone to human errors. Typically, automappers map properties on a "best-effort" basis by only automatically mapping properties that exists on both sides. Non-matching properties on either side still need some manual configuration or will otherwise be simply ignored.

PHP does not have a language construct to point to classes and member names (like ``` nameof``` in C#) and therefore, configuring a mapping manually usualy involves refering to properties using their name in a string value. E.g.

```php
$mapper
  ->forMember('id', Operation::mapFrom('prodcutId'))
  ->formember('dateCreated', Operation::ignore());
```

This is where bugs enter your system if there is no check on the existence of the properties or will at least cause you some headaches if you don't detect the typo in an early stage.

Validation is an early warning system that will reduce the headachs when dealing with mappings by checking the existence of the properties and the types ahead of time.

### Compilation
PHP provides multiple ways to read and write properties of an object:
- public fields,
- private fields with getter/setter methods
- array accessors
- magic methods \_\_get() and \_\_set()

An automapper would kinda lose it's value if we had to tell it what read/write method to use on the mapped objects so therefore, part of the "auto" in automapper includes some magic to figure out the right way to get/set the properties.

This magic comes with a performance price. Symfony provides the PropertyAccess component that makes it easier and faster (due to caching) but compiling the mapping to native php code with native performance would be better.

### mark-gerarts/automapper-plus
Pro's
- Highly configurable.
- Actively maintained.
- Good documentation.
- Plays well with Symfony though a separate bundle (mark-gerarts/automapper-plus-bundle)

Con's
- No way to validate the mapping (not convention based).
- Not compiled (which isn't an issue if you use custom mappers but that
kinda defeats the purpose of an auto mapper library).

### janephp/automapper
Pro's
- Compiled.
- Highly configurable.
- Plays well with Symphony (bundle included).

Con's
- Not very actively maintained.
- No way to validate the mapping.

### idr0id/papper
Pro's
- Mapping validation, yay!

Con's
- Not actively maintained, latest build fails.
- No documentation.
- No symfony bundle.

## Examples

```php
sdfsf
```
