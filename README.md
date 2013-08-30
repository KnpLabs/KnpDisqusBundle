# KnpDisqusBundle

If you use [Disqus](http://disqus.com) on your website for comments, you know that it's no good for SEO − as the comments are loaded dynamically via javascript.

This bundle will fetch the comments using Disqus API so that you can include them in your page… before replacing the comment `div` by the Disqus javascript widget.

This way you benefit from both the javascript widget and the robot friendly comments.

[![knpbundles.com](http://knpbundles.com/KnpLabs/KnpDisqusBundle/badge-short)](http://knpbundles.com/KnpLabs/KnpDisqusBundle)

## Requirements

* Symfony from 2.0
* Disqus API: [public key](http://disqus.com/api/applications/register/)
* Dependencies:
 * [`Buzz`](https://github.com/kriswallsmith/Buzz)

## Installation

With [composer](http://packagist.org), run:

    php composer.phar require knplabs/knp-disqus-bundle:dev-master

Register the bundles in your `AppKernel`:

```php
$bundles = array(
    //...
    new Knp\Bundle\DisqusBundle\KnpDisqusBundle(),
    //...
);
```

### SSO authentication (optional)

If you want to manage authentication through [Disqus SSO](http://docs.disqus.com/developers/sso/) mechanism, you have to add the application secret key in the configuration and pass user information (id, username, email) which will compose the HMAC payload from it, as well as specific login/logout service information to the helper. Make sure to setup your Disqus forum to use SSO and allow for local domains (for development purposes). More details hereunder.

## Configuration

### config.yml

```yaml
knp_disqus:
    api_key: %knp_disqus.api_key%
    secret_key: %knp_disqus.secret_key% # optional, for SSO auth only
    forums:
        lorem:
            shortname: %knp_disqus.lorem.shortname%
            cache: my_cache_for_lorem # cache template key, usage described below
        ipsum:
            shortname: %knp_disqus.ipsum.shortname%

my_cache_for_lorem:
    # If you setup up an cache, you should also configure cache provider, which will be used automatically
    # ...
```

### parameters.yml

```yaml
knp_disqus.api_key:    YOUR_PUBLIC_API_KEY
knp_disqus.secret_key: YOUR_SECRET_API_KEY # optional, for SSO auth only
# Insert your disqus shortname
# it's the unique identifier for your website as registered on Disqus
knp_disqus.lorem.shortname: "dolor-sid"
# you can also register more than one forum
knp_disqus.ipsum.shortname: "amet"
```

## Usage:

### In your Twig template:

```jinja
{{ knp_disqus_render('dolor-sid', {'identifier': '/december-2010/the-best-day-of-my-life/', 'limit': 10}) }}
```

You can also show comments for specific language:

```jinja
{{ knp_disqus_render('amet', {'identifier': '/december-2010/the-best-day-of-my-life/', 'language': 'de_formal'}) }}
```

To use SSO auth, pass ``sso.user`` information in the parameters to tell Disqus which user is logged in. Pass a user with an empty ``id`` to force Disqus to logout user, respectively to tell Disqus no user is logged in through SSO. Add information regarding your SSO Authentication service (login/logout urls, icon, etc.) in the ``sso.service`` parameter. See [Disqus SSO documentation](http://docs.disqus.com/developers/sso/) for more information.

```jinja
{{ knp_disqus_render(
    'dolor-sid',
    {
        'identifier': '/december-2010/the-best-day-of-my-life/',
        'limit': 100,
        'sso': {
            'user': {
                'id' : 'test',
                'username' : 'John Doe',
                'email': 'john.doe@example.com',
            },
            'service': {
                'name': 'MyAuthServiceProvider',
                'icon': 'http://example.com/favicon.png',
                'button': 'http://example.com/images/samplenews.gif',
                'url': 'http://example.com/login/',
                'logout': 'http://example.com/logout/',
                'width': '400',
                'height': '400'
            }
        }
    },
    'KnpDisqusBundle::list.html.twig' )
}}
```

### Or in Controller:

```php
public function myPageAction()
{
    // ...

    $comments = $this->get('knp_disqus.request')->fetch('dolor-sid', array(
        'identifier' => '/december-2010/the-best-day-of-my-life/',
        'limit'      => 10, // Default limit is set to max. value for Disqus (100 entries)
    //    'language'   => 'de_formal', // You can fetch comments only for specific language
    ));

    return $this->render('LoremIpsumBundle:Lorem:myPage.html.twig', array(
        'comments' => $comments,
    ));
}
```
