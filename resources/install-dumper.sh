#!/bin/bash

# Change dash to bash.
echo "dash dash/sh boolean false" | debconf-set-selections
DEBIAN_FRONTEND=noninteractive dpkg-reconfigure dash

# Update and install packages.
apt-get update
apt-get install libatomic1

# Refresh os release.
source /etc/os-release

# Download the mydumper and install.
release=$(curl -Ls -o /dev/null -w %{url_effective} https://github.com/mydumper/mydumper/releases/latest | cut -d'/' -f8)
wget https://github.com/mydumper/mydumper/releases/download/${release}/mydumper_${release:1}.${VERSION_CODENAME}_amd64.deb
dpkg -i mydumper_${release:1}.${VERSION_CODENAME}_amd64.deb
