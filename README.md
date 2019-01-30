[![Build Status](https://travis-ci.org/Seacommerce/php-mapper.svg?branch=master)](https://travis-ci.org/Seacommerce/php-mapper)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

# Seacommerce Mapper
A mapper for PHP to map data between types that's optimized for
performance and validation.

## Features
- Mapping from type to type.
- Automapping of matching properties, custom from->to, custom callbacks.
- Ignore properties.
- Validation (prevent unmapped properties).
- Compiled (using symfony/property-accessor for now, will add native compiler) 

## Why another mapper?
You're might wonder why this library exists since there are already 
at least three similar mapper libraries for PHP:

- [mark-gerarts/automapper-plus](https://github.com/mark-gerarts/automapper-plus)
- [janephp/automapper](https://github.com/janephp/janephp)
- [idr0id/papper](https://github.com/idr0id/Papper)

Although all of these libraries provide their own specific 
optimisations and features, none of them provided validation 
and compilation.

### mark-gerarts/automapper-plus
Pro's
- Highly configurable.
- Actively maintained.
- Good documentation.
- Plays well with Symphony (mark-gerarts/automapper-plus-bundle)

Con's
- No way to validate the mapping (not convention based).
- Not compiled (which isn't an issue if you use custom mappers but that
kinda defeats the purpose of the library).

### janephp/automapper
Pro's
- Compiled.
- Highly configurable.
- No validation.
- Not very actively maintained.
- Plays well with Symphony (bundle included).

Con's
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