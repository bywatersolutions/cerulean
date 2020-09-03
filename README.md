# cerulean
Web-based data migration toolkit based on Silverstripe and PHP-ETL

# Why? #
Sometimes you need to translate data from one system to another.  The ETL model (Extract - Transform - Load) works well to structure that transfer, by identifying concrete steps to take.  But how to you turn those steps into code?

[PHP-ETL](https://github.com/leomarquine/php-etl) provides a simple, clean toolkit for defining ETL processes.  It requires, however, commandline scripting knowledge, and not all folks who work with data transformations have that skill.  Enter Cerulean!

Cerulean provides a graphic user interface on top of PHP-ETL, as well as additional features like user management and file uploads.  By defining a JSON Schema for an ETL Process (as well as for remote data sources), and leveraging the awesome work of [JSON Editor](https://github.com/json-editor/json-editor) to turn that schema into a web form, Cerulean makes it easier for end users to define ETL processes and connect them data sources.

Additionally, several extra Extractors, Transformers and Loaders are provided (as necessitated by the author's use case), as well as support for RESTful web services.

## Okay, but why Postgres?

Cerulean depends on Postgres, rather than MySQL, for two main reasons:

1. Postgres has great JSON support, and the Cerulean scratch space saves all records as JSON
2. MySQL 8, which also has JSON support, doesn't play nicely with PHP out of the box; something about default user password encryption algorithms

# Philosophy #
```
ETLETLETLET LETLETLETLE T
T                L      L
L                E      E
E                T      T
TLETLE           L      L
L                E      E
E                T      T
T                L      L
LETLETLETLE      E      TLETLETLET
```
Cerulean can be thought of as "an ETL of ETLs".... each step of the three steps can be decomposed into their own ETL processes, with Extraction and Loading happening into Cerulean's 'scratch space' in between.  For now, this is ETL_Record, but may move out of Silverstripe in the near future.

Five ModelAdmins are created:
1. Extract
2. Analyze
3. Transform
4. Validate/Load
5. Remote Connections

After records are extracted into the scratch space, users can do some analysis on them in order to determine what transformations are necessary.  Records are then transformed until they meet a specified 'valid' state, at which point they can be loaded without error.  More scaffolding for all this is coming in future commits.

# RESTful Services #
Cerulean extends PHP-ETL to support not only SQL databases, but also RESTful web services.  These can be configured in the Remote Connections administration area. A Variety of authentication methods are supported now, more will be added as use case dictates.

# Extractors #

## MARC Extractor ##
Extract data from a MARC record, either at the biblio or item level (if you provide a tag number)

## SimpleXML Extractor ##
An alternate XML Extractor implementation.  Provides full XPath querying, at the cost of loading the entire XML file into memory

## GET Extractor ##
Fetch data from a RESTful endpoint.

# Transformers #
- **AutoIncrement**: provide an autoincremented value (good for when your database has an auto-incremented key, but you need to determine data relationships beforehand)
- **Callback**: run a PHP function against an entire Row (may be replaced by a PHP-ETL native Transformer)
- **Date**: reformat a Date-like string into another Date-like string
- **Defaults**: provide default values for columns
- **FaaS**: transform data with a RESTful Function-as-a-Service *not yet implemented*
- **Filter**:  *not yet implemented*
- **Map**: map a value into another, using a provided map
- **Math**: do algebra on a column's value
- **Regex**: alter a column's value based on a regex (may be replaced by a PHP-ETL native Transformer)
- **UUID**: Mint a [UUID](https://en.wikipedia.org/wiki/Universally_unique_identifier) of any version

# Loaders #

## File Loader ##
Save the data to a file (or STDOUT), in CSV, JSON or YAML format

## POST Loader ##
Send data to a RESTful endpoint as JSON

## PUT Loader ##
Send updated data (again, as JSON) to a RESTful endpoint / ID value

# Installation

**Prerequisites**
1. Git
2. Postgres database

**Process**
1. git clone
2. composer install
3. Postgres DB info into .env
4. /dev/build ?flush=1
