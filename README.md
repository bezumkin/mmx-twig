Twig Template Engine for MODX 3
---

> This extra is part of **MMX** initiative - the **M**odern **M**OD**X** approach.

### Prepare

This package can be installed only with Composer.

If you are still not using Composer with MODX 3, just download the `composer.json` of your version:
```bash
cd /to/modx/root/
wget https://raw.githubusercontent.com/modxcms/revolution/v3.0.5-pl/composer.json
```

Then run `composer update --no-dev` and you are ready to install the **mmx** packages.
### Install

```bash
composer require mmx/twig --update-no-dev --with-all-dependencies
composer exec mmx-twig install
```

### Remove

```bash
composer exec mmx-twig remove
composer remove mmx/twig
```

### How to use

You can get and configure the instance of Twig in any snippet.

For example, snippet `Test`:
```php
$tpl = $modx->getOption('tpl', $scriptProperties);
$var = $modx->getOption('var', $scriptProperties);

if ($service = $modx->services->get('mmxTwig')) {
    $service->addFilter(
        new \Twig\TwigFilter('hello', static function($var) {
            return $var . ' World!';
        }
    ));
    
    return $service->render($tpl, ['var' => $var]);
}

return '';
```

Chunk `Test`:
```html
{{ var | hello }}
```

And MODX call of snippet with chunk:
```
[[!Test?tpl=`test`&var=`Hello`]]
```

You will get `Hello World!`.

--- 

If you use this package as a dependency for your own extra, you can load and configure the instance inside your class
and make it shared through all snippets to make the same settings and modifiers.

### Template Loaders

You have 3 template loaders by default:
- MODX Chunk (default, no prefix - just specify id or name)
- MODX Template (template:1, or template:BaseTemplate)
- File (file:name.tpl)

If the MODX element has a static file, it will be used first, without checking the contents of the element in database.

File loader is native for Twig, it makes no connection to database at all. Use it for maximum Twig experience.

### System Settings

All settings are prefixed with `mmx-twig.`.

#### elements-path

The root directory for File provider.

If it is not existing or not readable, provider will be disabled and you will get INFO record in MODX log.

By default, it is not existing `core/elements` directory.

#### options

JSON encoded string with options to override defaults of Twig instance. For example:
```json
{"strict_variables":  true}
```

See [Twig documentation][twig_docs] for more information.

The default setting are:
```json
{
    "auto_reload": true,
    "strict_variables": false,
    "autoescape": false,
    "optimizations": -1
}
```

#### use-modx

You can enable the potentially **dangerous** use of MODX instance in templates with `{{ modx }}` global.

It will allow you to access to everything in MODX, including deleting resources, elements and directories!

```
Current id of MODX resource is: {{ modx.resource.id }}
```

### Filters

Feel free to use all the [standard Twig filters][twig_filters].

There are also 3 additional filters:
- `print` - print escaped variable, `{{ var | print }}`
- `dump` - dump escaped variable, `{{ var | dump }}`
- `esc` - escape MODX tags in variable, `{{ var | esc }}`

### Globals

You can access system globals in your template:

- `env` to access `$_ENV`
- `get` to access `$_GET`
- `post` to access `$_POST`
- `files` to access `$_FILES`
- `cookie` to access `$_COOKIE`
- `server` to access `$_SERVER`
- `session` to access `$_SESSION`
- `request` to access `$_REQUEST`

For example `{{ server | print }}`

### Database Tables

This extra use 2 additional database table to store time of update of MODX chunks and templates,
as they have no this data by default:
- `mmx_twig_chunks_time`
- `mmx_twig_templates_time`

Also, there is additional table for tracking migrations:
- `mmx_twig_migrations`

### Caching

When caching is enabled, you will get compiled templates in `core/cache/mmx-twig` directory.

This directory will be deleted when you clear MODX cache.

You cannot change this directory using the system settings, but you can set the value to `false` to disable caching.

[twig_docs]: https://twig.symfony.com/doc/3.x/api.html#environment-options
[twig_filters]: https://twig.symfony.com/doc/3.x/filters/index.html