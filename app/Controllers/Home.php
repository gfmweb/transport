<?php

namespace App\Controllers;

use App\Models\CompanyModel;

class Home extends BaseController
{
	
	public function index(){
		$Companies = model(CompanyModel::class);
		
		return view('welcome_message',['companies'=>$Companies->findAll()]);
	}
	
    public function setup()
    {
       $company = [
		   'required'=>[ // Говорим что помимо основных обязательных полей нам нужно еще
			   [
				   'type'=>'text', // тип поля
				   'name'=>'fio', // как зовут параметр
				   'label'=>'ФИО' // как его подписываем
			   ]
		   ],
	       'work_time'=>[ // Рабочее время компании
			   'start'=>8, // Начало
		       'end'=>19  // Конец
	       ],
	       
	       'extends'=>[ // Дополнения которые может выбрать клиент
			   [
				   'mode'=>'form-module', // Будет отдана целая строка
				   'name'=>'pallets', // Как зовется параметр
				   'label'=>'Палеты', // Как его подписываем
				   'type'=>'number', // Какой у него тип
				   'value'=>null, // Значение по умолчанию
				   'placeholder'=>'Укажите количество палет', // Подсказка внутри поля
				   'cost' =>'return 300;' // что вернем когда вызовем  eval вот этого ключа
				   
			   ],
		       [
				   'mode'=>'checkbox-group', // Для вьюшки сообщили что нужно будет отрисовать поле с чекбоксами
			       'label'=>'Название группы чекбоксов', // Назвали это поле
			       'items'=>[ // Массив самих чекбоксов
				       [
					   'name'   =>'insurance', // Имя параметра
				       'label'  =>'Страховка', // Подпись
				       'value'  =>'checked', // Уже выбран
					   'cost'   =>'return round($etalon_cost * 0.05,0);' // что вернем когда вызовем  eval вот этого ключа
			           ],
			            [
				       'name'   =>'termo',
				       'label'  =>'Термобудка',
				       'value'  =>'unchecked',
				       'cost'   =>'return 1000;'
			            ]
				   ]
		       ]
	       ],
	       'price'=>[ // Обычная стоимость доставки
		       'minCost'=>2000, // Минимальная цена
		       'km_cost'=>5, // Стоимость проезда 1 км пути
		       'day_distance'=>200, // Сколько за день (ну в среднем) проедет груз
		       'required_vars'=> // Тут описаны переменные (конкретно для этой компании) которые могут быть нужны при расчетах
			       'return $ext = [
		                 \'fio\' => $this->request->getVar(\'fio\'),
		                 \'ext\'=>
		                    [
		                        \'pallets\' => (int) $this->request->getVar(\'pallets\') * eval($company[\'company_info\'][\'extends\'][0][\'cost\']),
		                        \'insurance\'=>($this->request->getVar(\'insurance\')==\'checked\')?eval($company[\'company_info\'][\'extends\'][1][\'items\'][0][\'cost\']):0,
								\'termo\'=>($this->request->getVar(\'termo\')==\'checked\')?eval($company[\'company_info\'][\'extends\'][1][\'items\'][1][\'cost\']):0
		                    ]
		           ];',
		       'cost' =>'return (($weight/100) * $company[\'company_info\'][\'price\'][\'km_cost\'] * $distance);' // Расчет стоимости провоза груза
	       ],
	       'express_price'=>[ // Экспресс доставка
		       'minCost'=>5000,
		       'km_cost'=>8,
		       'day_distance'=>400,
		       'required_vars'=>
			       'return $ext = [
		                 \'fio\' => $this->request->getVar(\'fio\'),
		                 \'ext\'=>
		                    [
		                        \'pallets\' => (int) $this->request->getVar(\'pallets\') * eval($company[\'company_info\'][\'extends\'][0][\'cost\']),
		                        \'insurance\'=>($this->request->getVar(\'insurance\')==\'checked\')?eval($company[\'company_info\'][\'extends\'][1][\'items\'][0][\'cost\']):0,
								\'termo\'=>($this->request->getVar(\'termo\')==\'checked\')?eval($company[\'company_info\'][\'extends\'][1][\'items\'][1][\'cost\']):0
		                    ]
		           ];',
		       'cost' =>'return (($weight/100) * $company[\'company_info\'][\'express_price\'][\'km_cost\'] * $distance);'
	       ]
       ];
	   $company2 = [
		    'work_time'=>[
			    'start'=>10,
			    'end'=>10
		    ],
		    'price'=>[
			    'minCost'=>2000,
			    'km_cost'=>3,
			    'day_distance'=>150,
			    'cost' =>'return (($weight/100) * $company[\'company_info\'][\'price\'][\'km_cost\'] * $distance);'
		    ],
		    
	    ];
	   $Com = model(CompanyModel::class);
	   $Com->query("TRUNCATE companies");
	   $Com->addCompany('Едет и везёт',true,json_encode($company,256));
	   $Com->addCompany('НУ почти',false,json_encode($company2,256));
	   return $this->response->redirect('/');
    }
}
