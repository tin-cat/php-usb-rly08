This class allows you to control an usb-rly08, a device that connects to a USB port of a computer and is able to control up to 8 electrical relays.

Combining this device and this class, you'll be able to switch on/off lights, electrical appliances, motors and almost any other electrical device that can be triggered by a switch. Check out the maximum amperage that this device can handle.

The usb-rly08 uses the FTDI FT232R chip (http://www.ftdichip.com/Products/ICs/FT232R.htm), which is what makes this thing so easy to program. The device appears as a conventional serial port on your computer. In most linux systems with a decent kernel, it will even be automatically detected, so you will get up and running in a few minutes.

This can also serve you as a reference to program your own class for other similar devices.

Go to http://www.robot-electronics.co.uk/htm/usb_rly08tech.htm or http://www.superrobotica.com/S310240.htm for more info on the usb-rly08

I do not take any responsibility about the use you do of this software, please take care with the electric currents when playing around!

This PHP class needs the php-serial class in order to communicate with the device. It can be found here: http://code.google.com/p/php-serial/

Take a look at the example scripts (test.php ...) for configuration options. Also make sure there are the correct paths to the php-serial class and the php-usb-rly08 class itself.

This will work flawlessly on almost any linux system (be sure the usb-rly08 is correctly detected). It will work also on other systems like Windows and OSX, since the php-serial class is correctly prepared to do so.

The package comes not only with the class itself, but also:

test.php - The fast, clear an easy example that will let you learn how it works.

controller.php - An application that allows you to control visually each relay via a web browser. Make sure you use a decently upgraded browser since it uses CSS3 and glorious Ajax!

simulate_people.php - Connect one or more bulbs to your usb-rly08 and setup a cronjob to run this script each minute. It will switch on and off lights in a somewhat random way that will make thefts think there's someone in the house. Take a look inside the script for well-documented configuration parameters!
