<?php
	session_start();

	require_once '../config/config.inc.php';
	require_once '../language_files/language.inc.php';
    require_once '../lib/datetime.inc.php';
    require_once '../lib/viewutils.inc.php';
    require_once 'CCirculation.inc.php';
       
    $objMyCirculation = new CCirculation();

	$nConnection = mysql_connect($DATABASE_HOST, $DATABASE_UID, $DATABASE_PWD);
	$strParams				= 'language='.$_REQUEST['language'].'&circid='.$arrCirculationForm['nID'];
	$strEncyrptedParams		= $objURL->encryptURL($strParams);
	$strEncryptedLinkURL	= 'circulation_detail.php?key='.$strEncyrptedParams;

		$arr = array();
		$arrSlots = array();
		$data = array();
		// $query = "select * from cf_circulationform WHERE nID IN (139,138)";
			$query = "select * from cf_circulationform";
			$nResult = mysql_query($query, $nConnection);
			
			if ($nResult)
			{
				$arrCirculationForm = mysql_fetch_array($nResult);
				// print_r($arrCirculationForm);
				while (	$arrCirculationForm = mysql_fetch_array($nResult))
    			{
    				// echo $arrCirculationForm["strName"];
    				//-----------------------------------------------
					//--- get the mailing list
					//-----------------------------------------------
					$query23 = "select * from cf_mailinglist WHERE nID=".$arrCirculationForm["nMailingListId"];
					$nResult23 = mysql_query($query23, $nConnection);
					if ($nResult23)
					{
						if (mysql_num_rows($nResult23) > 0)
						{
							$arrMailingList = mysql_fetch_array($nResult23);
						}
					}
					$nMailingListID = $arrMailingList['nID'];
					
		            //-----------------------------------------------
		            //--- get the template
		            //-----------------------------------------------	            
		            $strQuery24 = "SELECT * FROM cf_formtemplate WHERE nID=".$arrMailingList["nTemplateId"];
		    		$nResult24 = mysql_query($strQuery24, $nConnection);
		    		if ($nResult24)
		    		{
		    			if (mysql_num_rows($nResult24) > 0)
		    			{
		    				$arrTemplate = mysql_fetch_array($nResult24);
		   					$strTemplateName = $arrTemplate["strName"];
		    			}
		    		}
	    			// echo $arrCirculationForm["strName"];  
	    			$arr['BDI No'] = $arrCirculationForm["strName"]; 

    				// echo $CIRCDETAIL_TEMPLATE_TYPE;
			     	// echo $strTemplateName;  
			        $arr['Type'] = $strTemplateName; 
	    
	    			$getTotDays = $objMyCirculation->getTotalDays($arrCirculationForm["nID"]); 
	    			$arr['Total Days'] = $getTotDays; 
	    

	    			$getDiv = $objMyCirculation->getDiv($arrCirculationForm["nID"]); 
	    			$arr['Division'] = $getDiv; 


		    		$getCustName = $objMyCirculation->getCustName($arrCirculationForm["nID"]); 
		    		$arr['Customer Name'] = $getCustName; 


	    			$getProcessedDays = $objMyCirculation->getProcessedDays($arrCirculationForm["nID"]);  
	    			$arr['Current Processed Days'] = $getProcessedDays;  

	    
	    			// echo $arrUsers[$arrCirculationForm["nSenderId"]]["strLastName"].", ".$arrUsers[$arrCirculationForm["nSenderId"]]["strFirstName"]." (".$arrUsers[$arrCirculationForm["nSenderId"]]["strUserId"].")";
	         
	    
	    //     		foreach ($arrHistoryData as $arrCurHistory)
	    //     		{
					// 	$check = "";
					// 	if($_REQUEST["nRevisionId"] == $arrCurHistory["nID"])
					// 		$check = "selected";
						
					// 	echo "<option value=\"".$arrCurHistory["nID"]."\" ".$check.">#".$arrCurHistory["nRevisionNumber"]." - ".convertDateFromDB($arrCurHistory["dateSending"])."</option>";
					// }
					// $arr['BDI Date'] = convertDateFromDB($arrCurHistory["dateSending"]);
				
		
	    // 			echo str_replace("\n", "<br>", $arrHistoryData[$_REQUEST["nRevisionId"]]["strAdditionalText"]);


		$query3 = "select * from cf_mailinglist WHERE nID=".$arrCirculationForm["nMailingListId"];
			$nResult3 = mysql_query($query3, $nConnection);
			if ($nResult3)
			{
				if (mysql_num_rows($nResult3) > 0)
				{
					$arrMailingList = mysql_fetch_array($nResult3);
				}
			}
			$nMailingListID = $arrMailingList['nID'];

			// echo "<script>alert('$nMailingListID');</script>";

            $strQuery4 = "SELECT * FROM cf_formslot WHERE nTemplateID=".$arrMailingList["nTemplateId"]."  ORDER BY nSlotNumber ASC";
    		$nResult4 = mysql_query($strQuery4, $nConnection);
    		if ($nResult4)
    		{
    			if (mysql_num_rows($nResult4) > 0)
    			{
    				while (	$arrRow11 = mysql_fetch_array($nResult4))
    				{
    					$arrSlots[] = $arrRow11;
    				}
    			}
    		}


    		if ($_REQUEST["nRevisionId"] == "")
    		{
    			//-----------------------------------------------
				//--- get history (all revisions)
				//-----------------------------------------------
				$arrHistoryData = array();
				$nMaxRevisionId = 0;
				$strQuery55 = "SELECT MAX(nID) FROM cf_circulationhistory WHERE nCirculationFormId=".$arrCirculationForm["nMailingListId"];
				$nResult55 = mysql_query($strQuery55, $nConnection);
				if ($nResult55)
	    		{
	    			if (mysql_num_rows($nResult55) > 0)
	    			{
	    				$arrRow55 = mysql_fetch_array($nResult55);
	    				$_REQUEST["nRevisionId"] = $arrRow55[0];
	    			}
	    		}
    		}

    		//-----------------------------------------------
            //--- get the field values
            //-----------------------------------------------	
			            
            $arrValues = array();
            $strQuery44 = "SELECT * FROM cf_fieldvalue WHERE nFormId=".$arrCirculationForm["nID"];

             // echo $strQuery44;

    		$nResult44 = mysql_query($strQuery44, $nConnection);
    		if ($nResult44)
    		{
    			if (mysql_num_rows($nResult44) > 0)
    			{
    				while (	$arrRow44 = mysql_fetch_array($nResult44))
    				{
    					$arrValues[$arrRow44["nInputFieldId"]."_".$arrRow44["nSlotId"]] = $arrRow44;
    				}
    			}
    		}



    		// print_r($arrSlots);

    		foreach ($arrSlots as $arrSlot)
				{
					$strQuery5 = "SELECT * FROM cf_inputfield INNER JOIN cf_slottofield ON cf_inputfield.nID = cf_slottofield.nFieldId WHERE cf_slottofield.nSlotId = ".$arrSlot["nID"]."  ORDER BY cf_slottofield.nPosition ASC";
									$nResult5 = mysql_query($strQuery5, $nConnection) or die ($strQuery5."<br>".mysql_error());
                   					if ($nResult5)
				                  	{
            			       			if (mysql_num_rows($nResult5) > 0)
                   						{
											$nRunningCounter = 1;
			    		                  	while (	$arrRow6 = mysql_fetch_array($nResult5))
            			       				{
            			       				// echo "<pre>";	
            			       				// print_r($arrRow6);											
            			       				// echo "</pre>";		
												if ($arrRow6["nType"] == 1)
												{

													// echo $arrRow6["nFieldId"].'*****'.$arrSlot["nID"].'####';
													// echo 'RRRRR'.$arrValues[$arrRow6["nFieldId"]."_".$arrSlot["nID"]]["strFieldValue"].'PPPPP';


													if ($arrValues[$arrRow6["nFieldId"]."_".$arrSlot["nID"]]["strFieldValue"]!='')
													{
														$arrValue = split('rrrrr',$arrValues[$arrRow6["nFieldId"]."_".$arrSlot["nID"]]["strFieldValue"]);
														
														$arr[$arrRow6["strName"]] = $arrValue[0];
													}
													
												}
												else if ($arrRow6["nType"] == 3)
												{
													if ($arrValues[$arrRow6["nFieldId"]."_".$arrSlot["nID"]]["strFieldValue"]!='')
													{
														$arrValue = split('xx',$arrValues[$arrRow6["nFieldId"]."_".$arrSlot["nID"]]["strFieldValue"]);								
														$nNumGroup 	= $arrValue[1];														
														$arrValue1 = split('rrrrr',$arrValue[2]);														
														$strMyValue	= $arrValue1[0];
													}

													$arr[$arrRow6["strName"]] = $strMyValue;
													
												}
												else if ($arrRow6["nType"] == 4)
												{
													if ($arrValues[$arrRow6["nFieldId"]."_".$arrSlot["nID"]]["strFieldValue"]!='')
													{
														$arrValue = split('xx',$arrValues[$arrRow6["nFieldId"]."_".$arrSlot["nID"]]["strFieldValue"]);
														$nDateGroup 	= $arrValue[1];
														$arrValue2 = split('rrrrr',$arrValue[2]);
														$strMyValue 	= $arrValue2[0];
													}
													
													// $output = replaceLinks($strMyValue); 
													// if ($arrRow6['strBgColor'] != "") 
													// {
													// 	$output = '<span style="background-color: #'.$arrRow6['strBgColor'].'">'.$output.'<span>';
													// }																
													// echo $output;
													$arr[$arrRow6["strName"]] = $strMyValue;
												}
																						
												
												$nRunningCounter++;
											}
											
										}
									}
							// break;	
				}
				// echo "<pre>";
				// print_r($arr);
				// echo "</pre>";

				$data[] = $arr;
				
}}

// echo "<pre>";
// print_r($data);
// echo "</pre>";

// $data = array(
//     array( 'item' => 'Server', 'cost' => 10000, 'approved by' => 'Joe'),
//     array( 'item' => 'Mt Dew', 'cost' => 1.25, 'approved by' => 'John')
//     array( 'item' => 'IntelliJ IDEA', 'cost' => 500, 'approved by' => 'James'),
// );

outputCsv('abc.csv', $data);


function outputCsv($fileName, $assocDataArray)
{
    ob_clean();
    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Cache-Control: private', false);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=' . $fileName);    
    if(isset($assocDataArray['0'])){
        $fp = fopen('php://output', 'w');
        fputcsv($fp, array_keys($assocDataArray['0']));
        foreach($assocDataArray AS $values){
            fputcsv($fp, $values);
        }
        fclose($fp);
    }
    ob_flush();
}
			
?>
