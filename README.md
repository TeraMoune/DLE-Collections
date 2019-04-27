# Collections-DLE
Модуль позволит создавать подборки новостей.

Тестировался на версиях движка 13.0 и 13.2, PHP 7, MySQL 5.7

---
 - Открытый код
 - CEO оптимизация
 - Закладки
 - Отдельная сортировка новостей
 - Добавление новости в подборки на этапе её создания
 - Разрешение на добавление группам (Админ бог админу разрешения не нужны)
 - Вывод подборок в любом месте сайта
 - В админке в разделе поиск и замена так же производить замену текста в описании подборок
---

# Шаблоны
`login.tpl` 
  - {favorites-collections-link} - Выводит ссылку на раздел закладок подборок.

`collections_item.tpl`
  - {url} - Ссылка на подборку.
  - {title} - Выводится заголовок подборки.
    - {title limit="N"} - Выводится урезанный до N количества символов, заголовок подборки.
  - {num_elem} - Количество элементов.
  - {favorites} - Элемент добавления в закладки. (По умолчанию содержит svg объект <https://icomoon.io>)
    - Аналогичные обвёртки [add-favorites] text|img|obj [/add-favorites] и [del-favorites] text|img|obj [/del-favorites]
  - {date} - Дата обновления, формат вывода даты настраивается в настройках плагина.
    - {date=формат даты} - Выводит дату в заданном в теге формате.
  - {create_date} - Дата создания, формат вывода даты настраивается в настройках плагина.
  - {descr} - Описание.
    - {descr limit="N"} - Выводится урезанный до N количества символов, описание подборки.
  
`shortstory_collections.tpl`
  - Все теги которые можно использовать в коротких новостях.
  
`fullstory.tpl`
  - {collections} - Выводит простые названия текстом.
  - {collections-link} - Выводит названия в виде ссылок.
  - [not-collections] text [/not-collections] - Скрывает содержимое если подборок не назначено.
  
`main.tpl` И в подключённых шаблонах.
  - {collections} - Выводит список подборок. Имеет параметры.
    - id - Выведет определённую подборку по ID. (По умолчанию выведет всё)
    - limit - Ограничить список подборок. (Если id не задан)
    - days - Указывает временной период.
    - template - Задать свой шаблон. (По умолчанию collections_block.tpl)
    - sort - Указывает порядок сортировки подборок. При использовании значения desc публикации сортируются по убыванию, а при использовании asc по возрастанию.
    - order - Критерий сортировки подборок, может принимать следующие значения:date, create_date, num_elem, name, rand. (По умолчанию date)
    
Пример: 
 - `{collections limit="5" days="1"}` - Выведет 5 подборок которые были обновлены сегодня.
    

# ЧПУ
В файле `.htaccess` Добавить ниже строки `RewriteEngine On`
```
RewriteRule ^collections/([0-9]+)-(.*)/page/([0-9]+)(/?)+$ index.php?do=collections&id=$1&cstart=$3 [L]
RewriteRule ^collections/([0-9]+)-(.*)(/?)+$ index.php?do=collections&id=$1 [L]
RewriteRule ^collections/favorites(/?)+$ index.php?do=collections&action=favorites [L]
RewriteRule ^collections/favorites/page/([0-9]+)(/?)+$ index.php?do=collections&action=favorites&cstart=$1 [L]
RewriteRule ^collections/page/([0-9]+)(/?)+$ index.php?do=collections&cstart=$1 [L]
RewriteRule ^collections(/?)$ index.php?do=collections [L]
```
# Скриншоты

<p>
<img src="https://user-images.githubusercontent.com/44625352/55650636-9ecf6f80-57e6-11e9-86e1-cff1eec8b3fa.png" width="430">
<img src="https://user-images.githubusercontent.com/44625352/55650755-e0f8b100-57e6-11e9-8b2f-b27bcf14f3af.png" width="430">
<img src="https://user-images.githubusercontent.com/44625352/55650754-e0f8b100-57e6-11e9-92fa-8d9d176dbb2f.png" width="430">
<img src="https://user-images.githubusercontent.com/44625352/55650753-e0601a80-57e6-11e9-981f-2877fb2cadf8.png" width="430">
<img src="https://user-images.githubusercontent.com/44625352/55650756-e0f8b100-57e6-11e9-9a94-12bf6a018785.png" width="430">
<img src="https://user-images.githubusercontent.com/44625352/55650574-6039b500-57e6-11e9-83e6-daaff65c6129.png" width="430">
</p>

teramoune@gmail.com на всякий случай.
