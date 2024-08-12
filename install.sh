#!/usr/bin/env bash
set -euo pipefail


write_timesync_conf() {
  cat <<EOL > "/etc/systemd/timesyncd.conf"
[Time]
NTP=time.cloudflare.com time.google.com
EOL
}

if [ $UID != 0 ]; then
    echo This must be run as root.
    exit 1
fi

# Check if docker is already installed in host and if not install
if ! [ -x "$(command -v docker)" ]; then
    echo "### Docker is not installed, installing it now..."
    apt-get update
    apt-get install -y \
        apt-transport-https \
        ca-certificates \
        curl \
        gnupg-agent \
        software-properties-common
    install -m 0755 -d /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
    chmod a+r /etc/apt/keyrings/docker.asc
    echo \
      "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu \
      $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
      tee /etc/apt/sources.list.d/docker.list > /dev/null
    apt-get update
    apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
fi

# Accurate time is critical for Netflow, so setup NTP as well as setting timezone to UTC if able and warn if not
. /etc/os-release
case $ID in
  ubuntu) echo "Running Ubuntu, attempting to enable NTP..."
    if [ -x "$(command -v timedatectl)" ]; then
      timedatectl set-timezone UTC
      write_timesync_conf
      systemctl restart systemd-timesyncd
      echo "NTP enabled!"
    else
      read -p "### WARNING: Unable to configure NTP on Ubuntu server, please make sure accurate time is setup using NTP and UTC timezone [continue]" -i n -n 1 -r
    fi
    ;;
  *) read -p "### WARNING: Not running Ubuntu, please make sure accurate time is setup using NTP and UTC timezone [continue]" -i n -n 1 -r
    ;;
esac

if [ -f src/.env ]; then
    read -p "### WARNING: Your environment appears to already be set up. Set it up again? [y/N] " -i n -n 1 -r
    echo
    [[ ! $REPLY =~ ^[Yy]$ ]] && exit 1;
fi

if lsof -Pi -sTCP:LISTEN | grep -P ':(80|443)[^0-9]' >/dev/null ; then
    read -p "Port 80 and/or 443 is currently in use. Do you wish to continue anyway? [y/N] " -i n -n 1 -r
    echo
    [[ ! $REPLY =~ ^[Yy]$ ]] && exit 1;
fi

# All checks passed we will now change ownership of the app files to the user / group used by docker images
# Then we will copy the main .env file over and create a unique app key for the installation.
chown -R 1000:1000 ./src
chown -R 1000:1000 ./netflowData
cp ./.env ./src/.env
printf -v UNIQUE_APP_KEY "%q" "base64:$(head -c32 /dev/urandom | base64)"
sed -i "s;APP_KEY=base64:UNIQUE_KEY_NEEDED;APP_KEY=${UNIQUE_APP_KEY};g" ./src/.env

# Bring up the docker images and after waiting for them to run perform the intial app initialization
docker compose up -d
until [ "`docker inspect -f {{.State.Running}} php`"=="true" ]; do
    sleep 0.1;
done;
docker exec php sh -c "/usr/local/bin/app_init.sh"

# SUCCESS - Let user know and provide the app key for backing up
echo "##########"
echo "### INSTALLATION COMPLETE"
echo "### The app key is: '$UNIQUE_APP_KEY'"
echo "### Back this up somewhere in case you need it."
echo "##########"
