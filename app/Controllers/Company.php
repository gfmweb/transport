<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CompanyModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\Request;

class Company extends BaseController
{
	use ResponseTrait;
	
	/**
	 * @return string
	 * UI для работы с прайсами конкретной компании
	 */
    public function index()
    {
	    $weight = ($this->request->getVar('weight')!=='null')?$this->request->getVar('weight'):null; // Возможно пришел вес
		$from = ($this->request->getVar('from')!=='null')?$this->request->getVar('from'):null; // Возможно пришел пункт отправки
	    $to = ($this->request->getVar('to')!=='null')?$this->request->getVar('to'):null; // Возможно есть пункт приема груза
		$companyRequest = $this->request->getUri()->getSegments(); // Какую компанию будем запрашивать
		$Companies = model(CompanyModel::class); // Взяли модель
	    $company = $Companies->getCompanyByName(urldecode($companyRequest[1]));
		if(!isset($company['company_id']))$this->response->redirect('/notFound'); // Если компания не найдена
		$required = [
			'weight'=>['type'=>'number','name'=>'weight','label'=>'Вес','value'=>$weight,'placeholder'=>'Вес груза'],
			'from'=>['type'=>'text','name'=>'from','label'=>'Пункт отправления','value'=>$from,'placeholder'=>'Кладр код пункта отправления'],
			'to'=>['type'=>'text','name'=>'to','label'=>'Пункт прибытия','value'=>$to,'placeholder'=>'Кладр код пункта назначения']
		]; // поля обязательные для расчета
	    if(isset($company['company_info']['required'])) { // Если есть у компании свои обязательные поля
		    foreach ($company['company_info']['required'] as $key => $val) {
			    $required[$key] = $val; // Добавляем поля для обязательного заполнения при расчете
		    }
	    }
		//Проверка на рабочие часы
	    if($company['company_info']['work_time']['start']==$company['company_info']['work_time']['end']){ //Компания принимает заказы круглосуточно
			$orderTime = 1;
	    }
		else{ // Проверка на попадание в рабочие часы
			$orderTime = ($this->hour > $company['company_info']['work_time']['start'] && $this->hour < $company['company_info']['work_time']['end'])?1:0;
		}
		$extends =(isset($company['company_info']['extends']))?$company['company_info']['extends'] : null; // Наличие доп опций при перевозке
	 
		return view('company',
			[   'weight'=>$weight,
				'from'=>$from,
				'to'=>$to,
				'required'=>$required,
				'orderTime'=>$orderTime,
				'extends'=>$extends,
				'company_name'=>$company['company_name'],
				
			]);
		
    }
	
	/**
	 * @param array $company
	 * @param int $distance
	 * @param float $weight
	 * @param string $way
	 * @return array
	 * Блок расчета стоимости по компании
	 */
	private function evalCode(array $company, int $distance, float $weight, string $way = 'price'): array
	{
		$etalon_cost = round(eval($company['company_info'][$way]['cost']),2); // Расчитываем простую стоимость провоза
		if(isset($company['company_info'][$way]['required_vars']))
			eval($company['company_info'][$way]['required_vars']); // Подключаем дополнительные переменные
		$days = (isset($company['company_info'][$way]['day_distance']))? round ($distance / $company['company_info'][$way]['day_distance'],0) :'Нет данных по времени доставки';
		$departure = (!is_string($days))? date('Y-m-d',strtotime(+$days.'day')):-1;
		// высчитываем окончательную стоимость После того как проинициализированы и посчитаны все переменные у компании
		$totalPrice = $etalon_cost;
		if(isset($ext['ext'])) {
			foreach ($ext['ext'] as $el){
				$totalPrice = $totalPrice+$el;
			}
		}
		// Проверяем на минимальную стоимость (если она есть)
		if(isset($company['company_info'][$way]['minCost'])){
			$totalPrice = ($totalPrice > $company['company_info'][$way]['minCost'])?$totalPrice:$company['company_info'][$way]['minCost'];
		}
		return [
			'etalon'=> $etalon_cost,
			'departure'=>$departure,
			'days'=>$days,
			'extensions'=>(isset($ext['ext']))?$ext['ext']:null,
			'total'=>$totalPrice
		];
		
	}
	
	/**
	 * @return \CodeIgniter\HTTP\Response
	 * Ответ клиенту по результатам расчета
	 */
	public function getPrice()
	{
		$weight =(float) $this->request->getVar('weight'); // Обязательная часть запроса (Вес)
		$companyRequest = $this->request->getUri()->getSegments(); // Какую компанию будем запрашивать
		$Companies = model(CompanyModel::class); // Взяли модель
		$company = $Companies->getCompanyByName(urldecode($companyRequest[1])); // Получили компанию для расчетов
		$distance = $this->getDistance($this->request->getVar('from'),$this->request->getVar('from'));
		if(!is_null($distance['errors'])){ // Если обнаружились ошибки то ничего не считаем и отдаем их клиенту
			return $this->respond(['price'=>0,'period'=>-1,'coefficient'=>0,'date'=>-1,'error'=>$distance['errors']],400);
		}
		$distance = $distance['distance']; // путь в километрах
		$data = $this->evalCode($company,$distance,$weight,'price'); // Расчет обычной стоимости
		
 		$response = [
			'coefficient'=>$data['total'],
			'date'=>$data['departure'],
			'error'=>'',
		];
		// Проверяем наличие пометки у компании о экспресс доставке и нужного блока прайс листа
		if(isset($company['company_has_express'])&&(isset($company['company_info']['express_price']))){
			$data = $this->evalCode($company,$distance,$weight,'express_price'); // Расчет стоимости экспресс доставки
			$response['price']=$data['total']; //Добавляем в ответ нужные нам поля по экспрессу
			$response['period']=$data['departure']; // Дата прибытия экспресса
		}
		return $this->respond($response,200);
	}
}
