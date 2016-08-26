<?

	// This script simulates the presence of people by switching on and off lights (or wathever is plugged onto the usb-rly08 device) in a somewhat random way
	// It must be called each minute by a cron job in order to work

	// Configuration
	define("SERIAL_DEVICE", "/dev/ttyUSB0"); // Where the device is located in the linux path
	
	// The rele numbers to perform the simulation
	$reles_to_simulate = array(
		0
	);
	
	// Configure percentage to determine randomness. Set to zero for never to happen.
	$percentage_chance_to_power_off_if_on = 30;
	$percentage_chance_to_power_on_if_off = 3;
	$percentage_chance_to_do_fast_movement_if_off = 5;
	
	// Configure days of week to do simulation, where 0 is sunday, 1 monday, 2 tuesday, 3 wednesday, 4 thursday, 5 friday and 6 saturday
	$dows = array(0, 1, 2, 3, 4, 5, 6);
	
	// Configure time ranges to do simulation. In 00:00 24 hour format. Always specify two digits for hour and two more for minute. Array of arrays containing pairs of from-to hours. First one always have to be earlier than second one, example: array("05:00", "09:00"). Set array("00:00", "23:59") to simulate during the whole day
	$time_ranges = array(
		// array("05:10", "08:45"),
		// array("00:00", "03:00"),
		array("00:00", "23:59")
	);
	
	// The timezone to work on (see available timezones here: http://es2.php.net/manual/es/timezones.php)
	date_default_timezone_set("Europe/Madrid");
	
	
	error_reporting(E_ALL);
	set_time_limit(58);
	
	header("content-type: text/plain");
	
	echo "Simulating people ...\n";
	
	// Including php-serial and php-usb-rly08 classes
	include "../lib/php-serial-read-only/php_serial.class.php";
	include "classes/php-usb-rly08.class.php";
	
	// Create object
	$usbrly08 = new usbrly08(SERIAL_DEVICE);
	
	$usbrly08->init();
	
	// Check day of week
	if(is_array($dows))
		if(!in_array(date("w"), $dows))
		{
			echo "Not one of the days of week for simulation, ending.\n";
			$usbrly08->disconnect();
			die;
		}
	
	// Check time range
	if(is_array($time_ranges))
	{
		$is_in_time = false;
		
		$now = date("Hi");
		
		foreach($time_ranges as $time_range)
		{
			list($from, $to) = $time_range;
			$from = str_replace(":", "", $from);
			$to = str_replace(":", "", $to);
			
			if($now >= $from && $now <= $to)
				$is_in_time = true;
		}
		
		if(!$is_in_time)
		{
			$usbrly08->set_all(false);
			echo "Not time for simulation now.\n";
			$usbrly08->disconnect();
			die;
		}
	}
	
	$current_status = $usbrly08->status_2_array($usbrly08->get_status());
	
	srand(make_seed());
	
	$is_movement = false;
	foreach($reles_to_simulate as $rele_number)
	{
		// If this rele is currently on
		if($current_status[$rele_number])
		{
			// Turn off?
			$lucky = rand(0, 100);
			if($lucky < $percentage_chance_to_power_off_if_on)
			{
				$is_movement = true;
				usleep(rand(0, 20000000));
				$usbrly08->set($rele_number, false);
				echo "Rele ".$rele_number." turned off.\n";
			}
		}
		// If this rele is currently off
		else
		{
			// Fast movement?
			$lucky = rand(0, 100);
			if($lucky < $percentage_chance_to_do_fast_movement_if_off)
			{
				$number_of_movements = rand(1, 3);
				for($i=0; $i<$number_of_movements; $i++)
				{
					usleep(rand(0, 5000000));
					$usbrly08->set($rele_number, true);
					usleep(rand(0, 5000000));
					$usbrly08->set($rele_number, false);
				}
				echo "Rele ".$rele_number." has got ".$number_of_movements." random movements.\n";
			}
			
			// Turn on?
			$lucky = rand(0, 100);
			if($lucky < $percentage_chance_to_power_on_if_off)
			{
				$is_movement = true;
				usleep(rand(0, 20000000));
				$usbrly08->set($rele_number, true);
				echo "Rele ".$rele_number." turned on.\n";
			}
		}
	}
	
	if(!$is_movement)
		echo "No movement for now.\n";
	
	echo
		"Current reles status: ".
		$usbrly08->get_visual_status(false, true).
		"\n";
	
	$usbrly08->disconnect();
	
	function make_seed()
	{
		list($usec, $sec) = explode(' ', microtime());
		return (float) $sec + ((float) $usec * 100000);
	}

?>