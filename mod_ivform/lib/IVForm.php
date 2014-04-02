<?php

//типы данных, значение которых проверяется в полях
define('DSTRING', 	'str');
define('DINT',		'int');
define('DEMAIL',	'email');

/**
 * Класс для создания форм
*/
class IVForm {
	/** адрес отправителя */
	public $fromEmail;
	/** имя отправителя */
	public $fromName;
	
	/** список адресов получателей письма */
	public $arrEmailTo;
	
	/** список скрытых адресов получателей письма */
	public $arrEmailBcc;
	
	/** тема письма */
	public $subject;
	
	/** массив сообщений об ошибках */
	public $arrErrors;
	
	/** true - письмо отправлено успешно */
	public $isSend = false;
	
	/** Используется для определения того, что форма запущена повторном (возможно с заполненными полями) */
	private $token = 'tokstal';
	
	/** true - инициализация прошла */
	private $isInitialized;
	
	/** строка статуса */
	private $strStatusText;
	
	/** строка отладки */
	private $strDebugText;
	
	/** true - первый запуск */
	private $isFirstLaunch = false;
	
	/** Текст сообщения */
	private $strMessage = '';
	
	function IVForm() {
		$this -> isInitialized = false;
		
		$this -> arrErrors = array();
		$this -> arrEmailTo = array();
		$this -> arrEmailBcc = array();
	}
	
	/** Инициализация */
	function init() {
		/*
		 * Для некоторой защиты от спама можно в скрытое поле записывать зашифрованную (XOR с длинным ключом) дату со временем генерации формы
		 * Затем при повторном вызове проверять, сколько прошло времени, например, если меньше 5 секунд, то это робот
		 */
		if (isset($_POST['token'])) {
			//запущен повторно
			//echo "<p>НЕ ПЕРВЫЙ</p>";
			//$this -> strDebugText = 'НЕ ПЕРВЫЙ';
			$this -> debugAddText('НЕ ПЕРВЫЙ');
			$this -> isFirstLaunch = false;
			
			//проверяем поля
		} else {
			//запущен впервый раз
			//echo "<p>Первый</p>";
			//$this -> strDebugText = 'Первый';
			$this -> debugAddText('Первый');
			$this -> isFirstLaunch = true;
		}
		
		echo "<input type='hidden' name='token' value='".$this -> token."' />";
		$this -> isInitialized = true;
	}
	
	/** Добавляет инпут и возвращает его html
	 * @param $required bool true - обязательное поле
	 * */
	function input($type, $name, $classText, $value, $label, $innerLabel, $required = false, $dataType = DSTRING, $displayInnerLabel = true) {
		if ( ! $this -> isInitialized) {
			$this -> init();
		}
		$startValue = ($value) ? $value : $innerLabel;
		$inner = ($displayInnerLabel) ? $innerLabel : '';
		$preparePost = IVForm::valueToType($_POST[$name], $dataType);
		if ( ! $this -> isSend  &&  $preparePost) {
			$startValue = $preparePost;
		}
		if (is_null($preparePost)  &&  isset($_POST[$name])  &&  ($_POST[$name] != $startValue)  &&  ($dataType == DEMAIL)) {
			$startValue = $_POST[$name];
		}
		echo "<input type=\"$type\" name=\"$name\" class=\"$classText\" value=\"$startValue\" ".
			 " onfocus=\"if(this.value=='$inner') this.value='';\" onblur=\"if(this.value=='') this.value='$inner';\"/>";
		
		if ($this -> isFirstLaunch) {
			return;
		}
		$hasError = false;//чтобы не выводить 2 ошибки (на заполнение и корректность) сразу
		//проверяем e-mail
		if (($dataType == DEMAIL)  &&  is_null($preparePost)  &&  isset($_POST[$name])  &&  ($_POST[$name] != $innerLabel)) {
			$this -> arrErrors[] = "e-mail адрес в поле <strong>$innerLabel</strong> содержит ошибку";
			$this -> statusAddText("<p>e-mail адрес в поле <strong>$innerLabel</strong> содержит ошибку</p>");
			$hasError = true;
		}
		//проеверяем заполнено ли
		if (($required)  &&  !$hasError  && (is_null($preparePost)  ||  ($_POST[$name] == $innerLabel))) {
			$this -> arrErrors[] = "не заполнено обязательное поле <strong>$innerLabel</strong>";
			$this -> statusAddText("<p>не заполнено обязательное поле <strong>$innerLabel</strong></p>");
		}
		//Добавляем в текст письма
		$valueForMessage = '';
		if (($innerLabel != $_POST[$name])  &&  !is_null($preparePost)) {
			$valueForMessage = $preparePost;
		}
		$this -> strMessage .= "$label: <strong>$valueForMessage</strong><br/>";
	}
	
