# Security policy

## Reporting a vulnerability

Email <contact@zlatanstajic.com> privately with the subject
`php-library security report`. Do not disclose vulnerability details in a public
issue, pull request or discussion before coordinating with the maintainer.

Include the affected library version or commit, PHP version, relevant dependency
versions, a minimal reproduction using synthetic data, and the expected impact.
Remove passwords, tokens, private URLs and personal data from reports and logs.

The maintainer will review the report and coordinate remediation and disclosure
with the reporter. Response and fix times depend on availability and severity;
there is no guaranteed response deadline.

## Supported code

Please reproduce issues against the latest stable release or current `master`
when possible, and identify any older affected versions. Older releases are not
guaranteed security backports. The current development line requires PHP 8.5;
use the runtime and dependency constraints in `composer.json` for your release.

## Integration boundaries

This package provides utilities, not application authentication or authorization.
Applications must authorize file access, constrain remote destinations, and keep
database credentials and executable configuration under their own control.
See [Security and trusted inputs](docs/security.md) for the relevant API boundaries.

Check locked dependencies with `composer audit --locked` when reviewing or
updating them. This advisory check needs network access and is separate from the
five-gate `composer run check` command.
