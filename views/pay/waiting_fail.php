<div align="center" style="color: #c44">    
    <h2>
        <b><span id="time"></span></b><br>
        Платеж не принят, сейчас вы будете перенаправлены.
    </h2>
</div>
<script type="text/javascript">
var i = 5;//время в сек.
function time(){
document.getElementById("time").innerHTML = i;//визуальный счетчик
 i--;//уменьшение счетчика
 if (i == 0)
 {
     location.href = "<?=$url?>";//редирект
 } 
}
time();
setInterval(time, 1000);
</script>
