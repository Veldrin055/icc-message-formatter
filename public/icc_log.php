<?php 
	ini_set("display_errors",0);
	date_default_timezone_set('Australia/Melbourne');
	
	$file = 'monitor.buf'; //$_REQUEST['lf']; 
	$allEvents = array();
	$tmpEvents = array();
	
	if(!$file)
		exit;
	
	$recstoshow = @$_POST['recstoshow'];
	if(!$recstoshow)
		$recstoshow = 100;
	
	$error = '';		
	$path = "./";
	$file2 = $path . $file;
	if(!file_exists($file2))
		$error = "File not found...";
	else 
	{
		$fp = fopen($file2, 'r');
		if(!$fp)
			$error = "File doesn't have read permissions......";
		fclose($fp);
	}
	
	$data = array();
	$outPut = array_reverse(groupEventAlert(loadBuffFile($file2)));
	//$outPut1 = array_reverse(groupEventInfo(loadBuffFile($file2)));
	
	$curPosition = 'start';
	$outPutData = '';
	foreach($outPut as $event => $eventMethod)
	{
		foreach($eventMethod as $method => $methodData)
		{			
			switch($method)
			{
				case 'start':
					if($curPosition == 'start')
					{
						$outPutData.='<div id="ev_" style="font-weight:bold" class="M_Log">'."\r\n".'<div id="ev_head">
							   <span class="SUF">'.ucwords($method).' '.getAgencies($methodData['time']).'</span>
							  </div>'."\r\n".
							  '<div id="ev_body">'."\r\n".
							  '	<span class="E_M">'.$methodData['data'].' </span>'.$methodData['brig']."\r\n";
						
						$curPosition = 'middle';
					}
					else
					{
						$outPutData.='</div>'."\r\n".'</div>'."\r\n".'<div id="ev_" style="font-weight:bold" class="M_Log">'."\r\n".'
							<div id="ev_head">
							   <span class="SUF">'.ucwords($method).' '.getAgencies($methodData['time']).'</span>
							  </div>'."\r\n".
							  '<div id="ev_body">'."\r\n".
							  '	<span class="E_M">'.$methodData['data'].' </span>'.$methodData['brig']."\r\n";
					}
					//echo $methodData['data'].' '. $methodData['brig'].'<br>';	
					break;
				case 'further':
					for($i=0;$i<count($methodData);$i++)
					{						
						$outPutData.='<br /><span class="F_B">'.ucwords($method).' '.$methodData[$i]['time'].'</span><span class="E_M">'.$methodData[$i]['data'].'</span>';
					}
					break;
				case 'update':
					for($i=0;$i<count($methodData);$i++)
					{
						$outPutData.='<br />
							   <span class="F_B">'.ucwords($method).' '.$methodData[$i]['time'].'</span> - 
							   '.$methodData[$i]['brig']."\r\n";
					}
					break;
			}
			//.$methodData.'<br />';
		}
		//print_r($eventMethod['start']);
	}
	echo '<div style="text-align:left; float:left;font-size:7px; font-weight:bold"><span style="background:#0C0; width: 200px; padding-right: 5px; padding-left:5px">0 > 4:59min</span> | <span style="background:#FF0; color:#000; width: 200px; padding-right: 5px; padding-left:5px">5:00min > 14:59min</span> | <span style="background:#F00; width: 200px; padding-right: 5px; padding-left:5px">> 15:00min</span></div><div style="text-align:right; width:100px; float:right"><span class="SUF" style="font-weight:bold">'.date("H:i:s", time()).'</span></div><div style="clear:both"></div>';
	echo $outPutData;
	die;
  
function parseMsg($msg)
{
	$msg_chunk = explode(' ',$msg);
	$msg_type = substr($msg_chunk[0],0,2);
	$msg_start = substr($msg_chunk[0],2,strlen($msg_chunk[0])-2);
	switch($msg_type)
	{
		case '@@':	
	}
}

