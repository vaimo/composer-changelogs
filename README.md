# composer-changelogs

Provides information about package changes based on changelog files that are bundled with releases and introduces tools/commands for generating documentation files from changelog sources.

It comes with several commands that aid developer on setting up automatic package publishing logic in CI.

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
detail): breaking, feature, fix (extras: overview, maintenance). The extra keys are mostly meant for dumping
some data into the release notes about general theme of the new release or allowing some extra details to be 
added for the developers.

```json
{
    "1.0.0": {
        "overview": "Some general overarching description about this release; Can be declared as a list",
        "breaking": [
            "code: Something changed in the sourcecode",
            "data: Something changed about the data format",
            "schema: Something changed about the database"
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
        ]
    }
}
```

Note that all the groups are optional - the documentation generation and other features of the plugin 
will not error out when they're missing.

Developer is not limited only to these groups and any other group will end up being used in documentation 
generator output as well. The exception to this rule is the "overview" group, which is bound to additional
processing logic and is not perceived as a "changes" group in the code. 

## Configuration: upcoming releases

To make sure that all the commands of the plugin work as intended, upcoming releases should be marked in
the changelog as "DEV.1.2.3", which will cause latest version reporter command to skip over it. Same could be
achieved if the values is left blank.

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

## Configuration: generators

This example is based on making Sphinx documentation generation available

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

## Configuration: generator templates

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

## Commands

```shell
# Generate documentation pages from changelog configuration
composer changelog:generate 

# Report latest valid version from changelog (skip over the ones that are yet to be released)
composer changelog:version

# Report latest release details (in requested format)
composer changelog:info
```

## Upgrading The Module

When upgrading the module, one might encounter odd crashes about classes not being found or class 
constructor arguments being wrong. 

This usually means that the class structure or constructor footprint in some of the classes have changed 
after the upgrade which means that the plugin might be running with some classes from the old and some 
classes from the new version. 

It is safe to ignore errors like these when running the `composer update` command again does not produce 
any side-effects.

## Changelog 

_Changelog included in the composer.json of the package_
