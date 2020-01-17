# cerulean
Web-based data migration toolkit based on Silverstripe and PHP-ETL

# Why? #
Sometimes you need to translate data from one system to another.  The ETL model (Extract - Transform - Load) works well to structure that transfer, by identifying concrete steps to take.  But how to you turn those steps into code?

[PHP-ETL](https://github.com/leomarquine/php-etl) provides a simple, clean toolkit for defining ETL processes.  It requires, however, commandline scripting knowledge, and not all folks who work with data transformations have that skill.  Enter Cerulean!

Cerulean provides a graphic user interface on top of PHP-ETL, as well as additional features like user management and file uploads.  By defining a JSON Schema for an ETL Process (as well as for remote data sources), and leveraging the awesome work of [JSON Editor](https://github.com/json-editor/json-editor) to turn that schema into a web form, Cerulean makes it easier for end users to define ETL processes and connect them data sources.

Additionally, several extra Extractors, Transformers and Loaders are provided (as necessitated by the author's use case)

# Extractors #

## MARC Extractor ##
Extract data from a MARC record, either at the biblio or item level (if you provide a tag number)

## SimpleXML Extractor ##
An alternate XML Extractor implementation.  Provides full XPath querying, at the cost of loading the entire XML file into memory.

# Transformers #
- AutoIncrement: provide an autoincremented value (good for when your database has an auto-incremented key, but you need to determine data relationships beforehand)
- Callback: run a PHP function against an entire Row (may be replaced by a PHP-ETL native Transformer)
- Date: reformat a Date-like string into another Date-like string
- Defaults: provide default values for columns
- Filter:  *not yet implemented*
- Map: map a value into another, using a provided map
- Math: do algebra on a column's value
- Regex: alter a column's value based on a regex (may be replaced by a PHP-ETL native Transformer)

# Loaders #

## File Loader ##
Save the data to a file (or STDOUT), in CSV, JSON or YAML format

# Installation

**Prerequisites**
- Postgres database

1. git clone
2. composer install
