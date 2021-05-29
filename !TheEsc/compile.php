<?php
  $script = file_get_contents("m2_txt");

  $inevent = -1;

  $events = array();
  $eventactions = array();

  foreach(preg_split("/((\r?\n)|(\r\n?))/", $script) as $line)
  {
    $line = trim($line);

    if(strlen($line) == 0)
    {
      $inevent = -1;
      continue;
    }

    $split = preg_split("/\(|\)/",$line);

    if(count($split) != 3)
    {
      echo "  Syntax error: " . $line . "\n";
      continue;
    }

    switch ($split[0])
    {
      case "AddEvent":
        $csv = str_getcsv($split[1]);
        $events[$csv[0]] = array("Name"=>$csv[1],"Triggered"=>$csv[2],"RearmDelay"=>$csv[3],"NextRearm"=>$csv[4]);
        break;
      case "Event":
        $csv = str_getcsv($split[1]);
        $inevent = -1;
        foreach($events as $eventid=>$event)
        {
          if($event['Name'] == $csv[0])
            $inevent = $eventid;
        }
        if($inevent < 0)
          echo "  Unknown event: " . $csv[0] . "\n";
        break;
      case "AreaName":
        if($inevent < 0)
        {
          echo "  Invalid outside event\n";
        }
        else
        {
          $csv = str_getcsv($split[1]);
          $eventactions[] = array("Event"=>$inevent,"Action"=>1,"ActionValue"=>$csv[0],"ActionTarget"=>-1);
        }
        break;
      case "Sound":
        if($inevent < 0)
        {
          echo "  Invalid outside event\n";
        }
        else
        {
          $csv = str_getcsv($split[1]);
          $eventactions[] = array("Event"=>$inevent,"Action"=>2,"ActionValue"=>$csv[1],"ActionTarget"=>$csv[0]);
        }
        break;
      case "SetTile":
        if($inevent < 0)
        {
          echo "  Invalid outside event\n";
        }
        else
        {
          $csv = str_getcsv($split[1]);
          $eventactions[] = array("Event"=>$inevent,"Action"=>0,"ActionValue"=>$csv[1],"ActionTarget"=>$csv[0]);
        }
        break;
      case "ReArm":
        if($inevent < 0)
        {
          echo "  Invalid outside event\n";
        }
        else
        {
          $csv = str_getcsv($split[1]);

          $target = -1;
          foreach($events as $eventid=>$event)
          {
            if($event['Name'] == $csv[0])
              $target = $eventid;
          }

          if($target < 0)
            echo "  Unknown target: " . $csv[0] . "\n";
          else
            $eventactions[] = array("Event"=>$inevent,"Action"=>3,"ActionValue"=>255,"ActionTarget"=>$target);
        }
        break;
      default:
        echo "  Unknown command: " . $split[0] . "\n";
    }
  }

  $fp = fopen('m2_evt,ffd', 'w');
  foreach($events as $event)
  {
    fwrite($fp, $event['Name']);
    $pad = 16 - strlen($event['Name']);
    while($pad > 0)
    {
      $pad --;
      fwrite($fp, "\0");
    }

    if($event['Triggered'] == 1)
      fwrite($fp, "\1");
    else
       fwrite($fp, "\0");
    $pad = 3;
    while($pad > 0)
    {
      $pad --;
      fwrite($fp, "\0");
    }

    // TODO: ReArm stuff
    $pad = 8;
    while($pad > 0)
    {
      $pad --;
      fwrite($fp, chr(255));
    }
  }

  $fp = fopen('m2_evact,ffd', 'w');
  foreach($eventactions as $eventaction)
  {
    fwrite($fp, pack('V', $eventaction['Event'])); // Event

    fwrite($fp, chr($eventaction['Action']));  // Action
    fwrite($fp, chr($eventaction['ActionValue']));  // ActionValue
    
    fwrite($fp, chr(0));    // PAD
    fwrite($fp, chr(0));    // PAD

    fwrite($fp, pack('V', $eventaction['ActionTarget'])); // Event
  }

  fclose($fp);
?>