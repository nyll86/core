Version 0.2.4

\Common\Components\Model

Для получения экземпляра класса используем \Common\Components\Model::init('MODEL NAME');

Далее работаем с основными методами и подметодами, необходимые для выполнения условий

Методы:
 - get  		Запрос данных из БД
 - drop			Удаление данных из БД
 - update 		Обновление данных в БД
 - create 		Добавление данных в БД
 - truncate 	Очистка таблицы
 - set 			Установка предопределенных значений подключения
 - multi 		Для выполнения мультизапросов

=========================
МЕТОД get

Необходим для получения данных из БД

Используемые основные методы:
 - $model->get(array $attr): 					Запрос SELECT
 - $model->create(array $attr): 				Запрос INSERT
 - $model->drop(array $attr): 					Запрос DELETE
 - $model->update(array $attr): 				Запрос UPDATE
 - $model->multi(): 							Мультизапрос

Используемые доп. методы для запросов get, update, drop:
 - $model->table(string || array $table): 		Указать наименование таблицы
 - $model->join(array $data): 					Присоединить таблицу
 - $model->where(array $data): 					Условие WHERE
 - $model->order(array $data): 					Сортировка
 - $model->group(string || array $groupColumn): Группировка
 - $model->query(): bool						Выполнить запрос и вернуть результат запроса (true - успешно)

Используемые доп. запросы для get:
 - $model->limit(int $countRows): 				Кол-во вывода данных
 - $model->find(): array						Поиск более одной строки
 - $model->findOne(): array						Поиск одной строки

Испоьзуемые доп. запросы для create
 - $model->intoColumn(array $column)            Добавление для определенных column
 - $model->values(array $data)                  Добавляемые значения

Используемые дор. запросы для update
 - $model->setUpdate(array $data)               Данные для обновления

Остальные доп. запросы:
 - $model->lastID()                             Вернет последний ID добавленный в БД при create
 - $model->truncate(string $table)              Очистит указанную таблицу
 - $model->set(array $data)                     Необходимые установки
 - $model->startSubQuery()                      Открыть вложенный запрос
 - $model->endSubQuery(string $as)              Закрыть вложенный запрос


Example:

************
SELECT foo, bar ...

$model->get(['foo', 'bar']);

************
SELECT foo as f, bar as b ...

$model->get(['f' => 'foo', 'b' => 'bar']);

************
SELECT foo FROM tableName;

$model->get(['foo'])
	->table('tableName');

************
SELECT foo FROM tableName as t;

$model->get(['foo'])
	->table(['t' => 'tableName']);

************
SELECT foo as f FROM table1 as t JOIN table2 AS t2 ON (t.id = t2.id);

$model->get(['f' => 'foo'])
	->table(['t' => 'tableName'])
	->join([
		'table2' => [
			'as' => 't2',
			'on' => [
				'id' => 't.id'
			]
		]
	]);

************
SELECT foo as f FROM table1 as t LEFT JOIN table2 AS t2 ON (t.id = t2.id);

$model->get(['f' => 'foo'])
	->table(['t' => 'tableName'])
	->join([
		'type' 	=> 'left',
		'table2' => [
			'as' 	=> 't2',
			'on' 	=> [
				'id' => 't.id'
			]
		]
	]);

************
SELECT foo as f FROM table1 as t JOIN table2 AS t2 ON (t.id = t2.id AND t.type = t2.type);

$model->get(['f' => 'foo'])
	->table(['t' => 'tableName'])
	->join([
		'table2' => [
			'as' => 't2',
			'on' => [
				'id' => 't.id',
				'type' => 't.type'
			]
		]
	]);

************
SELECT foo as f FROM table1 as t WHERE t1.type LIKE 'foo%';

$model->get(['f' => 'foo'])
	->table(['t' => 'tableName'])
	->where([
		'__like__' => ['t1.type' => "'foo%'"]
	]);

