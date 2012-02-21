# KnpDisqusBundle

If you use [Disqus](http://disqus.com) on your website for comments, you know that it's no good for SEO − as the comments are loaded dynamically via javascript.

This bundle will fetch the comments using Disqus API so that you can include them in your page… before replacing the comment `div` by the Disqus javascript widget.

This way you benefit from both the javascript widget and the robot friendly comments.

## Requirements

* Symfony (_2.1 (master branch) or later_)
* Disqus API: [public key](http://disqus.com/api/applications/register/)
* Dependencies:
 * [`Buzz`](https://github.com/kriswallsmith/Buzz)

## Installation

Add this to your `deps`:

    [Buzz]
        git=https://github.com/kriswallsmith/Buzz.git

    [KnpDisqusBundle]
        git=https://github.com/KnpLabs/KnpDisqusBundle.git
        target=/bundles/Knp/Bundle/DisqusBundle

Then run the usual `bin/vendors`:

    bin/vendors install

Register autoloads:

    $loader->registerNamespaces(array(
        'Knp'              => __DIR__.'/../vendor/bundles',
        'Buzz'             => __DIR__.'/../vendor/Buzz/lib'
    ));

Register the bundles in your `AppKernel`:

    $bundles = array(
        new Knp\Bundle\DisqusBundle\KnpDisqusBundle(),
    );

### Optional (cache usage)

If you wanna use cache, you can to install [KnpZendCacheBundle](https://github.com/KnpLabs/KnpZendCacheBundle), to do that follow the installation instructions in [KnpZendCacheBundle README file](https://github.com/KnpLabs/KnpZendCacheBundle/blob/master/README.markdown).

## Configuration

### config.yml

    knp_disqus:
        api_key: %knp_disqus.api_key%
        forums:
            lorem:
                shortname: %knp_disqus.lorem.shortname%
                cache: my_cache_for_lorem # cache template key, usage described below

### parameters.yml

    knp_disqus.api_key: YOUR_PUBLIC_API_KEY
    # Insert your disqus shortname
    # it's the unique identifier for your website as registered on Disqus
    knp_disqus.lorem.shortname: "lorem"

If you setup up an cache, you should also configure cache provider, which will be used automaticlly:

### config.yml
    knp_zend_cache:
        templates:
            my_cache_for_lorem:
                frontend:
                    name: Core
                    options:
                        lifetime: 7200
                        automatic_serialization: true
                backend:
                    name: File
                    options:
                        cache_dir: %kernel.root_dir%/cache/%kernel.environment%

## Usage:

### In your Twig template:

```jinja
{{ knp_disqus_render('lorem', {'identifier': '/december-2010/the-best-day-of-my-life/', 'limit': 100, 'language': 'de_formal'}) }}
```

### Or in Controller:

```php
<?php
public function myPageAction()
{
    // ...

    $comments = $this->get('knp_disqus.forum.lorem')->fetch(array(
        'identifier' => '/december-2010/the-best-day-of-my-life/',
        'limit' => 100,
        'language' => 'de_formal'
    ));

    return $this->render("LoremIpsumBundle:Lorem:myPage.html.twig", array(
        'comments' => $comments,
    ));
}
```
