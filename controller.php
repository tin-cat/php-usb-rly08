<?

	// Controlling a USB ftdi based 8-rele switch (USB-RLY08) (http://www.superrobotica.com/S310240.htm)

	// Configuration
	define("SERIAL_DEVICE", "/dev/ttyUSB0"); // Where the device is located in the linux path
	define("AUTO_RELES_STATUS_UPDATE_INTERVAL_SECONDS", 5); // The number of seconds between each automatic rele status update. Set to false to avoid automated status retrieval. Use this only when there are other software that is accessing the device and opening/closing relays. Anyway, using other programs changing the relays at the same time could lead to some errors, like a relay don't being opened or closed as desired until the second click due to non-completely-real-time updateing of the relays status. Decrease this update interval to avoid this, or completely disable this feature by setting it to false and do not use any other programs changing the relays.

	error_reporting(E_ALL);

	// Including php-serial and php-usb-rly08 classes
	include "../lib/php-serial-read-only/php_serial.class.php";
	include "classes/php-usb-rly08.class.php";
	
	// Create object
	$usbrly08 = new usbrly08(SERIAL_DEVICE);
	
	// Command run section
	$command = (isset($_REQUEST["command"]) ? $_REQUEST["command"] : "home");
	
	switch($command)
	{
		case "home":
			home();
			break;
		
		case "ajax_get_rele_status":
			ajax_get_rele_status();
			break;
		
		case "ajax_set_rele_status":
			ajax_set_rele_status();
			break;
	}
	
	// Functions
	function out($data)
	{
		echo $data;
	}
	
	function html_header()
	{
		out("
			<!DOCTYPE html>
			<head>
				<title>USB-RLY08 controller</title>
				<script src=\"http://www.google.com/jsapi\"></script>
			</head>
			<body>
				<script>
					google.load(\"jquery\", \"1.7.1\");
				</script>
		");
	}
	
	function html_footer()
	{
		out("
			</body>
			</html>
		");
	}
	
	function home()
	{
		global $usbrly08;
		
		html_header();
		
		$init_result = $usbrly08->init();
		
		$r =
		"
			<style>
				#board
				{
					position: fixed;
					top: 50%;
					left: 50%;
					margin-top: -187px;
					margin-left: -236px;
					width: 473px;
					height: 375px;
					background-image: url('images/board.jpg');
				}
				
				#message
				{
					position: absolute;
					width: 473px;
					top: 380px;
					text-align: center;
					
					font-family: Helvetica, Sans Serif;
					font-size: 10pt;
					
					display: none;
				}
				
				.led
				{
					position: absolute;
					display: none;
				}
				
				.led.vertical
				{
					width: 42px;
					height: 52px;
				}
				
				.led.horizontal
				{
					width: 52px;
					height: 42px;
				}
				
				.led.vertical.red
				{
					background-image: url('images/led_on_vertical_red.png');
				}
				
				.led.horizontal.red
				{
					background-image: url('images/led_on_horizontal_red.png');
				}
				
				.led.horizontal.green
				{
					background-image: url('images/led_on_horizontal_green.png');
				}
				
				.led.horizontal.yellow
				{
					background-image: url('images/led_on_horizontal_yellow.png');
				}
				
				#led_power{ left: 208px; top: 20px; }
				
				#led_rx{ left: 139px; top: 305px; }
				#led_tx{ left: 139px; top: 323px; }
				
				#led_rele_0{ left: 165px; top: 52px; }
				#led_rele_1{ left: 165px; top: 119px; }
				#led_rele_2{ left: 165px; top: 186px; }
				#led_rele_3{ left: 165px; top: 253px; }
				#led_rele_4{ left: 268px; top: 253px; }
				#led_rele_5{ left: 268px; top: 186px; }
				#led_rele_6{ left: 268px; top: 119px; }
				#led_rele_7{ left: 268px; top: 52px; }
				
				.switch
				{
					position: absolute;
					width: 250px;
					height: 25px;
					
					font-family: Helvetica, Sans Serif;
					font-weight: bold;
					font-size: 13pt;
					
					padding: 15px;
					
					cursor: pointer;
					
					filter: alpha(opacity=0);
					-khtml-opacity: 0;
					-moz-opacity: 0;
					opacity: 0;
				}
				
				.switch:hover
				{
					filter: alpha(opacity=100);
					-khtml-opacity: 1;
					-moz-opacity: 1;
					opacity: 1;
					background-color: rgba(255, 255, 255, 0.3);
				}
				
				.switch.left
				{
				}
				
				.switch.right
				{
					text-align: right;
				}
				
				#switch_rele_0{ left: -80px; top: 43px; }
				#switch_rele_1{ left: -80px; top: 112px; }
				#switch_rele_2{ left: -80px; top: 181px; }
				#switch_rele_3{ left: -80px; top: 252px; }
				#switch_rele_4{ right: -80px; top: 252px; }
				#switch_rele_5{ right: -80px; top: 181px; }
				#switch_rele_6{ right: -80px; top: 112px; }
				#switch_rele_7{ right: -80px; top: 43px; }
				
			</style>
			
			<div id=\"board\">
				<div class=\"led horizontal red\" id=\"led_power\"></div>
				<div class=\"led horizontal green\" id=\"led_rx\"></div>
				<div class=\"led horizontal yellow\" id=\"led_tx\"></div>
				<div class=\"led vertical red\" id=\"led_rele_0\"></div>
				<div class=\"led vertical red\" id=\"led_rele_1\"></div>
				<div class=\"led vertical red\" id=\"led_rele_2\"></div>
				<div class=\"led vertical red\" id=\"led_rele_3\"></div>
				<div class=\"led vertical red\" id=\"led_rele_4\"></div>
				<div class=\"led vertical red\" id=\"led_rele_5\"></div>
				<div class=\"led vertical red\" id=\"led_rele_6\"></div>
				<div class=\"led vertical red\" id=\"led_rele_7\"></div>
				<div id=\"message\"></div>
				<a onclick=\"switch_rele(0);\" class=\"switch left\" id=\"switch_rele_0\">switch</a>
				<a onclick=\"switch_rele(1);\" class=\"switch left\" id=\"switch_rele_1\">switch</a>
				<a onclick=\"switch_rele(2);\" class=\"switch left\" id=\"switch_rele_2\">switch</a>
				<a onclick=\"switch_rele(3);\" class=\"switch left\" id=\"switch_rele_3\">switch</a>
				<a onclick=\"switch_rele(4);\" class=\"switch right\" id=\"switch_rele_4\">switch</a>
				<a onclick=\"switch_rele(5);\" class=\"switch right\" id=\"switch_rele_5\">switch</a>
				<a onclick=\"switch_rele(6);\" class=\"switch right\" id=\"switch_rele_6\">switch</a>
				<a onclick=\"switch_rele(7);\" class=\"switch right\" id=\"switch_rele_7\">switch</a>
			</div>
			
			<script>
			
				(function($){
					
					$.ajaxSetup({
						url: '".$_SERVER["PHP_SELF"]."',
						type: 'POST',
						async: true,
						cache: false,
						timeout: 10000,
						error: function(request, status, error){
							set_message('Web connection error ' + status + ': ' + error);
						}
					});
					
					$.fn.led_on = function(callback){
						this.fadeIn(40, callback);
					}
					
					$.fn.led_off = function(callback){
						this.fadeOut(40, callback);
					}
					
					$.fn.blink = function(callback)
					{
						this.led_on();
						this.led_off();
						this.led_on();
						this.led_off(callback);
					}
					
				})(jQuery);
				
				function simulate_tx()
				{
					$('#led_tx').blink();
				}
				
				function simulate_rx()
				{
					$('#led_rx').blink();
				}
				
				function simulate_tx_rx()
				{
					$('#led_tx').blink(function(){ $('#led_rx').blink(); });
				}
				
				function simulate_rx_tx()
				{
					$('#led_rx').blink(function(){ $('#led_tx').blink(); });
				}
				
				function set_message(string)
				{
					$('#message').html(string);
					$('#message').fadeIn();
				}
				
				var rele_status = new Array();
				
				function update_rele_leds_status()
				{
					simulate_tx();
					$.ajax({
						data: {
							command: 'ajax_get_rele_status'
						},
						success: function(r){
							simulate_rx();
							r = eval(r);
							for(i=0; i<=7; i++)
								if(r[i])
								{
									rele_status[i] = true;
									$('#led_rele_'+i).led_on();
								}
								else
								{
									rele_status[i] = false;
									$('#led_rele_'+i).led_off();
								}
						}						
					});
				}
				
				function switch_rele(rele_number)
				{
					simulate_tx();
					
					if(rele_status[rele_number])
						new_status = 0;
					else
						new_status = 1;
					
					$.ajax({
						data: {
							command: 'ajax_set_rele_status',
							rele_number: rele_number,
							status: new_status
						},
						success: function(r){
							if(new_status)
								$('#led_rele_'+rele_number).led_on();
							else
								$('#led_rele_'+rele_number).led_off();
							rele_status[rele_number] = new_status;
						}						
					});
				}
				
				$(function(){
		";
		
		if($init_result)
		{
			$r .= "$('#led_power').led_on();\n";
			$r .= "set_message('Connected to USB-RLY08 on ".SERIAL_DEVICE."');\n";
			$r .= "update_rele_leds_status();\n";
			if(AUTO_RELES_STATUS_UPDATE_INTERVAL_SECONDS)
				$r .= "setInterval('update_rele_leds_status()', ".(AUTO_RELES_STATUS_UPDATE_INTERVAL_SECONDS*1000).");\n";
		}
		else
		{
			$r .= "set_message('Can\'t connect to USB-RLY08 on ".SERIAL_DEVICE.": ".$usbrly08->last_error."');\n";
		}
				
		$r .=
		"
				});
			</script>
		";
		
		if($init_result)
			$usbrly08->disconnect();
		
		out($r);
		
		html_footer();
	}
	
	function ajax_get_rele_status()
	{
		global $usbrly08;
		
		if(!$usbrly08->init())
			return false;
			
		$r = json_encode($usbrly08->status_2_array($usbrly08->get_status()));
			
		$usbrly08->disconnect();
		
		out($r);
	}
	
	function ajax_set_rele_status()
	{
		global $usbrly08;
		
		$rele_number = $_REQUEST["rele_number"];
		$status = $_REQUEST["status"];
		
		if(!$usbrly08->init())
			return false;
			
		$usbrly08->set($rele_number, ($status ? true : false));
			
		$usbrly08->disconnect();
	}

?>