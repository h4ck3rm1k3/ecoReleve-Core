<?php
App::import('Lib', 'FPDF/mctable');
App::uses('CartoModel', 'Model');
class MapSelectionManager extends CartoModel {
	//public $useDbConfig = 'mycoflore';
	public $useTable = 'TMapSelectionManager';
	//public $primaryKey = 'TSta_PK_ID';
	
	//creation of gpx and pdf and csv from export
	public function export_save($stations,$file_name,$table_name,$filters){
		//gpx header
		$gpx='<?xml version="1.0" encoding="UTF-8" standalone="no" ?>
<gpx xmlns="http://www.topografix.com/GPX/1/1" creator="byHand" version="1.1"
   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
   xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd">';
   
		//pdf creation and header
		$keyarr=array_keys($stations[0][0]);
		$nbstation=count($stations);
		$l=count($keyarr);
		//print_r($keyarr);
		$pdf = new  PDF_MC_Table('L','mm','A4'/*array($l*2,$l)*/);
		/*$pdf->SetAuthor('Hyperion - Ben Hermans');
		$pdf->SetCreator('CygnusEd 4.21 & Fpdf');
		$pdf->SetTitle('Amiwest AmigaOS 4 Presentation');
		$pdf->SetSubject('Remix by bIgdAn');*/
		$cdate=date('l jS \of F Y h:i:s A');
		$pdf->SetTitle("PDF export of $table_name,from $cdate");
		$pdf->AddPage();
		//pdf title
		$fontsize=14;		
		//$table_name="'Table name'";
		$pdf->SetFont('Arial','B',$fontsize);		
		//$pdf->Cell(0,10,"PDF export of $table_name",0,1,'C');
		//$pdf->Cell(0,10,"from $cdate",0,1,'C');
		//$pdf->Cell(0,10,"",0,1);
		$fontsize=11;
		//$filters="CXD<XDEttttjhgkjhgyfuzeifgysodfmlkhttttttttgfthykiuyoliliuoloyulyopmljiuiyjytttttttttttttttttttttte";
		$pdf->SetWidths(array(0));
		$pdf->SetFont('Arial','B',$fontsize);
		$pdf->Cell(20,10,"Filter(s) : ",0,0,'');
		$pdf->SetFont('Arial','',$fontsize);
		if(strlen($filters)>90)
			$pdf->Row(array("$filters"),false);	
		else
			$pdf->Cell(20,10,"$filters",0,0,'');			
		$pdf->Cell(0,10,"",0,1);
		$pdf->SetFont('Arial','B',$fontsize);
		$pdf->Cell(37,10,"Number of station: ",0,0,'');
		$pdf->SetFont('Arial','',$fontsize);
		$pdf->Cell(20,10,"$nbstation",0,0,'');
		$pdf->Cell(0,10,"",0,1);
		$cellwidth=25;	
		$limitnbcell=7;		
		$widtharr=array();
		$cellallign="C";
		$allignarr=array();
		
		$pdf->SetFont('Arial','B',$fontsize);
		$fontsize=13;
		$limitfield="";
		if($l>$limitnbcell+1)
			$limitfield="(fields limited to $limitnbcell for pdf export)";
		$table_title_width=$l<=6?$cellwidth*($l-1):0;
		$pdf->Cell($table_title_width,10,"Export Table $limitfield: ",0,1,'C');
		
		
		//ini val
		$error=false;
		$i=0;
		$date="";
		$ele="";
		$lat="";
		$lon="";
		$name="";
		
		// pdf column names
		$fontsize=11;
		$inputfields=array("","Date de saisie","Vu","Entendu","Perdu","Mort","Repro","No Check");
		$inputvalblank=array("","","","","","","","");
		$fun=function($item){
			 return preg_replace(array("/id(.)+@/","/@Station/"), '', $item);
			$itemsplit=explode("@",$item);
			if(count($itemsplit)>1){				
				$itemsplitsplit=preg_split("/id(.)+@/", $itemsplit[0]);
				if(count($itemsplitsplit)>1)
					return $itemsplit[1];
			}
				
		};
		$keyarr=array_map($fun,$keyarr);
		$exportfields=array_slice($keyarr,1,$limitnbcell);
		$fields = array_merge($exportfields,$inputfields);
		$pdf->SetFont('Arial','B',$fontsize);
		foreach($fields as $name) {
			if($name=='LAT' || $name=='LON' || $name=='Mort' || $name=='Repro')
				$widtharr=array_merge($widtharr, array(17));
			else if($name=='Vu')
				$widtharr=array_merge($widtharr, array(10));
			else if($name=='Perdu' || $name=='No Check')
				$widtharr=array_merge($widtharr, array(15));
			else if($name=='Entendu')
				$widtharr=array_merge($widtharr, array(20));
			else if($name=='Date de saisie')
				$widtharr=array_merge($widtharr, array(30));
			else if($name=='')
				$widtharr=array_merge($widtharr, array(2));		
			else
				$widtharr=array_merge($widtharr, array($cellwidth));
			$allignarr=array_merge($allignarr, array($cellallign));	
		}
		$pdf->SetWidths($widtharr);
		$pdf->SetAligns($allignarr);
		
		
		$pdf->Row($fields);
		
		//csv open
		if(stristr($_SERVER["SERVER_SOFTWARE"], 'apache')){
			$fpcsv = fopen("csv/$file_name.csv", 'w');			
			//fwrite($fpcsv, print_r($gpx,true)); 
		}
		else{
			$fpcsv = fopen("app/webroot/csv/$file_name.csv", 'w');			
			//fwrite($fpcsv, print_r($gpx,true)); 
		}
		//csv header
		fputcsv($fpcsv,array_slice($keyarr,1),';');
				
		$fontsize=11;
		$pdf->SetFont('Times','',$fontsize);				
		//for each row	
		foreach($stations as $rmodel){
			$j=0;
			//pdf part
			$exportval=array_slice($rmodel[0],1,$limitnbcell);
			$fields = array_merge($exportval,$inputvalblank);
			$pdf->Row($fields);			
			
			//csv part
			fputcsv($fpcsv,array_slice($rmodel[0],1),';');			
			
			//gpx part
			if(!isset($rmodel[0]['LAT']) || !isset($rmodel[0]['LON'])
			&& (!isset($rmodel[0]['Date']) || !isset($rmodel[0]['DATE'])) 
			&& (!isset($rmodel[0]['Station']) || !isset($rmodel[0]['Site_name']))){
				$error=true;
			}
			else{
				$lat=$rmodel[0]['LAT'];
				$lon=$rmodel[0]['LON'];
				if(isset($rmodel[0]['ELE']))
					$ele=$rmodel[0]['ELE'];
				else if(isset($rmodel[0]['ELE']))
					$ele="";
				if(isset($rmodel[0]['Date']))
					$date=$rmodel[0]['Date'];
				else if(isset($rmodel[0]['DATE']))
					$date=$rmodel[0]['DATE'];
				$date=str_replace(" ","T",$date)."Z";		
				if(isset($rmodel[0]['Station']))
					$name=$rmodel[0]['Station'];
				else if(isset($rmodel[0]['Site_name']))	
					$name=$rmodel[0]['Site_name'];
				$sym="";
				
				$gpx.="\n<wpt lat='$lat' lon='$lon'>\n";
				$gpx.="<ele>$ele</ele>\n";
				$gpx.="<time>$date</time>\n";
				$gpx.="<desc></desc>\n";
				$gpx.="<name>$name</name>\n";
				$gpx.="<sym>Flag, Blue</sym>\n";			
				$gpx.="</wpt>\n";
			}	
			$i++;
		}
		
		$gpx.='</gpx>';
		if($error)
			$gpx="error fields need 'LAT' 'LON' 'ELE' 'Date' 'Station or Site_name'";
		
		if(stristr($_SERVER["SERVER_SOFTWARE"], 'apache')){
			$fp = fopen("gps/$file_name.gpx", 'w');			
			fwrite($fp, print_r($gpx,true)); //gpx write
			$pdf->Output("pdf/$file_name.pdf",'F');//pdf write
		}
		else{
			$fp = fopen("app/webroot/gps/$file_name.gpx", 'w');			
			fwrite($fp, print_r($gpx,true)); //gpx write
			$pdf->Output("app/webroot/pdf/$file_name.pdf",'F');//pdf write
		}
		
	}
	
}

?>