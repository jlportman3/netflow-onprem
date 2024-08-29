![Sonar Netflow](doc/sonarNetflow-small.png)

# netflow-onprem
Sonar Netflow on-premise processor

## Introduction
This repository contains everything needed to provide your [Sonar](https://sonar.software) V2 instance with
data collected through Netflow.  

**_If you are a current Sonar customer, and you need assistance with any part of this process, please don't hesitate to 
reach out to support@sonar.software for help. We are more than happy to assist!_**

The instructions that follow have been tested running Ubuntu 24.04 LTS release but should support any operating system
capable of running Docker properly.  This host system should be dedicated to the Netflow On Premise processing and not
shared with other services.  

```
WARNING:
BECAUSE NETFLOW TRAFFIC REQUIRES ACCURRATE TIMESTAMPS THE HOST SYSTEM WILL BE 
CONFIGURED WITH THE UTC TIMEZONE AND WILL SETUP NTP SERVERS SO THAT RELIABILITY 
IS MAINTAINED.
```

## Sonar User Configuration
Sonar would recommend that you create a dedicated user for the processor and that the user only be given limited
permissions to accomplish this task.  

Directions for setting this up can be found in the [Sonar Knowledge Base](https://docs.sonar.expert/networking/netflow-on-premise#creating_your_netflow_on_premises_user_and_user_role).

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

`SONAR_URL=https://myisp.sonar.software` - The URL used to access your Sonar application.  This should not have a 
trailing '/' at the end, like the example provided.

`SONAR_TOKEN=<Sonar User personal access token>` - The access token created and saved in the Sonar setup directions above.

`SONAR_NETFLOW_NAME="Netflow On Premise 1"` - This will be the name displayed in the Sonar app for this host.

`SONAR_NETFLOW_IP=127.0.0.1` - This needs to be adjusted to the public IP address of this host.

`NFDUMP_MAXLIFE=7d` - This is the number of days the raw data files will be kept on disk.  BEWARE that Netflow data files
can consume a large amount of space and you should make sure you have storage available for this.  

`NFDUMP_MAXSIZE=100G` - This is the total size for all of the raw data files that will be kept on disk.  Make sure the
storage location has approximately 10% more than this value.  BEWARE - should storage be exhausted the application will not
function properly!

If you wish to retain the raw files for longer periods of time we suggest moving them to another storage location.  Make
sure that they are not moved before they have been processed by the system and posted into Sonar.  You are able to view
the last file processed in your Sonar app.

`DB_PASSWORD=pleaseChangeMe` - Set this to a unique password in your environment to prevent unauthorized access.

Sonar would suggest leaving ALL other values in the file at their default settings.  Changing any of the other values 
could have undesired consequences for the application and its ability to perform as expected.

Make sure you have saved these changes before proceeding to the next step.

### Run installation script

```bash
chmod +x ./install.sh
sudo ./install.sh
```

There are a significant amount of items that need to be setup for the installation process including the installation of 
Docker and building of the docker images.  Depending on your hardware this could take 15-30 minutes to complete, please
be patient.

## Netflow Setup
At this point everything should be setup to begin the collection and processing of Netflow data.  The last step in the
process is to setup your hardware (typically routers) to send the correct Netflow data over to the system for collection 
and reporting.

Directions for setting this up can be found in the [Sonar Knowledge Base](https://docs.sonar.expert/networking/netflow-on-premise#configuring_your_delivery_agent).


## Hardware Guidelines
Defining the hardware requirements for this device has proven to be a difficult task.  Each environment will have different
needs and amount of data that is being captured.  However, here are some general guidelines that will help you to scale the
on-premise processor correctly:

| Component  | Minimum    | Description                                                                                                                                                                                                                                         |
|:----------:|:----------:|:----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
|    CPU     | 4 cores    | This should be run on data center grade hardware.  Aggregation of the traffic to the customer level can require a signifant amount or processing power and must be performed in real time. |
|    RAM     | 16 GB      | As the size of the network / unique IP addresses being monitored grows, so should this value.  If netflow collection traffic approaches 1 Gpbs we would suggested 32 GB minimum. |
|    Disk    | 150 GB SSD | This needs to be adjusted if you change either of the environment variables previously mentioned that relate to retention.  More importantly data is being constantly written and read from the storage.  High IOPS will ensure a smooth operation. |
|  Network   | 1 Gpbs     | A good rule of thumb is that Netflow traffic will be about 1% of your total data traffic.  However, there can be spikes in this so make sure you have the network capacity to allow for this. |

Again, these are just basic guidelines for scaling the hardware to meet the needs of your environment.  Sonar would suggest 
you monitor key metrics on the device to ensure that performance levels are not exceeded.  There are two options if you
are reaching capacity - increase hardware on the existing server or spin up a second server and have netflow traffic
split between the devices.  The second option will require adjusting the netflow settings of your network hardware so
each device is capturing unique traffic.


## Troubleshooting

**_If you are a current Sonar customer, and you need assistance with any part of this process, please don't hesitate to
reach out to support@sonar.software for help. We are more than happy to assist!_**
