# KnpDisqusBundle

If you use [Disqus](disqus.com) on your website for comments, you know that it's no good for SEO − as the comments are loaded dynamically via javascript.

This bundle will fetch the comments using Disqus API so that you can include them in your page… before replacing the comment `div` by the Disqus javascript widget.

This way you benefit from both the javascript widget and the robot friendly comments.

## Installation

WRITEME

**Requirements:** KnpDisqusBundle requires [KnpZendCacheBundle](https://github.com/KnpLabs/KnpZendCacheBundle).

## Usage

### config.yml

    knp_disqus:
        forums:
            lorem:
                shortname: %knp_disqus.lorem.shortname%
                cache: my_cache_for_lorem

Configure your cache provider:

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

### parameters.yml

    # Insert your disqus shortname
    # it's the unique identifier for your website as registered on Disqus
    knp_disqus.lorem.shortname: "lorem"

### Controller

In your controller:

```php
<?php
public function myPageAction()
{
    // ...

    $comments = $this->get('knp_disqus.forum.lorem')->fetch(array(
        'identifier' => '/december-2010/the-best-day-of-my-life/',
        'limit' => 100,
    ));

    return $this->render("LoremIpsumBundle:Lorem:myPage.html.twig", array(
        'comments' => $comments,
    ));
}
```

### Template

In your template:

```jinja
{{ include 'KnpDisqusBundle:Comment:list.html.twig' with {'comments': comments} }}
```

## Future

Introduce a twig tag:

```jinja
{{ knp_disqus_render('lorem', {'identifier': '/december-2010/the-best-day-of-my-life/', 'limit': 100}) }}
```