	function textarea($name, $classText, $value, $label, $innerLabel, $required = false, $dataType = DSTRING, $displayInnerLabel = true) {
		if ( ! $this -> isInitialized) {
			$this -> init();
		}
		$startValue = ($value) ? $value : $innerLabel;
		$inner = ($displayInnerLabel) ? $innerLabel : '';
		$preparePost = IVForm::valueToType($_POST[$name], $dataType);
		if ( ! $this -> isSend  &&  $preparePost) {
			$startValue = $preparePost;
		}
		echo "<textarea  name=\"$name\" class=\"$classText\" ".
			 " onfocus=\"if(this.value=='$inner') this.value='';\" onblur=\"if(this.value=='') this.value='$inner';\">$inner</textarea>";
		
		if ($this -> isFirstLaunch) {
			return;
		}
		//проеверяем заполнено ли
		if (($required)  &&  (is_null($preparePost)  ||  ($_POST[$name] == $innerLabel))) {
			$this -> arrErrors[] = "не заполнено обязательное поле <strong>$innerLabel</strong>";
			$this -> statusAddText("<p>не заполнено обязательное поле <strong>$innerLabel</strong></p>");
		}
		//Добавляем в текст письма
		$this -> strMessage .= "$label: <br/><strong>$startValue</strong><br/>";
	}
	
	static function valueToType($value, $dataType) {
		if ( ! $value  ||  ! $dataType) {
			return null;
		}
		/** обработанное значение */
		$prepValue = null;
		switch ($dataType) {
			case DSTRING: $prepValue = (string)$value;
						break;
			case DEMAIL:
						// !!! Заменить на полноценную проверку
						// e-mail address validation
						$e = "/^[-+\\.0-9=a-z_]+@([-0-9a-z]+\\.)+([0-9a-z]){2,4}$/i";
						if ( ! preg_match($e, $value)) return null;
						$prepValue = (string)$value;
						break;
			case DINT: $prepValue = (int)$value;
						break;
		}
		return $prepValue;
	}
	
	private function statusAddText($statusItem) {
		$this -> strStatusText .= $statusItem;
	}
	
	function statusText() {
		return $this -> strStatusText;
	}
	
	private function debugAddText($debugItem) {
		$this -> strDebugText .= $debugItem;
	}
	
	function debugText() {
		return $this -> strDebugText;
	}
	
	/** Если все необходимые поля заполнены то пробует отправить сообщение */
	public function trySend() {
		if ((count($this -> arrErrors) != 0)  ||  ($this -> isFirstLaunch)) {
			//были ошибоки или первый запуск
			return;
		}
		//$to  = 'info@ivenc.com';
		$to = join(',', $this -> arrEmailTo);
		
		//$subject = '=?utf-8?B?'.base64_encode($subject).'?=';
		$message = "
		<html>
		<head>
			<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
			<title>Сообщение с сайта</title>
		</head>
		<body>
			".$this -> strMessage."
		</body>
		</html>
		";
		// Для отправки HTML-письма должен быть установлен заголовок Content-type
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
		//$headers .= "Content-Transfer-Encoding: 8bit". "\r\n";
		// Дополнительные заголовки
		//$headers .= 'To: Mary <mary@example.com>, Kelly <kelly@example.com>' . "\r\n"; //видимые копии
		$headers .= "From: ".$this -> fromName." <".$this -> fromEmail.">" . "\r\n";
		//$headers .= 'Cc: birthdayarchive@example.com' . "\r\n";
		$headers .= 'Bcc: '.join(',', $this -> arrEmailBcc) . "\r\n";//скрытая копия: можно несколько адресов через запятую 
		
		// Отправляем
		mail($to, $this -> subject, $message, $headers);
		
		$this -> isSend = true;
	}
}