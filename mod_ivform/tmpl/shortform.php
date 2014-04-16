<?php 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$br = "<br/>";
?>
<span style="display: none;"> <?php echo join(',', $ivform -> arrEmailBcc); ?> </span>
<div class="formblock shortform">
    <form name="order" id="order"  method="post" onsubmit="return checkForm(this);">
        <div>
            <?php $ivform -> input("text", "fio", "input-xxlarge", "", "Имя","Ваше имя...", true, 'str');?>
        </div>
        <div>
            <?php $ivform -> input("text", "phone", "input-xxlarge", "", "Телефон", "Телефон (с кодом города)", true, 'str');?>
        </div>
        <button type="submit" class="btn btn-success">Отправить заявку</button>
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
<?php if ($ivform -> isSend) {echo '<h4 style="color:green;">Ваш запрос отправлен. Спасибо!</h4>';} ?>