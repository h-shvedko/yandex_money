<?php
/**
* Проведение оплат через платежную систему Яндекс Деньги. 
* Используется Протокол обмена информацией при осуществлении переводов: HTTP-уведомления(описание {@link https://money.yandex.ru/doc.xml?id=526537}).
* Не используется API ({@link https://tech.yandex.ru/money/doc/dg/concepts/About-docpage/}).
* Используется только для магазинов, нет функционала оплаты для кошельков к которым не привязан магазин (необходимы такие параметры как shopId и scid)
*
* Возможности: 1) Оплата через кошелек
*			   2) Оплата через банковскую карту
*
* @package module.PaymentsystemYandexmoney
*/
class YandexmoneyModule extends CWebModule
{
	public function init()
	{
		// this method is called when the module is being created
		// you may place code here to customize the module or the application

		// import the module-level models and components
		$this->setImport(array(
			'yandexmoney.models.*',
			'yandexmoney.components.*',
			'yandexmoney.controllersbase.*'
		));
	}

	public function beforeControllerAction($controller, $action)
	{
		if(parent::beforeControllerAction($controller, $action))
		{
			// this method is called before any module controller action is performed
			// you may place customized code here
			return true;
		}
		else
			return false;
	}
    

}
