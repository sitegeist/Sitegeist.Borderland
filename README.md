# Sitegeist.Borderland
### Transparent content-dimension activation with triggers

This package allows to define presets which can be activated via triggers (currently only url-parameter triggers are 
implemented) and stores the activated triggers a session cookie. Later on those stores presets are used transparently to 
display the content in the assigned dimension without modifying the public url.

### Authors & Sponsors

* Martin Ficzel - ficzel@sitegeist.de

*The development and the public-releases of this package is generously sponsored by our employer http://www.sitegeist.de.*

##  Triggers

The triggers describe the situations when a preset should be added to the user-session. The presets are grouped to make 
shure that no contradictings settings are activated.

### UrlParameter Triggers

This triggers are activated when a url with the given parameterPath (. seperated) and one of the defined values is called. 

``` 
Sitegeist:
  Borderland:
    triggers:
      urlParameter:
        '__parameter_path__':
          '__parameter_valuze_1__':
            group: example
            key: foo
          '__parameter_valuze_2__':
            group: example
            key: bar
```

## PresetGroups

The PresetGroups store control the manipulation that occur for a specific preset. Only one preset of each group can be 
active at a specific time but different groups can handle different presets or dimensions. The `displayDimensions` of 
a preset are used to override the dimensions that are extracted from the url while the `linkDimensions` is used to control    
    
```
Sitegeist:
  Borderland:
    presetGroups:
      example:
        'foo':
          displayDimensions: {example: 'foo'}
          linkDimensions: {example: 'none'}
        'bar':
          displayDimensions: {example: 'bar'}
          linkDimensions: {example: 'none'}
```

## Cookie Settings 

The cookie that stores the currently active presets can be configured. Below you can see the default configuration.
 
```
Sitegeist:
  Borderland:
    cookie:
      name: 'sitegeist-borderland'
      expires: 0
      maximumAge: null
      domain: null
      path: '/'
      secure: false
      httpOnly: true
```

## Installation

Sitegeist.Borderland is not yet available via packagist. To install add the following to the composer.json.

```
{
    "repositories": [{
        "url": "ssh://git@git.sitegeist.de:40022/sitegeist/Sitegeist.Borderland.git",
        "type": "vcs"
    }],
    "require": {
        "sitegeist/borderland": "@dev"
    }
}

```

~~Sitegeist.Borderland is available via packagist. Just add `"sitegeist/borderland" : "~1.0"` to the require-dev section of the composer.json or run `composer require --dev sitegeist/magicwand`. We use semantic-versioning so every breaking change will increase the major-version number.~~

## Contribution

We will gladly accept contributions. Please send us pull requests.