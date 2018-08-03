# composer-changelogs

Provides information about package changes based on changelog files that are bundled with releases; 

It also introduces tools/commands for generating documentation files from changelog sources.

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
on grouping the changes based on sematic versioning rules (+ provides some extra ones for even greater 
detail): breaking, feature, fix (extras: overview, maintenance). The extra keys are mostly meant for dumping
some data into the release notes about general theme of the new release or allowing some extra details to be 
added for the developers.

```json
{
    "1.0.0": {
        "overview": "Some general overarching description about this release",
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

Note that all the groups are optional - the documentation generation and other features of the plugin will not error out when they're missing.

## Configuration: upcoming releases

To make sure that all the commands of the plugin work as intended, upcoming releases should be marked in
the changelog as "X.X.X", which will cause latest version reporter command to skip over it. Same could be
achieved if the values is left blank.

 ```json
 {
     "X.X.X": {
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
          "template": "my/template/path"
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

## Commands

```shell
# Generate documentation pages from changelog configuration
composer changelog:generate 

# Report latest valid version from changelog (skip over the ones that are yet to be released)
composer changelog:version
```

## Changelog 

_Changelog included in the composer.json of the package_
