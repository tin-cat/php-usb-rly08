<?

	// Controlling a USB ftdi based 8-rele switch (USB-RLY08) (http://www.superrobotica.com/S310240.htm)

	// Configuration
	define("SERIAL_DEVICE", "/dev/ttyUSB0"); // Where the device is located in the linux path

	error_reporting(E_ALL);

	// Including php-serial and php-usb-rly08 classes
	include "../lib/php-serial-read-only/php_serial.class.php";
	include "classes/php-usb-rly08.class.php";
	
	// Create object
	$usbrly08 = new usbrly08(SERIAL_DEVICE);
	
	$usbrly08->init();
	
	// Set all reles
	// $usbrly08->set_all(true);
	
	// Set a rele
	// $usbrly08->set(0, true);
	
	// Set a number of reles or all reles at a time, using the current reles status or not
	/*
	$usbrly08->set_array(array(
		0 => true,
		7 => false,
		1 => true,
		6 => true
	), true);
	*/
	
	// Do a test sequence
	$usbrly08->test();
	
	echo "Current status:<br>";
	echo $usbrly08->get_visual_status();
	
	$usbrly08->disconnect();

?>