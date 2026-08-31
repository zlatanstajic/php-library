# Numericals

## Percentages and parity

```php
use PHP_Library\Core\Numericals\Math;

$percentage = Math::percentage(3, 7);
echo $percentage['value']; // approximately 42.857
echo $percentage['sign'];  // approximately 42.857%

$rowClass = Math::by_parity($rowIndex, 'even', 'odd');
```

`by_parity()` is stateless. The older `even_or_odd()`, `iterate()`,
`set_iterator()` and `get_iterator()` methods use request-global static state
and are deprecated; keep iteration state in the calling scope.

## Temperature conversion

```php
use PHP_Library\Core\Numericals\Temperature;

$result = Temperature::c_to_f(20);

echo $result['value'];   // 68.0
echo $result['rounded']; // 68
echo $result['signed'];  // 68 F
```

All six directions are available: `k_to_c`, `k_to_f`, `f_to_c`, `f_to_k`,
`c_to_f` and `c_to_k`. Each accepts an integer, float or numeric string and
returns `value`, `rounded` and `signed`.
