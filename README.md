# Site Manager

_This is ALPHA software and is not to be used in Production_

ExpressionEngine module that allows you to manage sites remotely as well as compare and syncronise channels, fields, fieldgroups and categories.

## Installation

Before installing the _Site Manager Client_ you will need to install the [RequireJS-for-EE](https://github.com/ckimrie/RequireJS-for-EE) extension.  The _Site Manager Server_ does not require RequireJS-for-EE.

- Install the _Site Manager Client_ module in the site you wish to use as the central point to manage your sites.
- Install the _Site Manager Server_ module in each site you wish to manage remotely.

**Warning** - This software is still in active development and is not fully compatible with all third party addons.  Please backup your database before performing any synchronisation operations.

## Notes for Testers

These are the following items that this module needs tested:

- Synchronising between different EE versions
- Data communication happens via Browser. Hence different browsers need to be tested in order to outline compatibility
- SSL communication testing
- Fieldtype testing (Particularly fields that store field settings in separate tables)

## Project License

![Creative Commons License](http://i.creativecommons.org/l/by/3.0/88x31.png)

This work is licensed under a [Creative Commons Attribution 3.0 Unported License](http://creativecommons.org/licenses/by/3.0/deed.en_US), however, this license does not apply to included libraries (see below) which may have different licenses.

## Included Libraries & Licenses

Site manager includes several libraries, each with its own license and development priorities:

- [Channel Data](http://www.objectivehtml.com/libraries/channel_data) - [BSD 2-clause License](http://en.wikipedia.org/wiki/BSD_licenses#2-clause_license_.28.22Simplified_BSD_License.22_or_.22FreeBSD_License.22.29)
- [Phpseclib](http://phpseclib.sourceforge.net) - [MIT License](http://en.wikipedia.org/wiki/MIT_License)
