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

class PayControllerBase extends UTIController
{
	/**
	* @var array конфигурация для модуля, берется из БД
	*/
    protected $config;
	/**
	* @var string псевдоним, который используется для поиска значений в таблице конфигурации
	*/
    protected $alias = 'yandexmoney_alias';
	/**
	* @var object экземпляр класса helpers/Yandexmoney
	*/
    protected $yandexmoney;
	/**
	* @var object экземпляр класса models/PaymentsystemYandexmoneyTransactions
	*/
    protected $paymentsystemYandexmoneyTransactions;
	/**
	* @var string сообщения модуля
	*/
    protected $description;

    public function init()
    {
        $this->config = PaymentsSystemConfig::GetConfigByAlias($this->alias);
        $this->yandexmoney = new Yandexmoney($this->config);
    }
	
	/**
	* Контроллер главной страницы модуля
	* @param varchar $guid уникальный идентификатор финансовой транзакции.
	*/

    public function Index($guid = '')
    {
        if (Yii::app()->user->isGuest)
        {
            throw new CHttpException(403, 'Forbidden');
        }
        if (empty($guid))
        {
            throw new CHttpException('400', 'Ошибочный запрос, не найден параметр guid.');
        }
		
		//проверяем способ оплаты: банковская карта или кошелек Яндекс Деньги
		$bank = (int)FALSE;
		
		if(array_key_exists('bank', $_SESSION) && $_SESSION['bank'] === (int)TRUE); 
		{
			$bank = $_SESSION['bank'];
		}
        $transaction_model = PaymentsSystem::GetModelTransactionByGuid($guid);

        if (empty($transaction_model))
        {
            throw new CHttpException('400', 'Ошибочный запрос, не найден параметр transaction model for guid.');
        }

        $this->pageTitle = $transaction_model->getModelSpecification()->title;
        $title = $this->pageTitle;
        $transaction_id = $transaction_model->getModelTransactions()->id;

        $amount = $this->_currency_converter($transaction_model->amount);
		
		//получаем сгененрированый код формы для оплаты
        $form = $this->yandexmoney->GetForm($transaction_id, $amount, $guid, $title, $guid, $bank);
		
		//если включена эмуляция, то записываем адресс для отправки запроса 
        if ($this->config['emulation'] == 'true')
        {
            $form['action'] = $this->createUrl('pay/emulation/guid/' . $guid);
        }
		
		$_SESSION['bank'] = (int)FALSE;
		$bank = (int)FALSE;
		
        PaymentsSystemLog::Addlog(PaymentsSystemLog::TYPE_PAYMENTSYSTEM_OUT, var_export($form, true), PaymentsSystemLog::TYPE_PAYMENTSYSTEM_REQUEST_POST, $this->yandexmoney->config['action'], $transaction_id, 'actionIndex', $this->alias);
        $this->render('index', array('form_yandexmoney' => $form, 'model' => $transaction_model));
    }
	
	/**
	* Контроллер страницы ошибки оплаты
	* @param varchar $guid уникальный идентификатор финансовой транзакции.
	*/
	public function Fail($guid = '')
    {
        if (Yii::app()->user->isGuest)
        {
            throw new CHttpException(403, 'Forbidden');
        }
        $this->pageTitle = 'Отмена оплаты';
        $this->include_jquery();
        PaymentsSystemLog::Addlog(PaymentsSystemLog::TYPE_PAYMENTSYSTEM_IN, var_export($_REQUEST, true), PaymentsSystemLog::TYPE_PAYMENTSYSTEM_REQUEST_POST, $this->yandexmoney->config['action'], '', 'actionFail', $this->alias);

        $transaction = NULL;

        if ((empty($_POST)) && (!empty($guid)) && ($this->config['emulation'] === 'true'))
        {
            $transaction = PaymentsSystem::GetModelTransactionByGuid($guid);
            $transaction = $transaction->getModelTransactions();
        }
        else
        {
            $guid = '';
        }

        if (array_key_exists('ordernumber', $_POST))
        {
            $guid = $_POST['ordernumber'];
            $transaction = PaymentsSystem::GetModelTransactionByGuid($guid);
            $transaction = $transaction->getModelTransactions();
        }


        $url = $transaction != NULL ? Yii::app()->createAbsoluteUrl($transaction->spec->redirect_decline) : Yii::app()->createAbsoluteUrl('/office');

        $this->render('waiting_fail', array('url' => $url, 'guid' => $guid));
    }
	
