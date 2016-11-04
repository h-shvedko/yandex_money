<?php
/**
* Класс обработки запросов от платежной системы
*
* Возможности: 1) Генерация формы оплаты для платежной системы
*			   2) Генарция формы оплаты для платежной системы (эмуляция)
			   3) Генерация успешного ответа для платежной системы
			   4) Генерация не успешного ответа для платежной системы
*
* @param string $PAYMENT_COMISSION комиссия платежной системы
* @param int $CHECKSUCCESS Контрагент дал согласие и готов принять перевод
* @param int $CHECKFAILHASH Несовпадение значения параметра md5 с результатом расчета хэш-функции. Оператор считает ошибку окончательной и не будет осуществлять перевод.
* @param int $CHECKFAILTRANSACT Отказ в приеме перевода с заданными параметрами. Оператор считает ошибку окончательной и не будет осуществлять перевод.
* @param int $CHECKFAILPARSE ИС Контрагента не в состоянии разобрать запрос. Оператор считает ошибку окончательной и не будет осуществлять перевод.
* @param int $AVISOSUCCESS Успешно — даже если Оператор прислал данный запрос повторно.
* @param int $AVISOFAILHASH Значение параметра md5 не совпадает с результатом расчета хэш-функции. Оператор не будет повторять запрос и пометит заказ как «Уведомление Контрагенту не доставлено».
* @param int $AVISOFAILPARSE ИС Контрагента не в состоянии разобрать запрос. Оператор не будет повторять запрос и пометит заказ как «Уведомление Контрагенту не доставлено».
* @param string $ACTIONCHECK Тип запроса "Проверка заказа".
* @param string $ACTIONAVISO Тип запроса "Уведомление о переводе"
* @param array $config конфигурация для модуля, берется из БД
* @param array $fields поля формы оплаты
*
* @package module.PaymentsystemYandexmoney.helpers
*/

class Yandexmoney 
{

	/**
	* @var string $PAYMENT_COMISSION комиссия платежной системы
	*/
    const PAYMENT_COMISSION = 0.005; 
   	/**
	* @var int $CHECKSUCCESS Контрагент дал согласие и готов принять перевод
	*/
	const CHECKSUCCESS = 0;
	/**
	* @var int $CHECKFAILHASH Несовпадение значения параметра md5 с результатом расчета хэш-функции. Оператор считает ошибку окончательной и не будет осуществлять перевод.
	*/
	const CHECKFAILHASH = 1;
	/**
	* @var int $CHECKFAILTRANSACT Отказ в приеме перевода с заданными параметрами. Оператор считает ошибку окончательной и не будет осуществлять перевод.
	*/
	const CHECKFAILTRANSACT = 100;
	/**
	* @var int $CHECKFAILPARSE ИС Контрагента не в состоянии разобрать запрос. Оператор считает ошибку окончательной и не будет осуществлять перевод.
	*/
	const CHECKFAILPARSE = 200;
	/**
	* @var int $AVISOSUCCESS Успешно — даже если Оператор прислал данный запрос повторно.
	*/
	const AVISOSUCCESS = 0;
	/**
	* @var int $AVISOFAILHASH Значение параметра md5 не совпадает с результатом расчета хэш-функции. Оператор не будет повторять запрос и пометит заказ как «Уведомление Контрагенту не доставлено».
	*/
	const AVISOFAILHASH = 1;
	/**
	* @var int $AVISOFAILPARSE ИС Контрагента не в состоянии разобрать запрос. Оператор не будет повторять запрос и пометит заказ как «Уведомление Контрагенту не доставлено».
	*/
	const AVISOFAILPARSE = 200;
	/**
	* @var string $ACTIONCHECK Тип запроса "Проверка заказа".
	*/
	const ACTIONCHECK = 'checkOrder';
	/**
	* @var string $ACTIONAVISO Тип запроса "Уведомление о переводе"
	*/
	const ACTIONAVISO = 'paymentAviso';
	/**
	* @var array $config конфигурация для модуля, берется из БД
	*/
    public $config = array();
	/**
	* @var array $fields поля формы оплаты
	*/
    public $fields = array();

    public function __construct($config) {
        $this->config = $config;
    }
	
