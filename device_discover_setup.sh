# Installing SNMPTT for Trap Listenting 
# Used to kick off device discovery dynamically. 

apt-get install snmp-mibs-downloader snmpd


# Add to /etc/snmp/snmptt.conf
# NEEDS WORK!!!
EVENT CiscoConfig .1.3.6.1.4.1.9.9.43.2.0.1 "Status Events" Normal
FORMAT Cisco config change from
EXEC php /PATH_TO_APP/artisan netman:discoverDevice --ip=$ar --hostname=$r --datetime=$x_$X --eventname=$N --severity=$s --message=$Fz --x=$x --X=$X --vars=$# --1=$*
SDESC
Cisco configuration change captured from device
EDESC
#