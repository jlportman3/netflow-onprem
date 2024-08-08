# netflow-onprem
Netflow on-premise processor

## Introduction
This repository contains everything needed to provide your [Sonar](https://sonar.software) V2 instance with
data collected through Netflow.  

**_If you are a current Sonar customer, and you need assistance with any part of this process, please don't hesitate to 
reach out to support@sonar.software for help. We are more than happy to help you get your portal setup!_**

The instructions that follow have been tested running Ubuntu 24.04 LTS release but should support any operating system
capable of running Docker properly.  

## Sonar User Configuration
Sonar would recommend that you create a dedicated user for the processor and that the user only be given limited
permissions to accomplish this task.  

=== TODO: Get Mitchell to write documentation for roles / user setup ===
1. Needs to be able to read accounts, ip addresses, subnets,...
2. Needs CREATE_DATA_USAGE

## Getting Started
The following steps will walk you through getting up and running in quick order.  Make sure that you have root access to
the machine that you are running each of the steps on as it will be needed for installation.  You will need to be 
accessing the machine via console or SSH for all of the following steps:

### Updating and pulling repository
The following commands will get the latest updates for your distribution and install a couple of needed packages.
Following that it will pull all of the necessary files from the Sonar repository.  You will then need to prepare a few
files for the steps that follow.

```bash
sudo apt-get -y update && sudo apt-get -y upgrade && sudo apt-get -y install git unzip
git clone https://github.com/SonarSoftwareInc/netflow-onprem.git
cd netflow-onprem
cp .env.example .env
```

### Environment setup
Edit the `.env` file which was created in the previous steps and replace the values as specified below:

`SONAR_URL=https://myisp.sonar.software`

`SONAR_TOKEN=<Sonar User personal access token>`

### Run installation script

```bash
chmod +x ./install.sh
TODO: SOME REALLY AWESOME SETUP COMMAND HERE
```

## Netflow Setup
Point netflow on routers here, watch the magic happen. (TODO)


## Hardware Requirements
Get the absolutely biggest box (100's of cores, TB's RAM, NVMe SSDs...) with the fastest NIC (6 port 100G LAG or better) that you can afford!  This will ensure you are happy!
TODO: Fine tune specs above

## Troubleshooting
This will not break nothing here - famous last words I know!
