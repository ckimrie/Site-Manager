# Site Manager

_This is ALPHA software and is not to be used in Production_

ExpressionEngine module that allows you to manage sites remotely as well as compare and syncronise channels, fields, fieldgroups and categories.

## Installation

- Install the _Site Manager Client_ module in the site you wish to use as the central point to manage your sites.
- Install the _Site Manager Server_ module in each site you wish to manage remotely.

## Notes for Testers

These are the following items that this module needs tested:

- Synchronising between different EE versions
- Data communication happens via Browser. Hence different browsers need to be tested in order to outline compatibility
- SSL communication testing
- Fieldtype testing (Particularly fields that store field settings in separate tables)

## Notice

This software is still in active development and is not fully compatible with all third party addons.  Please backup your database before performing any synchronisation operations.

