<?php
# Скрипт, удаляющий ключи в редисе по шаблону.

# Перед запуском скрипта необходимо пробросить порты подобным образом:
# ssh -4 -L 64820:127.0.0.1:6482 username@host,
# где 64820 - локальный порт, 6482 - порт боевого редиса.

# Шаблон, по которому удаляем ключи.
$keyName = 'push:*';

# Подключаемся к редису.
$redis = new Redis();
$redis->connect('127.0.0.1', 64820);
$redis->auth('');
$redis->select(0);

# Проверяем, доступен ли редис.
if ($redis->ping()) {
    echo 'Pong.' . PHP_EOL;
} else {
    echo 'Redis is not available. Exiting.';
    exit;
}


# Получаем ключи по шаблону.
$redisKeys = $redis->keys($keyName);

# Выводим все ключи.
echo "Keys: ";
print_r($redisKeys);

# Удаляем эти ключи.
$deletedKeysNumber = $redis->del($redisKeys);
echo 'Number of keys deleted: ' . $deletedKeysNumber . PHP_EOL;