Ниже рассмотрим все опциональные методы where (для версии 0.2.0 и выше)
************
[
	'__and__' => ['t1' => 1, 't2' => 2]		// t1 = 1 AND t2 = 2
]
----------
[
	'__or__' => ['t1' => 1, 't2' => 2]		// t1 = 1 OR t2 = 2
]
----------
[
	'__in__' => ['t1' => [1,2,3]]		// t1 IN (1,2,3)
]
----------
[
	'__not_in__' => ['t1' => [1,2,3]]		// t1 NOT IN (1,2,3)
]
----------
[
	'__like__' => ['t1' => "'foo%'"]		// t1 LIKE 'foo%'
]
----------
[
	'__like__' => ['t1' => "[$foo]%'"]		// t1 LIKE '?%'
]
----------
[
	'__not_like__' => ['t1' => "'foo%'"]	// t1 NOT IN (1,2,3)
]
----------
[
	'__gt__' => ['t1' => 2]					// t1 > 2
]
----------
[
	'__gte__' => ['t1' => 2]				// t1 >= 2
]
----------
[
	'__lt__' => ['t1' => 2]					// t1 < 2
]
----------
[
	'__lte__' => ['t1' => 2]				// t1 <= 2
]
----------
[
	'__eq__' => ['t1' => 2]					// t1 = 2
]
----------
[
	'__not_eq__' => ['t1' => 2]				// t1 != 2
]
----------
[
	'__is__' => ['t1' => 'NULL']			// t1 IS NULL
]
----------
[
	'__not_is__' => ['t1' => 'NULL']		// t1 IS NOT NULL
]
----------
[
	'__benween' => ['t1' => [1,3]]			// t1 BETWEEN 1 AND 3
]
----------
[
	'__not_between__' => ['t1' => [1,3]]	// t1 NOT BETWEEN 1 AND 3
]
----------



************
SELECT foo as f FROM table1 as t WHERE t1.type LIKE 'foo%' AND t1.id IS NOT NULL;

$model->get(['f' => 'foo'])
	->table(['t' => 'tableName'])
	->where([
		'__and__' => [
			'__like__' => ['t1.type' => "'foo%'"]
			'__not_is__' => ['t1.id' => 'NULL']
		]
	]);

************
SELECT foo as f FROM table1 as t WHERE t1.type LIKE 'foo%' AND (t1.id IS NOT NULL OR t1.id BETWEEN 1 AND 4);

$model->get(['f' => 'foo'])
	->table(['t' => 'tableName'])
	->where([
		'__and__' => [
			'__like__' => ['t1.type' => "'foo%'"]
			'__or__' => [
				'__not_is__' => ['t1.id' => 'NULL'],
				'__between__' => ['t1.id' => [1,4]]
			]
		]
	]);

************
SELECT foo as f FROM table1 as t WHERE t1.type LIKE 'foo%' AND t1.id IN (1,2,3);

$model->get(['f' => 'foo'])
	->table(['t' => 'tableName'])
	->where([
		'__and__' => [
			'__like__' => ['t1.type' => "'foo%'"]
			'__in__' => ['id' => [1,2,3]]
		]
	]);

Для предотвращения sql инъекций, например при передаче в качестве параметров значений between, рекомендуется использовать аргументы в квадратных скобках [] для подготавливаемых запросов

$model->get(['f' => 'foo'])
	->table(['t' => 'tableName'])
	->where([
		'__and__' => [
			'__like__' => ['t1.type' => "'foo%'"]
			'__or__' => [
				'__not_is__' => ['t1.id' => 'NULL'],
				'__benween__' => ['t1.id' => ["[1]","[4]"]]
			]
		]
	]);


************
SELECT * FROM table1 ORDER BY id

$model->get()
	->table('table1')
	->order('name');

$model->get()
	->table('table1')
	->order(['name' => 'id']);

************
SELECT * FROM table1 ORDER BY id DESC

$model->get()
	->table('table1')
	->order(['name' => 'id', 'desc' => true]);

