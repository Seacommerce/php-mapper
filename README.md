[![Build Status](https://travis-ci.org/Seacommerce/php-mapper.svg?branch=master)](https://travis-ci.org/Seacommerce/php-mapper)

# Seacommerce Mapper

A mapper for PHP to map data between types that's optimized for
performance and validation.

## Why another mapper?
You're might wonder why this library exists since there are already 
at least three similar mapper libraries for PHP:

- mark-gerarts/automapper-plus
- janephp/automapper
- idr0id/papper

Although all of these libraries provide their own specific 
optimisations and features, none of them provided ALL the features
that I need.

### mark-gerarts/automapper-plus
Pro's
- Highly configurable.
- Actively maintained.
- Good documentation.
- Plays well with Symphony (mark-gerarts/automapper-plus-bundle)

Con's
- No way to validate the mapping.
- Not compiled (which isn't an issue if you use custom mappers but that
kinda defeats the purpose).

