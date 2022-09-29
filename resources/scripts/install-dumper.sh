#!/bin/bash

# Change dash to bash.
echo "+ Exec: Change dash to bash."
echo "dash dash/sh boolean false" | debconf-set-selections
DEBIAN_FRONTEND=noninteractive dpkg-reconfigure dash

# Update and install packages.
echo "+ Exec: apt-get update"
apt-get update
echo "+ Exec: apt-get install libatomic1"
apt-get install libatomic1

# Refresh os release.
echo "+ Exec: source /etc/os-release"
source /etc/os-release

# Download the mydumper and install.
echo "+ Exec: get release"
release=$(curl -Ls -o /dev/null -w %{url_effective} https://github.com/mydumper/mydumper/releases/latest | cut -d'/' -f8)
echo "+ Exec: Download mydumper"
wget https://github.com/mydumper/mydumper/releases/download/${release}/mydumper_${release:1}.${VERSION_CODENAME}_amd64.deb
echo "+ Exec: dpkg -i"
dpkg -i mydumper_${release:1}.${VERSION_CODENAME}_amd64.deb
