<?php

/**
 * Модель класса для таблицы "paymentsystem_yandexmoney_transactions".
 *
 * @property integer $id уникальный идентификатор
 * @property string $requestdatetime дата запроса check
 * @property string $ordernumber guid финансовой операции
 * @property string $action тип запроса от платежной системы
 * @property string $md5 хэш платежа
 * @property integer $shopId Идентификатор Контрагента, присваиваемый Оператором.
 * @property integer $shopArticleId  Идентификатор товара, присваиваемый Оператором.
 * @property integer $invoiceId Уникальный номер транзакции в ИС Оператора.
 * @property integer $customerNumber Идентификатор плательщика (присланный в платежной форме) на стороне Контрагента: номер договора, мобильного телефона и т.п.
 * @property string $orderCreateddatetime Момент регистрации заказа в ИС Оператора.
 * @property string $orderSumAmount Стоимость заказа. Может отличаться от суммы платежа, если пользователь платил в валюте, которая отличается от указанной в платежной форме. В этом случае Оператор берет на себя все конвертации.
 * @property string $orderSumCurrencyPaycash Код валюты для суммы заказа.
 * @property string $orderSumBankPaycash Код процессингового центра Оператора для суммы заказа.
 * @property string $shopSumAmount Сумма к выплате Контрагенту на р/с (стоимость заказа минус комиссия Оператора).
 * @property string $shopSumCurrencyPaycash Код валюты для shopSumAmount.
 * @property string $shopSumBankPaycash Код процессингового центра Оператора для shopSumAmount.
 * @property string $paymentPayerCode Номер счета в ИС Оператора, с которого производится оплата.
 * @property string $paymentType  Способ оплаты заказа
 * @property string $MyField Параметры, добавленные Контрагентом в платежную форму.
 * @property integer $check флаг обработки
 * @property string $requestdatetimeAviso  дата запроса aviso
 * @property string $cps_user_country_code Двухбуквенный код страны плательщика
 * @property string $aviso 
 * @property string $is_confirmed флаг проведения платежа
 * @property string $reason причина отказа в обработке
 * @property string $created_at дата создания записи
 * @property integer $created_by ИД пользователя, который создал запись
 * @property string $created_ip IP пользователя, который создал запись
 * @property string $modified_at дата редактирования записи
 * @property integer $modified_by ИД пользователя, который редактировал запись
 * @property string $modified_ip IP пользователя, который редактировал запись
 *
 * @package module.PaymentsystemYandexmoney.models
 */