function loadBuffFile($fileName)//Invert File Contents
{
	global $recstoshow;
	$tmp_data = array();
	$error = '';		
	$path = "./";
	$file = $path . $fileName;
	if(!file_exists($file))
		$error = "File not found...";
	else 
	{
		$fp = fopen($file, 'r');
		if(!$fp)
			$error = "File doesn't have read permissions......";
		fclose($fp);
	}
	
	$lineNo = 0;
	$trimmed = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$start = false;
	foreach($trimmed as $line)
	{
		if(!$start)
		{
			if(trim($line) == '[HTML BUFFER]')
			{
				$start = 1;
				continue;
			}
			else
				continue;
		}
		if($lineNo <= $recstoshow -1)
		{
			$rq = explode('REQUIRED',$line);
			//Skipping double REQUIRED
			if(count($rq)<=2)
				$tmp_data[] = $line;
			
			$lineNo++;
		}
	}
	
	$rData = array();
	
	for($i=count($tmp_data);$i>=0;$i--)
	{
		if(!empty($tmp_data[$i]))
			$rData[] = $tmp_data[$i];
	}
	
	return $rData;
}

function groupEventAlert($data)
{
	global $allEvents, $tmpEvents;
	
	$lastRowData = '';
	$hbEvents = array();
	$lastUPD = '';
	$lastEvt = '';
	$evtime = array();
	
	foreach($data as $line)
	{
		$row = explode(' ', $line, 7);
		$rc = '';
		
		$type = substr($row[6], 0, 2);
		$eventCode = explode(' ',$row[6]);
		
		$eventTime = null;
		
		preg_match('/(F[\d]{9})/i', $line, $match_fire);
		
		if($match_fire) {
			$eventTime = $match_fire[0];
		}
		
		if($type == "@@")
		{	
			$ev = $row[6];
			$eventInfo = getEventData($ev,'info');
			$eventBrig = getEventData($ev,'brig');
			$eventAgency = getEventData($ev, 'agency');
			
			//echo $eventAgency;
			//print_r($row);
			//die;
			if(substr($eventTime,0,1) == 'F' && !in_array($eventCode[2],$hbEvents))
			{
				$lastUPD = '';
				$st = $row[1];
				$respTime = @getMyTimeDiff(date('H:i:s',time()),$st);
			
				if($respTime['h'] > 0)
					$resCode = 'UNIT_15';
				elseif($respTime['m'] >= 15)
					$resCode = 'UNIT_15';
				elseif(($respTime['m'] >= 5) || ($respTime['m'] == 14 && $respTime['s'] <= 59))
					$resCode = 'UNIT_5';
				elseif(($respTime['m'] < 5 && $respTime['s'] <= 59))
					$resCode = 'UNIT_0';
				else
					$resCode = 'UNIT_0';
				
				if(array_key_exists($eventTime,$allEvents))
				{
					$upData = explode(' ',$ev,7);
					
					if($allEvents[$eventTime]['start'] != $upData[6] && $row[6] == $ev)
					{
						$brig = '<span class="'.$resCode.'">'.$eventBrig.'</span>';
						$brigs = explode(' ', trim($eventBrig));
						foreach($brigs as $brg)
						{
							if(!empty($brg))
							{
								if(stristr($allEvents[$eventTime]['start']['brig'],$brg) == '')
								{
									preg_match('/(\s(F|AF|AFR|AFP|AFPR|AFPRS|FPR)\s)/i', " {$eventAgency} ", $isAgency);  // $data = preg_replace('/(\s(F|AF|AFR|AFP|AFPR|AFPRS|FPR)\s)/i', ' ', $data);
									$newEventAgency = '';
									
									if (!$isAgency) {
										$time = explode(' - ', $allEvents[$eventTime]['start']['time']);
										
										preg_match('/(\s(F|AF|AFR|AFP|AFPR|AFPRS|FPR)\s)/i', " {$time[2]} ", $isAgency1);
										
										$newEventAgency = $isAgency1?' - '.$time[2]:'';
									} else {
										$newEventAgency = ' - '.$eventAgency;
									}
									
									$allEvents[$eventTime]['start']['brig'] .= ' <span class="'.$resCode.'">'.$brg.'</span>';
									$allEvents[$eventTime]['start']['time'] = $row[1]. ' - <span style="background-color: #f00;">'.$eventTime.'</span>'. $newEventAgency; // ' <span class="'.$resCode.'">'.$brg.'</span>';
									
									$eventRow = $allEvents[$eventTime];
									unset($allEvents[$eventTime]);
									$allEvents[$eventTime] = $eventRow;
								}
							}
						}
					}
					else
					{
						$d = @explode(' ', $allEvents[$eventTime]['update'][0]['data'],8);
						$e = @explode(' ', $eventInfo,8);
						
						if(count($allEvents[$eventTime]['update'])>0)
						{
							for($i=0;$i<count($allEvents[$eventTime]['update']);$i++)
							{
								if($e[7] == $d[7])
								{
									$br = strpos($allEvents[$eventTime]['update'][0]['brig'],$eventBrig);
									
									if($br !== false)
									{}
									else
									{
										$allEvents[$eventTime]['update'][$i]['brig'] .= '<span class="'.$resCode.'">'.$eventBrig.'</span>';
										$allEvents[$eventTime]['update'][$i]['time'] = $row[1]. ' - '.$eventTime;
										queueEvent($eventCode[2]);
									}
								}
							}
						}
					}
				} else {
					if(getEventData($ev,'location')) {
						if (!$eventAgency) {
							$allEvents[$eventTime]['start'] = array('data'=>str_replace('@@ALERT','',$eventInfo),'brig' => '<span class="'.$resCode.'">'.$eventBrig.'</span>', 'time' => $row[1]. ' - <span style="background-color: #f00;">'.$eventTime.'</span> - blank');
						} else {
							$allEvents[$eventTime]['start'] = array('data'=>str_replace('@@ALERT','',$eventInfo),'brig' => '<span class="'.$resCode.'">'.$eventBrig.'</span>', 'time' => $row[1]. ' - <span style="background-color: #f00;">'.$eventTime.'</span> - '.$eventAgency);
						}
					}
					else
						$allEvents[$eventTime]['start'] = array('data'=>$ev,'brig' => '', 'time' => $row[1]. ' - <span style="background-color: #f00;">'.$eventTime.'</span> - '.$eventAgency);
				}
					
			} elseif (substr($eventTime,0,1) == 'F' || in_array($eventCode[2],$hbEvents)) {
				$upData = explode(' ',$ev,7);
				if($lastUPD != '')
					$lastUPD = $upData[6];
				
				$st = $row[1];
				$respTime = @getMyTimeDiff(date('H:i:s',time()),$st);
			
				if($respTime['h'] > 0)
					$resCode = 'UNIT_15';
				elseif($respTime['m'] >= 15)
					$resCode = 'UNIT_15';
				elseif(($respTime['m'] >= 5) || ($respTime['m'] == 14 && $respTime['s'] <= 59))
					$resCode = 'UNIT_5';
				elseif(($respTime['m'] < 5 && $respTime['s'] <= 59))
					$resCode = 'UNIT_0';
				else
					$resCode = 'UNIT_0';
				
				if(is_array($allEvents[$eventCode[2]]['start']))
				{
					if($lastUPD == $upData[6] || $row[6] == $ev)
					{	
						$d = @explode(' ', $allEvents[$eventCode[2]]['update'][0]['data'],8);
						$e = @explode(' ', $eventInfo,8);
						$allEvents[$eventCode[2]]['update'][] = array('data'=>str_replace('@@','',$eventInfo),'brig' => '<span class="'.$resCode.'">'.$eventBrig.'</span>', 'time' => $row[1]. ' - <span style="background-color: #f00;">'.$eventTime.'</span> - '.$eventAgency);
					}
					else
					{
						$allEvents[$eventCode[2]]['update'][] = array('data'=>str_replace('@@','',$eventInfo),'brig' => '<span class="'.$resCode.'">'.$eventBrig.'</span>', 'time' => $row[1]. ' - <span style="background-color: #f00;">'.$eventTime.'</span> - '.$eventAgency);
						
					}
					queueEvent($eventCode[2]);
				}
				else
				{
					//Using Update as Start Event
					if(getEventData($ev,'location'))
						$allEvents[$eventCode[2]]['start'] = array('data'=>str_replace('@@','',$eventInfo),'brig' => '<span class="'.$resCode.'">'.$eventBrig.'</span>', 'time' => $row[1]. ' - <span style="background-color: #f00;">'.$eventTime.'</span> - '.$eventAgency);
					else
						$allEvents[$eventCode[2]]['start'] = array('data'=>$ev,'brig' => '', 'time' => $row[1]. ' - <span style="background-color: #f00;">'.$eventTime.'</span> - '.$eventAgency);//$eventBrig);
				}
			}
		}
		elseif($type == "Hb")
		{
			if(isset($allEvents[$eventCode[2]]))
			{
				$ev = $allEvents[$eventCode[2]]['start'];
				
				$isCancel = (stristr($row[6],'CANCEL') != '' OR stristr($row[6],'NOT REQUIRED') != '' OR stristr($row[6],'STOP') != '')?true:false;
				
				$hbData = getEventData($row[6],'hbinfo');
				$hbData = str_replace('HbRE: EVENT: '.$eventCode[2],'',str_replace('HbRE: EVENT '.$eventCode[2],'',$hbData));
				$hbBrig = getEventData($row[6],'hbbrig');
				$countDis = (array_key_exists('further',$allEvents[$eventCode[2]]))?count($allEvents[$eventCode[2]]['further']):0;
				
				$st = $row[1];
				$respTime = @getMyTimeDiff(date('H:i:s',time()),$st);
				
				$evtime[$eventCode[2]] = $row[1];
				
				if($respTime['h'] > 0)
					$resCode = 'UNIT_15';
				elseif($respTime['m'] >= 15)
					$resCode = 'UNIT_15';
				elseif(($respTime['m'] >= 5) || ($respTime['m'] == 14 && $respTime['s'] <= 59))
					$resCode = 'UNIT_5';
				elseif(($respTime['m'] < 5 && $respTime['s'] <= 59))
					$resCode = 'UNIT_0';
				
				$hBrig = 'C'.str_replace(array('[',']'),'',$hbBrig);
				
				if($isCancel)
				{
					if(array_key_exists('further',$allEvents[$eventCode[2]]))
					{
						$isFound = false;
						
						for($f=0;$f<count($allEvents[$eventCode[2]]['further']);$f++)
						{
							if($allEvents[$eventCode[2]]['further'][$f]['data'] == $hbData)
							{
								if(!$isFound)
								{
									$isFound = true;
								}
							}
						}
						
						if(!$isFound)
						{
							$allEvents[$eventCode[2]]['further'][] = array('type'=>'cancel','data'=>$hbData,'brig'=>'<span class="'.$resCode.'">'.$hbBrig.'</span>', 'time' => $row[1]);
						}
						
						queueEvent($eventCode[2]);
					}
					else
					{
						$allEvents[$eventCode[2]]['further'][] = array('type'=>'cancel','data'=>str_replace('HbRE: EVENT: '.$eventCode[2],'',str_replace('HbRE: EVENT '.$eventCode[2],'',$hbData)),'brig'=>'<span class="'.$resCode.'">'.$hbBrig.'</span>', 'time' => $row[1]);
					}
					
					$allEvents[$eventCode[2]]['start']['brig'] = str_replace($hBrig,'<span class="'.$resCode.' UNIT_CANCEL">'.$hBrig.'</span><span class="'.$resCode.'">',$allEvents[$eventCode[2]]['start']['brig'].'</span>');
					
					queueEvent($eventCode[2]);
				} else {
					if(array_key_exists('further',$allEvents[$eventCode[2]]))//count($allEvents[$eventCode[2]]['further']) > 0)
					{
						$isFound = false;
						for($f=0;$f<count($allEvents[$eventCode[2]]['further']);$f++)
						{
							if($allEvents[$eventCode[2]]['further'][$f]['data'] == $hbData)
							{
								if(!$isFound)
								{
									$isFound = true;
								}
							}
						}
						
						if(!$isFound) {
							// $allEvents[$eventCode[2]]['further'][] = array('type'=>'cancel','data'=>$hbData,'brig'=>'<span class="'.$resCode.'">'.$hbBrig.'</span>', 'time' => $row[1]. ' - <span style="background-color: #f00;">'.$eventTime.'</span> - '.$eventAgency);
							$allEvents[$eventCode[2]]['further'][] = array('type'=>'cancel','data'=>$hbData,'brig'=>'<span class="'.$resCode.'">'.$hbBrig.'</span>', 'time' => $row[1]);
						}
						else {
							$allEvents[$eventCode[2]]['further'][$countDis - 1]['brig'] .= ' '.$hbBrig;
						}
							
						queueEvent($eventCode[2]);
					}
					else
					{
						//$allEvents[$eventCode[2]]['further'][] = array('type'=>'normal','data'=>str_replace('HbRE: EVENT: '.$eventCode[2],'',str_replace('HbRE: EVENT '.$eventCode[2],'',$hbData)),'brig'=>'<span class="'.$resCode.'">'.$hbBrig.'</span>', 'time' => $row[1]. ' - <span style="background-color: #f00;">'.$eventTime.'</span> - '.$eventAgency);
						
						$allEvents[$eventCode[2]]['further'][] = array('type'=>'normal','data'=>str_replace('HbRE: EVENT: '.$eventCode[2],'',str_replace('HbRE: EVENT '.$eventCode[2],'',$hbData)),'brig'=>'<span class="'.$resCode.'">'.$hbBrig.'</span>', 'time' => $row[1]);
						queueEvent($eventCode[2]);
					}
				}
			}
		}
	}
	
	$finalEvents = array_merge($allEvents,$tmpEvents);
	return $allEvents;
	//return $finalEvents;
}