	/**
	* Метод генерирующий форму платежной системы
	* @param int $transaction_id идентификатор финансовой операции.
	* @param float $amount сумма финансовой операции
	* @param string $object_details описание финансовой операции
	* @param string $title заголовок финансовой операции
	* @param string $guid уникальный идентификатор финансовой операции
	* @param integer $bank флаг использования банковской карты
	* @return array возвращает в массиве форму оплаты
	*/
    public function GetForm($transaction_id, $amount, $object_details, $title, $guid = false, $bank) {
        $transaction = FinanceTransactions::model()->findByPk($transaction_id);

        $fields = $this->getFields($transaction, $amount);
        
		if($bank != (int)TRUE)
		{
			$array = array('form' => '
                            <input type="hidden" name="shopid" value="' . $fields['shop_id'] . '">
                            <input type="hidden" name="scid" value="' . $fields['scid'] . '">
							<input type="hidden" name="shopsuccessurl" value="' . $fields['successurl'] . '">
							<input type="hidden" name="shopfailurl" value="' . $fields['failurl'] . '">
                            <input type="hidden" name="sum" value="' . $this->getSumm($amount) . '">
                            <input type="hidden" name="customernumber" value="' . Yii::app()->user->id . '">
                            <input type="radio" name="paymentType" checked="checked" value="' . $fields['payment_type_wallet'] . '">Яндекс.Деньгами</input>
                            <input type="radio" name="paymentType" value="' . $fields['payment_type_card'] . '">Банковской картой</input>
                            <input type="hidden" name="ordernumber" value="' . $guid . '"><br><br>
							<input class="btn btn-green-grad w200" type="submit" name="payment_method" value="' . $fields['payment_method'] . '" class="btn100">',
                            'action' => $this->config['action']);
		}
		else
		{
			$array = array('form' => '
                            <input type="hidden" name="shopid" value="' . $fields['shop_id'] . '">
                            <input type="hidden" name="scid" value="' . $fields['scid'] . '">
							<input type="hidden" name="shopsuccessurl" value="' . $fields['successurl'] . '">
							<input type="hidden" name="shopfailurl" value="' . $fields['failurl'] . '">
                            <input type="hidden" name="sum" value="' . $this->getSumm($amount) . '">
                            <input type="hidden" name="customernumber" value="' . Yii::app()->user->id . '">
                            <input type="radio" name="paymentType" value="' . $fields['payment_type_wallet'] . '">Яндекс.Деньгами</input>
                            <input type="radio" name="paymentType" checked="checked" value="' . $fields['payment_type_card'] . '">Банковской картой</input>
                            <input type="hidden" name="ordernumber" value="' . $guid . '"><br><br>
							<input class="btn btn-green-grad w200" type="submit" name="payment_method" value="' . $fields['payment_method'] . '" class="btn100">',
                            'action' => $this->config['action']);
		}
        return $array;
    }

	/**
	* Метод возвращает язык
	* @return sting язык приложения
	*/
    public function getLanguage() {
        if (Yii::app()->language == 'ru') {
            return 'ru-RU';
        }

        return 'en-US';
    }

	/**
	* Метод возвращает сумму платежа с учетом комиссии платежной системы
	* @param float $amount сумма финансовой операции
	* @return float сумма финансовой операции с учетом комиссии платежной системы
	*/
    public function getSumm($amount) {
        return sprintf('%.2f', $amount+$amount*Yandexmoney::PAYMENT_COMISSION);
    }

	/**
	* Метод возвращает массив значений из конфигурации платежной системы
	* @param FinanceTransactions $transaction  экземпляр класса FinanceTransactions - модель текущей финансовой операции
	* @param float $amount сумма финансовой операции
	* @return array данные конфигурации
	*/
    public function getFields($transaction, $amount) {
        
        $fields['shop_id'] = $this->config['shop_id'];
        $fields['scid'] = $this->config['scid'];
        $fields['successurl'] = $this->config['successurl'];
        $fields['failurl'] = $this->config['failurl'];
        $fields['payment_type_wallet'] = $this->config['payment_type_wallet'];
        $fields['payment_type_card'] = $this->config['payment_type_card'];
        $fields['checkorder'] = $this->config['checkorder'];
		$fields['currency'] = $this->config['currency'];
		$fields['bankpaycash'] = $this->config['bankpaycash'];
		$fields['client_secret'] = $this->config['client_secret'];
        $fields['payment_method'] = Yii::t('app', 'Оплатить');

        return $fields;
    }

