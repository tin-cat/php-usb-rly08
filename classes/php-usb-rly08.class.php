<?

	// Controlling an USB RLY08 device (http://www.robot-electronics.co.uk/htm/usb_rly08tech.htm or http://www.superrobotica.com/S310240.htm)
	// Requires phpSerial class (http://code.google.com/p/php-serial/)
	// v1.01

	class usbrly08
	{
		var $baud_rate = 19200;
		var $parity = "none";
		var $character_length = 8;
		var $stop_bits = 1;
		var $flow_control = "none";
		
		var $device;
		var $phpserial;
		
		var $reles = array(
			0 => 1,
			1 => 2,
			2 => 4,
			3 => 8,
			4 => 16,
			5 => 32,
			6 => 64,
			7 => 128
		);
		
		var $command_on = array(
			0 => 0x65,
			1 => 0x66,
			2 => 0x67,
			3 => 0x68,
			4 => 0x69,
			5 => 0x6A,
			6 => 0x6B,
			7 => 0x6C
		);
		
		var $command_off = array(
			0 => 0x6F,
			1 => 0x70,
			2 => 0x71,
			3 => 0x72,
			4 => 0x73,
			5 => 0x74,
			6 => 0x75,
			7 => 0x76
		);
		
		var $command_all_on = 0x64;
		var $command_all_off = 0x6E;
		
		var $command_set_all_to = 0x5C;
		
		var $last_error;
		
		function usbrly08($device)
		{
			$this->device = $device;
		}
		
		function init()
		{
			if(!file_exists($this->device))
			{
				$this->error = "Device ".$this->device." doesn't exists";
				return false;
			}
			
			if(!is_writable($this->device))
			{
				$this->error = "Can't open ".$this->device;
				return false;
			}
			
			$this->phpserial = new phpSerial;
			$this->phpserial->deviceSet($this->device);
			
			$this->phpserial->confBaudRate($this->baud_rate);
			$this->phpserial->confParity($this->parity);
			$this->phpserial->confCharacterLength($this->character_length);
			$this->phpserial->confStopBits($this->stop_bits);
			$this->phpserial->confFlowControl($this->flow_control);
			
			$this->phpserial->deviceOpen();
			
			return true;
		}
		
		function send($command) // A command is a single byte, or an array of single bytes
		{	
			if(is_array($command))
			{
				$final_command = "";
				foreach($command as $byte)
					$final_command .= chr($byte);
				$this->phpserial->sendMessage($final_command);
			}
			else
				$this->phpserial->sendMessage(chr($command));
		}
		
		function device_flush()
		{
			$this->phpserial->serialflush();
		}
		
		function question($command)
		{
			$this->send($command);
			return $this->phpserial->readPort();
		}
		
		function get_version()
		{
			return $this->question(0x5A);
		}
		
		function get_version_number()
		{
			return ord($this->get_version());
		}
		
		function set_all($status)
		{
			$this->send(($status ? $this->command_all_on : $this->command_all_off));
		}
		
		function set_all_to($status)
		{
			$this->send(array($this->command_set_all_to, $status));
		}
		
		function set($rele_number, $status)
		{
			$this->send(($status ? $this->command_on[$rele_number] : $this->command_off[$rele_number]));
		}
		
		function set_array($status_array, $is_clear_current_status = false)
		{
			if(!is_array($status_array))
			{
				trigger_error("must be an array on the syntax: 1 => [true|false], 2 => [true|false] ...", E_USER_ERROR);
				die;
			}
			
			if($is_clear_current_status)
				$final_status_array = array(false, false, false, false, false, false, false, false);
			else
				$final_status_array = $this->status_2_array($this->get_status());
			
			while(list($rele_number, $stat) = each($status_array))
				$final_status_array[$rele_number] = $stat;
			
			$this->set_all_to($this->array_2_status($final_status_array));
		}
		
		function get_status()
		{
			return $this->question(0x5B);
		}
		
		function status_2_array($status)
		{
			$status_array = null;
			
			for($i=0; $i<=7; $i++)
				if(ord($status) & $this->reles[$i])
					$status_array[$i] = true;
				else
					$status_array[$i] = false;
			
			return $status_array;
		}
		
		function array_2_status($status_array)
		{
			$status = 0;
			for($i=0; $i<=7; $i++)
				if($status_array[$i])
					$status += $this->reles[$i];
			
			return $status;
		}
		
		function test($delay_millisecs = 100, $rele_number = false, $times = 1)
		{
			echo "Setting all reles to off<br>";
			$this->set_all(false);
			$this->device_flush();
			flush(); ob_flush();
			usleep($delay_millisecs*1000);
			
			if($rele_number === false)
			{
				for($loop_count = 1; $loop_count <= $times; $loop_count ++)
				{
					echo "Testing all reles in sequence #".$loop_count."<br>";
					for($i=0; $i<=7; $i++)
					{
						echo "Rele ".$i."<br>";
						flush(); ob_flush();
						usleep($delay_millisecs*1000);
						$this->set($i, true);
						usleep($delay_millisecs*1000);
						$this->set($i, false);
						$this->device_flush();
					}
				}
			}
			else
			{
				for($loop_count = 1; $loop_count <= $times; $loop_count ++)
				{
					echo "Testing rele ".$rele_number." #".$loop_count."<br>";
					$this->set($rele_number, true);
					usleep($delay_millisecs*1000);
					$this->set($rele_number, false);
					usleep($delay_millisecs*1000);
				}
			}
		}
		
		function get_visual_status($status = false, $is_text_mode = false)
		{
			if(!$status)
				$status = $this->get_status();
			
			if(!is_array($status))
				$status = $this->status_2_array($status);

			if($is_text_mode)
			{
				$r = "";
				for($i=0; $i<=7; $i++)
					$r .=
						$i.
						":".
						($status[$i] ? "On" : "Off").
						" ";
			}
			else
			{
				$r = "<div>";
				for($i=0; $i<=7; $i++)
					$r .=
						"<div ".
							"style=\"".
								"float: left; ".
								"width: 30px; ".
								"text-align: center; ".
								"color: white; ".
								"background-color: ".($status[$i] ? "green" : "grey")."; ".
							"\" ".
						">".
							$i.
						"</div>";
				$r .= "</div>";
			}
			
			return $r;
		}
		
		function disconnect()
		{
			$this->phpserial->deviceClose();
		}
	}

?>