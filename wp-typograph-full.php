<?php
/*
Plugin Name: ВП Типограф Фулл
Version: 2.3.5
Plugin URI: http://iskariot.ru/wordpress/typo/#typo-full
Description: Автоматическая замена спецсимволов, тире, кавычек, исправления и неразрывные конструкции, более верные с точки зрения русской типографики. Обработка кавычек, тире, спецсимволов вне безопасных блоков (pre, code, samp, textarea, script), правка кавычек внутри code, кликабельные ссылки в комментариях. Также правится неправильное форматирование TinyMCE, доступна автоматическая расстановка неразрывных конструкций. Гибкие настройки через админку.
Author: Сергей М.
Author URI: http://iskariot.ru/
*/
/*
 * Положить в /wp-content/plugins/
 * Зайти в систему администрирования WordPress и на вкладке "Плагины" активировать плагин
 * Совместимо со всеми версиями Wordpress
*/
/*  Основан на идее "Типографа" от Оранского Максима и Макарова Александра
 * http://rmcreative.ru/article/programming/typograph/  
  * ------------------------------------------------------------
 * использует скрипт Кавычкер Дмитрия Смирнова
 * http://spectator.ru/download
 * ------------------------------------------------------------
 * а также Format Control  от Владимира Колесникова
 * http://blog.sjinks.org.ua/wordpress/patches/224-formatcontrol-plugin-to-solve-formatting-bugs-in-wordpress/ 
 */
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/


/**
* НАСТРОЙКИ
* Работа с настройками и хранением их в базе
* 
*
*/
//добавляем пункт в меню "Настройки"
add_action('admin_menu', 'typoFullАdmin');	
function typoFullАdmin()
{
	add_options_page('Настройки Типографа', 'Типограф',  8, __FILE__, 'typoFullOptionsPage');
}
//настройки по умолчанию
register_activation_hook(__FILE__,'typoActivation'); 
function typoActivation(){
	add_option('wp_typograph_options', '0 1 0 1 1 1 1 0 1 0 0 0 1', 'Typo Options');
}

//забираем значения опций
global $typo_options;
$typo_options = get_option('wp_typograph_options');
global $typo_options_list;
$typo_options_list=explode(" ",$typo_options);

/**
* Страница конфигурации
* 
*
*/
function typoFullOptionsPage() {
global $typo_options;
global $typo_options_list;
//загружаем в базу, если отправлено на сохранение
if( $_POST['typo_hidden'] == 'Y' ){
	$typo_options=
		intval($_POST['op1'])." ".intval($_POST['op2'])." ".intval($_POST['op3'])." ".
		intval($_POST['op4'])." ".intval($_POST['op5'])." ".intval($_POST['op6'])." ".
		intval($_POST['op7'])." ".intval($_POST['op8'])." ".intval($_POST['op9'])." ".
		"0 0 0 1";
		
	
	update_option('wp_typograph_options', $typo_options);
	$typo_options = get_option('wp_typograph_options');
	$typo_options_list=explode(" ",$typo_options);
	?>
	<div id="message" class="updated fade"><p><strong>Опции сохранены в базе</strong></p></div>
	<?php
}
?>
<div class="wrap">
<div id="icon-options-general" class="icon32">
<br/>
</div>
<h2>Настройки WP Typograph Full</h2>
<form name="form_typo_Options" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
	<input type="hidden" name="typo_hidden" value="Y">
	<p><strong>Основной функционал</strong>: замена символа копирайта, тире (в обычных фразах и диалогах), кавычки (елочки, вложенные лапки), между цифрами минус. Внутри некоторых тегов замен нет, в примерах кода кавычки исправляются на машинописные, а угловые скобочки - на HTML-сущности.<br /></p>
	<h2>Вторичные функции</h2>
	<table style="width:100%;text-align:center">
	<tr style="background:#DDD">
	<td style="font-weight:bold">&nbsp;</td>
	<td style="font-weight:bold">Заголовки</td>
	<td style="font-weight:bold">Основной текст</td>
	<td style="font-weight:bold">Комментарий</td>
	</tr>
	<tr>
	<td><strong>Спецсимволы</strong><br /><em>(r),(tm),1/2 и т.п.</em></td>
	<td><input type="checkbox" name="op1" value="1"
		<?php if($typo_options_list[0]=='1')  echo ' checked=""' ?>></td>
	<td><input type="checkbox" name="op2" value="1"
		<?php if($typo_options_list[1]=='1')  echo ' checked=""' ?>></td>
	<td><input type="checkbox" name="op3" value="1"
		<?php if($typo_options_list[2]=='1')  echo ' checked=""' ?>></td>
	</tr>
	<tr>
	<td><strong>Неразрывные конструкции</strong><br /><em>(на предлогах, коротких словах и пр.)</em></td>
	<td><input type="checkbox" name="op4" value="1"
		<?php if($typo_options_list[3]=='1')  echo ' checked=""' ?>></td>
	<td><input type="checkbox" name="op5" value="1"
		<?php if($typo_options_list[4]=='1')  echo ' checked=""' ?>></td>
	<td><input type="checkbox" name="op6" value="1"
		<?php if($typo_options_list[5]=='1')  echo ' checked=""' ?>></td>
	</tr>
	<tr>
	<td><strong>Исправления</strong><br /><em>(повторяющиеся слова, пунктуация, пробелы)</em></td>
	<td><input type="checkbox" name="op7" value="1"
		<?php if($typo_options_list[6]=='1')  echo ' checked=""' ?>></td>
	<td><input type="checkbox" name="op8" value="1"
		<?php if($typo_options_list[7]=='1')  echo ' checked=""' ?>></td>
	<td><input type="checkbox" name="op9" value="1"
		<?php if($typo_options_list[8]=='1')  echo ' checked=""' ?>></td>
	</tr>
	</table>
	<br /><br />
		
	<p><input class="button-primary" type="submit" name="Submit" value="Сохранить изменения" /></p>
	</form>
</div>
<?php

}//typoOptionsPage

