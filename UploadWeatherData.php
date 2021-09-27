# SPDX-FileCopyrightText: 2021 ladyada for Adafruit Industries
# SPDX-License-Identifier: MIT

"""
Example showing how the BME280 library can be used to set the various
parameters supported by the sensor.
Refer to the BME280 datasheet to understand what these parameters do
"""
import math
import time
from datetime import datetime
from urllib.parse import urlencode
from urllib.request import Request, urlopen
import requests

import board
import busio
import adafruit_bme280

# Create library object using our Bus I2C port
i2c = busio.I2C(board.SCL, board.SDA)
bme280 = adafruit_bme280.Adafruit_BME280_I2C(i2c)

# OR create library object using our Bus SPI port
# spi = busio.SPI(board.SCK, board.MOSI, board.MISO)
# bme_cs = digitalio.DigitalInOut(board.D10)
# bme280 = adafruit_bme280.Adafruit_BME280_SPI(spi, bme_cs)

# Change this to match the location's pressure (hPa) at sea level
bme280.sea_level_pressure = 1024.7
bme280.mode = adafruit_bme280.MODE_NORMAL
bme280.standby_period = adafruit_bme280.STANDBY_TC_500
bme280.iir_filter = adafruit_bme280.IIR_FILTER_X16
bme280.overscan_pressure = adafruit_bme280.OVERSCAN_X16
bme280.overscan_humidity = adafruit_bme280.OVERSCAN_X1
bme280.overscan_temperature = adafruit_bme280.OVERSCAN_X2
# The sensor will need a moment to gather initial readings

time.sleep(2)

#while True:
#    print("\nTemperature: %0.1f C" % bme280.temperature)
#    print("Humidity: %0.1f %%" % bme280.relative_humidity)
#    print("Pressure: %0.1f hPa" % bme280.pressure)
#    print("Altitude: %0.2f meters" % bme280.altitude)
#    b = 17.62
#    c = 243.12
#    gamma = (b * bme280.temperature /(c + bme280.temperature)) + math.log(bme280.humidity / 100.0)
#    dewpoint = (c * gamma) / (b - gamma)
#    print("Dew Point: %0.1f C" % dewpoint)
#    time.sleep(10)

# REST API endpoint, given to you when you create an API streaming dataset
# Will be of the format: https://api.powerbi.com/beta/<tenant id>/datasets/< dataset id>/rows?key=<key id>
REST_API_URL = "<API Address to Power BI Streaming Dataset>"

# Gather temperature and sensor data and push to Power BI REST API
while True:
	try:
		# read and print out humidity and temperature from sensor
		temp = bme280.temperature
		humidity = bme280.relative_humidity
		pressure = bme280.pressure
		altitude = bme280.altitude
		b = 17.62
		c = 243.12
		gamma = (b * bme280.temperature /(c + bme280.temperature)) + math.log(bme280.humidity / 100.0)
		dewpoint = (c * gamma) / (b - gamma)
		
		# ensure that timestamp string is formatted properly
		now = datetime.strftime(datetime.now(), "%Y-%m-%dT%H:%M:%S%Z")
	
		# data that we're sending to Power BI REST API
		pload = '[{{ "timestamp": "{0}", "temperature": "{1:0.1f}", "humidity": "{2:0.1f}", "pressure": "{3:0.1f}", "altitude": "{4:0.2f}", "dewpoint": "{5:0.1f}" }}]'.format(now, temp, humidity, pressure, altitude, dewpoint)
		#print(pload)
		# make HTTP POST request to Power BI REST API
		#req = Request(REST_API_URL, urlencode(data).encode())
		#response = urlopen(req)
		#print(response)
        
		response = requests.post(REST_API_URL,data = pload)
        
		print("POST request to Power BI with data:{0}".format(pload))
		#print("Response: HTTP {0} {1}\n".format(response.getcode(), response.read().decode()))	

		time.sleep(600) #10 minutes
#	except urllib.HTTPError as e:
#		print("HTTP Error: {0} - {1}".format(e.code, e.reason))
#	except urllib.URLError as e:
#		print("URL Error: {0}".format(e.reason))
	except Exception as e:
		print("General Exception: {0}".format(e))

