# Security and trusted inputs

## Private vulnerability reporting

Report suspected vulnerabilities privately to <contact@zlatanstajic.com>.
The repository's [security policy](https://github.com/zlatanstajic/php-library/blob/master/SECURITY.md)
describes report contents and coordinated disclosure. Keep sensitive details out
of public issues and pull requests.

## Remote requests

`Web_Service` sends requests to the URL supplied by the caller.
`Website::image_size()` and `File::image()` can also read remote images through
PHP's configured stream wrappers. These helpers do not enforce an application
destination allowlist. Validate allowed schemes and destinations before passing
user-controlled locations to them, including restrictions on internal services.

## Files and downloads

File, directory, spreadsheet and sorter helpers use caller-supplied paths.
Keep paths within application-owned directories and authorize access before
reading, writing, importing or serving a file. `File::prepare_download()` checks
file existence and readability; it does not check whether the current user is
allowed to download that file.

Spreadsheet exports may interpret values as formulas. For XLSX and XLS exports,
configure untrusted text columns with `data_types` entries using `type => 'TEXT'`,
as shown in [Files and spreadsheets](files.md). That option does not sanitize CSV
or OSP exports; apply an application policy before opening untrusted exported
values in a spreadsheet program.

## Database dumps and credentials

Keep `Dump`'s executable, database names, connection settings and destination
under trusted configuration. Shell quoting protects argument boundaries but
does not authorize an executable, validate database names as path components,
or restrict access to a destination. Store dump files outside web-served
directories and restrict their filesystem permissions.

`Dump` supplies the password through `MYSQL_PWD`. It omits the password from
the dump executable's arguments, but the shell invocation includes the assignment
and local process inspection may expose it. MySQL also
[discourages `MYSQL_PWD`](https://dev.mysql.com/doc/refman/8.0/en/environment-variables.html).
Run dumps only in a trusted process environment with credentials limited to the
required databases.

Keep recorded database errors and PHP warnings in access-controlled logs rather
than showing them to visitors; they can contain connection or filesystem details.

## HTML and passwords

`Website::meta()` escapes its metadata values. Asset paths and inline content
passed to `head()` or `bottom()`, and creator data rendered by `signature()` or
`signature_hidden()`, are trusted configuration rather than an HTML sanitizer.
Do not populate these values directly from request input.

Use `Password::hash()` and `Password::verify()` for password storage. The
deprecated encoding, digest and strength helpers are compatibility APIs; encoding
and digests are not substitutes for password hashing.