function getEventData($eventRow, $dataType)
{
	$data = '';
	$t_dat = explode(' ',$eventRow);
	//print_r($t_dat);die;
	switch($dataType)
	{
		case 'rcvr':
			$os = explode(') ',$eventRow);
			$co = count($os);
			
			if($co>2)
				$locEnd = strpos($eventRow, ') '.$os[$co-1]);
			else
				$locEnd = strpos($eventRow,') ');
			
			$brig = substr($eventRow,$locEnd+1);
			$brigEnd = strpos($brig,' [');
			$rec = substr($brig,$brigEnd+2);
			$data = substr($rec,0, strlen($rec)-1);
			break;
		case 'brig':
			$os = explode(') ',$eventRow);
			$co = count($os);
			
			if($co>2)
				$locEnd = strpos($eventRow, ') '.$os[$co-1]);
			else
				$locEnd = strpos($eventRow,') ');
				
			$rec = getEventData($eventRow,'rcvr');
			
			$unit = substr($eventRow,$locEnd+1);
			$data = substr($unit,0,strlen($unit)-(strlen($rec)+3));
			$eventId = getEventData($eventRow,'eventid');
			$agency = getEventData($eventRow, 'agency');
			
			//$data = preg_replace('/(\s(F|AF|AFR|AFP|AFPR|AFPRS|FPR)\s)/i', ' ', $data);
			//preg_match('/\s[AFPRS]{1,5}\s/i', ' '.trim($data), $matches);
			$data = preg_replace('/\s[AFPRS]{1,5}\s/i', ' ', $data);
			
			//echo $agency;
			//$data = trim(str_replace($agency, '', str_replace($eventId, '', $data)));
			$data = trim(str_replace($eventId, '', $data));
			break;
		case 'info':
			$os = explode(') ',$eventRow);
			$co = count($os);
			
			if($co>2)
				$locEnd = strpos($eventRow, ') '.$os[$co-1]);
			else
				$locEnd = strpos($eventRow,') ');
				
			$brig = substr($eventRow,$locEnd+1);
			$data = substr($eventRow,0,strlen($eventRow)-strlen($brig));
			
			break;
		case 'hbinfo':			
			if(stristr($eventRow,'[')!='')
			{
				$locStart = strpos($eventRow,' [');
				$locEnd = strpos($eventRow,']');
				$brig = substr($eventRow,$locStart+1);
				$data = substr($eventRow,0,strlen($eventRow)-strlen($brig));
			}
			else
				$data = $eventRow;
			
			break;
		case 'hbbrig':
			$locStart = strpos($eventRow,']');
			$locEnd = strpos($eventRow,' [');
			
			$data = substr($eventRow,$locEnd+1);
			
			break;
		case 'location':
			$os = explode(') ',$eventRow);
			$co = count($os);

			if($co>2)
				$locEnd = strpos($eventRow, ') '.$os[$co-1]);
			else
				$locEnd = strpos($eventRow,') ');
			
			if($locEnd !== false)
				$data = true;
			else
				$data = false;
		case 'eventid':
			preg_match('/(F[\d]{9})/i', $eventRow, $match_fire);
		
			if($match_fire) {
				$data = $match_fire[0];
			}
			
			break;
		case 'agency':
			$os = explode(') ',$eventRow);
			$co = count($os);
			
			if($co>2)
				$locEnd = strpos($eventRow, ') '.$os[$co-1]);
			else
				$locEnd = strpos($eventRow,') ');
				
			$rec = getEventData($eventRow,'rcvr');
			$unit = substr($eventRow,$locEnd+1);
			$data = substr($unit,0,strlen($unit)-(strlen($rec)+3));
			
			preg_match('/\s[AFPRS]{1,5}\s/i', ' '.trim($data), $matches);
			//echo sizeof($agency)?$agency[0]."<br>":$data."<br>";
			
			$agency = null;
			
			if (sizeof($matches)) {
				$agency = $matches[0];
			} else {
				//preg_match('/\s[AFPRS]{1,5}\s/i', ' '.trim($data).' ', $matches_new);
				//print_r($matches_new);
				//echo $eventRow."<br>";
				$agency = null; //'Not Found';
			}
			//$exp = explode(' ', trim($data));
			
			$data = $agency; //sizeof($agency)?$agency[0]:null;
			break;
	}
	
	return $data;
}

