# composer-changelogs

Provide information about package changes based on changelog files that are bundled with releases; 
introduces tools/commands for generating documentation files from changelog sources

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
  
## Configuring generators

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
  
## Changelog 

_Changelog included in the composer.json of the package_
