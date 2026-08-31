# Migration from legacy helpers

The compatibility methods below remain callable unless noted otherwise, but
new code should use their replacements. They can be removed in the next major
release after downstream callers have migrated.

| Legacy API | Replacement |
| --- | --- |
| `Format::search_wizard()` | `Format::search_clause()` with PDO bindings |
| `Format::in_wizard()` | `Format::in_clause()` with PDO bindings |
| `Format::pre()` | `Format::debug()`, emitted by the application when appropriate |
| `Format::array_to_string()` | PHP's `implode()` |
| `Format::fullname()` | String interpolation |
| `Validation::comma()` | An explicit `str_replace()` at the input boundary |
| `Validation::clear_number()` | Explicit validation followed by an integer cast |
| `Password::strength()` | `Password::meets_policy()` or a maintained estimator |
| `Password::encode()` / `decode()` | Base64 functions in an encoding component |
| `Password::digest()` / `set_method()` | `Password::hash()` for passwords; PHP's `hash()` for checksums |
| `Math::even_or_odd()` | `Math::by_parity()` with an explicit index |
| `Math::iterate()` and iterator accessors | A local counter |
| `File::read_file_contents()` | `File::read_csv()` |
| `File::force_download()` | `File::prepare_download()` plus an application response |
| `Export::export_file()` | `Export::build()` or `Export::save()` |
| `Website` keywords state | Omit it; search engines ignore meta keywords |
| `User_Agent` | Feature detection, Client Hints, or a maintained parser |

`Directory_Lister::listing()` is now data-only. It no longer prints debug HTML
when `print` is supplied and no longer adds an `open` anchor to each file row.

The ignored cURL `binary_transfer` response option was removed because its PHP
constant is deprecated. `Email::show()` and `Email::mailto()` now return safe
HTML directly rather than generating calls to `document.write()`.