/**
* ИНТЕРФЕЙС ПЛАГИНА
* 
*
*/
//удаляем фильтры
	remove_filter('category_description', 'wptexturize');
	remove_filter('single_post_title', 'wptexturize');
	remove_filter('the_title', 'wptexturize');
	remove_filter('the_content', 'wptexturize');
	remove_filter('the_excerpt', 'wptexturize');
	remove_filter('link_title', 'wptexturize');
	remove_filter('single_cat_title', 'wptexturize');
	remove_filter('single_tag_title', 'wptexturize');
	remove_filter('single_post_title', 'wptexturize');
	remove_filter('comment_text', 'wptexturize');
	remove_filter('comment_text', 'convert_chars'); 
	remove_filter('comment_text', 'make_clickable',9);
	remove_filter('comment_text', 'wpautop', 30);
//добавляем свои
	add_filter('single_post_title', 'typoFullFilterHeader', 9);
	add_filter('the_title', 'typoFullFilterHeader', 9);
	add_filter('link_title', 'typoFullFilterHeader', 9);
	add_filter('list_cats', 'typoFullFilterHeader', 9);
	add_filter('single_cat_title', 'typoFullFilterHeader', 9);
	add_filter('single_tag_title', 'typoFullFilterHeader', 9);
	add_filter('single_post_title', 'typoFullFilterHeader', 9);
	add_filter('the_content', 'typoFullFilterText', 9);
	add_filter('the_excerpt', 'typoFullFilterText', 9);
	add_filter('category_description', 'typoFullFilterText', 9);
	add_filter('comment_text', 'typoFullFilterComment', 9);	


