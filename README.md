# Swagger API generator
PHP client libraries for RESTful-like APIs generator based on Swagger annotations. Parses API's [swagger annotations](https://github.com/zircote/swagger-php) and creates client library able to work with this API.

## Usage
`./bin/generator generate -s /path/to/api/source -d /path/to/api/client -p ApiClientNamespace`

Use --help for more information.

## Example
In [example](example) folder there is the client, generated for [FooController](example/FooController.php) annotations with command `./bin/generator generate -s ./example -d ./example -p Baz`
