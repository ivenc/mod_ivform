<?php 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$br = "<br/>";
//echo "Имя модуля:".$br;
//echo JText::_('MOD_IVFORM_NAME').$br;
//echo "e-mail:".$emailTo.$br;
//echo "form text:".$formText.$br; 

?>
<h3>Отправьте нам заявку:</h3>
<div class="formblock" style="margin-right: 15px;">
<form name="order" id="order"  method="post" onsubmit="return checkForm(this);">
<div class="input-prepend">
<span class="add-on icon-user required">*</span>
		<?php $ivform -> input("text", "fio", "input-xxlarge", "", "Имя","Ваше имя...", true, 'str');?>
	</div>
	<div class="input-prepend">
		<span class="add-on icon-phone required">*</span>
		<?php $ivform -> input("text", "phone", "input-xxlarge", "", "Телефон", "Телефон (с кодом города)", true, 'str');?>
	</div>
	<div class="input-prepend">
		<span class="add-on icon-envelope"></span>
		<?php $ivform -> input("text", "email", "input-xxlarge", "", "E-mail", "E-mail", false, 'email');?>
	</div>
	<div class="input-prepend">
		<span class="add-on icon-group"></span>
		<?php $ivform -> input("text", "company", "input-xxlarge", "", "Компания", "Название компании", false, 'str');?>
	</div>
	<p>Сообщение</p>
	<div class="input-prepend">
		<span class="add-on icon-comment"></span>
		<?php $ivform -> textarea("addinfo", "text-xxlarge", "", "Сообщение", "Сообщение", false, 'str', false);?>
	</div>
	<button type="submit" class="btn btn-success" style="margin-left: 27px;">Отправить</button>
	<?php
		$ivform -> trySend();
		//echo '<p class="debug">'.$ivform -> debugText().'</p>';
		//echo ini_get( 'include_path' );
		if ($ivform -> isSend) {
			echo '<p class="sended">'.$textSended.'</p>';
		} else {
			//echo '<p class="errors">'.$ivform -> statusText(). '</p>';
		}
		echo '<p class="status">'.$ivform -> statusText().'</p>';
	?>
	</form>
</div>
<?php if ($isSend) {echo '<h4 style="color:green;">Ваш запрос отправлен. Спасибо!</h4>';} ?>