/**
* ФОРМАТИРУЕМ ЗАГОЛОВКИ
* 
*
*/
	function typoFullFilterHeader($text, $opt_spec = -1, $opt_nobr = -1, $opt_correct = -1, $flag=false){
		//если настройки по умолчанию, берем настройки для данного типа блока (заголовок)
		global $typo_options_list;
		if(-1==$opt_spec) {
			if(1==$typo_options_list[0]) $opt_spec=1; else $opt_spec=0;
			if(1==$typo_options_list[3]) $opt_nobr=1; else $opt_nobr=0;
			if(1==$typo_options_list[6]) $opt_correct=1; else $opt_correct=0;
			}
	
		if(!$flag) {
		$text=preg_replace('~(\s*)\.$~','', $text);
		}
	
/*ОСНОВНОЙ ФУНКЦИОНАЛ*/		
		//апостроф апострофом
		$text = preg_replace("('|&#146;)", "&#39;", $text);
		//отключено, потому что надо давать возможность вставлять самому!
		//правим неправильные кавычки
		//$text = preg_replace("(«|»|„|“|”|&quot;|&ldquo;|&rdquo;)", "\"", $text);
		//правим неправильные тире
		//$text = preg_replace("(&ndash;|&minus;|–|−|—|—|—)", "-", $text);

/*СПЕЦСИМВОЛЫ*/
	if (1==$opt_spec)	{
		$replace=array(
			//Многоточие
			//Больше 5 - авторский знак
			"~(?<!\.)\.{2,5}(?!\.)~" => "...",
			//правим заодно правильную пунткуацию у восклицательного и вопросительного знаков
			"~([\?!])\.{2,5}(?!\.)~" => "$1..",
			//закомментируйте предыдущее и раскомментируйте следующую строку,
			//если нужен знак многоточия
			//"~(?<!\.)\.{2,5}(?!\.)~" => "&hellip;",
			
			// Знаки (c), (r), (tm)
			'~\((c|C|с|С)\)~i' 	=> '&copy;',
			'~\((r|R)\)~i' 	=>	'<sup><small>&reg;</small></sup>',
			'~\((tm|TM|тм|ТМ)\)~i'	=>	'<sup>&trade;</sup>',
			//знак умножения
			'~\b(\d+)(х|x)(\d+)\b~' => '$1&times;$3',
			// Спецсимволы для 1/2 1/4 3/4
			'~\b1/2\b~'	=> '&frac12;',
			'~\b1/4\b~' => '&frac14;',
			'~\b3/4\b~' => '&frac34;',
			//Плюс-минус
			'~([^\+]|^)\+-~' => '$1&plusmn;',
			//Меры измерения в степени
            '~\s(мм|см|м|км|г|кг|т|б|кб|Кб|Мб|Гб|кбит|Кбит|мбит|Мбит|гбит|Гбит)(\d+)\b~' => '&nbsp;$1<sup>$2</sup>',
			//Меры измерения в квадрате
             '~\s(кв\.(мм|г|л|м|см|км))\b~' => '&nbsp;$2<sup>2</sup>',
			//Меры измерения в кубе
             '~\s(куб\.(мм|г|л|м|см|км|л))\b~' => '&nbsp;$2<sup>3</sup>',			
			 
			 );
		$text=preg_replace(array_keys($replace), array_values($replace), $text);/**/
		}

/*ИСПРАВЛЕНИЯ*/		
	if(1==$opt_correct) {	
	$replace=array(
			//убираем ненужные табуляции и пробелы
			"~( |\t)+~" => " ",
			
			//оторвать тире от знаков препинания
			'~-([\.,])~' => '- $1',
			'~([\.,])- ?~' => '$1 - ',
			 
			// Разносим неправильные кавычки
			'~([^"]\w+)"(\w+)"~' => '$1 "$2"',
			'~"(\w+)"(\w+)~' => '"$1" $2',
			'~&nbsp;"~' => ' "',
			
			// Оторвать скобку от слова
			'~(\w)\(~' => '$1 (',
			'~\)(\w)~' => ') $1',
			
			//Слепляем скобки со словами
		     '~(?<![:;8=-])\(\s~s' => '(',
			 '~\s\)~s' => ')',
			 
			//Знаки с предшествующим пробелом... нехорошо!
			'~(\S) +([,])~' => '$1$2',
			'~([?,!])([-А-Яа-я"])~' => '$1 $2',
			 
			 //неправильное количество других знаков препинания
			/*"~[,]{2,6}~" => ',',
			"~[;]{2,6}~" => ';',
			"~[:]{3,6}~" => ':',
			"~[\?]{2}~" => '?',
			"~[\?]{4,}~" => '???',
			"~[!]{4,}~" => '!!!',
			"~[!]{2}~" => '!',/**/
			
			//забытый пробел после тире в начале строки
			"~(^|\n)(--?)(?!\s|-)~" => "$1- ",
			
			//отбиваем кавычки от пунктуации
			'~([,\.!?:-;])(")~' => '$1 $2',
			
			//дважды повторяющиеся слова убираем (выключено)
			//"~(\S+)\s\\1~" => "$1",
			//многажды повторяющиеся слова убираем (выключено)
			//"~(\S+)(\s\\1)+~" => "$1",
			);
		$text=preg_replace(array_keys($replace), array_values($replace), $text);/**/
	}

/*ОСНОВНОЙ ФУНКЦИОНАЛ*/
		$replace=array(
			
			//дефисы перед закрывающими тегами
			//ну вообще, это, наверное, зря
			"~-</~" => "- </",
			
			 // Знак дефиса или два-три знака дефиса подряд — на знак длинного тире.
			// + Нельзя разрывать строку перед тире
			// Добавлена обработка диалогов
			"/( |&nbsp;|(?:(?U)<.*>))(--?-?)(?=\s)/" => '$1&mdash;',
			"~(\n|^)(--?)(?=\s)~" => "$1&mdash;",
			);
		$text=preg_replace(array_keys($replace), array_values($replace), $text);/**/
		
		//только если нобры разрешены
		if(1==$opt_nobr){
			$replace=array("(( |\t|&nbsp;)+&mdash;)" => '&nbsp;&mdash;',);
			$text=preg_replace(array_keys($replace), array_values($replace), $text);/**/
			}
		
		
		//ИНТЕРВАЛЬНЫЕ ТИРЕ
		$pre_days='(понедельник|вторник|среда|четверг|пятница|суббота|воскресенье)';
		$pre_month='((январ|феврал|апрел|июн|июл|сентябр|октябр|ноябр|декабр)(ь|я)|(март|август)(а)?)';
		
		$replace=array(
			// Знак дефиса, ограниченный с обоих сторон цифрами — на знак минуса
			//убрано, потому что так часто публикуются счета, номера телефонов и прочее.
			//'/(?<=\d)-(?=\d)/' => '&minus;',
			//'/(?<=\s)-(?=\d)/' => '&minus;',
			//'/(?<=\d)-(?=\s)/' => '&minus;',
			
			//диапазоны дат, периодов?
			//тоже всегда можно закомментировать ^_^
			'/([^\d]\d{4})(&minus;|-)(\d{4}[^\d])/' => '\\1&mdash;\\3',
			'/'.$pre_days.'( |&nbsp;)?(-|&minus;|&mdash;)( |&nbsp;)?'.$pre_days.'/i' => '$1&mdash;$5',
			'/('.$pre_month.')( |&nbsp;)?(-|&minus;|&mdash;)( |&nbsp;)?(\d|'.$pre_month.')/i' => '$1&mdash;$10',
			'/('.$pre_month.'|\d)( |&nbsp;)?(-|&minus;|&mdash;)( |&nbsp;)?('.$pre_month.')/i' => '$1&mdash;$10',
			/**/
			
			);
		$text=preg_replace(array_keys($replace), array_values($replace), $text);/**/

		//вносим кавычки под тег, если они стоят вне (позже вернем)
		$text = preg_replace('~"(<[^\/][^>]*>)~','$1"',$text);
		$text = preg_replace('~(<\/[^>]*>)"~','"$1',$text);
		
		//КАВЫЧКИ
		// Использован "кавычкер" Version 3.0
        // Copyright (c) Dmitry Smirnov (Nudnik.ru)
		  $text = preg_replace( "/\"\"/i", "\"\"", $text );
	      //$text = preg_replace( "/\"\.\"/i", "&quot;.&quot;", $text );
	      $_text = "\"\"";
		  
      /*while ($_text != $text)
	      { 
		        $_text = $text;
		        $text = preg_replace( "/(^|\s|\201|\200|>)\"([0-9A-Za-z\'\!\s\.\?\,\-\&\;\:\_\200\201]+(\"|&#148;))/i", "\\1&ldquo;\\2", $text );
		       $text = preg_replace( "/((&ldquo;)([A-Za-z0-9\'\!\s\.\?\,\-\&\;\:\200\201\_]*)[A-Za-z0-9][\200\201\?\.\!\,]*)\"/i", "\\1&rdquo;", $text );
		      }//while/**/
			$text = preg_replace("#(\>|^)#", "$1 ", $text);
			
	        $text = preg_replace ("/([¬(\s\"])(\")([^\"]*)([^\s\"(])(\")/", "\\1`laquo;\\3\\4`raquo;", $text); 
	        // что, остались в тексте нераставленные кавычки? значит есть вложенные!
	        if (strrpos ($text, '"'))
	        { 
	            // расставляем оставшиеся кавычки (еще раз).
	            $text = preg_replace ("/([¬(\s\"])(\")([^\"]*)([^\s\"(])(\")/", "\\1`laquo;\\3\\4`raquo;", $text); 
	            // расставляем вложенные кавычки
	            // видим: комбинация из идущих двух подряд открывающихся кавычек без закрывающей
	            // значит, вторая кавычка - вложенная. меняем ее и идущую  после нее, на вложенную
//	        while (preg_match ("/(&laquo;)([^»]*)(&laquo;)([^»]*)(&raquo;)/", $text, $regs))
	        while (preg_match ("/(`laquo;)([^`]*)(`laquo;)([^`]*)(`raquo;)/", $text, $regs)) {
				$text = str_replace ($regs[0], "{$regs[1]}{$regs[2]}&bdquo;{$regs[4]}&ldquo;", $text);
				}
	        } ; 
			
			$text = preg_replace("#\> #", ">", $text);
			
			//заменяем елочки обратно
			$text = preg_replace("#`(l|r)aquo;#", "&$1aquo;", $text);

/*НЕРАЗРЫВНЫЕ*/
	if (1==$opt_nobr) {
		include 'nobr.php';
		//ОСНОВНЫЕ ЗАМЕНЫ
		/*$prepos = '(:?а|в|во|вне|и|или|к|о|с|у|о|со|об|обо|от|ото|то|на|не|ни|но|из|изо|за|уж|на|по|под|подо|пред|предо|про|над|надо|как|без|безо|что|да|для|до|там|ещё|их|или|ко|меж|между|перед|передо|около|через|сквозь|для|при|я)';
			$replace=array(
		     //Неразрывные названия организаций и абревиатуры форм собственности
		     // ~ почему не один &nbsp;?
             // ! названия организаций тоже могут содержать пробел !
			 //!!неправильно - имена организаций могут быть сколь угодно длинными
			 //кроме того, не всегда заключаются в кавычки
			'~(:?ООО|ОАО|ЗАО|ЧП|ИП|НПФ|НИИ)\s+(.*)~' => '$1&nbsp;$2',
			
			//Не разделять 2007 г., ставить пробел, если его нет. Ставит точку, если её нет.
			'~([0-9]{2,5})\s*(гг|г|Г)\.?~s' => '$1&nbsp;$2. ',
			
			//Нельзя отрывать имя собственное от относящегося к нему сокращения.
			 //Например: тов. Сталин, г. Воронеж
 			 //Ставит пробел, если его нет. И точку тоже ставит на всякий случай. :)
			'~(\n| )(г|Г|тов|Тов|гр|Гр|пос|c|ул|д|пер|м)\.\s*([А-Я]+)~s' => '$1$2.&nbsp;$3',
		
			//Единицы измерения (список неполный :)
			 //Неразрывный пробел между цифрой и единицей измерения
			 '~([0-9]+)\s*(мм|см|м|км|г|кг|т|б|кб|Кб|Мб|Гб|кбит|Кбит|мбит|Мбит|гбит|Гбит)\b~s' => '$1&n1bsp;$2',
			
			// Нельзя отрывать частицы бы, ли, же от предшествующего слова, например: как бы, вряд ли, так же.
			"#(?<=\S)(\s+)(бы|б|же|ж|ли|ль|либо|или)([ \s)!,:;?.])#i" => '&nbsp;$2$3',
			//толибонибудь
			"#(>|;|\s)(([^>; ])*)-(то|либо|нибудь)([ \s)!?.,:;])#i" => '$1<span style="white-space:nowrap">$2-$4</span>$5',
			//коекой
			"#\b(кое|кой)-(\S*)\b#i" => '<span style="white-space:nowrap">$1-$2</span>$4',
			//из-за из-под
			"~(\b(И|и)з-(за|под)\b)~is" => '<1span style="white-space:nowrap">$1<1/span>',

			
		/*
							 
			 
			
			
			
			//до н.э, н.э, заодно фиксится пробел
			"#(\d)\s*(до)\s+(н\.э\.)#i" => "$1&nbsp;$2&nbsp;$3",
			"#\s*(и)\s+(т\.(д|п)\.)#i" => " $1&nbsp;$2",
			"#(\d)\s*(н\.э\.)#i" => "\\1&nbsp;\\2",
			
			// Нельзя оставлять в конце строки предлоги и союзы
			'/(?<=\s|^|\W)'.$prepos.'(\s+)/i' => '$1&nbsp;',
			
			// Неразрывный пробел после инициалов.
			//по-русски обычно ставятся после, да
			'~([А-ЯA-Z]\.)\s?([А-ЯA-Z]\.)\s?([А-Яа-яA-Za-z]+)~s' => '$1$2&nbsp;$3',
			'~([А-Яа-яA-Za-z]+)\s?([А-ЯA-Z]\.)\s?([А-ЯA-Z]\.)~s' => '$1&nbsp;$2$3',

			// Русские денежные суммы, расставляя пробелы в нужных местах.
			'~(\d+)\s?(млн.|тыс.|млрд.)?\s?(руб.|коп.)~s'	=>	'$1&nbsp;$2&nbsp;$3',
			'~(\d+)\s?(руб.|коп.)~s'	=>	'$1&nbsp;$2',

			//Номер версии программы пишем неразрывно с буковкой v.
			'~([vв]\.) ?([0-9])~i' => '$1&nbsp;$2',
			'~(\w) ([vв]\.)~i' => '$1&nbsp;$2',/
		);
		$text=preg_replace(array_keys($replace), array_values($replace), $text);/**/
	}
	
		//если запрещены неразрывные, убираем их
		if(1!=$opt_nobr){
			$text=preg_replace("~&nbsp;~", " ", $text);/**/
		}
		
		$text=preg_replace("~^&nbsp;~", "", $text);/**/
		
		return trim($text);
	}