	/**
	* Контроллер страницы успешной оплаты
	* @param varchar $guid уникальный идентификатор финансовой транзакции.
	*/
    public function Success($guid = '')
    {
        if (Yii::app()->user->isGuest)
        {
            throw new CHttpException(403, 'Forbidden');
        }
        $this->pageTitle = 'Оплата проведена';
        $this->include_jquery();
        PaymentsSystemLog::Addlog(PaymentsSystemLog::TYPE_PAYMENTSYSTEM_IN, var_export($_REQUEST, true), PaymentsSystemLog::TYPE_PAYMENTSYSTEM_REQUEST_POST, $this->yandexmoney->config['action'], '', 'actionSuccess', $this->alias);

        $transaction = NULL;

        if ((empty($_POST)) && (!empty($guid)) && ($this->config['emulation'] === 'true'))
        {
            $transaction = PaymentsSystem::GetModelTransactionByGuid($guid);
            $transaction = $transaction->getModelTransactions();
        }
        else
        {
            $guid = '';
        }

        if (array_key_exists('ordernumber', $_POST))
        {
            $guid = $_POST['ordernumber'];
            $transaction = PaymentsSystem::GetModelTransactionByGuid($guid);
            $transaction = $transaction->getModelTransactions();
        }


        $url = $transaction != NULL ? Yii::app()->createAbsoluteUrl($transaction->spec->redirect_confirm) : Yii::app()->createAbsoluteUrl('/office');

        $this->render('waiting_success', array('url' => $url, 'guid' => $guid));
    }

	/**
	* Метод эмулирующий отправку POST-запросов платежной системы
	*/

