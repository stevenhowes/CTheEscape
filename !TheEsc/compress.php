<?php

function map_compress($data)
{
	$base64 = base64_encode($data);
	$newstring = "";

	$array = str_split($base64);
	$array[] = ".";

	$consec = 1;

	for($i = 0; $i < strlen($base64);$i++)
	{
		if(($array[$i] != $array[$i+1]) || ($consec == 255))
		{
			if($consec > 3)
				$newstring .= ">" . chr($consec)  . $array[$i];
			else
				$newstring .= str_repeat($array[$i],$consec);
			$consec = 1;
		}else{
			$consec++;
		}
	}
	return $newstring;
}

function map_decompress($data)
{
	$newstring = "";
	$array = str_split($data);
	$inrep = 0;
	$repcount = "";
	for($i = 0; $i < strlen($data);$i++)
	{
		if(($array[$i] == ">") && ($inrep == 0))
			$inrep = 1;
		elseif($inrep == 1)
		{
			$inrep = 0;
			$repcount .= $array[$i];
		}
		else
		{
			if($repcount != "")
			{
				$newstring .= str_repeat($array[$i],intval(ord($repcount)));
				$repcount = "";
			}
			else
			{
				$newstring .= $array[$i];
			}
		}
	}
	return base64_decode($newstring);
}

$mapdata = file_get_contents("S:\\RiscOSDev\\rpcemu-win32-0.9.3-bundle-371-issue-1\\RPCEmu - 371\\hostfs\\Dev\\!TheEsc\\m2_map,ffd");

echo "In Raw  " . md5($mapdata). " " . strlen($mapdata). "\n";

$compressedstring = map_compress($mapdata);
echo "Cmprssd " . md5($compressedstring). " " . strlen($compressedstring). "\n";

$decompressedstring = map_decompress($compressedstring);
echo "Out Raw " .md5($decompressedstring). " " . strlen($decompressedstring). "\n";

?>