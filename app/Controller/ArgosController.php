<?php
	App::uses('AppController','Controller');
	class ArgosController extends AppController{
		var $helpers = array('Xml', 'Text','form','html','Cache','Json');
		public $components = array('RequestHandler');
		public $cacheAction = array(  //set the method(webservice) with a cached result		
			//'proto_list' => cache_time
		);
		
		public function beforeFilter() {
			
		}
		
		function index(){
			
		}	
		
		//Return a list of theme view  
		function argos_stat(){
			$this->loadModel('Argos');
			//$this->MapSelectionManager->setSource("TThemeEtude");
			//$model = new AppModel("TMapSelectionManager","TMapSelectionManager",base);	
			
			//format from request
			if(stripos($this->request->header('Accept'),"application/xml")!==false)
				$format="xml"; 
			else if(stripos($this->request->header('Accept'),"application/json")!==false)
				$format="json";	
			
			//format return from param
			if(isset($this->params['url']['format']) && $this->params['url']['format']!=""){
				$tmp_format=$this->params['url']['format'];
				if($tmp_format=="json"){
					$format="json";
				}
				else if($tmp_format=="xml"){
					$format="xml";
				}
				else if($tmp_format=="test"){
					$format="test";
				}
			}
			
			$conditions=array();
			$debug="";
			
			$m=date("m");
			$d=date("d");
			$y=date("Y");
			$today = date("Y-m-d",mktime(0,0,0,$m,$d,$y));	
			
			$result = $this->Argos->find("all",array(
					'fields'=>array("CONVERT(char(10),date,120) as date","nbArgos","nbGPS","nbPTT"),
					'limit'=>7
				)+$conditions
			);	
			
			
			$resultfinal=array('label'=>array(),'nbArgos'=>array(),'nbGPS'=>array(),'nbPTT'=>array());	
			for($i=0;$i<7;$i++){
				$exist=false;
				$today = date("Y-m-d",mktime(0,0,0,$m,$d-$i,$y));
				array_push($resultfinal['label'],$today);
				for($j=0;$j<count($result);$j++){
					if(is_array($result) && count($result)>0 && $result[$j][0]['date']==$today){
						array_push($resultfinal['nbArgos'],$result[$j]['Argos']['nbArgos']);
						array_push($resultfinal['nbGPS'],$result[$j]['Argos']['nbGPS']);
						array_push($resultfinal['nbPTT'],$result[$j]['Argos']['nbPTT']);
						$exist=true;
						break;
					}
				}
				if(!$exist){
					array_push($resultfinal['nbArgos'],0);
					array_push($resultfinal['nbGPS'],0);
					array_push($resultfinal['nbPTT'],0);
				}
			}
		
			
			
			
			
			//$this->set('date_name',$date_name);
			$this->set('model',$this->Argos);
			$this->set('result', $resultfinal);
			$this->set("debug",$debug);
			// Set response as XML
			$this->RequestHandler->respondAs($format);
			$this->viewPath .= "/".$format;
			$this->layout = $format;
			$this->layoutPath = $format;	
		}
		
	}

?>