	/**
	* Метод генерирует сигнатуру платежа
	* @param array $params  массив параметров запроса платежной системы
	* @return string сигнатура платежа
	*/
    public function getAnswerSignature($params)
	{
		if($this->config['emulation'] === true && array_key_exists('md5',$params))
		{
			return $params['md5'];
		}
		
        if (array_key_exists('checkorder', $params))
        {
            $string = $params['checkorder'] . ';' . $params['payment_amount'] . ';' .
                $params['currency'] . ';' . $params['bankpaycash'] . ';' .
                $params['shop_id'] . ';' . $params['invoiceid'] . ';' .
                $params['sender'] . ';' . $params['client_secret'];
				
				return strtoupper(md5($string));
        }
		elseif (array_key_exists('action', $params))
        {
            $string = $params['action'] . ';' . $params['ordersumamount'] . ';' .
                $params['ordersumcurrencypaycash'] . ';' . $params['ordersumbankpaycash'] . ';' .
                $params['shopid'] . ';' . $params['invoiceid'] . ';' .
                $params['customernumber'] . ';' . $this->config['client_secret'];
				
				return strtoupper(md5($string));
        }
        
		return FALSE;
        
    }

	
	/**
	* Метод возвращает массив значений из конфигурации платежной системы (эмуляция)
	* @param FinanceTransactions $transaction  экземпляр класса FinanceTransactions - модель текущей финансовой операции
	* @param float $amount сумма финансовой операции
	* @param string $status статус финансовой операции (success, fail...)
	* @param int $invoiceid номер платежа (параметр присваивается платежной системой)
	* @return array с данными конфигурации
	*/
    public function getFieldsForEmulation($transaction, $amount, $status, $invoiceid) {

		$fields['shop_id'] = $this->config['shop_id'];
        $fields['scid'] = $this->config['scid'];
        $fields['successurl'] = $this->config['successurl'];
        $fields['failurl'] = $this->config['failurl'];
        $fields['payment_type_wallet'] = $this->config['payment_type_wallet'];
        $fields['payment_type_card'] = $this->config['payment_type_card'];
        $fields['checkorder'] = $this->config['checkorder'];
		$fields['currency'] = $this->config['currency'];
		$fields['bankpaycash'] = $this->config['bankpaycash'];
		$fields['client_secret'] = $this->config['client_secret'];
		$fields['payment_amount'] = $this->getSumm($amount);
		$fields['invoiceid'] = $invoiceid;
		$fields['datetime'] = date('Y/m/d H:i:s');
		$fields['sender'] = $transaction->debit_object_id;
		$fields['codepro'] = FALSE;
        $fields['lable'] = $transaction->guid;
        $fields['payment_method'] = Yii::t('app', 'Оплатить');
		
		$fields['ordernumber'] = $transaction->guid;
		$fields['action'] = 'checkOrder';
		$fields['ordersumamount'] = $this->getSumm($amount);
		$fields['ordersumcurrencypaycash'] = $this->config['currency'];
		$fields['ordersumbankpaycash'] = $this->config['bankpaycash'];
		$fields['shopid'] = $this->config['shop_id'];
		
		$fields['customernumber'] = $transaction->debit_object_id;
		
		$fields['md5'] = $status == 'success' ? $this->getAnswerSignature($fields) : '';
	
        return $fields;
    }

	
	/**
	* Метод возвращает URL результата
	* @return sting URL результата
	*/
    public static function getResultUrl() {
        return Yii::app()->createAbsoluteUrl('/office');
    }

	/**
	* Метод возвращает URL успешного результата обработки
	* @return sting URL успешного результата обработки
	*/
    public static function getsuccessurl() {
        return Yii::app()->createAbsoluteUrl('/paymentsystem/yandexmoney/pay/success');
    }

	/**
	* Метод возвращает URL не успешного результата обработки
	* @return sting URL не успешного результата обработки
	*/
    public static function getfailurl() {
        return Yii::app()->createAbsoluteUrl('/paymentsystem/yandexmoney/pay/fail');
    }

	/**
	* Метод возвращает URL успешного результата обработки
	* @param array $post массив POST-запроса от платежной системы
	* @param string $answer код ответа на запрос платежной системы
	* @param string $action тип запроса платежной системы
	* @return sting ответ для платежной системы об успешной обработке (в формате XML)
	*/
	public function sendSuccessAnswer($post, $answer, $action) 
	{
		
		 $dom = new DOMDocument("1.0", "utf-8");
		
		if($action == self::ACTIONCHECK)
		{
			$root = $dom->createElement('checkOrderResponse');
			$dom->appendChild($root);
			$root->setAttribute("performedDatetime", app_date("c")); 
			$root->setAttribute("code", self::CHECKSUCCESS); 
			$root->setAttribute("invoiceId", $post['invoiceId']); 
			$root->setAttribute("shopId", $this->config['shop_id']);
		}
		else
		{
			$root = $dom->createElement('paymentAvisoResponse');
			$dom->appendChild($root);
			$root->setAttribute("performedDatetime", app_date("c")); 
			$root->setAttribute("code", self::AVISOSUCCESS); 
			$root->setAttribute("invoiceId", $post['invoiceId']); 
			$root->setAttribute("shopId", $this->config['shop_id']);
		}
		$str = $dom->saveXML();
		
		return $str;
		
    }
	

	/**
	* Метод возвращает URL успешного результата обработки
	* @param array $post массив POST-запроса от платежной системы
	* @param string $answer код ответа на запрос платежной системы
	* @return sting ответ для платежной системы об не успешной обработке (в формате XML)
	*/
	public function sendErrorAnswer($post = FALSE, $answer) 
	{
		if($post == FALSE)
		{
			$post = array();
			$post['invoiceId'] = "FALSE";
		}
		$dom = new DOMDocument("1.0", "utf-8");
		
		if($answer == Yandexmoney::CHECKFAILPARSE)
		{
			$root = $dom->createElement('checkOrderResponse');
			$dom->appendChild($root);
			$root->setAttribute("performedDatetime", app_date("c")); 
			$root->setAttribute("code", self::AVISOSUCCESS); 
			$root->setAttribute("invoiceId", $post['invoiceId']); 
			$root->setAttribute("shopId", $this->config['shop_id']);
			$root->setAttribute("message", "Указанный платеж не существует");
			$root->setAttribute("techMessage", "Неверный запрос");
		}
		$str = $dom->saveXML();
		
		return $str;
    }
}
