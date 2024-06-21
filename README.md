<p align="center"><a href="https://github.com/beebmx/kirby-patrol" target="_blank" rel="noopener"><img src="https://github.com/beebmx/kirby-patrol/blob/main/assets/logo.svg?raw=true" width="125" alt="Kirby Patrol Logo"></a></p>

<p align="center">
<a href="https://github.com/beebmx/kirby-patrol/actions"><img src="https://img.shields.io/github/actions/workflow/status/beebmx/kirby-patrol/tests.yml?branch=main" alt="Build Status"></a>
<a href="https://packagist.org/packages/beebmx/kirby-patrol"><img src="https://img.shields.io/packagist/dt/beebmx/kirby-patrol" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/beebmx/kirby-patrol"><img src="https://img.shields.io/packagist/v/beebmx/kirby-patrol" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/beebmx/kirby-patrol"><img src="https://img.shields.io/packagist/l/beebmx/kirby-patrol" alt="License"></a>
</p>

# Kirby Patrol

An easy and customizable way to manage access to website pages according to the roles assigned to users within the Kirby panel interface.

****

## Overview

- [1. Installation](#installation)
- [2. Usage](#usage)
- [3. Options](#options)
- [4. Roadmap](#roadmap)
- [5. License](#license)
- [6. Credits](#credits)

## Installation

### Download

Download and copy this repository to `/site/plugins/kirby-patrol`.

### Composer

```
composer require beebmx/kirby-patrol
```

## Usage

Out of the box, you don't need to do anything to start using (except for installation),
but if you require customizing the default behavior, there are some options to personalize `Kirby Patrol`.

### Panel

All users with panel access will see the new area and will be able to update the access to the pages on the website.

If you need to restrict this behavior, you can do so by adding the permission in the user YAML file:

```yaml
title: Editor

permissions:
  beebmx.kirby-patrol:
    access: false
```

> [!NOTE]
> The `access` is set to `true` by default

The pages displayed in the new panel area will be all the `site` childrens published (with status `listed` and `unlisted`)
and two levels inside every page. If you need a specific collection of pages you can change it with the `query` option
in your `config.php` file:

```php
use Kirby\Cms\App;
use Kirby\Cms\Pages;
use Kirby\Cms\Site;

'beebmx.kirby-patrol' => [
    'content' => [
        'query' => function (Site $site, Pages $pages, App $kirby) {
            return $site->find('secure-page')->children()->listed();
        },
    ],
],
```

And if you need to update the `depth` of the pages displayed, update the `config.php` file:

```php
'beebmx.kirby-patrol' => [
    'content' => [
        'depth' => 3,
    ],
],
```

Here's an example of `Kirby Patrol` view page:

![Patrol panel example](https://raw.githubusercontent.com/beebmx/kirby-patrol/main/assets/patrol-panel.png)

### Frontend

When a logged-in user visits any page, `Kirby Patrol` will automatically validate the request. If the user has access
to the visited page, they can normally view the content, but if not, an error page will be thrown with a `401` status code.

> [!WARNING]
> It's important that you use a logged-in user when the validation occurs;
> otherwise, an error will be thrown. If the default

### Middleware

Even when `Kirby Patrol` tries to validate a user, it's possible that behavior won't be enough for your own validation.
In that case, you can customize and add additional restrictions to every page.

#### Closure middleware

The easyest way to add additional validation is with `Closures`.
Added this in the `config.php` file:

```php
use Kirby\Http\Response;

'beebmx.kirby-patrol' => [
    'permissions' => [
        'middleware' => [
            function (array $data, Closure $next) {
                if($data['page']->is('secure-page')) {
                    return Response::redirect('login')
                }

                return $next($data);
            },
        ],
    ],
],
```

As you can see, the `Closure` requires two parameters: an `array` called `$data` and a `Closure` called `$next`.
The `$data` array contains at least four elements: `$data['kirby']`, `$data['site']`, `$data['pages']`, and `$data['page']`.
Those four elements come from a `Kirby Hook`, and you can use them for your own convenience.

The second parameter `$next`, you should call it at the end of the process to proceed to the next validation with the `$data`.

> [!NOTE]
> You can return a `Response::class` object. When you do that, `Kirby Patrol` will automatically send the request.

#### Class middleware

If your own validation is more complex for a simple `Closure`, you can use a custom class for that purpose:

```php
'beebmx.kirby-patrol' => [
    'permissions' => [
        'middleware' => [
            MyCustomMiddleware::class,
        ],
    ],
],
```

And your class should look like:

```php
use Closure;
use Kirby\Cms\App as Kirby;
use Kirby\Exception\ErrorPageException;
use Beebmx\KirbyPatrol\Middleware\Middleware;

class MyCustomMiddleware extends Middleware
{
    public function handle(array $data, Closure $next)
    {
        $kirby = Kirby::instance();

        if ($kirby->site()->isDisabled()->toBool()) {
            return throw new ErrorPageException([
                'fallback' => 'Unauthorized',
                'httpCode' => 401,
            ]);
        }

        return $next($data);
    }
}
```

Your middleware logic should be inside the `handle` method; otherwise, the middleware will never be triggered.

> [!NOTE]
> You can throw an exception `ErrorPageException::class` with your custom data in case you need it.

### Redirection

Sometimes you don't need an error in your website to display an error, in that cases you can make a redireccion:

```php
'beebmx.kirby-patrol' => [
    'permissions' => [
        'redirect' => 'login',
    ],
],
```

As you can see, when a redirection is set, you don't need to customize an extra `middleware`.

### Utilities

You have utilities available to incorporate into your existing workflow:

#### User utilities

If you want to validate if a `user` has access to a given Page:

```php
user()->can($page)
```

> [!NOTE]
> Page can be a `string` or a `Kirby\Cms\Page` object


If you want to retrieve all the pages with access or without access

```php
user()->patrol(bool)
```

> [!NOTE]
> A `true` value returns all pages with access.
> A `false` value returns all pages without access.

#### Pages utility

If you want to know if a `pages` collection have access or not:

```php
pages()->patrol(bool)
```

> [!NOTE]
> A `true` value returns all pages with access.
> A `false` value returns all pages without access.

## Options

| Option                                     | Default |    Type    | Description                                                                                                       |
|:-------------------------------------------|:-------:|:----------:|:------------------------------------------------------------------------------------------------------------------|
| beebmx.kirby-patrol.enabled                |  true   |   `bool`   | Enable access in `Kirby Panel`                                                                                    |
| beebmx.kirby-patrol.icon                   | keyhole |  `string`  | Icon displayed in `Kirby Panel`. Options available are: `flash` `keyhole` `police` `shield` `siren` `star` `user` |
| beebmx.kirby-patrol.name                   | Patrol  |  `string`  | Set a `string` to display in the `Kirby Panel`.                                                                   |
| beebmx.kirby-patrol.content.columns        |    4    |   `int`    | Set how many `columns` will be displayed into the `Kirby Patrol` view.                                            |
| beebmx.kirby-patrol.content.depth          |    2    |   `int`    | Set the `depth` to dig into the `pages` collection.                                                               |
| beebmx.kirby-patrol.content.direction      |   asc   |  `string`  | Set the sort `direction` of the content.                                                                          |
| beebmx.kirby-patrol.content.sort           |  title  |  `string`  | Set the `sort` value for the content.                                                                             |
| beebmx.kirby-patrol.content.query          |  null   | `?Closure` | Use a specific query to display and validate by `Kirby Patrol`. It requires returning a collection of `pages`.    |
| beebmx.kirby-patrol.permissions.default    |  true   |   `bool`   | Set the `default` values of all the checkboxes when no patrol values are set.                                     |
| beebmx.kirby-patrol.permissions.enabled    |  true   |   `bool`   | Enable/Disable the default `middleware` functionality.                                                            |
| beebmx.kirby-patrol.permissions.middleware |   []    |  `array`   | Additional middleware functionality.                                                                              |
| beebmx.kirby-patrol.permissions.redirect   |  null   | `?string`  | Disabled the default `middleware` functionality and changed it to redirect to a specific URL path.                |

## Roadmap

- Custom hooks
- Multilanguage support
- Guest support

## License

Licensed under the [MIT](LICENSE.md).

## Credits

- Fernando Gutierrez [@beebmx](https://github.com/beebmx)
- [jonatanjonas](https://github.com/jonatanjonas) `logo`
- [All Contributors](../../contributors)
