<!doctype html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport"
	      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title><?=$company_name?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
</head>
<body>
	<section id="App">
		<div class="container">
		<h1 class="h1 text-primary text-center"><?=$company_name?></h1>
	</div>
		<div class="container">
		<div class="row justify-content-center">
			<div class="col-8">
				<div class="card">
					<div class="card-heder">
						<h3 class="text-center">Калькулятор доставки</h3>
						<template v-if="Errors!==null">
							<div class="bg-danger">
								<ol>
									<li v-for="error in Errors" v-text="error"></li>
								</ol>
							</div>
						</template>
					</div>
					<div class="card-body">
						<form id="form" v-on:submit.prevent="submitForm">
							<?php // Отрисовка обязательных полей
								$formFields = array_keys($required);
								foreach ($formFields as $field):?>
									
									<div class="container mt-2 mb-2">
										<label for="<?=$required[$field]['name']?>">
											<?=$required[$field]['label']?>
										</label>
										<input id="<?=$required[$field]['name']?>"
											   type="<?=$required[$field]['type']?>"
										       class="form-control required"
										       <?=($required[$field]['name']=='weight')?'step="0.01"':null?>
										       name="<?=$required[$field]['name']?>"
										       placeholder="<?= isset($required[$field]['placeholder'])?$required[$field]['placeholder']:null ?>"
										       value="<?= isset($required[$field]['value'])?$required[$field]['value']:null ?>"/>
									</div>
								<?php endforeach;?>
								<hr/>
								<?php if(!is_null($extends)): ?>
									<div class="container">
										<div class="row">
										<h4 class="h4 text-center">Дополнительные опции</h4>
										<?php foreach ($extends as $extend):?>
											<?php if($extend['mode']=='form-module'):?>
												<div class="container mt-2 mb-2">
													<label for="<?=$extend['name']?>">
														<?=$extend['label']?>
													</label>
													<input id="<?=$extend['name']?>"
													       type="<?=$extend['type']?>"
													       class="form-control"
													       name="<?=$extend['name']?>"
													       placeholder="<?= isset($extend['placeholder'])?$extend['placeholder']:null ?>"
													       value="<?= isset($extend['value'])?$extend['value']:null ?>"/>
												</div>
											<?php endif; ?>
											<?php if($extend['mode']=='checkbox-group'):?>
												<div class="container-fluid">
													<h4 class="text-secondary text-center"><?=$extend['label']?></h4>
													<div class="row justify-content-between">
														<?php foreach($extend['items'] as $box):?>
															<div class="col-3">
															<label class="form-check-label"  onclick="changeCheckbox('<?=$box['name']?>')" for="<?=$box['name']?>"><?=$box['label']?></label>
															<input type="checkbox" id="<?=$box['name']?>"
															                        class="form-check-input"
															                        value="<?=$box['value']?>"
															                        onclick="changeCheckbox('<?=$box['name']?>')"
															                        name="<?=$box['name']?>"
																					<?=$box['value']?>  />
															</div>
														<?php endforeach; ?>
													</div>
												</div>
											<?php endif;?>
										<?php endforeach;?>
									</div>
									</div>
								<?php endif;?>
								<div class="row justify-content-center">
									<div class="col-6">
										<div class="row" >
											<button class="btn btn-success btn-rounded" type="submit" v-text="BtnText" v-if="Atwork==1"></button>
											<button class="btn btn-dark btn-rounded" type="submit" v-text="BtnText" v-else></button>
										</div>
										
									</div>
								</div>
						</form>
						<div class="card-footer" v-if="Results!==null">
							<div class="row mt-4">
								<div class="col">
									<p>Доставлено будет к {{Results.date}}</p>
									<p>Стоимость {{Results.coefficient}}</p>
								</div>
								<div class="col" v-if="typeof Results.price!== 'undefined'">
									<p>Экспресс доставка к {{Results.period}}</p>
									<p>Стоимость экспресс доставки  {{Results.price}}</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	</section>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/vue@2.7.0"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.27.2/axios.js" integrity="sha512-rozBdNtS7jw9BlC76YF1FQGjz17qQ0J/Vu9ZCFIW374sEy4EZRbRcUZa2RU/MZ90X2mnLU56F75VfdToGV0RiA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
	<script>
	 function	changeCheckbox(id){
			let box = document.getElementById(id)
		    if (box.value =='checked'){
				box.value = 'unchecked'
		    }
			else{
				box.value = 'checked'
		    }
		}
	</script>
<script>
	const App = new Vue({
		el:'#App',
		data:{
		 Errors:null,
		 Results:null,
		 Atwork:'<?=$orderTime?>'
		},
		methods:{
			submitForm(){
			const self = this
			let errors = []
			 let forma = document.getElementById("form").elements;
			 let request = new FormData()
			  let len = forma.length
				for(i=0; i < len; i++){
					if(forma[i].classList.contains('required')){ // Проверка на обязательное поле
						if(forma[i].value == ''){
							errors.push('Вы пропустили поле '+forma[i].name)
						}
					}
					request.append(forma[i].name,forma[i].value)
					
				}
				if(errors.length > 0){ // Сообщаем об ошибках
					this.Errors = errors
				}
				else{
					axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
					axios.post('/company/<?=$company_name?>', request)
							.then(res => {
							 	self.Results = res.data
							})
							.catch((error) => {
								alert('УПС! Смотри в консоль')
								console.log(error);
							});
				}
			 
			}
		},
		computed:{
			BtnText: function(){
				if(this.Atwork == 1){
					return 'Посчитать и заказать'
				}
				else{
					return 'Посчитать но заказать не получится'
				}
			}
		}
	});
	
</script>
</body>
</html>