************
SELECT * FROM table1 ORDER BY id DESC,foo

$model->get()
	->table('table1')
	->order([
	['name' => 'id', 'desc' => true],
	['name' => 'foo']
	]);

$model->get()
	->table('table1')
	->order([
	['name' => 'id', 'desc' => true],
	'foo'
	]);

************
SELECT * FROM table1 GROUP BY id

$model->get()
	->table('table1')
	->group('id');

************
SELECT * FROM table1 GROUP BY id, name

$model->get()
	->table('table1')
	->group(['id', 'name']);

************
SELECT * FROM table1 LIMIT 10

$model->get()
	->table('table1')
	->limit(10);

************
SELECT * FROM table1 LIMIT 10, 2

$model->get()
	->table('table1')
	->limit('10,2');


************
Выбирать все записи:
SELECT * FROM table;
$model->get()
	->table('table1')
	->find();

************
Выбирать одну запись:
SELECT * FROM table WHERE name LIKE 'test';
$model->get()
	->table('table1')
	->where(['__like__' => ['name' => "'test'"]])
	->findOne();




=========================
Метод drop

Форма работы join, where описана выше

Example:

************
DELETE FROM table;
$model->drop()
	->table('table')
	->query();

************
DELETE t FROM table as t;
$model->drop()
	->table(['t' => 'table'])
	->query();




=========================
Метод update

Example:


UPDATE tableName SET foo = 1, bar = foo WHERE id = 1;

$model->update()
	->table('tableName')
	->setUpdate([
		'foo' => 1,
		'bar' => 'foo'
	])
	-where([
		'id' => 1
	])
	->query()

Если мы обновлем параметр каким-либо аргументом от пользователя, для избежания sql инъекций, лучше выполнить как:

$model->update()
	->table('tableName')
	->setUpdate([
		'foo' => '[1]',
		'bar' => 'foo'
	])
	-where([
		'id' => '[1]'
	])
	->query()

=========================
Метод insert

Основные ключи:
 - table (обязательный) - наименование таблицы
 - attr (необязательный) - перечисленные добавляемые значения в таблице
 - values (обязательный) - значения

Пример:
INSERT INTO `table`Name VALUES (1,2,3);
 
$model->create()
	->table('tableName')
	->values([1,2,3])
	->query()
*********************

Пример:
INSERT INTO `table`Name (id, foo, bar) VALUES (1,2,3);

$model->create()
	->table('tableName')
	->intoColumn(['id', 'foo', 'bar'])
	->values([1,2,3])
	->query()

*********************

Пример:
INSERT INTO `table`Name (id, foo, bar) VALUES (1,2,3), (1,2,3), (1,2,3);

$model->create()
	->table('tableName')
	->intoColumn(['id', 'foo', 'bar'])
	->values([
		[1,2,3],
		[1,2,3],
		[1,2,3]
	])
	->query()


=========================
Метод truncate

Выполняет очистку данных

Example:

TRUNCATE tableName;

$model->truncate('tableName');


=========================
Метод set

Устаанвливает необходимые предустановки

Example:

SET NAMES UTF8;

$model->set(['NAMES' => 'UTF8']);



=========================
Метод multi

Выполняет муьтизапрос, например:

SET @i := 1;
SELECT @i = @i + 1 as i FROM table;

$model->multi()
	->set(['@i' => ':= 1'])			// устанавливаем значение 1-й
	->add()							// добавляем в общий стек
	->get([							// пишем 2-й SELECT запрос
		'i' => '@i = @i + 1'
	])
	->table('table')				// второй запрос не обязательно добавлять в стек, он добавится автоматически
	->multiFind();					// выполняем мультизапрос

Вернет 2 массива, в первом - результат выполнения первой операции, во втором - второй


