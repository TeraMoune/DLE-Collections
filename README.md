# Collections-DLE (Обновлено от  [27.09.2020](https://github.com/TeraMoune/Collections-DLE/wiki#27092020))
Модуль позволит создавать подборки новостей.

DLE 14, PHP 7>, MySQL 5.7

:exclamation::exclamation::exclamation: Я не даю гарантий на правильную работу при использовании посторонних модулей, если у Вас не получается что-то или не работает, это ваша проблема, а не моя. Или проблема конфликта с модулями (Что уже не раз случалось) На чистом движке всё ставится без проблем.

Список изменений будет [Тут](https://github.com/TeraMoune/Collections-DLE/wiki)

[F.A.Q.](https://github.com/TeraMoune/Collections-DLE/wiki/F.A.Q.)

---
 - Открытый код
 - CEO оптимизация
 - Закладки
 - Отдельная сортировка новостей
 - Добавление новости в подборки на этапе её создания
 - Разрешение на добавление группам
 - Вывод подборок в любом месте сайта
 - В админке в разделе поиск и замена так же производить замену текста в описании подборок
 - Подборки по тегам и по дополнительным полям (Если указываются теги или значения полей, очистите строку новости )
 - Быстрый поиск группы новостей по заголовку, тегам или дополнительным полям (Для каждой подборки по тегам или дополнительным полям делает запрос в базу на получение количества новостей в подборке так же не проверял работу кэша с выборкой из тегов и полей)
 - Использование с кастомным выводом новостей
 - Выводить [блок](https://github.com/TeraMoune/Collections-DLE#%D1%81%D0%BA%D1%80%D0%B8%D0%BD%D1%88%D0%BE%D1%82%D1%8B-%D0%B4%D0%BE%D0%BF%D0%BE%D0%BB%D0%BD%D0%B5%D0%BD%D0%B8%D1%8F-%D0%B1%D0%BB%D0%BE%D0%BA%D0%BE%D0%B2-%D0%B2-%D0%BF%D0%BE%D0%BB%D0%BD%D0%BE%D0%B9-%D0%BD%D0%BE%D0%B2%D0%BE%D1%81%D1%82%D0%B8) в полной новости с табами по подборкам со списком новостей, или использовать определённое доп поле новости в котором будет ID подборки для вывода новостей из указанной подборки (Платно 250 рублей)
 - Изменения подборок прямо из полной новости
 - Дополнительный [плагин](https://github.com/TeraMoune/Collections-search-plugins) для поиска.
---

# Шаблоны
`collections_editnews.tpl`
  - {collections} - Выводит выборку подборок при изменении подборок новости.

`addnews.tpl` 
  - {collections} - Выводит выборку подборок при добавлении новости.

`login.tpl` 
  - {favorites-collections-link} - Выводит ссылку на раздел закладок подборок.

`collections_item.tpl`
  - {url} - Ссылка на подборку.
  - {title} - Выводится заголовок подборки.
    - {title limit="N"} - Выводится урезанный до N количества символов, заголовок подборки.
  - {num_elem} - Количество элементов.
  - {news_read} - Общее количество просмотров новостей.
  - {news_rating} - Общее количество рейтинга.
  - {news_vote} - Общее количество голосов.
  - {news_comm} - Общее количество комментариев.  
  - {favorites} - Элемент добавления в закладки. (По умолчанию содержит svg объект <https://icomoon.io>)
    - Аналогичные обвёртки [add-favorites] text|img|obj [/add-favorites] и [del-favorites] text|img|obj [/del-favorites]
  - {date} - Дата обновления, формат вывода даты настраивается в настройках плагина.
    - {date=формат даты} - Выводит дату в заданном в теге формате.
  - {create_date} - Дата создания, формат вывода даты настраивается в настройках плагина.
  - {descr} - Описание.
    - {descr limit="N"} - Выводится урезанный до N количества символов, описание подборки.
  - {cover} - Обложка. 
  
`shortstory_collections.tpl`
  - Все теги которые можно использовать в коротких новостях.
  - Теги относящиеся к подборкам применяемые в шаблоне `fullstory.tpl`.
  
`fullstory.tpl`
  - {collections} - Выводит простые названия текстом.
  - {collections-link} - Выводит названия в виде ссылок.
  - [not-collections] ... [/not-collections] - Скрывает содержимое если подборок не назначено.
  - [add-collections] ... [/add-collections] - Данный теги обернёт содержимое в ссылку по нажатию на которою можно открыть окно с изменением подборок новости.
  
`main.tpl` И в подключённых шаблонах.
  - {collections-custom} - Выводит список подборок. Имеет параметры.
    - id - Выведет определённую подборку по ID. (По умолчанию выведет всё)
    - limit - Ограничить список подборок. (Если id не задан)
    - days - Указывает временной период.
    - template - Задать свой шаблон. (По умолчанию collections_block.tpl)
    - sort - Указывает порядок сортировки подборок. При использовании значения desc публикации сортируются по убыванию, а при использовании asc по возрастанию.
    - order - Критерий сортировки подборок, может принимать следующие значения:date, create_date, num_elem, name, rand, news_read, news_rating, news_vote, news_comm (По умолчанию date)
    - owner_news - Если использовать данный параметр со значением "true" в полной новости или короткой новости то блок будет выводить все подборки принадлежащие новости. Так же этот параметр влияет на параметр limit и отключает его. (Если нужно разрешить дайте знать)
  - {collections ids-news id="N"} - Выводит список ID новостей принадлежащих подборке, где N id подборки. (Для использования в кастомном выводе новостей)
  - [collections-show] text [/collections-show] Выводит заключённый в теге текст на странице определённой подборки.
  - [collections-alllist] text [/collections-alllist] Выводит заключённый в теге текст на странице всех подборок.
  - {c-title} Выводит название подборки \ либо meta-title.
  - {c-descr} Выводит описание подборки.
  - {c-meta-descr} Выводит meta-description \ либо описание подборки.
  - [collections-often] ... [/collections-often] - Внутри блока описывается разметка для контейнера часто встречающихся подборок. (Наличие ul тега по бокам основного)
  - {collections-often} - Основной тег в котором будет определённое количество часто встречающихся подборок среди новостей подборки, исключая саму себя.
    
Пример пользовательского вывода подборок: 
 - `{collections-custom limit="5" days="1"}` - Выведет 5 подборок которые были обновлены сегодня.
 
 Теги используемые в шаблонах при пользовательском выводе подборок `{collections-custom}` (По умолчанию: **collections_block.tpl**)
 - {url} - Ссылка на подборку.
 - {title} - Выводится заголовок подборки.
    - {title limit="N"} - Выводится урезанный до N количества символов, заголовок подборки.
 - {descr} - Описание подборки.
     - {descr limit="N"} - Выводится урезанный до N количества символов, описание подборки.
 - {num_elem} - Количество элементов.
 - {news_rating} - Сумарный рейтинг всех новостей.
 - {news_vote} - Сумарное количество голосов.
 - {news_comm} - Сумарное количество комментариев.
 - {cover} - Обложка.
 - {date} - Дата обновления, формат вывода даты настраивается в настройках плагина.
    

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
<img src="https://user-images.githubusercontent.com/44625352/55650636-9ecf6f80-57e6-11e9-86e1-cff1eec8b3fa.png" width="400">
<img src="https://user-images.githubusercontent.com/44625352/62590580-875bdc80-b8d5-11e9-8cb8-8a4fc97777b3.jpg" width="400">
<img src="https://user-images.githubusercontent.com/44625352/55650754-e0f8b100-57e6-11e9-92fa-8d9d176dbb2f.png" width="400">
<img src="https://user-images.githubusercontent.com/44625352/55650753-e0601a80-57e6-11e9-981f-2877fb2cadf8.png" width="400">
<img src="https://user-images.githubusercontent.com/44625352/55650756-e0f8b100-57e6-11e9-9a94-12bf6a018785.png" width="400">
<img src="https://user-images.githubusercontent.com/44625352/55650574-6039b500-57e6-11e9-83e6-daaff65c6129.png" width="400">
</p>

# Скриншоты дополнения блоков в полной новости
<p>
<img src="https://user-images.githubusercontent.com/44625352/62590379-ce959d80-b8d4-11e9-9d15-fcaec7633050.jpg" width="400">
<img src="https://user-images.githubusercontent.com/44625352/62590380-ce959d80-b8d4-11e9-8e8e-05e271592bcc.jpg" width="400">
</p>

### Контакты
Email: teramoune@gmail.com

Telegram: TeraMoune
