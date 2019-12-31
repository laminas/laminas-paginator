# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.8.2 - 2019-08-21

### Added

- [zendframework/zend-paginator#49](https://github.com/zendframework/zend-paginator/pull/49) adds support for PHP 7.3.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.8.1 - 2018-01-30

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-paginator#45](https://github.com/zendframework/zend-paginator/pull/45) fixes an error
  in the `DbSelectFactory` whereby it ignored the fourth option passed via
  `$options`, which can be used to specify a laminas-db `Select` instance for
  purposes of counting the rows that will be returned.

## 2.8.0 - 2017-11-01

### Added

- [zendframework/zend-paginator#20](https://github.com/zendframework/zend-paginator/pull/20) adds
  and publishes the documentation to https://docs.laminas.dev/laminas-paginator/

- [zendframework/zend-paginator#38](https://github.com/zendframework/zend-paginator/pull/38) adds support
  for PHP 7.1.

- [zendframework/zend-paginator#38](https://github.com/zendframework/zend-paginator/pull/38) adds
  support for PHP 7.2. This is dependent on fixes in the upstream laminas-db
  package if you are using the various database-backed paginators; other
  paginators work on 7.2 at this time.

### Changed

- [zendframework/zend-paginator#32](https://github.com/zendframework/zend-paginator/pull/32) updates the
  `DbTableGateway` adapter's constructor to allow any
  `Laminas\Db\TableGateway\AbstractTableGateway` implementation, and not just
  `Laminas\Db\TableGateway\TableGateway` instances. This is a parameter widening,
  which poses no backwards compatibility break, but does provide users the
  ability to consume their own `AbstractTableGateway` extensions.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-paginator#35](https://github.com/zendframework/zend-paginator/pull/35) removes support
  for PHP 5.5.

- [zendframework/zend-paginator#35](https://github.com/zendframework/zend-paginator/pull/35) removes support
  for HHVM.

### Fixed

- [zendframework/zend-paginator#33](https://github.com/zendframework/zend-paginator/pull/33) fixes how cache
  identifiers are generated to work propertly with non-serializable pagination
  adapters.

- [zendframework/zend-paginator#26](https://github.com/zendframework/zend-paginator/pull/26) fixes an issue
  in `Paginator::count()` whereby it would re-count when zero pages had been
  previously detected.

## 2.7.0 - 2016-04-11

### Added

- [zendframework/zend-paginator#19](https://github.com/zendframework/zend-paginator/pull/19) adds:
  - `Laminas\Paginator\AdapterPluginManagerFactory`
  - `Laminas\Paginator\ScrollingStylePluginManagerFactory`
  - `ConfigProvider`, which maps the `AdapterPluginManager` and
    `ScrollingStylePluginManager` services to the above factories.
  - `Module`, which does the same, for laminas-mvc contexts.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.6.1 - 2016-04-11

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-paginator#7](https://github.com/zendframework/zend-paginator/pull/7) adds aliases for
  the old `Null` adapter, mapping them to the new `NullFill` adapter.

## 2.6.0 - 2016-02-23

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-paginator#4](https://github.com/zendframework/zend-paginator/pull/4),
  [zendframework/zend-paginator#8](https://github.com/zendframework/zend-paginator/pull/8), and
  [zendframework/zend-paginator#18](https://github.com/zendframework/zend-paginator/pull/18) update the code
  base to be forwards-compatible with the v3 releases of laminas-servicemanager and
  laminas-stdlib.