function getTimeDiff($YourFirstTime,$YourSecondTime)
{
	$time_one = new DateTime($YourFirstTime);
	$time_two = new DateTime($YourSecondTime);
	$difference = $time_one->diff($time_two);
	$h = $difference->format('%h');
	$m = $difference->format('%i');
	$s = $difference->format('%s');
	
	return array('h' => $h, 'm' => $m, 's' => $s);
}

function getMyTimeDiff($t1,$t2)
{
	$a1 = explode(":",$t1);
	$a2 = explode(":",$t2);
	$time1 = (($a1[0]*60*60)+($a1[1]*60)+($a1[2]));
	$time2 = (($a2[0]*60*60)+($a2[1]*60)+($a2[2]));
	$diff = abs($time1-$time2);
	$hours = floor($diff/(60*60));
	$mins = floor(($diff-($hours*60*60))/(60));
	$secs = floor(($diff-(($hours*60*60)+($mins*60))));
	$result = $hours.":".$mins.":".$secs;
	
	return array('h' => $hours, 'm' => $mins, 's' => $secs);
}

function move2Top($eventCode, $arrList)
{
	$my_key     = $eventCode;
	$my_value   = $arrList[$my_key];
	//$arrList	= array_merge(array($my_key => $my_value), $arrList);
	//$ar = array_reverse($arrList);
	//return array_merge(array($my_key => $my_value), array_reverse($ar));//$arrList);
	
	unset($arrList[$my_key]);
	return array_merge($arrList,array($my_key => $my_value));
}

