# Overview

This is a dev only module to get phpunit running parallel in silverstripe projects using docker

# Usage

vendor/bin/legion app/tests

# Details

Host
-> Docker container A  -- Primary controller for multiple container B secondaries
-> Docker container Bs -- Large number of these are spawned to run tests

Even though container Bs are created from inside of container A, they're actually siblings because of the shared /var/run/docker.sock between host, container A and container Bs.  This is the simplest way to enable 'docker in docker'

The /tmp folder is also shared between host, container A and container Bs.  This is so that when you run ?flush on container A, it's immediately usable with container Bs which spin in and out of existance as needed.

Host needs to share /tmp because container A and container Bs are siblings volume mounts won't work easily out of the box.  Also, possibly it would be better if it wasn't the /tmp folder shared, instead should maybe use /silverstripe-cache/ ?
