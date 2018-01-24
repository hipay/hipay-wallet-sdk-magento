#!/bin/bash

#=============================================================================
#  Use this script build hipays images and run our containers
#
#  WARNING : Put your credentials in hipay.env
#==============================================================================


if [ "$1" = '' ] || [ "$1" = '--help' ]; then
    echo " ==================================================== "
    echo "                     HIPAY'S HELPER                 "
    echo " ==================================================== "
    echo "      - init        : Build images and run containers (Delete existing volumes)"
    echo "      - restart     : Run all containers if they already exist"
    echo "      - logs        : Show all containers logs continually"
    echo "      - test        : Execute the tests battery"
    echo "      - test-engine : Launch advanced shell script for tests battery"
    echo "      - notif       : Simulate a notification to Magento server"
    echo ""
elif [ "$1" = 'init' ]; then
    docker-compose stop
    docker-compose rm -fv
    sudo rm -Rf data/ log/ web/
    docker-compose -f docker-compose.yml -f docker-compose.dev.yml build --no-cache
    docker-compose -f docker-compose.yml -f docker-compose.dev.yml up
elif [ "$1" = 'restart' ]; then
    docker-compose -f docker-compose.yml -f docker-compose.dev.yml up
elif [ "$1" = 'logs' ]; then
    docker-compose logs -f
else
    echo "Incorrect argument ! Please check the HiPay's Helper via the following command : 'sh magento.sh' or 'sh magento.sh --help'"
fi