/**
* Коллбэк для безопасных блоков
* 
*
*/
	function _stack($matches = false){
		static $safe_blocks = array();
		if ($matches !== false){
			$key = '<'.count($safe_blocks).'>';
			$safe_blocks[$key] = $matches[0];
			return $key;
		}
		else{
			$tmp = $safe_blocks;
			unset($safe_blocks);
			return $tmp;
		}
	}


/**
* ФОРМАТИРУЕМ ТЕКСТ
* 
*
*/
	function typoFullFilterText($text, $opt_spec = -1, $opt_nobr = -1, $opt_correct = -1){
		//если настройки по умолчанию, берем настройки для данного типа блока (текст)
		global $typo_options_list;
		if(-1==$opt_spec) {
			if(1==$typo_options_list[1]) $opt_spec=1; else $opt_spec=0;
			if(1==$typo_options_list[4]) $opt_nobr=1; else $opt_nobr=0;
			if(1==$typo_options_list[7]) $opt_correct=1; else $opt_correct=0;
			}
		
		//если в code есть переносы автоматически оборачиваем в pre
		//$text=preg_replace("~(?!<pre[^>]*>)\s*(<code[^>]*>[^>]*\n[^>]*<\/code>)\s*(?!<\/pre>)~","\n<pre>$1</pre>\n",$text);
		//в пре форматим внутренние code 
		preg_match_all( "#<pre([^>]*)>\s*<code([^>]*)>(.*)<\/code>\s*<\/pre>#isU", $text, $matches ); 
		$n = count( $matches[1] );
		for($i=0;$i<$n;$i++){
			$result = preg_replace("~<(\/?)code([^>]*)>~","<$1~~code$2>", $matches[3][$i]);
			$text = str_replace( $matches[0][$i], "<pre".$matches[1][$i]."><code".$matches[2][$i].">".$result."</code></pre>", $text );
		}
			
		//наши безопасные блоки
		$_safeBlocks = array(
			'<code[^>]*>' => '<\/code>',
			'<pre[^>]*>' => '<\/pre>',
			'<script[^>]*>' => '<\/script>',
			'<style[^>]*>' => '<\/style>',
			'<textarea[^>]*>' => '<\/textarea>',
			'<form[^>]*>' => '<\/form>',
			'<samp>' => '<\/samp>',
			'<kbd>' => '<\/kbd>',
			'{\[' => '\]}',
			'<!--' => '-->',
		);

		//ВНЕ БЕЗОПАСНЫХ БЛОКОВ
		$pattern = '(';
		foreach ($_safeBlocks as $start => $end){
			$pattern .= "$start.*$end|";
		}
		$pattern .= '<[^>]*[\s][^>]*>|\[[^\]]*\])';
		//$pattern .= '<[^>]*[\s][^>]*>)';
		$text = preg_replace_callback("~".$pattern."~isU", '_stack', $text);
			//тире кавычки и пр. из предыдущей функции
			$text = typoFullFilterHeader($text, $opt_spec, $opt_nobr, $opt_correct, true);
		$text = strtr($text, _stack());
		
		//ВНЕ КОДОВ, ПРЕ, ПРИМЕРОВ делаем пост-обработку
		$pattern = '(';
		foreach ($_safeBlocks as $start => $end){
			$pattern .= "$start.*$end|";
		}
		$pattern .= '\[[^\]]*\])';
		$text = preg_replace_callback("~".$pattern."~isU", '_stack', $text);
			//выносим пробел за тег (нет, потому что он может быть в коде)
			$text = preg_replace("~(<[^\/][^>]+>) +~",' $1',$text);
			$text = preg_replace("~ +(<\/[^>]+>)~",'$1 ',$text);
			//ну и за ссылку
			//$text = preg_replace("~(?!\s)(<a[^>]*>)\s?~",' $1',$text);
			//выносим кавычки за ссылку
			$text=preg_replace('/<a([^>]+)>&laquo;([^<]+)&raquo;<\/a>/usi', '&laquo;<a\1>\2</a>&raquo;', $text);
			$text=preg_replace('/<a([^>]+)>&bdquo;([^<]+)&ldquo;<\/a>/usi', '&bdquo;<a\1>\2</a>&ldquo;', $text);
		$text = strtr($text, _stack());
		
		//В ЦИТАТАХ МЕНЯЕМ МЕСТАМИ КАВЫЧКИ
		/*preg_match_all( "#<blockquote([^>]*)>(.+)<\/blockquote>#isU", $text, $matches ); 
		$n = count( $matches[0] );
		for($i=0;$i<$n;$i++){
		$replace=array(
			//вот эти кавычки, Дэвид Блейн!
			"~&laquo;~" => "`bdquo;",
			"~&raquo;~" => "`ldquo;",
			"~&bdquo;~" => "&laquo;",
			"~&ldquo;~" => "&raquo;",
			"~`(b|l)~" => "&$1",
		);
		$result = trim(preg_replace(array_keys($replace), array_values($replace), $matches[2][$i]));
		$text = str_replace( $matches[0][$i], "<blockquote".$matches[1][$i].">".$result."</blockquote>", $text );
		}/**/
		
		//В БЕЗОПАСНЫХ БЛОКАХ
		preg_match_all( "#<code([^>]*)>(.+)<\/code>#isU", $text, $matches ); 
		$n = count( $matches[0] );
		for($i=0;$i<$n;$i++){
		$replace=array(
			//делаем машинописные кавычки
			"~(\"|“|”|„)~" => '"',
			//и правильный для кода дефис
			"~–|−~" => "-",
			//спецсимволы - 
			//"#&(?!amp;)#" => "&amp;",
			//делаем теги в code текстом
			"~<~" => "&lt;",
			"~>~" => "&gt;",
			//апостроф - апострофом
			"~(&#39;|&#146;)~" => "'",
			//возвращаем
			"/~~code/" => "code",
		);
		$result = trim(preg_replace(array_keys($replace), array_values($replace), $matches[2][$i]));
		$text = str_replace( $matches[0][$i], "<code".$matches[1][$i].">".$result."</code>", $text );
		}/**/
		
	
		//на всякий случай - против косячного плагина
		$text = preg_replace('~(<p>(\s|\n)*<script)~', "<script", $text);
		$text = preg_replace('~(<\/script>(\s|\n)*</p>)~', "</script>", $text);
		$text=preg_replace("~\[CDATA\[(\n\n|</p>\n<p>)~","[CDATA[\n",$text);
		
		return $text;
	}