function queueEvent($eventCode)
{
	global $allEvents, $tmpEvents;
	
	$my_key     = $eventCode;
	$my_value   = $allEvents[$my_key];
	
	$tmpEvents[$my_key] = $my_value;
	unset($allEvents[$my_key]);
	//array_merge($allEvents,$tmpEvents);
	$allEvents[$my_key] = $tmpEvents[$my_key];
	unset($tmpEvents[$my_key]);
}

/* Not Used */
function addEvent($dataArray, $eventArray, $hasLocation = false)
{
	if($hasLocation)
		$events[] = array('code' => $eventTime, 'type'=> 'start','data'=>str_replace('@@','',$eventInfo),'brig' => '<span class="'.$resCode.'">'.$eventBrig.'</span>', 'time' => $row[1]);
	else
		$events[] = array('code' => $eventTime, 'type'=>'start','data'=>$ev,'brig' => '', 'time' => $row[1]);
}

function checkExist($eventArray,$eventCode)
{
	$found = false;
	foreach($eventArray as $event)
	{
		if(in_array($eventCode, $event))
		{
			$found = true;
		}
	}
	
	return $found;
}

function getAgencies($input)
{
	if (stristr($input, ' - blank')) {
		return $input;
	}
	
	$mEx = explode(' - ', $input);
	$agency = $mEx[sizeof($mEx) - 1];
	
	$agencyMap = [
		'A' => 'AV',
		'F' => 'FIRE',
		'P' => 'POL',
		'R' => 'RESC',
		'S' => 'SES'
	];
	
	$xp = str_split(trim($agency));
	
	$out = [];
	
	if ($input) {
		foreach($xp as $ag) {
			$out[] = $agencyMap[$ag];
		}
	}
	
	return preg_replace('/\s['.trim($agency).']{1,5}/i', implode(' / ', $out), $input);
	//die;
	//return implode(' / ', $out);
}
?>