<?php $form=$this->beginWidget('CActiveForm', array('enableAjaxValidation'=>false)); ?>
	<style>
		hr{     
			background: #0066A4;
			height: 1px;
			width: 100%;
			margin: 0;
		}
	</style>
	<div class="pay_block" align="center">
		
		<table style="width:50%; font-size: 12px;">
			<tr>
				<td align="left" style="background-color:#ffffff;">
					<div style ="height: auto; color: #05B2D2; overflow: auto; margin: 2px;">
					<h1 class="uppercase text-center mb50">	<?=$message?> </h1>
					</div>    
				</td>
			</tr>
			<tr>
				<td align="center">
					<? if (!(bool)$isConfirmed) : ?>
						<?=CHtml::submitButton('Эмулировать успешный ответ', array('name'=>'reply_confirmed', 'class' => 'btn300', 'title' => 'Эмуляция ответа удачной оплаты от сервера платежной системы')); ?><br />
						<?=CHtml::submitButton('Эмулировать успешную оплату', array('name'=>'reply_confirmed_pay', 'class' => 'btn btn-green-grad w500 mr20', 'title' => 'Эмуляция ответа удачной оплаты от сервера платежной системы и возврата к сайту')); ?><br />
						<?=CHtml::submitButton('Эмулировать ошибочный ответ', array('name'=>'reply_canceled', 'class' => 'btn300', 'title' => 'Эмуляция ответа неудачной оплаты от сервера платежной системы')); ?><br />
						<?=CHtml::submitButton('Эмулировать отказ от оплаты', array('name'=>'reply_canceled_pay', 'class' => 'btn300', 'title' => 'Эмуляция ответа неудачной оплаты от сервера платежной системы и возврата к сайту')); ?><br />
					<? else : ?>
						<? if ($isConfirmed == 'success') : ?>
							<?=CHtml::submitButton('Эмулировать возврат к сайту', array('name'=>'reply_return_success', 'class' => 'btn btn-green-grad w200 mr20', 'title' => 'Эмуляция возврата к сайту после успешной оплаты')); ?><br />
						<? elseif ($isConfirmed == 'fail') : ?>
							<?=CHtml::submitButton('Эмулировать возврат к сайту', array('name'=>'reply_return_fail', 'class' => 'btn btn-green-grad w300 mr20', 'title' => 'Эмуляция возврата к сайту после отказа от оплаты')); ?><br />
						<? endif; ?>
					<? endif; ?>
				</td>
		   </tr>
		</table>
	 </div>    
<?php $this->endWidget(); ?>
