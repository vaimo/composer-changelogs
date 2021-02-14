# Vaimo Composer Changelogs

[![Latest Stable Version](https://poser.pugx.org/vaimo/composer-changelogs/v/stable)](https://packagist.org/packages/vaimo/composer-changelogs)
[![Build Status](https://travis-ci.org/vaimo/composer-changelogs.svg?branch=master)](https://travis-ci.org/vaimo/composer-changelogs)
[![Total Downloads](https://poser.pugx.org/vaimo/composer-changelogs/downloads)](https://packagist.org/packages/vaimo/composer-changelogs)
[![Daily Downloads](https://poser.pugx.org/vaimo/composer-changelogs/d/daily)](https://packagist.org/packages/vaimo/composer-changelogs)
[![Minimum PHP Version](https://img.shields.io/packagist/php-v/vaimo/composer-changelogs.svg)](https://php.net/)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/vaimo/composer-changelogs/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/vaimo/composer-changelogs/?branch=master)
[![Code Climate](https://codeclimate.com/github/vaimo/composer-changelogs/badges/gpa.svg)](https://codeclimate.com/github/vaimo/composer-changelogs)
[![License](https://poser.pugx.org/vaimo/composer-changelogs/license)](https://packagist.org/packages/vaimo/composer-changelogs)

Provides information about package changes based on changelog files that are bundled with releases and introduces tools/commands for generating documentation files (in many different formats and with the option to create custom templates) from changelog sources. 

It comes with several commands that aid the developer on setting up automatic package publishing logic in CI.

More information on recent changes [HERE](./CHANGELOG.md) (which also serves as an example of how MD formatting for changelog.json looks like).

Note that the changelog output will only be generated when you have configured output generators for any of the output formats.

## Configuration: overview

Environment variables can be defined as key value pairs in the project's composer.json

```json
{
  "extra": {
    "changelog": {
      "source": "changelog.json"
    }
  }
}
```

## Configuration: changelog file format

The module expects certain conventions to be used when declaring new changelog records, which are based
on grouping the changes based on semantic versioning rules (+ provides some extra ones for even greater 
detail): breaking, feature, fix (extras: overview, maintenance). 

The extra keys are mostly meant for dumping some data into the release notes about general theme of the 
new release or allowing some extra details to be added for the developers.

```json
{
    "1.0.0": {
        "overview": "Some general overarching description about this release; Can be declared as a list",
        "breaking": [
            "code: Something changed in the sourcecode",
            "data: Something changed about the data format",
            "schema: Something changed about the database",
            "config: config path or flag renamed",
            "logic: default/expected execution path of the application/module changed"
        ],
        "feature": [
            "short description about feature1",
            "short description about feature2"
        ],
        "fix": [
            "short description about fix1",
            "short description about fix2"
        ],
        "maintenance": [
            "short description about changing something about the architecture, etc"
        ],
        "branch": "useful for multiple major-release branches and using 'version' and 'info' commands"
    }
}
```

Note that all the groups are optional - the documentation generation and other features of the plugin 
will not error out when they're missing.

Developer is not limited only to these groups and any other group will end up being used in documentation 
generator output as well. The exception to this rule is the "overview" group, which is bound to additional
processing logic and is not perceived as a "changes" group in the code. 

## Configuration: adding releases

The releases should be added in ascending order where the latest release is always the topmost record (as 
is the case with all change-logs that one might encounter).

```json
{
    "2.0.0": {},
    "1.2.1": {},
    "1.1.1": {},
    "1.1.0": {},
    "1.0.0": {}
}
``` 

## Configuration: upcoming releases

To make sure that all the commands of the plugin work as intended, upcoming releases should be marked in
the changelog as "DEV.1.2.3", which will cause latest version reporter command to skip over it. Same could be
achieved if the values is left blank.

This is useful in situations where multiple people are working on the upcoming release and you want to 
postpone the release of that version (in case you have some CI logic build around the changelog version command). 
As long as the 'DEV.' is there, the developers can stack their changes together under the same changelog record.

```json
{
    "DEV.1.2.3": {
        "feature": [
            "some upcoming, yet to be released feature"
        ]
    }
}
```
 
The plugin uses composer constraints validator so anything that does not validate as version constraint
will be skipped over.

## Feature: comments in changelog

Useful in situations when there's a specific guideline within a team on how changelog should be maintained and 
filled per release and what kind of groups should be used.

```json
{
    "_readme": "Make sure to follow the conventions: http://some.url/changelog-conventions.html",
    "_guide": "Some tips on how the changelog for this specific package is maintained and updated",
    "1.2.3": {
       "feature": [
           "some feature"
       ]
    }
}
```

Note that comments can be added on any level of the change-log and will always be ignored by the changelog 
reader/generator.

## Feature: repository links

The changelog generator is capable of adding repository links to the changelog for each version by 
presenting both source link for certain version as well as diff/comparison for the code when compared
to previous release.

This activates on two situations:

* When composer.json of the package has support/source defined (part of standard Composer package config schema)
* When repository with proper remote destination has been configured (with well formed URL)

The variables that become available through this in templates: {{link}}, {{diff}}.

This feature is enabled by default, but can be enabled by defining the following under changelog 
configuration within the composer.json of the package:

```json
{
  "extra": {
    "changelog": {
      "feature": {
        "links": false
      }
    }
  }
}
```

The links can be disabled for a single generator run as well by overriding the URL configuration with a 
specific command argument:

```shell
composer changelog:generate --url=false
``` 

## Feature: release dates

The module will try to resolve the release date of certain version from the package 
repository when it's available. The available options to be used in output templates 
are: {{date}}, {{time}}.

This feature is enabled by default, but can be enabled by defining the following under 
changelog configuration within the composer.json of the package: 

```json
{
  "extra": {
    "changelog": {
      "feature": {
        "dates": false
      }
    }
  }
}
```

## Output: generators

This example is based on making Sphinx documentation generation available.

```json
{
  "extra": {
    "changelog": {
      "source": "changelog.json",
      "output": {
        "sphinx": "docs/changelog.rst"
      }
    }
  }
}
```

Available types: html, md, rst, slack, sphinx, txt, yml

## Output: templates

The plugin ships with built-in templates for each of the generators, which can be configured by defining 
generators in an extended format.

```json
{
  "extra": {
    "changelog": {
      "source": "changelog.json",
      "output": {
        "sphinx": {
          "path": "docs/changelog.rst",
          "template": "my/template/path/template123.mustache"
        }
      }
    }
  }
}
```

Note that the template file path is relative to the package root that owns the changelog configuration.

Templates use Mustache syntax with some extra helpers (for which the built-in templates serve as 
documentation/examples).  

When relying on the generators to produce the changelog documentation, make sure to add the output path to 
VCS ignore file as well to avoid producing unintended modifications. The file will be overwritten if exist 
in the repository before the documentation generation is called.

The generator does support Mustache partials as well in which case the template paths should be given as 
dictionary where the entry point template has been defined under the key "root".

 ```json
 {
 
   "template": {
     "root": "my/template/path/custom_root.mustache",
     "mypartial": "my/template/path/release.mustache"
   }
 }
 ```

In this example, the choice to define a template for the key "mypartial" derives directly from referring to
such a Mustache partial in the template that's defined in "root". 

The reserved names for partials in this plugin are:

* root - main entry-point for the renderer
* release - used by default to output certain changelog group/version and it's details AND by changelog:info

The base files for each template can be found from [HERE](https://github.com/vaimo/composer-changelogs/tree/master/views).

## Commands

```shell
# Generate documentation pages from changelog configuration
composer changelog:generate 

# Generate changelog with version-bound (src) and (diff) links for each release
# Will override the default setting configured for the module
changelog:generate --url ssh://hg@bitbucket.org/some/repository

# Report latest valid version from changelog (skip over the ones that are yet to be released)
composer changelog:version

# Report latest valid version for release/1.X
composer changelog:version --branch release/1.X

# Report latest release record from changelog (might be same as last valid)
composer changelog:version --tip

# Report upcoming release version from changelog (returns blank if there is no upcoming version)
composer changelog:version --upcoming

# Report current latest version without PATCH version
composer changelog:version --segments 2

# Report latest release details (in requested format)
composer changelog:info

# Validate changelog files content
composer changelog:validate

# Setup basic usage of the changelogs for a module
composer changelog:bootstrap
```

## Upgrading The Module

When upgrading the module, one might encounter odd crashes about classes not being found or class 
constructor arguments being wrong. 

This usually means that the class structure or constructor footprint in some of the classes have changed 
after the upgrade which means that the plugin might be running with some classes from the old and some 
classes from the new version. 

It is safe to ignore errors like these when running the `composer update` command again does not produce 
any side-effects.

## Development

The modules ships with a dedicated development branch [devbox](https://github.com/vaimo/composer-changelogs/tree/devbox) 
which contains configuration for spinning up a dedicated development environment that can be used together 
with VSCode's [Remote Containers](https://code.visualstudio.com/docs/remote/containers).

Note that there is no strict requirement to use such a setup, but it's what was used to author the plugin
and if you want to be sure that you have everything up and running without hick-ups, you can just as well
take the shortcut.

System requirements:

1. Have Docker installed.
1. Have VSCode installed with 'Remote - Containers' extension.
1. Have Mutagen installed (used for selecting syncing).

Setup:

1. `git checkout devbox .devcontainer Dockerfile docker-compose.yml mutagen.yml`
1. `git reset .devcontainer Dockerfile docker-compose.yml mutagen.yml`
1. [open the project with VSCode that has Remote Container extension installed]
1. [use the 'Reopen in Container' option that is given in a prompt that opens]
1. (only on Windows) `mutagen project start`
1. Use 'Terminal > New Terminal' to open a terminal within the IDE.
1. [from the terminal you can install the packages, trigger debugger, etc]

Note this setup does come with a pre-bootstrapped xDebugger, you just have to use the Run menu 
in VSCode and start listening and trigger a command via the terminal.