class PaymentsystemYandexmoneyTransactions extends UTIActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return PaymentsystemYandexmoneyTransactions the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'paymentsystem_yandexmoney_transactions';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('id, check, created_by, modified_by', 'numerical', 'integerOnly'=>true),
            array('requestDatetime, orderNumber, action, md5, orderCreatedDatetime, orderSumCurrencyPaycash, orderSumBankPaycash, shopSumCurrencyPaycash, shopSumBankPaycash, paymentPayerCode, paymentType, MyField, requestDatetimeAviso, cps_user_country_code, aviso, is_confirmed, reason', 'length', 'max'=>255),
            array('orderSumAmount, shopSumAmount', 'length', 'max'=>8),
            array('created_ip, modified_ip', 'length', 'max'=>100),
            array('created_at, modified_at', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, requestDatetime, orderNumber, action, md5, shopId, shopArticleId, invoiceId, customerNumber, orderCreatedDatetime, orderSumAmount, orderSumCurrencyPaycash, orderSumBankPaycash, shopSumAmount, shopSumCurrencyPaycash, shopSumBankPaycash, paymentPayerCode, paymentType, MyField, check, requestDatetimeAviso, cps_user_country_code, aviso, is_confirmed, reason, created_at, created_by, created_ip, modified_at, modified_by, modified_ip', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'requestDatetime' => 'Request datetime',
            'orderNumber' => 'Order Number',
            'action' => 'Action',
            'md5' => 'Md5',
            'shopId' => 'Shop',
            'shopArticleId' => 'Shop Article',
            'invoiceId' => 'Invoice',
            'customerNumber' => 'Customer Number',
            'orderCreatedDatetime' => 'Order Created datetime',
            'orderSumAmount' => 'Order Sum Amount',
            'orderSumCurrencyPaycash' => 'Order Sum Currency Paycash',
            'orderSumBankPaycash' => 'Order Sum Bank Paycash',
            'shopSumAmount' => 'Shop Sum Amount',
            'shopSumCurrencyPaycash' => 'Shop Sum Currency Paycash',
            'shopSumBankPaycash' => 'Shop Sum Bank Paycash',
            'paymentPayerCode' => 'Payment Payer Code',
            'paymentType' => 'Payment Type',
            'MyField' => 'My Field',
            'check' => 'Check',
            'requestDatetimeAviso' => 'Request datetime Aviso',
            'cps_user_country_code' => 'Cps User Country Code',
            'aviso' => 'Aviso',
            'is_confirmed' => 'Is Confirmed',
            'reason' => 'Reason',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'created_ip' => 'Created Ip',
            'modified_at' => 'Modified At',
            'modified_by' => 'Modified By',
            'modified_ip' => 'Modified Ip',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('requestDatetime',$this->requestDatetime,true);
        $criteria->compare('orderNumber',$this->orderNumber,true);
        $criteria->compare('action',$this->action,true);
        $criteria->compare('md5',$this->md5,true);
        $criteria->compare('shopId',$this->shopId);
        $criteria->compare('shopArticleId',$this->shopArticleId);
        $criteria->compare('invoiceId',$this->invoiceId);
        $criteria->compare('customerNumber',$this->customerNumber);
        $criteria->compare('orderCreateddatetime',$this->orderCreatedDatetime,true);
        $criteria->compare('orderSumAmount',$this->orderSumAmount,true);
        $criteria->compare('orderSumCurrencyPaycash',$this->orderSumCurrencyPaycash,true);
        $criteria->compare('orderSumBankPaycash',$this->orderSumBankPaycash,true);
        $criteria->compare('shopSumAmount',$this->shopSumAmount,true);
        $criteria->compare('shopSumCurrencyPaycash',$this->shopSumCurrencyPaycash,true);
        $criteria->compare('shopSumBankPaycash',$this->shopSumBankPaycash,true);
        $criteria->compare('paymentPayerCode',$this->paymentPayerCode,true);
        $criteria->compare('paymentType',$this->paymentType,true);
        $criteria->compare('MyField',$this->MyField,true);
        $criteria->compare('check',$this->check);
        $criteria->compare('requestDatetimeAviso',$this->requestDatetimeAviso,true);
        $criteria->compare('cps_user_country_code',$this->cps_user_country_code,true);
        $criteria->compare('aviso',$this->aviso,true);
        $criteria->compare('is_confirmed',$this->is_confirmed,true);
        $criteria->compare('reason',$this->reason,true);
        $criteria->compare('created_at',$this->created_at,true);
        $criteria->compare('created_by',$this->created_by);
        $criteria->compare('created_ip',$this->created_ip,true);
        $criteria->compare('modified_at',$this->modified_at,true);
        $criteria->compare('modified_by',$this->modified_by);
        $criteria->compare('modified_ip',$this->modified_ip,true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

	/**
	* Метод записывает запросы плтежной системы в БД
	* @param array $post массив POST-запроса от платежной системы
	* @param boolen $real флаг типа платежа (реальный - TRUE, эмуляция - FALSE)
	* @return boolen возвращает результат сохранения записи в таблицу
	*/
    public function addResult($post, $real = TRUE) 
	{

		if (!(bool) $this->isNewRecord) 
		{
            return FALSE;
        }

        $this->requestDatetime = array_key_exists('requestdatetime', $post) ? $post['requestdatetime'] : '';
		$this->orderNumber = array_key_exists('ordernumber', $post) ? $post['ordernumber'] : '';
        $this->action = (array_key_exists('action', $post) ? $post['action'] : '');
        $this->md5 = (array_key_exists('md5', $post) ? $post['md5'] : '');
        $this->shopId = array_key_exists('shopid', $post) ? $post['shopid'] : '';
        $this->shopArticleId = array_key_exists('shoparticleid', $post) ? $post['shoparticleid'] : '';
        $this->invoiceId = array_key_exists('invoiceid', $post) ? $post['invoiceid'] : '';
        $this->customerNumber = array_key_exists('customernumber', $post) ? $post['customernumber'] : '';
		$this->orderCreatedDatetime = array_key_exists('ordercreateddatetime', $post) ? $post['ordercreateddatetime'] : '';
		$this->orderSumAmount = array_key_exists('ordersumamount', $post) ? $post['ordersumamount'] : '';
		$this->orderSumCurrencyPaycash = array_key_exists('ordersumcurrencypaycash', $post) ? $post['ordersumcurrencypaycash'] : '';
		$this->orderSumBankPaycash = array_key_exists('ordersumbankpaycash', $post) ? $post['ordersumbankpaycash'] : '';
		$this->shopSumAmount = array_key_exists('shopsumamount', $post) ? $post['shopsumamount'] : '';
		$this->shopSumCurrencyPaycash = array_key_exists('shopsumcurrencypaycash', $post) ? $post['shopsumcurrencypaycash'] : '';
		$this->shopSumBankPaycash = array_key_exists('shopsumbankpaycash', $post) ? $post['shopsumbankpaycash'] : '';
		$this->paymentPayerCode = array_key_exists('paymentpayercode', $post) ? $post['paymentpayercode'] : '';
		$this->paymentType = array_key_exists('paymenttype', $post) ? $post['paymenttype'] : '';
		$this->MyField = array_key_exists('myfield', $post) ? $post['myfield'] : '';
        $this->check = (int)TRUE;
		
        return $this->save();
    }
 
	/**
	* Метод обновляет запись с запросом плтежной системы в БД
	* @param array $post массив POST-запроса от платежной системы
	* @param boolen $real флаг типа платежа (реальный - TRUE, эмуляция - FALSE)
	* @return boolen возвращает результат сохранения записи в таблицу
	*/ 
    public function updateResult($post, $real = TRUE)
	{
		$model = PaymentsystemYandexmoneyTransactions::model()->findByGuid($post['ordernumber']);

        $model->requestDatetimeAviso = array_key_exists('requestdatetime', $post) ? $post['requestdatetime'] : '';
		$model->cps_user_country_code = array_key_exists('cps_user_country_code', $post) ? $post['cps_user_country_code'] : ''; 
        $model->aviso = (int)TRUE;
		
        return $this->save();
    }

	/**
	* Метод обновляет запись с запросом плтежной системы в БД дописывает результат успешной обработки
	* @return boolen возвращает результат сохранения записи в таблицу
	*/ 
    public function confirmResult() {
        if ((bool) $this->isNewRecord) {
            return FALSE;
        }

        $this->is_confirmed = (int) TRUE;
        return $this->save();
    }

	/**
	* Метод обновляет запись с запросом плтежной системы в БД дописывает причину отказа в обработке
	* @return boolen возвращает результат сохранения записи в таблицу
	*/ 
    public function addConfirmError($reason) {
        if ((bool) $this->isNewRecord) {
            return FALSE;
        }

        $this->is_confirmed = (int) FALSE;
        $this->reason = $reason;
        return $this->save();
    }
	
	/**
	* Метод поиска записи по guid финансовой операции
	* @param string $guid уникальный идентификатор финансовой операции
	* @return PaymentsystemYandexmoneyTransactions возвращает экземпляр класса модель таблицы
	*/ 
	public function findByGuid($guid)
	{
		$model = PaymentsystemYandexmoneyTransactions::model()->find('ordernumber = :guid', array(':guid' => $guid));
		
		return $model;
	}

}
