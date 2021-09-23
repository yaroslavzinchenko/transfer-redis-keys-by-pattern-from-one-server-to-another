<?php
# Скрипт для переноса хэшей из одного редиса в другой.

# Перед запуском скрипта необходимо пробросить порты подобным образом:
# ssh -4 -L 64820:127.0.0.1:6482 username@host,
# где 64820 - локальный порт, 6482 - порт боевого редиса.

# Шаблон, по которому выбираем ключи в редисе-источнике.
$keyPattern = 'push:*';

# Подключаемся к редису-источнику.
$redisSource = new Redis();
# port - локальный порт, который случает порт боевого редиса.
$redisSource->connect('127.0.0.1', 64820);
# Если редис без пароля, оставляем пустым.
$redisSource->auth('');

# Подключаемся к редису, в который пишем.
$redisDestination = new Redis();
# port - локальный порт, который случает порт боевого редиса.
$redisDestination->connect('127.0.0.1', 60379);
$redisDestination->auth('');
# Если необходимо выбрать базу, раскомментировать.
# По умолчанию выбрана база 0.
//$redisDestination->select(8);

# Проверяем, доступен ли редис-источник.
if ($redisSource->ping()) {
    echo 'Redis-source: Pong.' . PHP_EOL;
} else {
    echo 'Redis-source is not available. Exiting.';
    exit;
}

# Проверяем, доступен ли редис, в который пишем.
if ($redisSource->ping()) {
    echo 'Redis-destination: Pong.' . PHP_EOL;
} else {
    echo 'Redis-destination is not available. Exiting.';
    exit;
}

# Получаем названия всех ключей в редисе-источнике.
$keyNames = $redisSource->keys($keyPattern);

# Выводим количество ключей, соответствующих шаблону.
echo 'Всего ключей: ' . count($keyNames) . PHP_EOL;

# В цикле перебираем массив полученных ключей.
# В каждой итерации получаем хэш по ключу и вставляем его в конечный редис.

# Количество успешно перенесённых ключей.
$successfulTransfersCount = 0;
foreach ($keyNames as $keyName) {
    # Получаем хэш по ключу.
    $currentHash = $redisSource->hGetAll($keyName);

    # Вставляем полученный хэш во второй редис.
    if ($redisDestination->hMSet($keyName, $currentHash)) {
        echo 'OK.' . PHP_EOL;
        $successfulTransfersCount++;
    }
}

echo "Перенесено $successfulTransfersCount ключей." . PHP_EOL;