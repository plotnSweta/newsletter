// Рассылка владельцам приборов при приближении даты поверки(до поверки месяц и менее)
function ValidationPeriodEnds(){
	$int=date('t');
	$d=(date("d")+30-$int);
	$m=(date("m")<12)?(date("m")+1):(date("m")-11);
	$Y=(date("m")<12)?(date("Y")):(date("Y")+1);
	$upcoming_date=date("d.m.Y", mktime(0, 0, 0, $m, $d, $Y));

	$array_prop=array();
	If(CModule::IncludeModule("iblock")){
		//Получаем массив ID всех приборов
		$arSelect = Array("ID","NAME");
		$arFilter = Array("IBLOCK_ID"=>1, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
		$res = CIBlockElement::GetList(Array(), $arFilter, false,false, $arSelect);
		while($arr_res=$res->Fetch()){
			$array=array();
			$measuring_id=$arr_res["ID"];
			$measuring_name=$arr_res["NAME"];
			$array['measuring_id']=$measuring_id;
			$array['measuring_name']=$measuring_name;
			$Iblock=new CIBlockElement();
			$res_prop = $Iblock->GetProperty(1, $measuring_id, array(), Array( ));
			while ($ob = $res_prop->GetNext()) {
				$prop = $ob['VALUE_ENUM'];
				$val=$ob['VALUE'];
				$name=$ob['NAME'];
				$code=$ob['CODE'];
				if($code==='NEXT_VERIFICATION_DATE'){
					$date=$val;
					$array['date']=$date;
					
				}
				if($code==='RESPONSIBLE'){
					$resp=$val;
					$array['resp']=$resp;
					
				}
 				if($code==='FACTORY_NUMBER'){
					$factory=$val;
					$array['factory']=$factory;

				}

			}
			if((strtotime($date)<=strtotime($upcoming_date))&&($date!="")){
				$array_prop[]=$array;
			}
		};
		// Рассылка сообщений выбранным пользователям
		foreach($array_prop as $key=>$value){
			$User_ID=$value['prop'];
			$DATE=$value['date'];
			$MEASURING_ID=$value['measuring_id'];
			$MEASURING_NAME=$value['measuring_name'];
            $FACTORY_NUMBER=$value['factory'];
			// Получаем нужные свойства ответственных лиц
			$rsUser = CUser::GetByID($User_ID);
			$arUser = $rsUser->Fetch();
			$NAME=$arUser['NAME'];
			$LAST_NAME=$arUser['LAST_NAME'];
			$EMAIL=$arUser['EMAIL'];
			//формируем сообщение
			$arEventFields = array(
				"USER_ID" =>  $User_ID,
				"VERIFICATION_DATE"  => $DATE,
				"MEASURING_INSTRUMENT_NAME"  => $MEASURING_NAME,
				"MEASURING_INSTRUMENT_ID"  => $MEASURING_ID,
				"FACTORY_NUMBER" =>$FACTORY_NUMBER,
				"NAME"  => $NAME,
				"LAST_NAME"  => $LAST_NAME,
				"EMAIL"  => 'uzer1874@rambler.ru',//$EMAIL,
			);
			CEvent::Send("VALIDATION_PERIOD_ENDS", "s1", $arEventFields);
		}
	}
    return "ValidationPeriodEnds()";
}