====================================================================================================
Ver 0.1.3

 - Добавлена поддержка запроса вида INSERT ... SELECT Для этого доп. в метод get добавлен 2-й аргумент, указывающий на тип запроса ( (default) true - установить тип запроса SELECT, false - не устанавливать). При установке типа запроса сборщик будет собирать sql запрос сначала
 - Добавлена поддержка обновления нескольких таблиц
 - Изменены некоторые алгоритмы класса

=========================
 Пример: INSERT INTO `table`1 SELECT foo, bar FROM table2 WHERE `bar` IS NOT NULL

 $model->create()
 	->table('table1')
 	->intoColumn(['foo', 'bar'])
 	->get(['foo', 'bar'], false)
 	->table('table2')
 	->where([
 		'__not_is__' => ['var' => 'NULL']
 	])
 	->query();

=========================
 Пример: UDPATE table1 as t1, table2 as t2 ON t1.id = t2.id

 $model->update()
 	->table(['t1' => 'table1', ['t2' => 'table2']])
 	->setUpdate(['t1.id', 't2.id'])
 	->query();

====================================================================================================
Ver 0.1.4

 - Добавлена поддержка LIMIT вида LIMIT 10,1


====================================================================================================
Ver 0.1.5

 - Fixed bugs

====================================================================================================
Ver 0.1.6

 - Fixed bugs

====================================================================================================
Ver 0.1.7

 - Добавлена поддержка вложенных запросов (толкьо для запросов типа SELECT)

 =========================
 Пример:

 SELECT * FROM (
 	SELECT id, name FROM table WHERE id = 2
 ) as t GROUP BY name

 $model->get()
 	->from()
 	->startSubQuery()
 		->get(['id', 'name'])
 		->table('table')
 		->where(['id' => 2])
 	->endSubQuery('t')
 	->group('name')
 	->find();

 ====================================================================================================
Ver 0.2.0

 - Полностью переработаны методы where: добавлены дополнительные зарезервированых 17 функциий. Изменения внесены в текущий мануал

 ====================================================================================================
Ver 0.2.1

 - Fixed bugs

 ====================================================================================================
