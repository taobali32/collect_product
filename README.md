<h1 align="left"><a href="#">淘客</a></h1>


## Requirement

1. PHP >= 7.2
2. **[Composer](https://getcomposer.org/)**

## Installation

```shell
$ composer require "gather/jtar:dev-main" -vvv
```

## Usage

基本使用:

```php
<?php

use Gather\Factory;
$config = [
    'tk'    =>  [
        'miao_you_quan' =>  [
            'apkey'  =>  '',
            'tbname' => '',
            'pid'    => '',
        ]
    ],
];
try {
    $result = Factory::collect($config)->tk_product->productLinkId(627430191595,'2702510978');
    var_dump($result);
}catch (Exception $exception){
    //  打印日志
    $exception->getMessage();
}
```

## License

MIT
