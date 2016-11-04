<div align="center">    
    <h2>
        <b><span id="time"></span></b><br>
        <span id="title"><?=Yii::t('app', 'Платеж принят, ожидаем подтверждения платежа.')?></span>
    </h2>
</div>
<script type="text/javascript">

var i = 20;
var start_saccess = true;

$.ajaxSetup({
           type 	: "POST",
           async	: false,
           dataType	: 'json'
           });
           
function time()
{
    document.getElementById("time").innerHTML = i;
    i--;
    if (i == 0)
    {
        location.href = "<?=$url?>";
    } 
}
function success_transaction ()
{
        $.ajax({
         type: "POST",
         dataType: 'html',
         url:    app.createAbsoluteUrl('paymentsystem/perfect/pay/saccesstran'),
         data:{
               YII_CSRF_TOKEN: globalcsrfToken, 
               guid_success_transaction: "<?=$guid?>"
              },
         success: function(html){
             if(html == "closed")
             {
                 document.getElementById("title").innerHTML = "<?=Yii::t('app', 'Платеж подтвержден')?> <br/> <?=Yii::t('app', 'спасибо за ожидание, сейчас вы будете перенаправлены.')?><br /> <?=Yii::t('app', 'Или пройдите по этой')?> <a href='<?=$url?>'> <?=Yii::t('app', 'ссылке')?></a>.";
                 i = 5;
                 start_saccess = false;
             }
         }
       });
}
function start_success()
{
    if(start_saccess == true)
    {
        success_transaction ();
    }
}
$(function(){
    time();
    start_success();
    setInterval(time, 1000);
    setInterval(start_success, 10000);
});
</script>