	public function Send()
	{
		if( $curl = curl_init() ) 
		{
			curl_setopt($curl, CURLOPT_URL, 'http://test1.shvedko.d.ukrtech.info/paymentsystem/yandexmoney/pay/result');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, "requestdatetime=2015-02-18&shopid=25101&invoiceId=111111&action=checkOrder&ordernumber=e914466f311b04bdf672cb1642aa14b2&sum=414.16");
			$out = curl_exec($curl);
			vg($out);
			curl_close($curl);
		  }
	}

	/**
	* Контроллер страницы обработки и возврата ответов на запросы платежной системы
	* @return xml возвращает ответ в теле POST-запроса платежной системы
	*/
    public function Result()
    {
		
		if (empty($_POST))
        {
            throw new CHttpException('400', 'Ошибочный запрос, &_POST = empty.');
        }
		
		PaymentsSystemLog::Addlog(PaymentsSystemLog::TYPE_PAYMENTSYSTEM_IN, var_export($_POST, true), PaymentsSystemLog::TYPE_PAYMENTSYSTEM_REQUEST_POST, $this->yandexmoney->config['action'], '', 'actionResult', $this->alias);
		$this->paymentsystemYandexmoneyTransactions = new PaymentsystemYandexmoneyTransactions();
		
		$xml = $this->yandexmoney->sendErrorAnswer($_POST, Yandexmoney::CHECKFAILPARSE);
		
		//преобразовываем POST в массив
		$post = $this->_getFormFromPost($_POST);
		
		//проверяем реквизиты платежа до момента оплаты
		if($_POST['action'] == 'checkOrder')
		{
			//записываем POST-запрос в таблицу логирования модуля
			if (!(bool) $this->paymentsystemYandexmoneyTransactions->addResult($post))
			{
				throw new CHttpException('400', 'Ошибка записи платженой транзакции: ' . var_export($this->paymentsystemYandexmoneyTransactions->getErrors()));
			}
			
			//проверяем реквизиты платежа
			$result = $this->validateCheck($_POST);
			
			if($result)
			{
				//генерируем положительный ответ для платежной системы
				$xml = $this->yandexmoney->sendSuccessAnswer($_POST, Yandexmoney::CHECKSUCCESS, $_POST['action']);
			}
			else
			{	
				//генерируем отрицательный ответ для платежной системы
				$xml = $this->yandexmoney->sendErrorAnswer($_POST, Yandexmoney::CHECKFAILPARSE);
			}
		}
		// подтверждаем/отклоняем платеж
		elseif($_POST['action'] == 'paymentAviso')
		{
			//записываем POST-запрос в таблицу логирования модуля
			if (!(bool) $this->paymentsystemYandexmoneyTransactions->addResult($post))
			{
				throw new CHttpException('400', 'Ошибка записи платженой транзакции: ' . var_export($this->paymentsystemYandexmoneyTransactions->getErrors()));
			}
			
			//проверяем реквизиты платежа
			$result = $this->validate($_POST);
			
			if($result)
			{
				//генерируем положительный ответ для платежной системы
				$xml = $this->yandexmoney->sendSuccessAnswer($_POST, Yandexmoney::AVISOSUCCESS, $_POST['action']);
			}
			else
			{
				//генерируем отрицательный ответ для платежной системы
				$xml = $this->yandexmoney->sendErrorAnswer($_POST, Yandexmoney::CHECKFAILPARSE);
			}
		}
		else
		{
			//генерируем отрицательный ответ для платежной системы в случае если был направлен ошибочный запрос от платежной системы
			$xml = $this->yandexmoney->sendErrorAnswer($_POST, Yandexmoney::CHECKFAILPARSE);
		}
		
		print $xml;
        
    }

	/**
	* Контроллер страницы эмуляции действий платежной системы
	* @param varchar $guid уникальный идентификатор финансовой транзакции.
	*/
    public function Emulation($guid)
    {
        if (Yii::app()->user->isGuest)
        {
            throw new CHttpException(403, 'Forbidden');
        }
        if ($this->config['emulation'] !== 'true')
        {
            throw new CHttpException(403, 'Forbidden');
        }

        $isConfirmed = false;

        if (isset($_POST['payment_method']))
        {
            $this->paymentsystemYandexmoneyTransactions = new PaymentsystemYandexmoneyTransactions();

			//записываем POST-запрос в таблицу логирования модуля
            if (!(bool) $this->paymentsystemYandexmoneyTransactions->addResult($this->_getFormFromPost($_POST, $guid), FALSE))
            {
                throw new CHttpException('400', 'Ошибка записи платженой транзакции: ' . var_export($this->paymentsystemYandexmoneyTransactions->getErrors(), TRUE));
            }
        }
		
        $message_from_emulation = 'Емуляция платежной системы "Яндекс.Деньги". Гуид транзакции: ' . $guid . '<hr />';
		
		//обработка эмуляции положительного ответа платежной системы с подтверждением финансовой операции
        if (isset($_POST['reply_confirmed_pay']))
        {
		
            $form = $this->getformemulation($guid, 'success');

            $this->paymentsystemYandexmoneyTransactions = PaymentsystemYandexmoneyTransactions::model()->find('ordernumber = :ordernumber', array(':ordernumber' => $guid));

            if (!(bool) $this->paymentsystemYandexmoneyTransactions->updateResult($this->_getFormFromPost($form), FALSE))
            {
                throw new CHttpException('400', 'Ошибка записи платженой транзакции: ' . var_export($this->paymentsystemYandexmoneyTransactions->getErrors(), TRUE));
            }

            $result = $this->validateCheck($form);

            if ($result)
            {
                Yii::app()->request->redirect(Yandexmoney::getSuccessUrl());
            }
            else
            {
                throw new CHttpException('400', 'Ошибочный запрос emulation');
            }

            $isConfirmed = true;
        }
		
		//обработка эмуляции положительного ответа платежной системы без подтверждения финансовой операции
         if (isset($_POST['reply_confirmed'])) 
		 {

			  $form = $this->getformemulation($guid, 'success');

			  $this->paymentsystemYandexmoneyTransactions = PaymentsystemYandexmoneyTransactions::model()->find('ordernumber = :ordernumber', array(':ordernumber'=>$guid));

			  if (!(bool) $this->paymentsystemYandexmoneyTransactions->updateResult($form, FALSE)) {
			  throw new CHttpException('400', 'Ошибка записи платженой транзакции: ' . var_export($this->paymentsystemYandexmoneyTransactions->getErrors(), TRUE));
			  }

			  $result = $this->validate($form);

			  if ((bool) $result) {
			  $message_from_emulation .= '<br />Статус: Ok<br />Описание: ' . $this->description . '<hr />';
			  } else {
			  $message_from_emulation .= '<br />Статус: Fail<br />Описание: ' . $this->description . '<hr />';
			  }

			  $isConfirmed = 'success';
          }
		  
          //Эмуляция ответа неудачной оплаты от сервера платежной системы
          if (isset($_POST['reply_canceled']))
		  {
			  $form = $this->getformemulation($guid, 'fail');

			  $this->paymentsystemYandexmoneyTransactions = new PaymentsystemYandexmoneyTransactions();

			  if (!(bool) $this->paymentsystemYandexmoneyTransactions->addResult($form, FALSE)) {
			  throw new CHttpException('400', 'Ошибка записи платженой транзакции: ' . var_export($this->paymentsystemYandexmoneyTransactions->getErrors(), TRUE));
			  }

			  $result = $this->validate($form);

			  if ((bool) $result) {
			  $message_from_emulation .= '<br />Статус: Ok<br />Описание: ' . $this->description . '<hr />';
			  } else {
			  $message_from_emulation .= '<br />Статус: Fail<br />Описание: ' . $this->description . '<hr />';
			  }

			  $isConfirmed = 'fail';
          }
		//Эмуляция ответа неудачной оплаты от сервера платежной системы и возврата к сайту
          if (isset($_POST['reply_canceled_pay'])) 
		  {
			  $form = $this->getformemulation($guid, 'fail');

			  $this->paymentsystemYandexmoneyTransactions = new PaymentsystemYandexmoneyTransactions();

			  if (!(bool) $this->paymentsystemYandexmoneyTransactions->addResult($form, FALSE)) {
			  throw new CHttpException('400', 'Ошибка записи платженой транзакции: ' . var_export($this->paymentsystemYandexmoneyTransactions->getErrors(), TRUE));
			  }

			  $result = $this->validate($form);

			  Yii::app()->request->redirect(Yandexmoney::getfailurl());

			  $isConfirmed = true;
          } 

        if (isset($_POST['reply_return_success']))
        {
            Yii::app()->request->redirect(Yandexmoney::getSuccessUrl());
        }

        if (isset($_POST['reply_return_fail']))
        {
            Yii::app()->request->redirect(Yandexmoney::getfailurl());
        }

        $this->render('emulation', array('message' => $message_from_emulation, 'isConfirmed' => $isConfirmed));
    }

	
	/**
	* Метод генерирующий массив значений для формы платежной системы (эмуляция)
	* @param varchar $guid уникальный идентификатор финансовой транзакции.
	* @param varchar $status Статус операции (например: success).
	* @return array возвращает массив параметров которые генерирует платежная система
	*/
    private function getformemulation($guid, $status)
    {
        $transaction_model = PaymentsSystem::GetModelTransactionByGuid($guid);

        if ($transaction_model == NULL)
        {
            throw new CHttpException('400', 'Ошибочный запрос, не найден параметр transaction model for guid.');
        }

        return $this->yandexmoney->getFieldsForEmulation($transaction_model->getModelTransactions(), $transaction_model->amount, $status, $guid);
    }

	/**
	* Метод проверки POST запроса с action = 'orderCheck'
	* @param array $post POST запрос платежной системы
	* @return string возвращает результат проверки реквизитов платежа, в случае успешной проверки возвращает TRUE
	*/
	private function validateCheck($post = array())
    {	
		$result = TRUE;

		if (empty($post))
		{
			$result = $this->_validateFail('Передан пустой POST-массив');
		}

		PaymentsSystemLog::Addlog(PaymentsSystemLog::TYPE_PAYMENTSYSTEM_IN, var_export($post, true), PaymentsSystemLog::TYPE_PAYMENTSYSTEM_REQUEST_POST, $post['action'], '', 'validate', $this->alias);
		
		$form = $this->_getFormFromPost($post);

		if (empty($form))
		{
			$result = $this->_validateFail('Сформированная форма из POST массива пустая');
		}

		if (!array_key_exists('ordernumber', $form))
		{
			$result = $this->_validateFail('В принятом POST-массиве не найден id транзакции');
		}

		$transaction_model = PaymentsSystem::GetModelTransactionByGuid($form['ordernumber']);

		if ($transaction_model == NULL)
		{
			$result = $this->_validateFail('По id ' . $form['ordernumber'] . ' не найдена финансовая операция');
		}

		 if ((!array_key_exists('action', $form)) || (!array_key_exists('ordersumamount', $form)) || (!array_key_exists('ordersumcurrencypaycash', $form)) || (!array_key_exists('ordersumbankpaycash', $form)) || (!array_key_exists('shopid', $form)) ||
		  (!array_key_exists('invoiceid', $form)) || (!array_key_exists('customernumber', $form))) 
		  {
				$result = $this->_validateFail('Отсутствует необходимые параметры в POST ответе');
		  } 

		if ($form['ordernumber'] != $transaction_model->getModelTransactions()->guid)
		{
			$result = $this->_validateFail('ID финансовой операции (' . (int) $form['ordernumber'] . ') не соответствует ID операции (' . $transaction_model->getModelTransactions()->guid . ')');
		}
		
		if (!$this->_checkSignature($form) && $this->config['emulation'] != 'true') 
		{
		  $result = $this->_validateFail('Неверная сигнатура: ' . $form['md5']);
		} 

        return $result;
    }

	/**
	* Метод проверки POST запроса с action = 'paymentAviso'
	* @param array $post POST запрос платежной системы
	* @return string возвращает результат проверки реквизитов платежа, в случае успешной проверки возвращает TRUE
	*/
    private function validate($post = array())
    {

        PaymentsSystemLog::Addlog(PaymentsSystemLog::TYPE_PAYMENTSYSTEM_IN, var_export($post, true), PaymentsSystemLog::TYPE_PAYMENTSYSTEM_REQUEST_POST, $this->yandexmoney->config['action'], '', 'validate', $this->alias);
		
		$profileTransaction= Yii::app()->db->beginTransaction();
		$result = TRUE;
		try
		{
			
		
			if (empty($post))
			{
				$result = $this->_validateFail('Передан пустой POST-массив');
			}

			$form = $this->_getFormFromPost($post);

			if (empty($form))
			{
				$result = $this->_validateFail('Сформированная форма из POST массива пустая');
			}

			if (!array_key_exists('ordernumber', $form))
			{
				$result = $this->_validateFail('В принятом POST-массиве не найден id транзакции');
			}

			$transaction_model = PaymentsSystem::GetModelTransactionByGuid($form['ordernumber']);

			if ($transaction_model == NULL)
			{
				$result = $this->_validateFail('По id ' . $form['ordernumber'] . ' не найдена финансовая операция');
			}

			 if ((!array_key_exists('action', $form)) || (!array_key_exists('ordersumamount', $form)) || (!array_key_exists('ordersumcurrencypaycash', $form)) || (!array_key_exists('ordersumbankpaycash', $form)) || (!array_key_exists('shopid', $form)) ||
			  (!array_key_exists('invoiceid', $form)) || (!array_key_exists('customernumber', $form))) 
			  {
					$result = $this->_validateFail('Отсутствует необходимые параметры в POST ответе');
			  } 

			if ($form['ordernumber'] != $transaction_model->getModelTransactions()->guid)
			{
				$result = $this->_validateFail('ID финансовой операции (' . (int) $form['ordernumber'] . ') не соответствует ID операции (' . $transaction_model->getModelTransactions()->guid . ')');
			}

			if ((float) $form['ordersumamount'] != (float) $this->yandexmoney->getSumm($transaction_model->getModelTransactions()->amount))
			{
				$result = $this->_validateFail('Сумма финансовой операции (' . (float) $this->yandexmoney->getSumm($transaction_model->getModelTransactions()->amount) . ') не соответствует POST запросу (' . (float) $form['ordersumamount'] . ')');
			}

			
			if (!$this->_checkSignature($form)) 
			{
			  $result = $this->_validateFail('Неверная сигнатура: ' . $form['md5']);
			} 

			if ($result === TRUE)
			{
				
				$transaction_model->confirmSystem();

				$result = $this->_validateSuccess();

				$this->description = 'Финансовая операция № ' . $transaction_model->getModelTransactions()->id . ' подтверждена';

				$profileTransaction->commit();
			}
		}
			catch (Exception $e) 
			  {
				if ($profileTransaction->getActive())
				{
					$profileTransaction->rollback();
				}
					throw new CException($e->getMessage());
			  }
         

        return $result;
    }

	/**
	* Метод преобразует POST запроса в массив который участвует в дальшейшей работе
	* @param array $post POST запрос платежной системы
	* @param varchar $guid уникальный идентификатор финансовой транзакции.
	* @return array массив параметров запроса/ответа платежной системы
	*/	
    private function _getFormFromPost($post, $guid = false)
    {
	 
		if($this->config['emulation'] == 'true')
		{
			$form = array();
			$form['transactions__id'] = $guid;
			$form['requestdatetime'] = array_key_exists('requestdatetime', $post) ? $post['requestdatetime'] : '';
			$form['action'] = array_key_exists('action', $post) ? $post['action'] : '';
			$form['md5'] = array_key_exists('md5', $post) ? $post['md5'] : '';
			$form['shopid'] = array_key_exists('shopid', $post) ? $post['shopid'] : '';
			$form['shoparticleid'] = array_key_exists('shoparticleid', $post) ? $post['shoparticleid'] : '';
			$form['invoiceid'] = array_key_exists('invoiceid', $post) ? $post['invoiceid'] : '';
			$form['customernumber'] = array_key_exists('customernumber', $post) ? $post['customernumber'] : '';
			$form['ordercreateddatetime'] = array_key_exists('ordercreateddatetime', $post) ? $post['ordercreateddatetime'] : '';
			$form['ordersumamount'] = array_key_exists('sum', $post) ? $post['sum'] : '';
			$form['ordersumcurrencypaycash'] = array_key_exists('ordersumcurrencypaycash', $post) ? $post['ordersumcurrencypaycash'] : '';
			$form['ordersumbankpaycash'] = array_key_exists('ordersumbankpaycash', $post) ? $post['ordersumbankpaycash'] : '';
			$form['shopsumamount'] = array_key_exists('shopsumamount', $post) ? $post['shopsumamount'] : '';
			$form['shopsumcurrencypaycash'] = array_key_exists('shopsumcurrencypaycash', $post) ? $post['shopsumcurrencypaycash'] : '';
			$form['shopsumbankpaycash'] = array_key_exists('shopsumbankpaycash', $post) ? $post['shopsumbankpaycash'] : '';
			$form['paymentpayercode'] = array_key_exists('paymentpayercode', $post) ? $post['paymentpayercode'] : '';
			$form['paymenttype'] = array_key_exists('paymenttype', $post) ? $post['paymenttype'] : '';
			$form['myfield'] = array_key_exists('myfield', $post) ? $post['myfield'] : '';
			$form['ordernumber'] = array_key_exists('ordernumber', $post) ? $post['ordernumber'] : '';
			$form['cps_user_country_code'] = array_key_exists('cps_user_country_code', $post) ? $post['cps_user_country_code'] : '';
		}
		else
		{
		
			$form = array();
			$form['transactions__id'] = $guid;
			$form['requestdatetime'] = array_key_exists('requestDatetime', $post) ? $post['requestDatetime'] : '';
			$form['action'] = array_key_exists('action', $post) ? $post['action'] : '';
			$form['md5'] = array_key_exists('md5', $post) ? $post['md5'] : '';
			$form['shopid'] = array_key_exists('shopId', $post) ? $post['shopId'] : '';
			$form['shoparticleid'] = array_key_exists('shopArticleId', $post) ? $post['shopArticleId'] : '';
			$form['invoiceid'] = array_key_exists('invoiceId', $post) ? $post['invoiceId'] : '';
			$form['customernumber'] = array_key_exists('customerNumber', $post) ? $post['customerNumber'] : '';
			$form['ordercreateddatetime'] = array_key_exists('orderCreatedDatetime', $post) ? $post['orderCreatedDatetime'] : '';
			$form['ordersumamount'] = array_key_exists('orderSumAmount', $post) ? $post['orderSumAmount'] : '';
			$form['ordersumcurrencypaycash'] = array_key_exists('orderSumCurrencyPaycash', $post) ? $post['orderSumCurrencyPaycash'] : '';
			$form['ordersumbankpaycash'] = array_key_exists('orderSumBankPaycash', $post) ? $post['orderSumBankPaycash'] : '';
			$form['shopsumamount'] = array_key_exists('shopSumAmount', $post) ? $post['shopSumAmount'] : '';
			$form['shopsumcurrencypaycash'] = array_key_exists('shopSumCurrencyPaycash', $post) ? $post['shopSumCurrencyPaycash'] : '';
			$form['shopsumbankpaycash'] = array_key_exists('shopSumBankPaycash', $post) ? $post['shopSumBankPaycash'] : '';
			$form['paymentpayercode'] = array_key_exists('paymentPayerCode', $post) ? $post['paymentPayerCode'] : '';
			$form['paymenttype'] = array_key_exists('paymentType', $post) ? $post['paymentType'] : '';
			$form['myfield'] = array_key_exists('MyField', $post) ? $post['MyField'] : '';
			$form['ordernumber'] = array_key_exists('orderNumber', $post) ? $post['orderNumber'] : '';
			$form['cps_user_country_code'] = array_key_exists('cps_user_country_code', $post) ? $post['cps_user_country_code'] : '';
		}
		
		
        return $form;
    }

	/**
	* Метод форматирует суммы
	* @param float $amount входящаяя сумма
	* @return float отформатированная сумма вида '123456789.0000'
	*/
    private function _currency_converter($amount)
    {
        $amount_result = $amount;
        $amount_result = number_format($amount_result, 4, ".", "");
        return $amount_result;
    }

	/**
	* Метод проверяет сигнатуру запроса платежной системы
	* @param array $form входящая форма запроса платежной системы
	* @return bool TRUE - сигнатуры совпали, FALSE - сигнатуры не совпали
	*/
    private function _checkSignature($form)
    {

        $signature = $this->yandexmoney->getAnswerSignature($form);
		
        if ($signature === $form['md5'])
        {
            return TRUE;
        }

        return FALSE;
    }

	/**
	* Метод записывает причину отказа модуля от дальнейшей работы с платежной системой в таблицу логирования
	* @param string $description входящая строка сообщения
	* @return bool FASLE
	*/
    private function _validateFail($description)
    {
        $this->paymentsystemYandexmoneyTransactions->addConfirmError($description);
        $this->description = $description;
        return FALSE;
    }


	/**
	* Метод записывает в таблицу логирования результат работы модуля
	* @return bool TRUE
	*/
    private function _validateSuccess()
    {
        $this->paymentsystemYandexmoneyTransactions->confirmResult();
        return TRUE;
    }

}