/**
* ФОРМАТИРУЕМ КОММЕНТАРИИ
* 
*
*/
	function typoFullFilterComment($text){
		//берем настройки для данного типа блока (комментарии)
		global $typo_options_list;
		if(1==$typo_options_list[2]) $opt_spec=1; else $opt_spec=0;
		if(1==$typo_options_list[5]) $opt_nobr=1; else $opt_nobr=0;
		if(1==$typo_options_list[8]) $opt_correct=1; else $opt_correct=0;
		
		//бывают косяки от сторонних плагов, когда </p> идет внутри </a> - а поставим мы пробел!
		$text = preg_replace( "~<\/p>~", " </p>", $text );
		
		//кликабельные ссылки
		$text=preg_replace("~(^|\s|-|:| |\()(http(s?)://|(www\.))((\S{25})(\S{5,})(\S{15})([^\<\s.,>)\];'\"!?]))~i", "\\1<a href=\"http\\3://\\4\\5\">\\4\\6...\\8\\9</a>", $text);
		$text=preg_replace("~(^|\s|-|:|\(| |\xAB)(http(s?)://|(www\.))((\S+)([^\<\s.,>)\];'\"!?]))~i", "\\1<a href=\"http\\3://\\4\\5\">\\4\\5</a>", $text);
	
		//убираем / в конце ссылок без вложенности
		$text = preg_replace( "~(<a[^>]*>[^\/]+)\/<\/a>~", "$1</a>", $text );
	
		//Пустые строки - в топку
		//$text = preg_replace( "~<p>(\n|\s)*</p>~", "", $text );
		
		//Каждый абзац в комментариях - параграфом (вне pre)
		$pattern = '(<pre[^>]*>.*<\/pre>|<code[^>]*>.*<\/code>)';
		$text = preg_replace_callback("~".$pattern."~isU", '_stack', $text);
			$text = preg_replace("/(\n)?(.+?)(?:\n\s*\n*|\z)/s","<p>$2</p>",$text);
		$text = strtr($text, _stack());
						
		//правим цитаты
		$text = preg_replace('~<p><blockquote([^>]*)>~i', "<blockquote$1><p>", $text);
        $text = str_replace('</blockquote></p>', '</p></blockquote>', $text);
	
		//и еще косяки от сторонних плагинов - пустые абзацы
		$text = preg_replace("~<p>(\s)*<\/p>~", "", $text);
		$text = preg_replace("~<p>\s*<p>~", "<p>", $text);
		$text = preg_replace("~<\/p>\s*<\/p>~", "</p>", $text);
	
		//правильная обработка из прошлой функции
		$text = typoFullFilterText($text, $opt_spec, $opt_nobr, $opt_correct);
	
		return $text;
	}

