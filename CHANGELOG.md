# Release notes for Mystique Module

### Added

- `attrs`, `wrapAttrs` for multiple attributes, `attr`, `wrapAttr` for single attribute

## v.0.0.16

### Added

- Module config `useGlob` for find config files

## v.0.0.15

### Fixed

- Fix template context save, is current page null or not ?

## v.0.0.14

### Fixed

- Fix template context save

## v.0.0.13

### Fixed

- Fix getting overwritten inputfield config values

## v.0.0.12

### Fixed

- Fix set language value for admin side
- Fix don't check folders if name start with "." dot

## v.0.0.11

### Added

- add support for `$page->mystiqueField->array();` and `$page->mystiqueField->json();`

## v.0.0.10

### Added

- add support for `$page->setAndSave('yourfield', ['foo'=>'bar']);`

## v.0.0.9

### Added

- add support for overwrite input field configs

## v.0.0.8

### Added

- add finder function for different operation systems

## v.0.0.7

- 

## v.0.0.6

### Changed

- rename `getResources()` as `resources($json = false)`
- rename `getResource($name = '')` as `resource($name = '', $json = false)`
- add option to input field, `use json string` instead of a `config file`

## v.0.0.5

- Fix inside repeater field usage

## v.0.0.5

- Fix module requires and installs

## v.0.0.4

- Update external usage

## v.0.0.3

- MystiqueFormManager re-written for use `$inputfield->set()` function

## v.0.0.2

- Initial commit

## v.0.0.1