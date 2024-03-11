# beltranslit
Транслитератор для белорусского языка. 

## Использование
```
composer require pathologic/beltranslit
```

```
<?php

use Pathologic/BelTranslit;

//BelTranslit::convert($text, $latTrad = false, $unhac = true);
// $latTrad - true, если традиционная латиница
// $unhac - true, если преобразовывать без диактрических символов

echo BelTranslit::convert('Гарэцкі Максім', true, true);

```