Ver 0.2.2

 - Добавлены новые операции: HAVING, UNION, UNION ALL
 - Для condition IS NOT NULL можно не указывать NULL. Для этого передаем для ключа __is__ значение как строку, а не как массив
 - Добавлена поддержка функций mysql: CONCAT, DATE_ADD, DATE_SUB. При наличии функций алиас обязателен! (function(...) as alias)

  =========================
 Пример:
 	SELECT id FROM table1
 	UNION 
 	SELECT id FROM table2

 $model->get(['id'])
 	->table('table1')
 	->union()
 	->get(['id'])
 	->table('table2')
 	->find();

  =========================
 Пример:
 	SELECT id FROM table1
 	UNION ALL
 	SELECT id FROM table2

 $model->get(['id'])
 	->table('table1')
 	->union(true)
 	->get(['id'])
 	->table('table2')
 	->find();


 =========================
 Пример:
 	SELECT count(*) cnt, name FROM table GROUP BY name HAVING cnt > 2

 $model->get(['cnt' => 'count(*)', 'name'])
 	->table('table')
 	->group('name')
 	->having(['__gt__ => ['cnt' => 2]])
 	->find();

 =========================
 Пример:
 	SELECT count(*) cnt, name FROM table GROUP BY name HAVING cnt > 2 AND name IS NOT NULL

 $model->get(['cnt' => 'count(*)', 'name'])
 	->table('table')
 	->group('name')
 	->having([
 		'__and__' => [
 			'__gt__' => ['cnt' => 2],
 			'__not_is__' => 'name'
 		]
 	])

 =========================
 Пример:
 	SELECT * FROM table WHERE id IS NOT NULL

 Раньше:
 $model->get()
 	->table('table')
 	->where([
 		'__not_is__' => ['id' => 'NULL']
 	])
 	->find();

 Сейчас:

 $model->get()
 	->table('table')
 	->where([
 		'__not_is__' => 'id'
 	])
 	->find();

 =========================
 Пример:
 	SELECT DATE_ADD(NOW(), INTERVAL 10 DAY) AS date FROM table;

$model->([
	'date' => ['date_add' => ['day' => 10]]
	])
	->tale('table')
	->find();

 =========================
 *Пример:
 	SELECT CONCAT(name, ' ', '_name') AS name FROM table;


$model->([
	'name' => [
		'concat' => [
			'name', 
			' ', 
			[
				'string' => '_name'
			]
		]
	]
	])
	->tale('table')
	->find();
 =========================
 Пример:
 	SELECT CONCAT(name, ' _name') AS name FROM table;
 	$model->([
	'name' => [
		'concat' => ['name', ' _name']
	])
	->tale('table')
	->find();

* При наличии пробела в строке значения массива, нет необходимости указывать, что это строка, а не column. Однако если значения является строкой, и пробелы отсутствуют, необходимо добавить значение как массив с укзанием ключа 'string'

 ====================================================================================================
Ver 0.2.3

 - Добавлена поддержка функций: GROUP_CONCAT(), IF, SUM(), COUNT()
 - Добавлена поддержка сортировки по нескольким столбцам: ORDER BY foo, bar DESC
 - Добавлен unit test модуля /tests/model.php
 - ФИкс множественных багов

 =========================
 Пример:
 SELECT COUNT(id) as cnt FROM table GROUP BY id;

 $model->get([
    	'cnt' => [
        	'count' => '*',
    	],
	])
    ->table('table')
    ->group('id')
    ->find()


 =========================
 Пример:
 SELECT IF(id > 0, id, -1) AS check FROM table

 $model->get([
    	'check' => ['if' => [ ['__gt__' => ['id', 0]], 'id', 0] 
    	]
	])
    ->table('table')
    ->find()

 =========================
 Пример:
 SELECT SUM(price) AS price FROM table

 $model->get([
    	'price' => ['sum' => ['price']]
    	]
	])
    ->table('table')
    ->find()

 =========================
 Пример:
 SELECT SUM(IF(price > 100, price, 0)) AS sum_price FROM table

 $model->get([
    	'sum_price' => ['sum_if' => [ ['__gt__' => ['price', 100]], 'price']]
    	]
	])
    ->table('table')
    ->find()

 =========================
 Пример:
 SELECT COUNT(IF(price > 100, price, NULL)) AS cnt_price FROM table

 $model->get([
    	'cnt_price' => ['count_if' => [ ['__gt__' => ['price', 100]], 'price']]
    	]
	])
    ->table('table')
    ->find()

=========================
 Пример:
 SELECT * FROM table ORDER BY name DESC, id

 $model->get()->table('table')->order([
 	['name' => 'name', 'desc' => true],
 	['name' => 'id']
 ])
 ->find();

  ====================================================================================================
Ver 0.2.3.1

 - Добавлена упрощеная запись запроса кол-ва строк COUNT(id) as cnt
 SELECT COUNT(*) AS `cnt` FROM table;
 $model->get('cnt')->table('table');

 - Для InnoDB таблиц необходимо запись задавать как: inno_<column name>. Обращаю внимание: слово innno_ будет опущено при выведении результатов
 SELECT COUNT(id) AS `cnt` FROM table;
 $model->get('inno_cnt')->table('table');


====================================================================================================
Ver 0.2.4

 - Добавлена поддержка синтаксиса для всех значений ``
 - Теперь для запроса кол-ва строк необходимо запросить только $model->get('cnt')->table('table'); (ранее было $model->get('cnt' => ['count' => 'cnt'])->table('table'))

====================================================================================================
Ver 0.2.4.1

 - Добавлена поддержка raw

  SELECT COUNT(*) FROM table
  $model->raw("SELECT COUNT(*) FROM table")->find();

  // пример подготавливаемого запроса:
  // где ? - 'test'
  SELECT COUNT(*) FROM table WHERE search = ?
  $model->raw("SELECT COUNT(*) FROM table WHERE search = ?", ['test'])->find();




