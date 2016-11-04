<div class="pay_block" align="center">
    
    <table >
        <tr><td align="center" style="color:#000; font-size:14px;"><h1 class="uppercase text-center mb50"><?=Yii::t('app', 'Информация')?></h1></td></tr>
        <tr>
            <td align="center">
				<table class="table table-hover">
					<tr>
						<td style="background-color:#EEEEEE; padding:3px;"><?=Yii::t('app', 'Описание')?></td>
						<td style="background-color:#ffffff; padding:3px;"><?=CHtml::encode($model->getModelSpecification()->title)?></td>
					</tr>
					<tr>
						<td style="background-color:#EEEEEE; padding:3px;"><?=Yii::t('app', 'Сумма операции')?></td>
						<td style="background-color:#ffffff; padding:3px;"><?=sprintf('%.2f', $model->amount);?></td>
					</tr>
					<tr>
						<td style="background-color:#EEEEEE; padding:3px;"><?=Yii::t('app', 'Комиссия 0.5%')?></td>
						<td style="background-color:#ffffff; padding:3px;"><?=sprintf('%.2f', $model->amount*0.005);?></td>
					</tr>
					<tr>
						<td style="background-color:#EEEEEE; padding:3px;"><?=Yii::t('app', 'Сумма к оплате')?></td>
						<td style="background-color:#ffffff; padding:3px;"><?=sprintf('%.2f', $model->amount+$model->amount*0.005);?></td>
					</tr>
				</table>
            </td>
        </tr>
        <tr>
            <td align="center">
                <form action="<?=$form_yandexmoney['action']?>" method="POST" enctype="application/x-www-form-urlencoded" >
					<?=$form_yandexmoney['form']?> 
				</form>
            </td>
       </tr>
    </table>
 </div>    