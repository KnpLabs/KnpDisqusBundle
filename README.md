# KnpDisqusBundle

If you use [Disqus](https://disqus.com) on your website for comments the
comments are loaded dynamically via JavaScript, which could negatively
impact SEO.

This bundle will fetch the comments using Disqus API so that you can include
them on your page… before replacing the comment `div` by the Disqus JavaScript widget.

This way you benefit from both the JavaScript widget and the robot-friendly comments.

[![Build Status](https://travis-ci.org/KnpLabs/KnpDisqusBundle.png?branch=master)](https://travis-ci.org/KnpLabs/KnpDisqusBundle)

[![knpbundles.com](http://knpbundles.com/KnpLabs/KnpDisqusBundle/badge-short)](http://knpbundles.com/KnpLabs/KnpDisqusBundle)

## Requirements

* Disqus API: [public key](http://disqus.com/api/applications/register/)

## Installation

With [composer](http://packagist.org), run:

    composer require knplabs/knp-disqus-bundle

If you're not using Symfony Flex, then you will also need to enable
`Knp\Bundle\DisqusBundle\KnpDisqusBundle` in your `bundles.php` file.

Next, create a `config/packages/knp_disqus.yaml` file:

```yaml
# config/packages/knp_disqus.yaml
knp_disqus:
    api_key: '%env(DISQUS_API_KEY)%'
```

And finally, configure the `DISQUS_API_KEY` in your `.env` or `.env.local` file:

```
# .env

DISQUS_API_KEY=ABC123
```

## Usage:

### In your Twig template:

```jinja
{{ knp_disqus_render('your_disqus_shortname', {'identifier': '/december-2010/the-best-day-of-my-life/', 'limit': 10}) }}
```

You can also show comments for specific language:

```jinja
{{ knp_disqus_render('your_disqus_shortname', {'identifier': '/december-2010/the-best-day-of-my-life/', 'language': 'de_formal'}) }}
```

### Or in Controller:

```php
use Knp\Bundle\DisqusBundle\Client\DisqusClientInterface;

public function somePage(DisqusClientInterface $disqusClient)
{
    // ...

    $comments = $disqusClient->fetch('your_disqus_shortname', [
        'identifier' => '/december-2010/the-best-day-of-my-life/',
        'limit'      => 10, // Default limit is set to max. value for Disqus (100 entries)
    //    'language'   => 'de_formal', // You can fetch comments only for specific language
    ]);

    return $this->render('articles/somePage.html.twig', [
        'comments' => $comments,
    ]);
}
```

### Adding a Callback for New Comments

If you want a JavaScript function to be called when a new comment is added
(e.g. to trigger some Analytics), first, create a global JavaScript function
somewhere (i.e. one that is attached to the `windows` object):

```javascript
window.onNewComment = function(comment) {
    console.log(comment);
}
```

Next, pass the function name when rendering:

```jinja
{{ knp_disqus_render('your_disqus_shortname', {
    'identifier': '/december-2010/the-best-day-of-my-life/',
    'limit': 10,
    'newCommentCallbackFunctionName': 'onNewComment'
}) }}
```

## SSO authentication (optional)

If you want to manage authentication through [Disqus SSO](http://docs.disqus.com/developers/sso/) mechanism, you have to add the application secret key in the configuration and pass user information (id, username, email) which will compose the HMAC payload from it, as well as specific login/logout service information to the helper. Make sure to setup your Disqus forum to use SSO and allow for local domains (for development purposes).

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

## Configuration

```yaml
# config/packages/knp_disqus.yaml
knp_disqus:
    api_key: 'your-disqus-api-key'
    secret_key: 'disqus-api-key' # optional, for SSO auth only
```

Enjoy!
