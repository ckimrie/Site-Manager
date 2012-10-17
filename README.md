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

## License

![Creative Commons License](http://i.creativecommons.org/l/by/3.0/88x31.png)

This work is licensed under a [Creative Commons Attribution 3.0 Unported License](http://creativecommons.org/licenses/by/3.0/deed.en_US)
