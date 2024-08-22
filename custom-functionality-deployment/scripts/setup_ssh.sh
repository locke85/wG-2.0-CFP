#!/bin/bash

# Set up SSH for the deployment process
mkdir -p ~/.ssh
echo "$SSH_PRIVATE_KEY" > ~/.ssh/id_rsa
chmod 600 ~/.ssh/id_rsa
ssh-keyscan -H $HOSTNAME >> ~/.ssh/known_hosts
