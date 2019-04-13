# Changelog

_This file has been auto-generated from the contents of changelog.json_

## 0.15.1

### Fix

* load every class on plugin startup to make sure that there is no version clash when upgrading the plugin (upgrade run would end with using old version's code)

### Maintenance

* switch to Mustache template engine that's referred to on the official Mustache page (it also comes with lower PHP version expectation)
* lowered PHP version requirement (possible due to dependency switch)
* added php compatibility check (starting from 5.3) and code static analysis

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.15.1) [diff](https://github.com/vaimo/composer-changelogs/compare/0.15.0...0.15.1)

## 0.15.0 (2019-03-20)

### Feature

* added better JSON validation to provide user with proper error messages rather than just obscure 'something is wrong' ones

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.15.0) [diff](https://github.com/vaimo/composer-changelogs/compare/0.14.0...0.15.0)

## 0.14.0 (2019-03-12)

### Feature

* allow comment blocks in JSON (anything that starts with underscore) to allow embedded guides or references to schemas to be part of the changelog

### Maintenance

* make sure that the proxy-plugin does not crash due to some dependency not loading properly on startup

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.14.0) [diff](https://github.com/vaimo/composer-changelogs/compare/0.13.1...0.14.0)

## 0.13.1 (2019-02-05)

### Fix

* package repository resolver: fuzzy query used when package query not provided (resulting in failure that proposes all packages as potential matches): should have used root package
* package repository resolver: exact package name match not prioritized over partial match

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.13.1) [diff](https://github.com/vaimo/composer-changelogs/compare/0.13.0...0.13.1)

## 0.13.0 (2019-02-05)

### Feature

* added GIT support for diff links and date resolver
* allow fuzzy package names when generating changelog for sub-repository

### Fix

* potentially confusing anchors for releases in Sphinx changelog format (where releases got anchors like #id1, #id2, etc)
* upcoming version had DEV marker in it; should be valid version instead (made no sense otherwise when used with --segments, etc)

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.13.0) [diff](https://github.com/vaimo/composer-changelogs/compare/0.12.0...0.13.0)

## 0.12.0 (2019-01-30)

### Feature

* allow specifying repository url with changelog:generate command argument --url to provide custom url in cases where no other source for resolving it is available

### Maintenance

* allow the commands that this plugin repository provides to be used on itself
* added changelog output with MD format output

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.12.0) [diff](https://github.com/vaimo/composer-changelogs/compare/0.11.0...0.12.0)

## 0.11.0 (2019-01-30)

### Feature

* add repository from version to version diff links when module has repository reference in composer.json or when one provided via call argument (with optional variable replacement options); Currently only features BitBucket support. See the topic 'Feature: repository links' for more information.
* add repository versioned source links when module has repository reference in composer.json or when one provided via call argument (with optional variable replacement options); Currently only features BitBucket support. See the topic 'Feature: release dates' for more information.
* add release date next to the version when repository present and there's a matching tag available for a changelog record.

### Fix

* no extra line before summary when release has no overview
* yml format error when using summary due to a colon within the value with no wrapping quotes
* corrupt yml format on full changelog generation where nesting level did not get correctly reset for every release but the first

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.11.0) [diff](https://github.com/vaimo/composer-changelogs/compare/0.10.1...0.11.0)

## 0.10.1 (2019-01-28)

### Fix

* the 'overview-reduced' not considered as something that is not a changes group type
* the value of overview-reduced not composed correctly: some words merged together without whitespace

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.10.1) [diff](https://github.com/vaimo/composer-changelogs/compare/0.10.0...0.10.1)

## 0.10.0 (2019-01-24)

### Feature

* --segments argument added for changelog:version to be able to query for the latest MAJOR version from changelog

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.10.0) [diff](https://github.com/vaimo/composer-changelogs/compare/0.9.4...0.10.0)

## 0.9.4 (2019-01-17)

### Fix

* slack format to use overview info a bit differently to avoid odd line wrapping: lines are merged, only totally new lines are respected and paragraph separators

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.9.4) [diff](https://github.com/vaimo/composer-changelogs/compare/0.9.3...0.9.4)

## 0.9.3 (2018-12-10)

### Fix

* formatting fixes to templates when used for full documentation generation (whitespace missing between certain titles in some cases)
* version wrapper for TXT changelog template

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.9.3) [diff](https://github.com/vaimo/composer-changelogs/compare/0.9.2...0.9.3)

## 0.9.2 (2018-12-03)

### Fix

* minor whitespace issues with Slack changelog release info templates; too many empty lines when overview not present

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.9.2) [diff](https://github.com/vaimo/composer-changelogs/compare/0.9.1...0.9.2)

## 0.9.1 (2018-11-30)

### Fix

* whitespace usage in certain formats (sphinx) caused generated documentation to be incorrect
* summary merged into overview, which made it impossible to properly format summarized version changelog output

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.9.1) [diff](https://github.com/vaimo/composer-changelogs/compare/0.9.0...0.9.1)

## 0.9.0 (2018-11-30)

### Feature

* new output format: slack (formatting markup for Slack)

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.9.0) [diff](https://github.com/vaimo/composer-changelogs/compare/0.8.0...0.9.0)

## 0.8.0 (2018-11-28)

### Feature

* new output format: txt (no formatting markup)

### Fix

* output format types could not be used with ':info' even when output file was not defined
* removed excess whitespace from changelog output (for both ':info' and ':generate')
* undefined array key crash when configuring custom templates for changelog output within the package that owns the changelog

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.8.0) [diff](https://github.com/vaimo/composer-changelogs/compare/0.7.0...0.8.0)

## 0.7.0 (2018-10-04)

### Feature

* added .md, .yml and .rst format options for info command
* sphinx template upgraded to take use of some it's more advanced features (overview decorated and made to stand out more)

### Fix

* the reason for changelog not being valid not shown when running in non-verbose mode, leaving the user wondering what went wrong (now lists all reasons)
* bug in html output template (list item tag never closed on changelog release listing level)

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.7.0) [diff](https://github.com/vaimo/composer-changelogs/compare/0.6.4...0.7.0)

## 0.6.4 (2018-09-02)

### Fix

* removed code that was incompatible with 5.3

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.6.4) [diff](https://github.com/vaimo/composer-changelogs/compare/0.6.3...0.6.4)

## 0.6.3 (2018-09-02)

### Fix

* composer run crashes when changelog plugin gets uninstalled while running (when defined under require-dev and running with --no-dev)

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.6.3) [diff](https://github.com/vaimo/composer-changelogs/compare/0.6.2...0.6.3)

## 0.6.2 (2018-08-15)

### Fix

* url-decoded branch names not dealt with correctly when provided as branch variables

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.6.2) [diff](https://github.com/vaimo/composer-changelogs/compare/0.6.1...0.6.2)

## 0.6.1 (2018-08-15)

### Fix

* treat 'master' and 'default' branches also match with changelog records that don't have branch specified (these branch names CAN still be used on changelog items)
* changelog:version --upcoming not taking --branch config into account

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.6.1) [diff](https://github.com/vaimo/composer-changelogs/compare/0.6.0...0.6.1)

## 0.6.0 (2018-08-15)

### Feature

* support for using changelog-based releases on multiple branches (--branch option added, version command now return version that either has no branch or matches branch)
* added 'upcoming' support for changelog:info command

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.6.0) [diff](https://github.com/vaimo/composer-changelogs/compare/0.5.2...0.6.0)

## 0.5.2 (2018-08-08)

### Fix

* avoid loud crash when changelog file has syntax errors; proper error handling and validation introduced instead

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.5.2) [diff](https://github.com/vaimo/composer-changelogs/compare/0.5.1...0.5.2)

## 0.5.1 (2018-08-07)

### Fix

* validation exited with wrong exit code on failure

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.5.1) [diff](https://github.com/vaimo/composer-changelogs/compare/0.5.0...0.5.1)

## 0.5.0 (2018-08-07)

### Feature

* added new command to validate the changelog's contents

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.5.0) [diff](https://github.com/vaimo/composer-changelogs/compare/0.4.0...0.5.0)

## 0.4.0 (2018-08-07)

### Feature

* format option added for version command
* upcoming version output added to version command

### Fix

* info command was missing one formatting option
* improved error management

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.4.0) [diff](https://github.com/vaimo/composer-changelogs/compare/0.3.1...0.4.0)

## 0.3.1 (2018-08-06)

### Fix

* brief changelog info mode added overview separator even when there was no overview set

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.3.1) [diff](https://github.com/vaimo/composer-changelogs/compare/0.3.0...0.3.1)

## 0.3.0 (2018-08-06)

### Feature

* new command added for acquiring information about specific release: changelog:info
* allow multi-line contents for 'overview'

### Fix

* template overrides not properly applied (over the ones that ship with the plugin)

### Maintenance

* output templating changed to be more granular to allow same templates to be used for both changelog:info output and for documentation generation

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.3.0) [diff](https://github.com/vaimo/composer-changelogs/compare/0.2.0...0.3.0)

## 0.2.0 (2018-08-03)

### Feature

* new command added for reporting latest valid version tag from changelog: changelog:version

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.2.0) [diff](https://github.com/vaimo/composer-changelogs/compare/0.1.2...0.2.0)

## 0.1.2 (2018-08-02)

### Fix

* wrong path resolved for root package (causing event handler to fail and no docs to be generated)
* generate command failure printed out whole exception rather than just it's message

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.1.2) [diff](https://github.com/vaimo/composer-changelogs/compare/0.1.1...0.1.2)

## 0.1.1 (2018-08-02)

### Fix

* fixed a typo in plugin's event observer name (changelog bot generated when package installed as root package)

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.1.1) [diff](https://github.com/vaimo/composer-changelogs/compare/0.1.0...0.1.1)

## 0.1.0 (2018-08-02)

### Feature

* allow Sphinx documentation file to be generated from changelog contents ('changelog:generate' command)
* generate changelog for root package on install/update

Links: [src](https://github.com/vaimo/composer-changelogs/tree/0.1.0) [diff](https://github.com/vaimo/composer-changelogs/compare/451d290bfed9a87b59afc6f980827bf307d38e6e...0.1.0)