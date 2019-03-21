#!/bin/bash
#
# This adds Supervisor to monitor the Queue workers. 
#
sleep 2
printf "\n###########################################################\n"
printf "Install Supervisor Script \n"
#
#
printf "Insalling Supervisor...\n"
sudo apt-get install supervisor
#
printf "Waiting 15 secs for install...\n"
sleep 15
#
#
echo "The current working directory: $PWD"
_mydir="$PWD"
echo $_mydir
echo $_mydir/etc/supervisor/conf.d/laravel-scan-worker.conf
# Create new file with the working directory. 

printf "Generating Configuration File...\n"
echo "

[program:laravel-scan-worker]
process_name=%(program_name)s_%(process_num)02d

command=php "$_mydir/"artisan queue:work --daemon --tries=3
autostart=true
autorestart=true

numprocs=8
redirect_stderr=true
stdout_logfile="$_mydir"/storage/logs/laravel-scan-worker.log" > $_mydir/etc/supervisor/conf.d/laravel-scan-worker.log

sleep 2
# Creater Symlink to the Supervisor directory in /etc.
printf "Creating Symlink to config file...\n"
#ln -s $_mydir/etc/supervisor/conf.d/laravel-scan-worker.conf /etc/supervisor/conf.d/laravel-scan-worker.conf

sleep 2
printf "Readin Supervisor Worker Config...\n"
sudo supervisorctl reread


sleep 2
printf "Update Supervisor Worker Config...\n"
sudo supervisorctl update

sleep 2
printf "Starting Supervisor Task...\n"
sudo service supervisor start

sleep 2
printf "Ending Task\n"
printf "\n###########################################################\n"
