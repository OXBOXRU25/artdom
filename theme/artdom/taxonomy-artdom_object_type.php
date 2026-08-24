<?php
/**
 * Категория объектов.
 *
 * Иерархия шаблонов WordPress для таксономии не заглядывает в archive-{тип}.php,
 * поэтому без этого файла страница категории падала на archive.php базовой темы.
 * Разметка та же самая — держим один шаблон, а не две копии.
 *
 * @package artdom
 */

require get_template_directory() . '/archive-artdom_object.php';
