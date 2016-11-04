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
* @param array $config конфигурация для модуля, берется из БД
* @param string $alias псевдоним, который используется для поиска значений в таблице конфигурации
* @param object $yandexmoney экземпляр класса helpers/Yandexmoney
* @param object $paymentsystemYandexmoneyTransactions экземпляр класса models/PaymentsystemYandexmoneyTransactions
* @param string $description сообщения модуля
*
* @package module.PaymentsystemYandexmoney.controllersbase
*/
class PayController extends PayControllerBase
{	
	/**
	* Контроллер главной страницы модуля
	* @param varchar $guid уникальный идентификатор финансовой транзакции.
	*/
    public function actionIndex($guid = '')
	{
		$this->Index($guid);
	}
    
	/**
	* Контроллер страницы ошибки оплаты
	* @param varchar $guid уникальный идентификатор финансовой транзакции.
	*/
    public function actionFail($guid = '')
    {
		$this->Fail($guid);
    }
    
	/**
	* Контроллер страницы успешной оплаты
	* @param varchar $guid уникальный идентификатор финансовой транзакции.
	*/
    public function actionSuccess($guid = '')
    {
		$this->Success($guid);
    }
	
	/**
	* Метод эмулирующий отправку POST-запросов платежной системы
	*/
	public function actionSend()
    {
		$this->Send();
    }
    
	/**
	* Контроллер страницы обработки и возврата ответов на запросы платежной системы
	* @return xml возвращает ответ в теле POST-запроса платежной системы
	*/
    public function actionResult()
    {
		$this->Result();
    }
    
	/**
	* Контроллер страницы эмуляции действий платежной системы
	* @param varchar $guid уникальный идентификатор финансовой транзакции.
	*/	
    public function actionEmulation($guid)
    {
		$this->Emulation($guid);
    }
}