/**
* ИСПРАВЛЕНИЕ ОШИБОК ВЕРСТКИ
* 
*
*/
	function typoFullFilterWPautop($pee, $br = 1)
        {
			$pee=preg_replace("~\[CDATA\[(\n\n|</p>\n<p>)~","[CDATA[\n",$pee);
			$pee = preg_replace('~(<p>(\s|\n)*<script)~', "<script", $pee);
			$pee = preg_replace('~(<\/script>(\s|\n)*</p>)~', "</script>", $pee);
			
			$pee = preg_replace('~(<hr[^>]*>)~', "$1<p>", $pee);/**/			
	
            $pee = $pee . "\n"; // just to make things a little easier, pad the end
            $pee = preg_replace('|<br />\s*<br />|', "\n\n", $pee);
            // Space things out a little
            $allblocks = '(?:table|thead|tfoot|caption|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|map|area|blockquote|address|math|style|input|p|h[1-6]|hr)';
            $pee = preg_replace('!(<' . $allblocks . '[^>]*>)!', "\n$1", $pee);
            $pee = preg_replace('!(</' . $allblocks . '>)!', "$1\n\n", $pee);
            $pee = str_replace(array("\r\n", "\r"), "\n", $pee); // cross-platform newlines
            if ( strpos($pee, '<object') !== false ) {
                $pee = preg_replace('|\s*<param([^>]*)>\s*|', "<param$1>", $pee); // no pee inside object/embed
                $pee = preg_replace('|\s*</embed>\s*|', '</embed>', $pee);
            }
            $pee = preg_replace("/\n\n+/", "\n\n", $pee); // take care of duplicates
            $pee = preg_replace('/\n?(.+?)(?:\n\s*\n|\z)/s', "<p>$1</p>\n", $pee); // make paragraphs, including one at the end
            $pee = preg_replace('|<p>\s*?</p>|', '', $pee); // under certain strange conditions it could create a P of entirely whitespace
            $pee = preg_replace('!<p>([^<]+)\s*?(</(?:div|address|form)[^>]*>)!', "<p>$1</p>$2", $pee);
            $pee = preg_replace("/<p>\\s*(<{$allblocks}.*?)<\\/p>/ism", '$1', $pee);
            $pee = preg_replace( '|<p>|', "$1<p>", $pee );
            $pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee); // don't pee all over a tag
            $pee = preg_replace("|<p>(<li.+?)</p>|", "$1", $pee); // problem with nested lists
          	$pee = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $pee);
			$pee = preg_replace('|^<blockquote([^>]*)>(?!<p>)|i', "<blockquote$1><p>", $pee);
			
            $pee = str_replace('</blockquote></p>', '</p></blockquote>', $pee);
			
			$pee = preg_replace_callback("/<p>(.*?)(<\\/{$allblocks}>)/s", 'replace_callback', $pee);
            $pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)!', "$1", $pee);
            $pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee);
            if ($br) {
                $pee = preg_replace_callback('/<(script|style).*?<\/\\1>/s', create_function('$matches', 'return str_replace("\n", "<WPPreserveNewline />", $matches[0]);'), $pee);
                $pee = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $pee); // optionally make line breaks
                $pee = str_replace('<WPPreserveNewline />', "\n", $pee);
            }
            $pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*<br />!', "$1", $pee);
            $pee = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $pee);
            if (strpos($pee, '<pre') !== false) {
                $pee = preg_replace_callback('!(<pre.*?>)(.*?)</pre>!is', 'clean_pre2', $pee);
            }
            $pee = preg_replace( "|\n</p>$|", '</p>', $pee );
            $pee = preg_replace('/<p>\s*?(' . get_shortcode_regex() . ')\s*<\/p>/s', '$1', $pee); // don't auto-p wrap shortcodes that stand alone

		    return $pee;
        }
	
	function clean_pre2($matches) {
	if ( is_array($matches) )
		$text = $matches[1] . $matches[2] . "</pre>";
	else
		$text = $matches;

	$text = str_replace('<br />', '', $text);
	$text = str_replace('<p>', "\n", $text);
	$text = str_replace('</p>', '', $text);

	return $text;
	}
	
	function replace_callback($matches)
		{
			if ('</p>' == $matches[2]) {
				return '<p>' . $matches[1] . $matches[2];
			}

			return $matches[1] . $matches[2];
		} 

remove_filter('the_content', 'wpautop', 30);
remove_filter('the_excerpt', 'wpautop', 30);
//уже убраны
//remove_filter('comment_text', 'wpautop', 30);

add_filter('the_content', 'typoFullFilterWPautop', 30);
add_filter('the_excerpt', 'typoFullFilterWPautop', 30);
//уже используется другая обработка
//add_filter('comment_text', 'typoFullFilterWPautop', 30);/**/

//ШОРТКОДЫ ДОЛЖНЫ ИДТИ ПОСЛЕ wpautop
remove_filter('the_content', 'do_shortcode', 11);
add_filter('the_content', 'do_shortcode', 43);

?>